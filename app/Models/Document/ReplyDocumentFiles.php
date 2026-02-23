<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplyDocumentFiles extends Model
{
    use HasFactory;

    protected $fillable = [
        'step_id',
        'j_id',
        'file_name',
        'file_hash',
        'file_path',
        'file_ext',
        'file_size'
    ];
}
