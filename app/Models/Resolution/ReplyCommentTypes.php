<?php

namespace App\Models\Resolution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplyCommentTypes extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'lang_id',
        'doc_code',
        'status'
    ];
}
