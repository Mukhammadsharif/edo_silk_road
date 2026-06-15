<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'org_unit_id',
        'max_users',
        'max_documents',
        'duration_days',
        'signature',
        'status',
        'subscription_id',
        'generated_by',
        'activated_by',
        'activated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscriptions::class, 'subscription_id');
    }

    public function getStatusTextAttribute(): string
    {
        return match ((int) $this->status) {
            1 => 'Yaratilgan',
            2 => 'Faollashtirilgan',
            0 => 'Bekor qilingan',
            default => "Noma'lum",
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ((int) $this->status) {
            1 => '<span class="badge badge-pill badge-info">Yaratilgan</span>',
            2 => '<span class="badge badge-pill badge-success">Faollashtirilgan</span>',
            0 => '<span class="badge badge-pill badge-danger">Bekor qilingan</span>',
            default => '<span class="badge badge-pill badge-secondary">?</span>',
        };
    }
}
