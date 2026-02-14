<?php

namespace App\Models\Protocol;

use Illuminate\Database\Eloquent\Model;

class ProtocolDocFile extends Model
{
    protected $fillable = [
        'doc_id',
        'file_name',
        'file_hash',
        'file_path',
        'file_ext',
        'file_size',
    ];
}
