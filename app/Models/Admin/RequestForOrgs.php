<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class RequestForOrgs extends Model
{
    //
    protected $fillable = [
        'user_id',
        'org_unit_id',
        'tax_number',
        'status',
    ];
}
