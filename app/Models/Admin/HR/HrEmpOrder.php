<?php

namespace App\Models\Admin\HR;

use App\Models\Admin\StatusList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\TranslationLoader\LanguageLine;

class HrEmpOrder extends Model
{
    protected $fillable = [
        'user_id',
        'w_user_id',
        'ord_num',
        'ord_name',
        'ord_code',
        'from_date',
        'to_date',
        'created_id',
        'isActive'
    ];

    public function empExp(): BelongsTo
    {
        return $this->belongsTo(HrEmpExperience::class, 'id','hr_emp_order_id');
    }

    public static function orderType($key) {

        $lang_name = 'Type';

        $status_list = StatusList::where('type', 'hr_emp_status')->where('code', $key)->first();

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
