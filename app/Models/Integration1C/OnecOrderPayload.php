<?php

namespace App\Models\Integration1C;

use App\Models\Document\DocumentJournals;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Структурированные данные кадрового приказа для выгрузки в 1С.
 *
 * В data лежат ЛОКАЛЬНЫЕ идентификаторы EDOLINE (user_id, dep_id, post_id,
 * work_id) и enum'ы контракта. GUID'ы 1С не храним — их резолвит
 * OnecGuidResolver на чтении, чтобы связка всегда была актуальной.
 */
class OnecOrderPayload extends Model
{
    protected $table = 'onec_order_payloads';

    protected $fillable = [
        'j_id',
        'doc_id',
        'journal_type_id',
        'org_unit_id',
        'order_type',
        'order_number',
        'created_date',
        'sign_date',
        'data',
        'missing_fields',
        'export_status',
        'exported_at',
        'export_error',
    ];

    protected $casts = [
        'data'           => 'array',
        'missing_fields' => 'array',
        'created_date'   => 'date',
        'sign_date'      => 'date',
        'exported_at'    => 'datetime',
    ];

    /**
     * Девять эндпоинтов контракта.
     * Четырнадцать типов документов 1С укладываются в них за счёт подтипов:
     * отпуска различаются vacation_type (5 значений), отсутствия — absence_type (2).
     */
    public const TYPES = [
        'hirings',
        'terminations',
        'transfers',
        'business_trips',
        'vacations',
        'absences',
        'additional_duties',
        'disciplinary_actions',
        'removal_of_disciplinary_actions',
    ];

    public const STATUS_NEW      = 'new';
    public const STATUS_EXPORTED = 'exported';
    public const STATUS_ERROR    = 'error';

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(DocumentJournals::class, 'j_id');
    }

    /* ─────────────────────── Скоупы под фильтры /get_list ─────────────────────── */

    public function scopeOfType(Builder $q, string $type): Builder
    {
        return $q->where('order_type', $type);
    }

    /** Только неподтверждённые 1С-ой приказы. */
    public function scopeNotExported(Builder $q): Builder
    {
        return $q->where('export_status', '!=', self::STATUS_EXPORTED);
    }

    public function scopeSignedBetween(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('sign_date', '>=', $from);
        if ($to)   $q->whereDate('sign_date', '<=', $to);

        return $q;
    }

    public function scopeModifiedSince(Builder $q, ?string $since): Builder
    {
        return $since ? $q->where('updated_at', '>=', $since) : $q;
    }

    public function markExported(): void
    {
        $this->update([
            'export_status' => self::STATUS_EXPORTED,
            'exported_at'   => now(),
            'export_error'  => null,
        ]);
    }
}
