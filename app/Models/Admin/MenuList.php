<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\TranslationLoader\LanguageLine;

class MenuList extends Model
{
    protected $fillable = [
        'title',
        'url_path',
        'lang_id',
        'icon_id',
        'count',
        'parent_id',
        'code',
        'status'
    ];

    public function iconClass(): BelongsTo
    {
        return $this->belongsTo(IconClassList::class, 'icon_id')->where('type', 1);
    }

    public static function lang($id) {

        $lang_name = '-';
        $model = MenuList::find($id);
        if ($model) {
            $lang_name = $model->title;

            $language = LanguageLine::find($model->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }
        }

        return $lang_name;
    }
}
