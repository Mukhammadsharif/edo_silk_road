<?php

namespace App\Models\Didox;

use Illuminate\Database\Eloquent\Model;

/**
 * Входящий документ Didox (см. миграцию didox_documents).
 * Константы статусов повторяют легенду бизнес-процесса.
 */
class DidoxDocument extends Model
{
    /* --- list_status: поле в списке входящих --- */
    public const LIST_SIGNED            = 'signed';             // 🟢 Подписан в Didox
    public const LIST_ON_INTERNAL       = 'on_internal_review'; // 🟡 На внутреннем согласовании
    public const LIST_NOT_SIGNED        = 'not_signed';         // 🔴 Не подписан

    /* --- approval_status: поле в карточке (внутреннее согласование) --- */
    public const APPROVAL_APPROVED      = 'approved';   // 🟢 Прошёл внутреннее согласование
    public const APPROVAL_ON_REVIEW     = 'on_review';  // 🟡 На согласовании
    public const APPROVAL_NOT_SENT      = 'not_sent';   // ⚪ Не отправлен на согласование

    protected $fillable = [
        'org_unit_id',
        'owner',
        'didox_id',
        'doc_type',
        'doc_number',
        'doc_date',
        'partner_tin',
        'seller_tin',
        'buyer_tin',
        'owner_tin',
        'didox_status',
        'list_status',
        'approval_status',
        'edo_document_id',
        'raw',
        'received_at',
        'print_html',
        'print_cached_at',
    ];

    public const OWNER_INCOMING = 0;
    public const OWNER_OUTGOING = 1;

    protected $casts = [
        'owner'        => 'integer',
        'doc_date'     => 'date',
        'didox_status' => 'integer',
        'raw'          => 'array',
        'received_at'     => 'datetime',
        'print_cached_at' => 'datetime',
    ];

    /** Согласующие по документу (внутреннее согласование). */
    public function approvers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DidoxDocumentApprover::class, 'didox_document_id');
    }

    /** Человекочитаемая метка типа документа Didox. */
    public function docTypeLabel(): string
    {
        return DidoxDocType::LABELS[$this->doc_type] ?? $this->doc_type ?? '—';
    }
}
