<?php

namespace App\Services\Integration1C;

use App\Models\Document\DocumentFiles;
use App\Models\Document\Documents;
use App\Models\Integration1C\OnecOrderPayload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Сборка тела ответа /get_list по контракту 1С.
 *
 * Формат (согласован в Postman-коллекции EdoLine):
 *   {
 *     "order_ID": "...", "created_date": "...", "sign_date": "...",
 *     "order_data": { ...поля, зависящие от типа приказа... },
 *     "attached_files": [ ... ]
 *   }
 *
 * ВЛОЖЕНИЯ. По умолчанию отдаём метаданные и ссылку на скачивание.
 * Base64-тело — только при with_files=1 и до 5 МБ на файл: 50 приказов с PDF
 * в base64 дают сотни мегабайт в одном ответе, nginx оборвёт по таймауту.
 * Файлы документов лежат на диске ftp_13, путь — file_path . file_hash.
 */
class OnecOrderExporter
{
    /** Порог инлайна файла в base64 (байт). */
    private const INLINE_FILE_LIMIT = 5 * 1024 * 1024;

    /** Диск пользовательских вложений — куда их кладут DocumentController и ExecutionController. */
    private const FILES_DISK = 'ftp_13';

    /**
     * Каталог PDF самих приказов.
     *
     * GenerateHrOrderPdfJob кладёт готовый приказ в storage/app/public/hr_orders/
     * под именем doc_code.pdf — то есть НЕ в document_files и не на ftp_13.
     * Поэтому сам документ надо забирать отдельно от пользовательских вложений.
     */
    private const HR_ORDER_PDF_DIR = 'hr_orders';

    public function __construct(
        private OnecGuidResolver $guid,
        private OnecLogger $logger
    ) {}

    /**
     * Резолвер, которым пользуется экспортёр.
     *
     * Контроллеру нужен именно ЭТОТ экземпляр: OnecGuidResolver не singleton,
     * поэтому инжектированный в контроллер объект — другой, и накопленные
     * здесь промахи резолва в нём не видны.
     */
    public function guidResolver(): OnecGuidResolver
    {
        return $this->guid;
    }

    public function export(OnecOrderPayload $payload, bool $withFiles = false): array
    {
        $data = $payload->data ?? [];

        $order = [
            'order_ID'       => $payload->order_number ?: ('EDO-' . $payload->j_id),
            'created_date'   => $payload->created_date?->format('Y-m-d'),
            'sign_date'      => $payload->sign_date?->format('Y-m-d'),
            'order_data'     => $this->orderData($payload->order_type, $data, $payload),
            'attached_files' => $this->attachedFiles($payload, $withFiles),
        ];

        // Диагностика для 1С-стороны: чего не хватает именно в этом приказе.
        // Отдельным блоком, чтобы не ломать основной контракт.
        if (!empty($payload->missing_fields)) {
            $order['_incomplete_fields'] = $payload->missing_fields;
        }

        return $order;
    }

    private function orderData(string $type, array $d, OnecOrderPayload $p): array
    {
        $company = ['company_ID' => $this->guid->company($p->org_unit_id)];

        return match ($type) {
            'hirings'                         => $company + $this->hirings($d),
            'terminations'                    => $company + $this->terminations($d),
            'transfers'                       => $company + $this->transfers($d),
            'business_trips'                  => $company + $this->businessTrips($d),
            'vacations'                       => $company + $this->vacations($d),
            'absences'                        => $company + $this->absences($d),
            'additional_duties'               => $company + $this->additionalDuties($d),
            'disciplinary_actions'            => $company + $this->disciplinaryActions($d),
            'removal_of_disciplinary_actions' => $company + $this->removalOfDisciplinaryActions($d),
            default                           => $company,
        };
    }

    /* ─────────────────────────────── Приказы ─────────────────────────────── */

    /**
     * Приём на работу. Здесь person_ID, а не employee_ID: сотрудника ещё нет,
     * запись user_work_histories появляется уже по факту приказа.
     */
    private function hirings(array $d): array
    {
        return [
            'person_ID'          => $this->guid->person($this->int($d, 'user_id')),
            'employment_type'    => $d['employment_type'] ?? 'mainWork',
            'hiring_date'        => $this->date($d, 'hiring_date') ?? $this->date($d, 'start_date'),
            'hiring_division_ID' => $this->guid->division($this->int($d, 'dep_id')),
            'hiring_position_ID' => $this->guid->position($this->int($d, 'post_id')),
        ];
    }

    private function terminations(array $d): array
    {
        return [
            'employee_ID'      => $this->employee($d),
            'termination_date' => $this->date($d, 'termination_date') ?? $this->date($d, 'start_date'),
        ];
    }

    /** end_date опционален: пусто — постоянный перевод, заполнено — временный. */
    private function transfers(array $d): array
    {
        return array_filter([
            'employee_ID'     => $this->employee($d),
            'start_date'      => $this->date($d, 'start_date'),
            'end_date'        => $this->date($d, 'end_date'),
            'new_division_ID' => $this->guid->division($this->int($d, 'new_dep_id')),
            'new_position_ID' => $this->guid->position($this->int($d, 'new_post_id')),
        ], fn ($v) => $v !== null);
    }

    private function businessTrips(array $d): array
    {
        return [
            'employee_ID' => $this->employee($d),
            'start_date'  => $this->date($d, 'start_date'),
            'end_date'    => $this->date($d, 'end_date'),
        ];
    }

    /**
     * Отпуска — пять типов документов 1С в одном приказе.
     * vacation_type: paid | unpaid | parentalLeave | educational | militaryService.
     */
    private function vacations(array $d): array
    {
        return [
            'employee_ID'   => $this->employee($d),
            'vacation_type' => $d['vacation_type'] ?? 'paid',
            'start_date'    => $this->date($d, 'start_date'),
            'end_date'      => $this->date($d, 'end_date'),
        ];
    }

    /** Отсутствия — два типа: sickLeave, other. */
    private function absences(array $d): array
    {
        return [
            'employee_ID'  => $this->employee($d),
            'absence_type' => $d['absence_type'] ?? 'other',
            'start_date'   => $this->date($d, 'start_date'),
            'end_date'     => $this->date($d, 'end_date'),
        ];
    }

    /** Совмещение: кто кого замещает. end_date опционален. */
    private function additionalDuties(array $d): array
    {
        return array_filter([
            'replacing_employee_ID' => $this->guid->employee($this->int($d, 'replacing_work_id'))
                ?? $this->guid->employeeByUser($this->int($d, 'replacing_user_id')),
            'replaced_employee_ID'  => $this->guid->employee($this->int($d, 'replaced_work_id'))
                ?? $this->guid->employeeByUser($this->int($d, 'replaced_user_id')),
            'start_date'            => $this->date($d, 'start_date'),
            'end_date'              => $this->date($d, 'end_date'),
        ], fn ($v) => $v !== null);
    }

    /** Дисциплинарные взыскания — один приказ на нескольких сотрудников. */
    private function disciplinaryActions(array $d): array
    {
        return array_filter([
            'action_date' => $this->date($d, 'action_date') ?? $this->date($d, 'start_date'),
            // remark | reprimand | severeReprimand — в контракте 1С поля нет,
            // но без него взыскание неотличимо по виду. Отдаём дополнительно.
            'action_type' => $d['action_type'] ?? null,
            'employees'   => $this->employeeRows($d, ['start_date', 'end_date']),
        ], fn ($v) => $v !== null);
    }

    /**
     * Снятие взыскания.
     *
     * disciplinary_action_order_ID в контракте отсутствует, но при двух
     * действующих взысканиях у сотрудника непонятно, какое снимать.
     * Отдаём дополнительно — 1С может игнорировать, пока не доработает приём.
     */
    private function removalOfDisciplinaryActions(array $d): array
    {
        return array_filter([
            'action_date'                  => $this->date($d, 'action_date') ?? $this->date($d, 'start_date'),
            'disciplinary_action_order_ID' => $d['disciplinary_action_order_id'] ?? null,
            'employees'                    => $this->employeeRows($d, ['start_date', 'removal_date']),
        ], fn ($v) => $v !== null && $v !== []);
    }

    /**
     * Табличная часть «сотрудники» для массовых приказов.
     *
     * Если форма не собрала список, но известен один сотрудник —
     * отдаём его одной строкой: лучше корректная строка, чем пустой массив.
     *
     * @param  string[]  $dateKeys  Какие даты класть в строку (у снятия — removal_date)
     */
    private function employeeRows(array $d, array $dateKeys): array
    {
        $rows = [];

        foreach ((array) ($d['employees'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $out = [
                'employee_ID' => $this->guid->employee($this->int($row, 'work_id'))
                    ?? $this->guid->employeeByUser($this->int($row, 'user_id')),
            ];

            foreach ($dateKeys as $key) {
                $out[$key] = $this->date($row, $key) ?? $this->date($d, $key);
            }

            $rows[] = $out;
        }

        if (empty($rows) && ($guid = $this->employee($d))) {
            $single = ['employee_ID' => $guid];

            foreach ($dateKeys as $key) {
                $single[$key] = $this->date($d, $key);
            }

            $rows[] = $single;
        }

        return $rows;
    }

    /* ─────────────────────────────── Вложения ─────────────────────────────── */

    /**
     * Файлы приказа: сам подписанный приказ первым, затем вложения.
     *
     * Сам приказ помечен is_main_document — это тело документа, которое
     * 1С показывает пользователю. Остальное — приложения к нему.
     */
    private function attachedFiles(OnecOrderPayload $payload, bool $withFiles): array
    {
        $out = [];

        if ($main = $this->hrOrderPdf($payload, $withFiles)) {
            $out[] = $main;
        }

        foreach (DocumentFiles::where('j_id', $payload->j_id)->get() as $file) {
            $item = [
                'attached_file_name'      => $file->file_name,
                'attached_file_extension' => ltrim((string) $file->file_ext, '.'),
                'attached_file_size'      => (int) $file->file_size,
                'attached_file_ID'        => $file->file_hash,
                'is_main_document'        => false,
                'attached_file_url'       => $this->apiUrl('attachments/' . $file->file_hash),
            ];

            if ($withFiles) {
                $item['attached_file'] = $this->encodeFile($file);
            }

            $out[] = $item;
        }

        return $out;
    }

    /**
     * PDF самого приказа, сформированный при подписании резолюции.
     *
     * null — если задание очереди ещё не отработало или упало. Это штатная
     * ситуация: PDF генерируется асинхронно в очереди pdf, приказ может
     * приехать в выгрузку на секунду раньше своего файла.
     */
    private function hrOrderPdf(OnecOrderPayload $payload, bool $withFiles): ?array
    {
        $docCode = $payload->data['doc_code']
            ?? Documents::where('id', $payload->doc_id)->value('doc_code');

        if (!$docCode) {
            return null;
        }

        $path = self::HR_ORDER_PDF_DIR . '/' . $docCode . '.pdf';

        // Путь с префиксом public/ — ровно как в GenerateHrOrderPdfJob
        if (!Storage::exists('public/' . $path)) {
            $this->logger->event('ORDER PDF NOT READY', [
                'j_id'     => $payload->j_id,
                'doc_code' => $docCode,
            ], 'warning');

            return null;
        }

        $item = [
            'attached_file_name'      => $this->orderFileName($payload, $docCode),
            'attached_file_extension' => 'pdf',
            'attached_file_size'      => (int) Storage::size('public/' . $path),
            'attached_file_ID'        => $docCode,
            'is_main_document'        => true,
            'attached_file_url'       => $this->apiUrl('document/' . $payload->j_id),
        ];

        if ($withFiles && $item['attached_file_size'] <= self::INLINE_FILE_LIMIT) {
            $item['attached_file'] = base64_encode(Storage::get('public/' . $path));
        }

        return $item;
    }

    /** Читаемое имя файла: номер приказа вместо служебного doc_code. */
    private function orderFileName(OnecOrderPayload $payload, string $docCode): string
    {
        $number = $payload->order_number ?: $docCode;

        return 'prikaz_' . str_replace(['/', '\\', ' '], '-', $number) . '.pdf';
    }

    private function apiUrl(string $suffix): string
    {
        return rtrim((string) config('app.url'), '/')
            . '/api/main/api-1c/v1/orders/' . $suffix;
    }

    /** Base64 тела файла. null — если файла нет на диске или он больше лимита. */
    private function encodeFile(DocumentFiles $file): ?string
    {
        try {
            // Размер берём из БД: ftp-диск на size() ходил бы по сети лишний раз
            if ((int) $file->file_size > self::INLINE_FILE_LIMIT) {
                return null; // 1С заберёт его по attached_file_url
            }

            $path = '/' . $file->file_path . $file->file_hash;
            $disk = Storage::disk(self::FILES_DISK);

            if (!$disk->exists($path)) {
                $this->logger->event('ORDER FILE MISSING', [
                    'file_hash' => $file->file_hash,
                    'path'      => $path,
                ], 'warning');

                return null;
            }

            return base64_encode($disk->get($path));
        } catch (\Throwable $e) {
            $this->logger->event('ORDER FILE ENCODE FAILED', [
                'file_hash' => $file->file_hash,
                'error'     => $e->getMessage(),
            ], 'warning');

            return null;
        }
    }

    /* ─────────────────────────────── Утилиты ─────────────────────────────── */

    private function employee(array $d): ?string
    {
        return $this->guid->employee($this->int($d, 'work_id'))
            ?? $this->guid->employeeByUser($this->int($d, 'user_id'));
    }

    private function int(array $d, string $key): ?int
    {
        return isset($d[$key]) && $d[$key] !== '' ? (int) $d[$key] : null;
    }

    /** Даты наружу всегда в Y-m-d, как в согласованных примерах коллекции. */
    private function date(array $d, string $key): ?string
    {
        $value = $d[$key] ?? null;

        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
