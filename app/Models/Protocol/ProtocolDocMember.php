<?php

namespace App\Models\Protocol;

use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\TranslationLoader\LanguageLine;

class ProtocolDocMember extends Model
{
    protected $fillable = [
        'doc_id',
        'user_id',
        'role_id',
        'order_number',
        'status',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProtocolDocument::class, 'doc_id');
    }

    public function userWork(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'user_id');
    }

    public function statusName(): BelongsTo
    {
        return $this->belongsTo(StatusList::class, 'status', 'code')->where('type', 'prt_status');
    }

    public static function statusDoc($id) {

        $lang_name = 'Status';

        $status_list = StatusList::where('type', 'prt_status')->where('code', $id)->first();

        if ($status_list) {

            $lang_name = $status_list->title;

            $language = LanguageLine::find($status_list->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }

        }

        return $lang_name;
    }

    public static function getMemberRole($user_id, $doc_lang)
    {
        $user = UserWorkHistory::find($user_id);

        $lang = '-';

        if ($user) {
            $lang = $user->department->docLang($user->department->id, $doc_lang);
        }

        return $lang;
    }

    public static function getDepartment($user_id, $doc_lang)
    {
        $user = UserWorkHistory::find($user_id);

        $lang = '-';

        if ($user) {
            $lang = $user->department->docLang($user->department->id, $doc_lang);
        }

        return $lang;
    }

    public static function getPosition($user_id, $doc_lang)
    {
        $user = UserWorkHistory::find($user_id);

        $lang = '-';

        if ($user) {
            $lang = $user->position->docLang($user->position->id, $doc_lang);
        }

        return $lang;
    }

    public function QrCode($doc_id, $user_id)
    {
        $protocol = ProtocolDocument::find($doc_id);
        $user = ProtocolDocMember::where('doc_id', $doc_id)->where('user_id', $user_id)->first();
        if ($protocol && $user && $user->status == 3) {
            $domainName = env('APP_URL', 'https://edoline.uz');
            return QrCode::size(60)
                ->generate($domainName.'/p/'.$protocol->doc_code);
        }

        return $this->qrImage();
    }

    public function qrImage()
    {
        return '<img src="'.asset('/images/signature.png').'" style="width: 48px">';
    }
}
