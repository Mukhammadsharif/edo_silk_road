<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Messages\OnlyOfficeDoc;
use App\Services\OnlyOfficeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnlyOfficeController extends Controller
{
    //
    protected OnlyOfficeService $onlyOfficeService;

    public function __construct(OnlyOfficeService $onlyOffice)
    {
        $this->onlyOfficeService = $onlyOffice;
    }

    public function create(Request $request)
    {
        //
        $data = $request->all();

        $service = $this->onlyOfficeService->openWordEditor();

        $document_params = $service['document_params'];

        $token = $service['token'];

        $onlyOfficeJsUrl = $document_params['only_office_js_url'];

        return view('office.document.create-only', compact( 'document_params', 'token', 'onlyOfficeJsUrl', 'data'));
    }

	public function edit(Request $request, $j_id, $doc_hash)
    {
        //
        $data = $request->all();

        $service = $this->onlyOfficeService->editWordEditor($j_id, 'DOC');

        $document_params = $service['document_params'];

        $token = $service['token'];

        $onlyOfficeJsUrl = $document_params['only_office_js_url'];

        return view('office.document.edit-only', compact( 'document_params', 'token', 'j_id', 'doc_hash', 'onlyOfficeJsUrl', 'data'));

    }

    public function docView()
    {
        return view('office.document.doc_view');
    }

    public function convertToPDF($onlyDocId)
    {

        $model = OnlyOfficeDoc::find($onlyDocId);

        $service = $this->onlyOfficeService->createHtmlToPDFConverted($model->doc_key, $model->doc_path);

        return Storage::disk('ftp')->download($service);
    }
}
