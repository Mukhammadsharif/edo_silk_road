<?php

namespace App\Models\Projects;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'title', 'is_done', 'position'];
    protected $casts = ['is_done' => 'boolean'];
    public function checklist() { return $this->belongsTo(Checklist::class); }
}
