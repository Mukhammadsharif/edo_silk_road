<?php

namespace App\Models\Comment;

use App\Models\Admin\UserWorkHistory;
use App\Models\Protocol\ProtocolDocComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentCommentUser extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'doc_id',
        'user_id',
        'is_read',
        'is_read_date'
    ];

    public function userWork(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'user_id');
    }

    public function comment(): HasOne
    {
        return $this->hasOne(DocumentComment::class, 'id', 'comment_id');
    }
}
