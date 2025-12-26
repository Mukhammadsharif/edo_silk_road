<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class UserAuthHistory extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_name',
        'device_name',
        'json_data',
        'auth_date'
    ];

}
