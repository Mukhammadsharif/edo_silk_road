<?php

namespace App\Models\Projects;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = ['card_id', 'title', 'position'];
    public function card() { return $this->belongsTo(Card::class); }
    public function items() { return $this->hasMany(ChecklistItem::class)->orderBy('position'); }
}
