<?php

namespace App\Models\ECP;

use Illuminate\Database\Eloquent\Model;

class UserSignatureKey extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'serial_number',
        'json_data',
        'key_status'
    ];
}
