<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Маркер «этот тип журнала — Внешаблонные документы (free-sign)».
 *
 * Мультитенантность: тип создаётся для каждой компании отдельно
 * (свой id, свободный doc_code) — признаком служит запись здесь.
 * Пометка: INSERT для journal_type_id (см. миграцию/документацию).
 */
class FreeSignJournalType extends Model
{
    protected $table = 'free_sign_journal_types';

    protected $fillable = ['journal_type_id'];

    /** Помечен ли тип как внешаблонный. */
    public static function isType(?int $journalTypeId): bool
    {
        return $journalTypeId
            && static::where('journal_type_id', $journalTypeId)->exists();
    }

    /** Помечен ли ХОТЯ БЫ ОДИН из типов (для org-scoped резолва по doc_code). */
    public static function anyMarked(iterable $journalTypeIds): bool
    {
        $ids = collect($journalTypeIds)->filter()->values();

        return $ids->isNotEmpty()
            && static::whereIn('journal_type_id', $ids)->exists();
    }
}
