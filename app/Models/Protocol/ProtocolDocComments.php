<?php

namespace App\Models\Protocol;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProtocolDocComments extends Model
{
    public $timestamps = false;

    protected $fillable = [
       'parent_id',
       'doc_id',
       'doc_text',
       'created_id',
       'status',
       'created_date',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProtocolDocument::class, 'doc_id');
    }

    public function userWork(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'created_id');
    }

    public function commentUsers(): HasMany
    {
        return $this->hasMany(ProtocolDocCommentUsers::class, 'doc_com_id', 'id');
    }
}
