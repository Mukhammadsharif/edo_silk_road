<?php

namespace App\Models\Projects;

use App\Models\Projects\Board;
use App\Models\Projects\Card;
use Illuminate\Database\Eloquent\Model;

class BoardLabel extends Model
{
    protected $fillable = ['board_id', 'name', 'color', 'position'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_label');
    }
}
