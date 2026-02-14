<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariffs extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'max_users',
        'max_documents',
        'price_monthly',
        'price_yearly',
        'status'
    ];

    public function getStatusBadgeAttribute()
    {
        return $this->status == 1
            ? '<span class="badge badge-pill badge-success">Faol</span>'
            : '<span class="badge badge-pill badge-danger">No faol</span>';
    }

    public function lang($column = 'name')
    {
        $locale = session()->get('locale', 'uz');
        $col = $column . '_' . $locale;
        return $this->$col ?? $this->$column;
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscriptions::class);
    }
}
