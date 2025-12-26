<?php

namespace App\Models\Journal;

use App\Models\Document\DocumentExecutionRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalExecutionSteps extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'journal_type_id',
        'exec_role_id',
        'exec_status',
    ];

    public function executionRole()
    {
        return $this->belongsTo(DocumentExecutionRoles::class, 'exec_role_id');
    }

    public function executionStepRolesIds()
    {
        return $this->hasMany(JournalExecutionStepRoles::class, 'j_exec_step_id', 'id');
    }
}
