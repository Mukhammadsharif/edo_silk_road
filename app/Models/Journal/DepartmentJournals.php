<?php

namespace App\Models\Journal;

use App\Models\Admin\Department;
use App\Models\Admin\StatusList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\TranslationLoader\LanguageLine;

class DepartmentJournals extends Model
{
    //
    protected $fillable = [
        'dep_id',
        'type',
        'name_uz',
        'name_kr',
        'name_ru',
        'name_en',
        'status',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    public static function lang($id) {

        $lang_name = DepartmentJournals::find($id);

        $lang = session()->get('locale');

        $name = 'Lang ID';

        if ($lang_name) {
            $name = $lang_name->name_uz;
            if ($lang == 'ru') {
                $name = $lang_name->name_ru;
            } elseif ($lang == 'en') {
                $name = $lang_name->name_en;
            } elseif ($lang == 'kr') {
                $name = $lang_name->name_kr;
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
