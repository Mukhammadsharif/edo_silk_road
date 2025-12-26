<?php

namespace App\Models\Protocol;

use Illuminate\Database\Eloquent\Model;

class ProtocolDocDecision extends Model
{
    protected $fillable = [
        'doc_id',
        'doc_text',
        'doc_term',
        'doc_performer',
        'descr',
        'created_dep',
        'created_user',
        'status',
    ];
}
