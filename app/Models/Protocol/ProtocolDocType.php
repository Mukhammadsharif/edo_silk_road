<?php

namespace App\Models\Protocol;

use App\Models\Admin\Role;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\TranslationLoader\LanguageLine;

class ProtocolDocType extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'lang_id',
        'doc_code',
        'doc_hash',
        'order_number',
        'level_number',
        'role_id',
        'secretary_role_id',
        'member_role_id',
        'sign_type', // 0-ketma-ket; 1-parallel
        'status',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProtocolDocument::class, 'doc_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProtocolDocType::class, 'parent_id');
    }

    public function child(): HasMany
    {
        return $this->hasMany(ProtocolDocType::class, 'parent_id')->where('status', 1);
    }

    public function docTypeUsers(): HasMany
    {
        return $this->hasMany(ProtocolDocTypeUser::class, 'doc_type_id')->where('role_id', '>', 1);
    }

    public function memberRoleID(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'member_role_id');
    }

    public function roleID(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public static function lang($id) {

        $model = ProtocolDocType::find($id);

        $lang_name = '-';

        if ($model) {
            $lang_name = $model->title;

            $language = LanguageLine::find($model->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }
        }

        return $lang_name;
    }

    public static function menuCount($id) {

        $currentProtocol = ProtocolDocument::where('doc_type_id', $id)->whereIn('status', [1,2,4])->count();

        //$parentProtocol = ProtocolDocument::where('parent_id', $currentProtocol->id??0)->whereIn('status', [1,2,3,4])->count();

        return $currentProtocol;
    }

    public static function menuCountMem($id) {

        $workIds = UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();

        $protocolDocType = ProtocolDocument::whereHas('docType', function ($query) use ($id) {
            $query->where('id', $id);
        })->pluck('id')->toArray();

        $protocolMember = ProtocolDocMember::whereIn('doc_id', $protocolDocType)->whereIn('user_id', $workIds)
            ->whereIn('status', [2,4])->count();

        return $protocolMember;
    }

    public static function isActive($key) {

        $lang_name = 'Status';

        $status_list = StatusList::where('type', 'status')->where('code', $key)->first();

        if ($status_list) {

            $language = LanguageLine::find($status_list->lang_id);

            $lang_name = $status_list->title;

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }

        }

        return $lang_name;
    }
}
