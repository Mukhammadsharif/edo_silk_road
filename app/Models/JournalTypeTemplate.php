<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Шаблон OnlyOffice типа журнала (v63). Один шаблон на тип.
 * Файл — docx на диске 'ftp'.
 */
class JournalTypeTemplate extends Model
{
    protected $table = 'journal_type_templates';

    protected $fillable = ['journal_type_id', 'file_path', 'orig_name'];

    /** Путь к шаблону типа на FTP или null. */
    public static function pathFor(?int $journalTypeId): ?string
    {
        if (!$journalTypeId) {
            return null;
        }

        return static::where('journal_type_id', $journalTypeId)->value('file_path');
    }
}
