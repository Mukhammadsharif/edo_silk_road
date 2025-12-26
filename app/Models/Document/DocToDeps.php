<?php

namespace App\Models\Document;

use App\Models\Admin\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocToDeps extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable =[
        'j_id',
        'dep_id'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }
}
