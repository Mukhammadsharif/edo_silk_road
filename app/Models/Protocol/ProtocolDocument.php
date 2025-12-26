<?php

namespace App\Models\Protocol;

use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use App\Models\ECP\SignatureDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\TranslationLoader\LanguageLine;

class ProtocolDocument extends Model
{

    protected $fillable = [
        'doc_name',
        'doc_number',
        'doc_date',
        'doc_term',
        'doc_hash',
        'doc_code',
        'doc_type_id',
        'visibility',
        'created_dep',
        'created_user',
        'doc_lang',
        'status',
    ];

    public function docType(): BelongsTo
    {
        return $this->belongsTo(ProtocolDocType::class, 'doc_type_id');
    }

    public function docText()
    {
        return $this->belongsTo(ProtocolDocText::class, 'doc_id');
    }

    public function docFiles()
    {
        return $this->hasMany(ProtocolDocFile::class, 'doc_id');
    }

    public function docDecisions()
    {
        return $this->hasMany(ProtocolDocDecision::class, 'doc_id');
    }

    public function docMembers()
    {
        return $this->hasMany(ProtocolDocMember::class, 'doc_id');
    }

    public function docReplyComments()
    {
        return $this->hasMany(ProtocolDocReplyComment::class, 'doc_id');
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'created_user');
    }

    public function protocolEcpInfo($protocol_id, $user_id)
    {
        return SignatureDocuments::where('doc_id', $protocol_id)->where('user_id', $user_id)->whereHas('type', function ($query) {
            $query->whereIn('doc_code', ['protocol_document', 'pr_emp_hr_order']);
        })->first();
    }

    public function statusName(): BelongsTo
    {
        return $this->belongsTo(StatusList::class, 'status', 'code')->where('type', 'prt_status');
    }

    public static function statusDoc($key) {

        $lang_name = 'Status';

        $status_list = StatusList::where('type', 'prt_status')->where('code', $key)->first();

        if ($status_list) {

            $language = LanguageLine::find($status_list->lang_id);

            $lang_name = $status_list->title;

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }

        }

        return $lang_name;
    }

    public function docQrCode($id, $doc_code)
    {
        // return qrCode
        $document = ProtocolDocument::where('id', $id)->where('doc_code', $doc_code)->where('status', 3)->first();

        if ($document) {
            $domainName = env('APP_URL', 'https://edoline.uz');
            return QrCode::size(60)
                ->generate($domainName.'/p/'.$document->doc_code);
        }

        return $this->qrImage();
    }

    public function qrImage()
    {
        // return image
        return '<img src="'.asset('/images/signature.png').'" style="width: 48px">';
    }
}
