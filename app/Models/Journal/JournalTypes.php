<?php

namespace App\Models\Journal;

use App\Models\Admin\AppTemps;
use App\Models\Admin\StatusList;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class JournalTypes extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'title',
        'lang_id',
        'doc_code',
        'order_number',
        'level_number',
        'status'
    ];

    public function parent()
    {
        return $this->belongsTo(JournalTypes::class, 'parent_id', 'id');
    }

    public function statusName()
    {
        return $this->belongsTo(StatusList::class, 'status', 'code')
            ->where('type', 'journal_type');
    }

    public function langName()
    {
        return $this->belongsTo(LanguageLine::class, 'lang_id');
    }

    public static function lang($key) {

        $lang_name = 'Lang';

        $language = LanguageLine::find($key);

        if ($language) {
            $lang_name = __($language->group.'.'.$language->key);
        }

        return $lang_name;
    }

    public function controlStatus()
    {
        return $this->hasOne(JournalTypeControls::class, 'journal_type_id', 'id');
    }

    public function childs() {

        return $this->hasMany(JournalTypes::class,'parent_id','id')
            ->orderBy('order_number')
            ->where('status', 1) ;
    }

    public function apptemp()
    {
        return $this->hasMany(AppTemps::class, 'journal_id', 'id');
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
