<?php

namespace App\Models\Admin\HR;

use App\Models\Admin\StatusList;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class HrEmpFamily extends Model
{
    protected $fillable = [
        'user_id',
        'fam_code',
        'fam_name',
        'br_address',
        'live_address',
        'post_address',
        'post_name',
        'gender',
        'br_date',
        'created_id',
        'isActive'
    ];

    public static function familyName($key) {

        $lang_name = 'Family';

        $status_list = StatusList::where('type', 'hr_emp_fam_name')->where('code', $key)->first();

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
