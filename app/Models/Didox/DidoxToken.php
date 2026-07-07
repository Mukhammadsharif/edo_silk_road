<?php

namespace App\Models\Didox;

use Illuminate\Database\Eloquent\Model;

/**
 * User-токен Didox (см. миграцию didox_tokens).
 */
class DidoxToken extends Model
{
    protected $fillable = [
        'user_id',
        'org_unit_id',
        'tin',
        'token',
        'auth_method',
        'expires_at',
        'last_used_at',
        'used_count',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
        'used_count'   => 'integer',
    ];

    /** Токен ещё жив (с учётом запаса на рефреш). */
    public function isFresh(): bool
    {
        if (empty($this->token) || is_null($this->expires_at)) {
            return false;
        }

        $skew = (int) config('didox.token_refresh_skew_minutes', 10);

        return now()->addMinutes($skew)->lt($this->expires_at);
    }

    public function markUsed(): void
    {
        $this->forceFill([
            'last_used_at' => now(),
            'used_count'   => $this->used_count + 1,
        ])->save();
    }
}
