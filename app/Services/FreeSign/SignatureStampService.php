<?php

namespace App\Services\FreeSign;

use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Models\FreeSignDocumentField;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Инкрементальный штамп меток подписанта на PDF с ВЕРСИОНИРОВАНИЕМ.
 *
 * Схема версий (новая запись document_files на каждый штамп):
 *   версия 0 — исходный PDF (загруженный пользователем, не трогаем)
 *   версия 1 = версия 0 + метки подписанта 1
 *   версия 2 = версия 1 + метки подписанта 2   ...
 *
 * Текущая версия = последняя запись document_files по (j_id, doc_id, pdf).
 * Исходник всегда доступен как самая ранняя запись.
 *
 * Координаты меток — нормализованные (0..1). TCPDF считает Y сверху вниз,
 * как и редактор, поэтому переворот оси НЕ нужен: xMm = x * pageWidth.
 *
 * Данные подписи ($ecpData) передаются ПАРАМЕТРОМ из signDocuments(),
 * т.к. saveSignatureData() вызывается позже и в БД их ещё нет.
 */
class SignatureStampService
{
    private const DISK = 'ftp_13';

    /** Метка версии в имени файла, чтобы отличать штампованные от исходника. */
    private const VERSION_TAG = '_signed_v';

    public function __construct(private StampRenderer $renderer)
    {
    }

    /**
     * Проштамповать метки одного подписанта поверх ТЕКУЩЕЙ версии PDF
     * и сохранить результат НОВОЙ версией.
     *
     * @param  int   $journalId  document_journals.id
     * @param  int   $documentId documents.id
     * @param  int   $userId     подписант (users.id / to_user шага)
     * @param  array $ecpData    данные подписи из prepareEcpData()
     * @return DocumentFiles|null новая версия файла (null если штамповать нечего)
     */
    public function stampForSigner(int $journalId, int $documentId, int $userId, array $ecpData): ?DocumentFiles
    {
        Log::info('[free-sign] STAMP start', compact('journalId', 'documentId', 'userId'));

        // 1. Метки этого подписанта.
        $fields = FreeSignDocumentField::where('document_id', $documentId)
            ->where('signer_id', $userId)
            ->orderBy('page')
            ->get();

        if ($fields->isEmpty()) {
            // ЧАСТАЯ ПРИЧИНА «нет штампа»: подписанту не расставили метки,
            // либо signer_id в метках не совпал с users.id подписанта.
            $all = FreeSignDocumentField::where('document_id', $documentId)
                ->get(['id', 'type', 'signer_id']);

            Log::warning('[free-sign] STAMP: у подписанта НЕТ меток — штамп пропущен', [
                'looking_for_signer_id' => $userId,
                'document_id'           => $documentId,
                'all_fields_in_doc'     => $all->toArray(),
                'hint'                  => 'signer_id в метках должен = users.id подписанта',
            ]);
            return null;
        }

        Log::info('[free-sign] STAMP: меток найдено', [
            'count' => $fields->count(),
            'types' => $fields->pluck('type')->toArray(),
        ]);

        // 2. Текущая версия PDF (последняя штампованная либо исходник).
        $currentFile = $this->currentVersion($journalId, $documentId);
        if (!$currentFile) {
            Log::warning('free-sign stamp: PDF не найден', compact('journalId', 'documentId'));
            return null;
        }

        $srcPath = '/' . $currentFile->file_path . $currentFile->file_hash;
        if (!Storage::disk(self::DISK)->exists($srcPath)) {
            Log::warning('free-sign stamp: файл отсутствует на диске', ['path' => $srcPath]);
            return null;
        }

        // 3. Готовим временный файл — FPDI читает с пути.
        $tmpSrc = tempnam(sys_get_temp_dir(), 'fs_src_') . '.pdf';
        file_put_contents($tmpSrc, Storage::disk(self::DISK)->get($srcPath));

        try {
            $out = $this->renderStamped($tmpSrc, $fields, $userId, $documentId, $ecpData);
        } finally {
            @unlink($tmpSrc);
        }

        // 4. Сохраняем НОВУЮ версию отдельной записью document_files.
        $newFile = $this->storeNewVersion($journalId, $documentId, $userId, $currentFile, $out);

        Log::info('[free-sign] STAMP OK — создана новая версия', [
            'file_id'   => $newFile->id,
            'file_name' => $newFile->file_name,
            'size'      => $newFile->file_size,
            'from'      => $currentFile->file_name,
        ]);

        return $newFile;
    }

    /**
     * Отрисовка: импорт страниц исходника + метки поверх.
     */
    private function renderStamped(string $srcPath, $fields, int $userId, int $documentId, array $ecpData): string
    {
        $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pageCount = $pdf->setSourceFile($srcPath);
        $byPage    = $fields->groupBy('page');

        // Данные для меток (готовим один раз).
        $ctxData = $this->buildContextData($userId, $documentId, $ecpData);

        for ($p = 1; $p <= $pageCount; $p++) {
            $tpl  = $pdf->importPage($p);
            $size = $pdf->getTemplateSize($tpl);

            $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            foreach ($byPage->get($p, []) as $field) {
                // Нормализованные доли → мм. Ось Y совпадает с редактором.
                $rect = [
                    'x' => $field->x * $size['width'],
                    'y' => $field->y * $size['height'],
                    'w' => $field->width  * $size['width'],
                    'h' => $field->height * $size['height'],
                ];

                $this->renderer->render($pdf, $field->type, $rect, $field->params ?? [], $ctxData);
            }
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Данные для подстановки в метки.
     * Время/сертификат берём из $ecpData (в БД их ещё нет на момент штампа).
     */
    private function buildContextData(int $userId, int $documentId, array $ecpData): array
    {
        $work = \App\Models\Admin\UserWorkHistory::find($userId);
        $name = $work?->personal?->fullName()
            ?? \App\Models\Admin\UserPersonalInfo::where('user_id', $userId)->first()?->fullName()
            ?? '';

        $signingTime = !empty($ecpData['signingTime'])
            ? Carbon::parse($ecpData['signingTime'])
            : Carbon::now();

        // QR ведёт на страницу проверки документа: route /d/{doc_code}
        // (QrCodeDocController::viewWorkDocument ищет Documents по doc_code).
        // ВНИМАНИЕ: раньше тут был url('/qr-code-doc/'.$key_code) — такого
        // роута НЕ СУЩЕСТВУЕТ, QR вёл в 404.
        $docCode = \App\Models\Document\Documents::where('id', $documentId)->value('doc_code');

        return [
            'full_name'    => $name,
            'position'     => $work?->position?->name_ru ?? '',
            'sign_date'    => $signingTime->format('d.m.Y'),
            'sign_time'    => $signingTime->format('H:i'),
            'cert_number'  => $ecpData['serialNumber'] ?? '',
            'cert_subject' => $ecpData['subjectName'] ?? '',
            // Ссылка проверки документа (та же, что у штатных документов).
            'qr_payload'   => $docCode ? url('/d/' . $docCode) : url('/d/' . $documentId),
        ];
    }

    /**
     * Текущая (последняя) версия PDF документа.
     * Если штампов ещё не было — вернёт исходник.
     */
    public function currentVersion(int $journalId, int $documentId): ?DocumentFiles
    {
        // Последняя ПРОШТАМПОВАННАЯ версия (по метке _signed_v в имени).
        // Не берём просто последний PDF: у документа могут быть другие
        // PDF-вложения (приложения), они не должны подменить основной файл.
        $stamped = DocumentFiles::where('j_id', $journalId)
            ->where('doc_id', $documentId)
            ->where('file_ext', 'pdf')
            ->where('file_name', 'ilike', '%' . self::VERSION_TAG . '%')
            ->orderByDesc('id')
            ->first();

        return $stamped ?: $this->originalVersion($journalId, $documentId);
    }

    /** Исходный PDF (самая ранняя запись, без меток). */
    public function originalVersion(int $journalId, int $documentId): ?DocumentFiles
    {
        return DocumentFiles::where('j_id', $journalId)
            ->where('doc_id', $documentId)
            ->where('file_ext', 'pdf')
            ->where('file_name', 'not ilike', '%' . self::VERSION_TAG . '%')
            ->orderBy('id')
            ->first();
    }

    /** Все версии по возрастанию (0 — исходник). */
    public function allVersions(int $journalId, int $documentId)
    {
        return DocumentFiles::where('j_id', $journalId)
            ->where('doc_id', $documentId)
            ->where('file_ext', 'pdf')
            ->orderBy('id')
            ->get();
    }

    /**
     * Сохранить новую версию: файл на ftp_13 + запись document_files.
     * Путь и хэш формируются по тому же паттерну, что в DocumentController::saveFiles().
     */
    private function storeNewVersion(int $journalId, int $documentId, int $userId, DocumentFiles $prev, string $binary): DocumentFiles
    {
        $today = Carbon::today();
        $path  = 'doc/' . $today->year . '/' . $today->month . '/' . $today->day . '/';

        // Номер версии = сколько pdf-записей уже есть.
        $version = DocumentFiles::where('j_id', $journalId)
            ->where('doc_id', $documentId)
            ->where('file_ext', 'pdf')
            ->count();

        $hash = $this->uniqueHash(16) . '.pdf';

        Storage::disk(self::DISK)->put($path . $hash, $binary);

        // Имя: исходное + метка версии (видно в списке файлов).
        $baseName = preg_replace('/' . preg_quote(self::VERSION_TAG, '/') . '\d+/', '', pathinfo($prev->file_name, PATHINFO_FILENAME));

        $file = new DocumentFiles();
        $file->j_id      = $journalId;
        $file->doc_id    = $documentId;
        $file->from_user = $userId;
        $file->file_name = $baseName . self::VERSION_TAG . $version . '.pdf';
        $file->file_hash = $hash;
        $file->file_path = $path;
        $file->file_ext  = 'pdf';
        $file->file_size = strlen($binary);
        $file->save();

        return $file;
    }

    /** Хэш имени файла (как uniqueHash в базовом контроллере). */
    private function uniqueHash(int $length): string
    {
        return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
    }
}
