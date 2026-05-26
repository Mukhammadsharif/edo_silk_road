<?php

namespace App\Http\Controllers;

use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\Documents;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfGenerateController extends Controller
{
    //
    public function workingDocument($doc_id, $doc_hash): Response
    {

        $document = Documents::where('id', $doc_id)->where('doc_hash', $doc_hash)->firstOrFail();

        $journal = DocumentJournals::where('doc_id', $doc_id)->first();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $str_rep1 = str_replace('font-family:', '', $document->doc_text);
        $str_rep2 = str_replace('margin-left:', '', $str_rep1);

        $doc_text = $str_rep2;

        $data = [
            'document'          => $document,
            'doc_text'          => $doc_text,
            'journal'           => $journal,
            'directorDocument'  => $directorDocument,
            'signer'            =>  $signer,
            'agreement_users'   => $agreement_users,
            'execQrCode'        => $this->execQrCode($document->id)
        ];

        if (str_contains($journal->journal->doc_code, 'APP')) {
            $pdf = PDF::loadView( 'pdf.app-doc', $data);
        } elseif($journal->journal->doc_code === 'COMMAND') {
            $pdf = PDF::loadView( 'pdf.order-doc', $data);
        } else {
            $pdf = PDF::loadView( 'pdf.work-doc', $data);
        }

        $fName = $journal->fromUser->personal->f_name??'first_name';
        $lName = $journal->fromUser->personal->l_name??'last_name';
        $pdf_name = 'EAI'.$lName.'_'.$fName.'.pdf';

        return $pdf->download($pdf_name);

    }


    public function inboxDocument($doc_id, $doc_hash): Response
    {

        $document = Documents::where('id', $doc_id)->where('doc_hash', $doc_hash)->firstOrFail();

        $journal = DocumentJournals::where('doc_id', $doc_id)->first();

        $str_rep1 = str_replace('font-family:', '', $document->doc_text);
        $str_rep2 = str_replace('margin-left:', '', $str_rep1);

        $doc_text = $str_rep2;

        Pdf::setOption(['defaultFont' => 'Cambria', 'isRemoteEnabled', true]);

        $data = [
            'document'      => $document,
            'doc_text'      => $doc_text,
            'journal'       => $journal
        ];

        $pdf = PDF::loadView( 'pdf.inbox-doc', $data);

        $fName = $journal->fromUser->personal->f_name??'first_name';
        $lName = $journal->fromUser->personal->l_name??'last_name';
        $pdf_name = 'EAI'.$lName.'_'.$fName.'.pdf';

        return $pdf->download($pdf_name);
    }

    public function sentDocument($doc_id, $doc_hash): Response
    {
        $document = Documents::where('id', $doc_id)->where('doc_hash', $doc_hash)->firstOrFail();

        $journal = DocumentJournals::where('doc_id', $doc_id)->first();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $str_rep1 = str_replace('font-family:', '', $document->doc_text);
        $str_rep2 = str_replace('margin-left:', '', $str_rep1);

        $doc_text = $str_rep2;

        Pdf::setOption(['defaultFont' => 'Cambria', 'isRemoteEnabled', true]);

        $data = [
            'document'          => $document,
            'doc_text'          => $doc_text,
            'journal'           => $journal,
            'execQrCode'        => $this->execQrCode($document->id),
            'directorDocument'  => $directorDocument,
        ];

        $pdf = PDF::loadView( 'pdf.sent-doc', $data);

        $fName = $journal->fromUser->personal->f_name??'first_name';
        $lName = $journal->fromUser->personal->l_name??'last_name';
        $pdf_name = 'EAI'.$lName.'_'.$fName.'.pdf';

        return $pdf->download($pdf_name);
    }


    public function execQrCode($doc_id): string
    {
        $edoDocument = Documents::find($doc_id);

        $edoJournal = DocumentJournals::where('doc_id', $doc_id)->where('status', '>', 4)->first();
        if ($edoDocument && $edoJournal) {
            $domainName = env('APP_URL', 'https://edoline.uz');
            $qr_code = base64_encode(QrCode::size(60)->generate($domainName.'/d/'.$edoDocument->doc_code));
            return '<img src="data:image/png;base64, '.$qr_code.'">';
        }

        return $this->qrImage();
    }

    public function qrImage(): string
    {
        return '<img src="'.asset('/images/signature.png').'" style="width: 48px">';
    }
}
