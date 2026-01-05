<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'org_unit_id',
        'tariff_id',
        'starts_at',
        'ends_at',
        'status'
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
    ];

    public function tariff()
    {
        return $this->belongsTo(Tariffs::class);
    }

    public function organization()
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            1 => 'Faol',
            2 => 'Kutilmoqda',
            0 => 'Rad etilgan',
            default => 'Noma\'lum',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            1 => '<span class="badge badge-success">Faol</span>',
            2 => '<span class="badge badge-warning">Kutilmoqda</span>',
            0 => '<span class="badge badge-danger">Rad etilgan</span>',
            default => '<span class="badge badge-secondary">Noma\'lum</span>',
        };
    }
}
