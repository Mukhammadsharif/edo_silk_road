<?php

namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class OnlyOfficeDoc extends Model
{
    //
    public $timestamps = false;

    protected $fillable = [
        'doc_type',
        'doc_id',
        'doc_key',
        'doc_path',
        'created_at'
    ];
}
