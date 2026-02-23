<?php

namespace App\Models\Admin;

use Spatie\Permission\Models\Role as OriginalRole;

use Spatie\TranslationLoader\LanguageLine;

class Role extends OriginalRole
{
    public static function lang($id) {

        $model = Role::find($id);

        $lang_name = 'Title';

        if ($model) {
            $lang_name = $model->name;

            $language = LanguageLine::find($model->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }
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
}
