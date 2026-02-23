<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCodeKeys extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doc_id',
        'doc_type',
        'key_code',
        'key_date'
    ];
}
