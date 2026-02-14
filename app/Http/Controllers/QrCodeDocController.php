<?php

namespace App\Http\Controllers;

use App\Models\Admin\StatusList;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\Documents;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocText;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use App\Services\DocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class QrCodeDocController extends Controller
{
    //
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }
    public function viewProtocolDocument($doc_code): Factory|View|Application
    {

        $document = ProtocolDocument::where('doc_code', $doc_code)->firstOrFail();

        $documentText = ProtocolDocText::where('doc_id', $document->id)->first();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->get();

        $documentMembers = ProtocolDocMember::where('doc_id', $document->id)->orderBy('order_number')->get();

        $documentMemberRoles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->orderBy('order_number')->get();

        $html_doc = null;
        $model = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $document->id)->first();
        if ($model) {
            $path = $model->doc_path.$model->doc_key.'.html';
            if (Storage::disk('ftp')->exists($path)){
                $html_doc = Storage::disk('ftp')->get($path);
            }
        }

        return view('qr-code-doc.protocol-document',
            compact('document','documentText', 'documentFiles', 'documentMembers', 'documentMemberRoles', 'documentHrEmp',
            'html_doc'));
    }

    public function viewWorkDocument($doc_code): Factory|View|Application
    {
        //
        App::setLocale('uz');
        $document = Documents::where('doc_code', $doc_code)->first();

        if (!$document) {
            return view('qr-code-doc.404');
        }

        $journal = DocumentJournals::where('doc_id', $document->id)->first();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $qrCodeSignUser = $directorDocument;

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        if ($journal->journal->doc_code == 'BASE_OUTGOING') {
            $view = 'doc-sent';
        } elseif ($journal->journal->doc_code == 'BASE_INCOMING') {
            $view = 'doc-inbox';
        } elseif ($journal->journal->doc_code == 'HR_ORD_MT') {
            $view = 'doc-hr-ord';
        } else {
            $view = 'doc-work';
        }
        /*// PDF generatsiya qilish
        $pdf = Pdf::loadView('qr-code-doc.doc-hr-ord', compact('document', 'directorDocument', 'html_doc'));

        // PDF faylni saqlash
        $fileName = "document_1.pdf";
        $filePath = "documents/pdf/{$fileName}";
        Storage::disk('public')->put($filePath, $pdf->output());

        $base64 = base64_encode($pdf->output());

        print_r($base64); die;*/

        return view('qr-code-doc.'.$view, compact('document', 'journal', 'directorDocument', 'signer', 'agreement_users',
            'qrCodeSignUser', 'html_doc', 'model'));

    }

    public function confirmKey(Request $request): JsonResponse
    {
        //
        $key = $request->key;
        $type = $request->type;
        $id = $request->id;

        $success = false;
        $message = "Noto'g'ri kod kiritildi. Iltimos, qaytadan urunib ko'ring.";

        $checkKey = QrCodeKeys::where('doc_id', $id)->where('doc_type', $type)->first();

        if($checkKey->key_code == $key) {
            $success = true;
            $message = "Kod muvaffaqiyatli tasdiqlandi.";
        }

        return response()->json(['success' => $success, 'message' => $message]);
    }

    public function createQrKeyCode(Request $request)
    {
        //create qr key code
        $doc_id = $request->id;
        $doc_type = $request->type;
        $date = Carbon::now()->subDay()->format('Y-m-d');

        $check_code = QrCodeKeys::where('doc_id', $doc_id)->where('doc_type', $doc_type)->first();
        if($check_code) {
            $check_code->update(['key_code' => rand(1000, 9999), 'key_date' => $date]);
            return $check_code->key_code;
        } else {
            $qr_key = new QrCodeKeys();
            $qr_key->doc_id = $doc_id;
            $qr_key->doc_type = $doc_type;
            $qr_key->key_code = rand(1000, 9999);
            $qr_key->key_date = $date;
            $qr_key->save();
            return $qr_key->key_code;
        }
    }
}
