<?php

namespace App\Models\Protocol;

use Illuminate\Database\Eloquent\Model;

class ProtocolDocConfigs extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [
        'protocol_type_id',
        'role_id',
        'order',
        'status',
    ];
}
