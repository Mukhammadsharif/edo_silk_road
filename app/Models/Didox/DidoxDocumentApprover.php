<?php

namespace App\Models\Didox;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Согласующий по входящему документу Didox.
 */
class DidoxDocumentApprover extends Model
{
    public const PENDING  = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'didox_document_id',
        'user_id',
        'assigned_by',
        'status',
        'comment',
        'decided_at',
        'pkcs7',
        'signature_hex',
        'signer_serial',
        'signed_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'signed_at'  => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DidoxDocument::class, 'didox_document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
