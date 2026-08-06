<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Индивидуальный выбор контроля исполнения для free-sign документа.
 *
 * Одна запись на журнал (j_id unique). Отсутствие записи = выбор
 * не делался (старый документ или штатный) -> поведение по настройке
 * типа (journal_type_controls).
 *
 * Namespace App\Models — как у FreeSignDocumentField (модели free-sign
 * модуля живут здесь, не в App\Models\Document, чтобы не смешиваться
 * со штатными).
 */
class FreeSignJournalControl extends Model
{
    protected $table = 'free_sign_journal_controls';

    protected $fillable = [
        'j_id',
        'enabled',
        'resolution_required',
    ];

    protected $casts = [
        'enabled'             => 'boolean',
        'resolution_required' => 'boolean',
    ];

    /**
     * Обязательна ли резолюция для журнала перед подписью роли RESOL.
     *
     * Записи нет -> true (текущее строгое поведение, как у старых
     * и штатных документов).
     */
    public static function resolutionRequiredFor(int $journalId): bool
    {
        $value = static::where('j_id', $journalId)->value('resolution_required');

        return $value === null ? true : (bool) $value;
    }

    /**
     * Явный выбор контроля для журнала.
     *
     * @return bool|null true/false — выбор сделан; null — записи нет,
     *                   решать по настройке типа
     */
    public static function choiceFor(int $journalId): ?bool
    {
        $enabled = static::where('j_id', $journalId)->value('enabled');

        return $enabled === null ? null : (bool) $enabled;
    }
}
