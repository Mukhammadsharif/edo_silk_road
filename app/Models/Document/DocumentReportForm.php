<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class DocumentReportForm extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['document_id', 'fields'];

    protected $casts = [
        'fields' => 'array',
    ];

    public function document(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Documents::class, 'document_id');
    }

    public function entries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentReportFormEntry::class);
    }
}
