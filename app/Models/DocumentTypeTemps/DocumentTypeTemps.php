<?php

namespace App\Models\DocumentTypeTemps;

use App\Models\Admin\StatusList;
use App\Models\Journal\JournalTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\TranslationLoader\LanguageLine;

class DocumentTypeTemps extends Model
{
    //
    protected $fillable = [
        'doc_type_id',
        't_text',
        'status',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(JournalTypes::class, 'doc_type_id');
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
