<?php

namespace App\Models\Admin;

use App\Models\Journal\JournalTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class AppTemps extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        't_name',
        't_code',
        't_text',
        't_category',
        'created_id',
        'status'
    ];

    public function statusName()
    {
//        return $this->belongsTo(StatusList::class, 'isActive', 'status_code')
//            ->where('status_type', 'sts');
    }

    public static function lang($key) {

        $lang_name = 'Lang';

        $language = LanguageLine::find($key);

        if ($language) {
            $lang_name = __($language->group.'.'.$language->key);
        }

        return $lang_name;
    }

    public function apptemps()
    {
        return $this->belongsTo(JournalTypes::class, 'journal_id', 'id');
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
