<?php

namespace App\Models\Projects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardComment extends Model
{
    protected $fillable = [
        'card_id', 'user_id', 'body',
    ];

    protected static function booted(): void
    {
        static::creating(function (CardComment $comment) {
            $comment->user_id ??= auth()->id();
        });
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
