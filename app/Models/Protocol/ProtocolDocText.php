<?php

namespace App\Models\Protocol;

use Illuminate\Database\Eloquent\Model;

class ProtocolDocText extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doc_id',
        'doc_text',
    ];
}
