<?php

namespace App\Http\Controllers\FreeSign;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Services\FreeSign\SignatureStampService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Storage;

/**
 * Просмотр и скачивание версий free-sign документа.
 *
 * Версии = записи document_files по (j_id, doc_id, pdf):
 *   первая (min id)  — исходник без меток
 *   последняя (max)  — текущая, со всеми собранными подписями
 */
class FreeSignVersionController extends Controller
{
    private const DISK = 'ftp_13';

    public function __construct(private SignatureStampService $stamp)
    {
    }

    /**
     * GET /free-sign/{journal}/versions
     * Список версий документа (JSON) — для UI истории подписания.
     */
    public function index($journalId): JsonResponse
    {
        $journal = DocumentJournals::findOrFail($journalId);

        $versions = $this->stamp->allVersions($journal->id, $journal->doc_id)
            ->values()
            ->map(function (DocumentFiles $f, int $i) {
                return [
                    'id'        => $f->id,
                    'version'   => $i,                       // 0 = исходник
                    'is_source' => $i === 0,
                    'name'      => $f->file_name,
                    'size'      => $f->file_size,
                    'signed_by' => $i === 0 ? null : $f->from_user,
                    'created_at'=> optional($f->created_at)->format('d.m.Y H:i'),
                    'url'       => route('free-sign.version.show', $f->id),
                ];
            });

        return response()->json(['data' => $versions]);
    }

    /**
     * GET /free-sign/version/{fileId}
     * Отдать конкретную версию inline (как preView).
     */
    public function show($fileId): Response|JsonResponse
    {
        $file = DocumentFiles::find($fileId);
        if (!$file) {
            return response()->json(__('document_not_found'), 404);
        }

        $path = '/' . $file->file_path . $file->file_hash;
        if (!Storage::disk(self::DISK)->exists($path)) {
            return response()->json(__('document_not_found'), 404);
        }

        return ResponseFacade::make(Storage::disk(self::DISK)->get($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
        ]);
    }

    /**
     * GET /free-sign/{journal}/download/{which}
     * Скачать: which = source (исходник) | current (последняя версия).
     */
    public function download($journalId, string $which = 'current')
    {
        $journal = DocumentJournals::findOrFail($journalId);

        $file = $which === 'source'
            ? $this->stamp->originalVersion($journal->id, $journal->doc_id)
            : $this->stamp->currentVersion($journal->id, $journal->doc_id);

        if (!$file) {
            return response()->json(__('document_not_found'), 404);
        }

        $path = '/' . $file->file_path . $file->file_hash;
        if (!Storage::disk(self::DISK)->exists($path)) {
            return response()->json(__('document_not_found'), 404);
        }

        return Storage::disk(self::DISK)->download($path, $file->file_name);
    }
}
