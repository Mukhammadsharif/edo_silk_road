<?php

namespace App\Models\Journal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalTypeControls extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
       'journal_type_id',
       'status'
    ];
}
