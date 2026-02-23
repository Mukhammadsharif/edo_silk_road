<?php

namespace App\Models\Protocol;

use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\TranslationLoader\LanguageLine;

class ProtocolDocReplyComment extends Model
{
    protected $fillable = [
        'doc_id',
        'doc_member_id',
        'user_id',
        'doc_text',
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
}
