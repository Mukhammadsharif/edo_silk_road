<?php

namespace App\Models\Document;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Documents extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doc_number',
        'doc_date',
        'doc_name',
        'doc_text',
        'doc_hash',
        'doc_code',
        'doc_term',
        'doc_lang'
    ];

    public function journal()
    {
        return $this->belongsTo(DocumentJournals::class, 'id','doc_id');
    }

    public function documentReportForm(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentReportForm::class, 'id','document_id');
    }

    public function QrCode($doc_id)
    {
        $edoDocument = Documents::find($doc_id);
        $journal = DocumentJournals::where('doc_id', $doc_id)->first();
        $user = $journal->fromUser;
        if ($edoDocument && $user) {
            $domainName = env('APP_URL', 'https://demoedo.uztest.uz');
            return QrCode::size(60)
                ->generate($domainName.'/qr-document/'.$edoDocument->doc_hash.'/'.$edoDocument->doc_code);
        }

        return $this->qrImage();
    }

    public function execQrCode($doc_id)
    {
        $edoDocument = Documents::find($doc_id);
        $journal = DocumentJournals::where('doc_id', $doc_id)->first();
        $user = $journal->fromUser;
        if ($edoDocument && $user && $journal->status > 4) {
            $domainName = env('APP_URL', 'https://demoedo.uztest.uz');
            return QrCode::size(60)
                ->generate($domainName.'/d/'.$edoDocument->doc_code);
        }

        return $this->qrImage();
    }

    public function qrImage()
    {
        return '<img src="'.asset('/images/signature.png').'" style="width: 48px">';
    }

    public function dep()
    {
        return $this->hasOne(DocToDeps::class, 'j_id', 'id');
    }
}
