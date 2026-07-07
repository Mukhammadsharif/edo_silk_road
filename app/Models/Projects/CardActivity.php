<?php

namespace App\Models\Projects;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CardActivity extends Model
{
    protected $fillable = ['card_id', 'user_id', 'type', 'description'];
    public function user() { return $this->belongsTo(User::class); }
    public function card() { return $this->belongsTo(Card::class); }
}
