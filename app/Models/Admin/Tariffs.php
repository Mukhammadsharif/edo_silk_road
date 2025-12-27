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
}
