<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentExecutionStepHis extends Model
{
    use HasFactory;

    protected $fillable = [
        'j_id',
        'from_user',
        'exec_type',
        'exec_text',
        'status'
    ];
}
