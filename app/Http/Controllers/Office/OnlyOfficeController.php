<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Messages\OnlyOfficeDoc;
use App\Services\OnlyOfficeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OnlyOfficeController extends Controller
{
    public function __construct(
        protected OnlyOfficeService $onlyOfficeService
    ) {}

    /**
     * Создать новый документ
     */
    public function create(Request $request): View
    {
        $savedFilePaths = [];

        if ($request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $file) {
                $storedPath = $file->store('temp_uploads', 'local');
                $savedFilePaths[] = [
                    'path'     => $storedPath,
                    'original' => $file->getClientOriginalName(),
                    'ext'      => $file->getClientOriginalExtension(),
                    'size'     => $file->getSize(),
                ];
            }
        }

        $data = array_merge($request->except(['doc_files', 'main_file']), [
            'doc_files_json' => json_encode($savedFilePaths), // одна строка вместо массива
        ]);

        // Если загружен основной файл (main_file) — сохраняем его и открываем
        // в OnlyOffice (вместо прямого сохранения / пустого шаблона).
        $typeTemplatePath = \App\Models\JournalTypeTemplate::pathFor((int) $request->journal_type);

        if ($request->hasFile('main_file') && $request->file('main_file')->isValid()) {
            $uploaded = $this->onlyOfficeService->storeUploadedMainFile($request->file('main_file'));
            $service = $this->onlyOfficeService->openWordEditor($uploaded['url'], $uploaded['key']);
        } elseif ($typeTemplatePath && ($copy = $this->onlyOfficeService->storeTemplateCopy($typeTemplatePath))) {
            /* v63: у типа есть docx-шаблон (например, заявка на закуп) —
               открываем его КОПИЮ вместо пустого листа. Товары/текст
               пользователь заполняет руками; дальше штатное сохранение
               (document_key копии уйдёт с формой в DefaultDocumentHandler). */
            $service = $this->onlyOfficeService->openWordEditor($copy['url'], $copy['key']);
        } else {
            $service = $this->onlyOfficeService->openWordEditor();
        }

        return view('office.document.create-only', [
            'document_params' => $service['document_params'],
            'token' => $service['token'],
            'onlyOfficeJsUrl' => $service['document_params']['only_office_js_url'],
            'data' => $data
        ]);
    }

    /**
     * Редактировать существующий документ
     */
    public function edit(Request $request, int $jId, string $docHash): View
    {
        $savedFilePaths = [];

        if ($request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $file) {
                $storedPath = $file->store('temp_uploads', 'local');
                $savedFilePaths[] = [
                    'path'     => $storedPath,
                    'original' => $file->getClientOriginalName(),
                    'ext'      => $file->getClientOriginalExtension(),
                    'size'     => $file->getSize(),
                ];
            }
        }

        $data = array_merge($request->except('doc_files'), [
            'doc_files_json' => json_encode($savedFilePaths), // одна строка вместо массива
        ]);

        $service = $this->onlyOfficeService->editWordEditor($jId, 'DOC');

        return view('office.document.edit-only', [
            'document_params' => $service['document_params'],
            'token' => $service['token'],
            'j_id' => $jId,
            'doc_hash' => $docHash,
            'onlyOfficeJsUrl' => $service['document_params']['only_office_js_url'],
            'data' => $data
        ]);
    }

    /**
     * Просмотр документа
     */
    public function docView(): View
    {
        return view('office.document.doc_view');
    }

    /**
     * Принудительное сохранение документа (forcesave) перед «Продолжить».
     * Вызывается AJAX-ом из редактора, пока он открыт.
     */
    public function forceSave(Request $request): \Illuminate\Http\JsonResponse
    {
        $key = $request->input('key');

        if (!$key) {
            return response()->json(['ok' => false, 'error' => 'no_key'], 400);
        }

        $result = $this->onlyOfficeService->forceSave($key);

        return response()->json($result);
    }

    /**
     * Конвертировать и скачать PDF
     */
    public function convertToPDF(int $onlyDocId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $model = OnlyOfficeDoc::findOrFail($onlyDocId);

        $pdfPath = $this->onlyOfficeService->createHtmlToPDFConverted(
            $model->doc_key,
            $model->doc_path
        );

        return Storage::disk('ftp')->download($pdfPath);
    }
}
