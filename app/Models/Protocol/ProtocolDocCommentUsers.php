<?php

namespace App\Models\Protocol;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProtocolDocCommentUsers extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doc_com_id',
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
        return $this->hasOne(ProtocolDocComments::class, 'id', 'doc_com_id');
    }
}
