<?php

namespace App\Models\Journal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalExecutionStepRoles extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'j_exec_step_id',
        'role_id',
    ];
}
