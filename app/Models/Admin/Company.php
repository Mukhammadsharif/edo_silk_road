<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $fillable = [
        'name',
        'parent_id',
        'is_branch',
        'status',
    ];
}
