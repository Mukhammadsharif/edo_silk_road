<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitrixHashes extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [
        'type',
        'hash',
        'local_id'
    ];
}
