<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as OriginalPermission;
use Spatie\TranslationLoader\LanguageLine;

class Permission extends OriginalPermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'lang_id',
        'parent_id',
    ];

    public static function lang($key) {

        $lang_name = 'Lang';

        $language = LanguageLine::find($key);

        if ($language) {
            $lang_name = __($language->group.'.'.$language->key);
        }

        return $lang_name;
    }

    public static function isActive($key) {

        $lang_name = 'Status';

        $status_list = StatusList::where('type', 'status')->where('code', $key)->first();

        if ($status_list) {

            $language = LanguageLine::find($status_list->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }

        }

        return $lang_name;
    }

    public function parent(): BelongsTo {

        return $this->belongsTo(Permission::class, 'parent_id');

    }

    public function getChild(): \Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(Permission::class,  'parent_id', 'id');

    }
}
