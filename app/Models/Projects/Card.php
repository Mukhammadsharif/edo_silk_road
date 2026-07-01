<?php

namespace App\Models\Projects;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $fillable = [
        'board_list_id', 'title', 'description', 'position',
        'due_date', 'label_color', 'priority', 'cover_color', 'assigned_to',
    ];

    protected $casts = [
        'due_date' => 'date',
        'position' => 'integer',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(BoardList::class, 'board_list_id');
    }
    public function labels()
    {
        return $this->belongsToMany(BoardLabel::class, 'card_label');
    }
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class)->latest();
    }

    // Удобный доступ к доске через список
    public function board(): BelongsTo
    {
        return $this->list->board();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'card_user')->withTimestamps();
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }

    public function activities()
    {
        return $this->hasMany(CardActivity::class)->latest();
    }
}
