<?php

namespace App\Models\ECP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureDocumentType extends Model
{
    use HasFactory;

    protected $table = 'signature_document_types';

    public $timestamps = false;

    protected $fillable =[
        'doc_name',
        'doc_code',
        'status'
    ];
}
