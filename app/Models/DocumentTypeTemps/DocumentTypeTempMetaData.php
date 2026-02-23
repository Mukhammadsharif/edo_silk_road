<?php

namespace App\Models\DocumentTypeTemps;

use Illuminate\Database\Eloquent\Model;

class DocumentTypeTempMetaData extends Model
{
    //
    protected $fillable = [
        'doc_id',
        'meta_data',
    ];
}
