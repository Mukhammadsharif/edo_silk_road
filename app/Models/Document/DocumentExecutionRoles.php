<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class DocumentExecutionRoles extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'lang_id',
        'doc_code',
        'status'
    ];

    public static function lang($key) {

        $lang_name = 'Lang';

        $language = LanguageLine::find($key);

        if ($language) {
            $lang_name = __($language->group.'.'.$language->key);
        }

        return $lang_name;
    }
}
