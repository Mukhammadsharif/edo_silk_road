<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class UserPosition extends Model
{
    protected $fillable = [
        'org_id',
        'name_uz',
        'name_kr',
        'name_ru',
        'name_en',
        'order_number',
        'dep_ids',
        'status',
    ];

    public static function lang($id)
    {

        $lang_name = UserPosition::find($id);

        $lang = session()->get('locale');

        $name = 'Lang ID';

        if ($lang_name) {
            $name = $lang_name->name_uz;
            if ($lang == 'ru') {
                $name = $lang_name->name_ru;
            } elseif ($lang == 'en') {
                $name = $lang_name->name_en;
            } elseif ($lang == 'kr') {
                $name = $lang_name->name_en;
            }
        }

        return $name;
    }

    public static function docLang($id, $lang)
    {

        $lang_name = UserPosition::find($id);

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

    public static function isActive($key)
    {

        $lang_name = 'Status';

        $status_list = StatusList::where('type', 'status')->where('code', $key)->first();

        if ($status_list) {

            $language = LanguageLine::find($status_list->lang_id);

            if ($language) {
                $lang_name = __($language->group . '.' . $language->key);
            }

        }

        return $lang_name;
    }

    public static function selectedDeps($dep_ids)
    {
        //
        $dep_ids = json_decode($dep_ids, true);

        $departments = Department::whereIn('id', $dep_ids)->get();

        $dep_names = '';

        $lang = session()->get('locale');

        if (count($departments)) {
            foreach ($departments as $department) {
                if ($departments) {
                    $dep_names = $department->name_uz;
                    if ($lang == 'ru') {
                        $dep_names = $department->name_ru;
                    } elseif ($lang == 'en') {
                        $dep_names = $department->name_en;
                    }
                }
            }
        }
        return $dep_names;
    }

}
