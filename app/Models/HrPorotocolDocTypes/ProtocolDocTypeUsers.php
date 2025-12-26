<?php

namespace App\Models\HrPorotocolDocTypes;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolDocTypeUsers extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_type_id',
        'user_id',
        'order_number',
        'status'
    ];

}
