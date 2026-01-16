<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\TranslationLoader\LanguageLine;

class StatusList extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'lang_id',
        'type',
        'code',
        'icon_id',
        'class_id',
        'order_number'
    ];

    public function iconClass(): BelongsTo
    {
        return $this->belongsTo(IconClassList::class, 'icon_id');
    }

    public function badgeClass(): BelongsTo
    {
        return $this->belongsTo(IconClassList::class, 'class_id')->where('type', 2);
    }

    public static function lang($id) {

        $name = StatusList::find($id);

        $lang_name = 'Lang ID';

        if ($name) {
            $lang_name = $name->title;
            $language = LanguageLine::find($name->lang_id);
            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }
        }

        return $lang_name;
    }

    public static function docLang($id, $lang)
    {
        //
        $lang_name = StatusList::find($id);

        $name = 'Lang ID';

        if ($lang_name) {
            $name = $lang_name->name_uz;
            $language = LanguageLine::find($lang_name->lang_id);
            if ($lang == 'ru') {
                $name = $language->text['ru'] ?? '???';
            } elseif ($lang == 'en') {
                $name = $language->text['en'] ?? '???';
            } elseif ($lang == 'uz' || $lang == 'kr') {
                $name = $language->text['uz'] ?? '???';
            }
        }

        return $name;
    }

}
