<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailCronLists extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_user',
        'journal_type',
        'from_user_name',
        'document_link',
        'doc_name',
        'status'
    ];
}
