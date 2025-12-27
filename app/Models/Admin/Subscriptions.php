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
}
