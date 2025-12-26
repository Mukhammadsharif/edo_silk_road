<?php

namespace App\Models\HrPorotocolDocTypes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolDocTypes extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'lang_id',
        'doc_code',
        'doc_hash',
        'order_number',
        'level_number',
        'role_id',
        'status'
    ];
}
