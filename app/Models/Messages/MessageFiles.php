<?php

namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class MessageFiles extends Model
{
    //
    protected $fillable = [
        'message_id',
        'file_name',
        'file_hash',
        'file_path',
        'file_ext',
        'file_size',
    ];
}
