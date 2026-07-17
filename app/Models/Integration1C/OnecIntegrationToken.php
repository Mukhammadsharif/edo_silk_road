<?php

namespace App\Models\Integration1C;

use App\Models\Admin\OrganizationUnits;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OnecIntegrationToken extends Model
{
    protected $table = 'onec_integration_tokens';

    protected $fillable = [
        'token', 'org_unit_id', 'created_by', 'expires_at', 'last_used_at', 'used_count',
        'refresh_token', 'refresh_expires_at', 'refresh_used_at', 'parent_id',
    ];

    protected $casts = [
        'expires_at'         => 'datetime',
        'last_used_at'       => 'datetime',
        'used_count'         => 'integer',
        'refresh_expires_at' => 'datetime',
        'refresh_used_at'    => 'datetime',
    ];

    /** Истёк ли access-токен (используется в каждом запросе 1С) */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Истёк ли refresh-токен (после этого нужен человек и новый выпуск в кабинете) */
    public function isRefreshExpired(): bool
    {
        return $this->refresh_expires_at === null || $this->refresh_expires_at->isPast();
    }

    /** Уже обменян (ротация) — повторное предъявление считается компрометацией */
    public function isRefreshUsed(): bool
    {
        return $this->refresh_used_at !== null;
    }

    /** Можно ли по этому refresh-токену получить новый access-токен */
    public function isRefreshUsable(): bool
    {
        return $this->refresh_token !== null
            && !$this->isRefreshUsed()
            && !$this->isRefreshExpired();
    }

    public function organization()
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Токен, из которого этот получен по refresh */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
