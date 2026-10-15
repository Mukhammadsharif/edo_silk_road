<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentFiles;
use App\Models\Document\ReplyDocumentFiles;
use App\Models\Protocol\ProtocolDocFile;
use App\Support\FilePreview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /** Диск, на котором лежат вложения документов. */
    private const DISK = 'ftp_13';

    /* ══════════════════════════════════════════════════════════════
     | Общая отдача файла
     |
     | Раньше каждый метод предпросмотра содержал свою лесенку
     | if (PDF) ... elseif (PNG|JPG|JPEG) ... else download. Из-за неё
     | word, excel, текст, звук и видео уходили на скачивание, а в
     | шаблонах такие файлы и вовсе не отрисовывались.
     |
     | Теперь тип решает один справочник App\Support\FilePreview,
     | а здесь остаётся только отдача.
     ══════════════════════════════════════════════════════════════ */

    /** Полный путь файла на диске. */
    private function pathOf(Model $model): string
    {
        return '/' . $model->file_path . $model->file_hash;
    }

    /**
     * Показать файл в браузере, если его тип это позволяет,
     * иначе отдать на скачивание.
     */
    private function inlineResponse(?Model $model): \Illuminate\Http\Response|StreamedResponse|JsonResponse
    {
        if (!$model) {
            return response()->json(__('document_not_found'));
        }

        $path = $this->pathOf($model);

        if (!Storage::disk(self::DISK)->exists($path)) {
            return response()->json(__('document_not_found'));
        }

        $ext  = $model->file_ext;
        $mime = FilePreview::mime($ext);

        // Офисные документы браузер сам не покажет — их открывает
        // OnlyOffice, который забирает файл отдельным маршрутом
        // officeSource. Здесь такой запрос означает «скачать».
        if (!$mime || !FilePreview::isInline($ext)) {
            return Storage::disk(self::DISK)->download($path, $model->file_name);
        }

        $headers = [
            'Content-Type' => $mime,
            // Имя в кавычках ломается на кириллице и пробелах, поэтому
            // дублируем его в filename* по RFC 5987
            'Content-Disposition' => "inline; filename*=UTF-8''" . rawurlencode($model->file_name),
            // Без этого браузер может «додумать» тип и показать
            // присланный txt как HTML со скриптами внутри
            'X-Content-Type-Options' => 'nosniff',
        ];

        // SVG и HTML — это документы с исполняемым содержимым.
        // sandbox без параметров отключает скрипты, формы и доступ
        // к нашему домену, но оставляет отрисовку.
        if (FilePreview::isSandboxed($ext)) {
            $headers['Content-Security-Policy'] = "sandbox; default-src 'none'; img-src data: blob:; style-src 'unsafe-inline'";
        }

        return Response::make(Storage::disk(self::DISK)->get($path), 200, $headers);
    }

    /** Скачивание без вариантов. */
    private function downloadResponse(?Model $model): StreamedResponse|RedirectResponse
    {
        if ($model) {
            $path = $this->pathOf($model);

            if (Storage::disk(self::DISK)->exists($path)) {
                return Storage::disk(self::DISK)->download($path, $model->file_name);
            }
        }

        return back()->with('error', __('document_not_found'));
    }

    /* ─────────────── Файлы протоколов ─────────────── */

    public function protocolDocFileDownload($id): StreamedResponse|RedirectResponse
    {
        return $this->downloadResponse(ProtocolDocFile::find($id));
    }

    public function protocolDocFilePreView($id): \Illuminate\Http\Response|StreamedResponse|JsonResponse
    {
        return $this->inlineResponse(ProtocolDocFile::find($id));
    }

    public function protocolDocFileDelete($id): JsonResponse
    {
        $file = ProtocolDocFile::find($id);

        $res = 1;
        if ($file) {
            $path = $this->pathOf($file);

            if (Storage::disk(self::DISK)->exists($path)) {
                Storage::disk(self::DISK)->delete($path);
            }
            $file->delete();

            $res = 2;
        }

        return response()->json(['res' => $res, 'id' => $id, 'message' => __('message.task_completed_successfully')]);
    }

    public function download($id): StreamedResponse|RedirectResponse
    {
        return $this->downloadResponse(ProtocolDocFile::find($id));
    }

    /* ─────────────── Вложения документов ─────────────── */

    public function fileDownload($id): StreamedResponse|RedirectResponse
    {
        return $this->downloadResponse(DocumentFiles::find($id));
    }

    public function preView($id): \Illuminate\Http\Response|StreamedResponse|JsonResponse
    {
        return $this->inlineResponse(DocumentFiles::find($id));
    }

    /* ─────────────── Файлы ответных писем ─────────────── */

    public function downloadReply($id): StreamedResponse|RedirectResponse
    {
        return $this->downloadResponse(ReplyDocumentFiles::find($id));
    }

    public function preViewReply($id): \Illuminate\Http\Response|StreamedResponse|JsonResponse
    {
        return $this->inlineResponse(ReplyDocumentFiles::find($id));
    }

    public function fileDelete($id): JsonResponse
    {
        $file = DocumentFiles::find($id);

        $res = 1;
        $message = __('unk');

        if ($file) {
            $path = $this->pathOf($file);

            if (Storage::disk(self::DISK)->exists($path)) {
                Storage::disk(self::DISK)->delete($path);
            }
            $file->delete();

            $res = 2;
            $message = __('file_succ_deleted');
        }

        return response()->json(['res' => $res, 'id' => $id, 'message' => $message]);
    }

    /* ══════════════════════════════════════════════════════════════
     | Исходник для OnlyOffice
     |
     | Document Server скачивает файл сам, своим HTTP-клиентом — без
     | нашей сессии и куки. Поэтому маршрут живёт ВНЕ группы auth, а
     | доступ даёт подпись Laravel с ограниченным сроком жизни
     | (URL::temporarySignedRoute). Подобрать ссылку или подставить
     | чужой id нельзя: подпись считается от всего адреса.
     |
     | Маршрут регистрируется с middleware('signed') — см. README.
     ══════════════════════════════════════════════════════════════ */
    public function officeSource($id): \Illuminate\Http\Response|JsonResponse
    {
        $model = DocumentFiles::find($id);

        if (!$model) {
            return response()->json(__('document_not_found'), 404);
        }

        $path = $this->pathOf($model);

        if (!Storage::disk(self::DISK)->exists($path)) {
            return response()->json(__('document_not_found'), 404);
        }

        return Response::make(Storage::disk(self::DISK)->get($path), 200, [
            'Content-Type' => FilePreview::mime($model->file_ext) ?: 'application/octet-stream',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . rawurlencode($model->file_name),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
