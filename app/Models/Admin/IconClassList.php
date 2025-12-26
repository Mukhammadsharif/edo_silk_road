<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class IconClassList extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
        'status',
    ];
}
