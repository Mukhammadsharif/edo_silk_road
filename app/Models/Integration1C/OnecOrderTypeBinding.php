<?php

namespace App\Models\Integration1C;

use App\Models\Journal\JournalTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Соответствие журнала EDOLINE типу приказа в контракте 1С.
 *
 * Заполняется автоматически классификатором при первом обращении к журналу
 * и правится администратором в кабинете. Ручная правка (source = manual)
 * приоритетнее: классификатор её больше не перезаписывает.
 */
class OnecOrderTypeBinding extends Model
{
    protected $table = 'onec_order_type_bindings';

    protected $fillable = [
        'journal_type_id',
        'org_id',
        'doc_code',
        'title',
        'order_type',
        'subtype',
        'source',
        'confidence',
        'matched_by',
        'status',
    ];

    protected $casts = [
        'subtype'    => 'array',
        'confidence' => 'integer',
    ];

    public const SOURCE_AUTO   = 'auto';
    public const SOURCE_MANUAL = 'manual';

    public const STATUS_ACTIVE  = 'active';
    public const STATUS_IGNORED = 'ignored';

    public function journalType(): BelongsTo
    {
        return $this->belongsTo(JournalTypes::class, 'journal_type_id');
    }

    /** Готово к выгрузке: тип определён и журнал не исключён вручную. */
    public function isUsable(): bool
    {
        return $this->order_type !== null
            && $this->status === self::STATUS_ACTIVE;
    }

    /** Нужна проверка человеком: тип не распознан или уверенность низкая. */
    public function needsReview(): bool
    {
        if ($this->source === self::SOURCE_MANUAL) {
            return false;
        }

        // Сознательно исключённые (разделы, документы вне контракта)
        // разбирать не нужно — решение по ним уже принято
        if ($this->status === self::STATUS_IGNORED) {
            return false;
        }

        return $this->order_type === null
            || $this->confidence < \App\Services\Integration1C\OnecOrderTypeClassifier::CONFIDENCE_THRESHOLD;
    }
}
