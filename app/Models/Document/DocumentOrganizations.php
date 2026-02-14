<?php

namespace App\Models\Document;

use App\Models\Admin\StatusList;
use App\Models\Journal\JournalTypes;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class DocumentOrganizations extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'for_doc_type',
        'status'
    ];

    public function docType()
    {
        return $this->belongsTo(JournalTypes::class, 'for_doc_type');
    }

    public static function lang($id) {

        $lang_name = DocumentOrganizations::find($id);

        $lang = session()->get('locale');

        $name = 'Lang ID';

        if ($lang_name) {
            $name = $lang_name->name_uz;
            if ($lang == 'ru') {
                $name = $lang_name->name_ru;
            } elseif ($lang == 'en') {
                $name = $lang_name->name_en;
            }
        }

        return $name;
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
}
