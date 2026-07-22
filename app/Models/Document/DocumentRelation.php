<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRelation extends Model
{
    protected $fillable = [
        'j_id',
        'related_j_id',
        'created_by',
        'status',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(DocumentJournals::class, 'j_id');
    }

    public function relatedJournal(): BelongsTo
    {
        return $this->belongsTo(DocumentJournals::class, 'related_j_id');
    }
}
