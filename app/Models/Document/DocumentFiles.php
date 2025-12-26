<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFiles extends Model
{
    use HasFactory;

    protected $fillable = [
        'j_id',
        'doc_id',
        'from_user',
        'file_name',
        'file_hash',
        'file_path',
        'file_ext',
        'file_size'
    ];
}
