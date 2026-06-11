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
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
        'used_count'   => 'integer',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function organization()
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
