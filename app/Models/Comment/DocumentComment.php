<?php

namespace App\Models\Comment;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentComment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'doc_id',
        'doc_type',
        'doc_text',
        'created_id',
        'status',
        'created_date',
    ];

    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'created_id');
    }

    public function commentUsers(): HasMany
    {
        return $this->hasMany(DocumentCommentUser::class, 'comment_id', 'id');
    }
}
