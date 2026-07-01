<?php

namespace App\Models\Projects;

use App\Models\Admin\OrganizationUnits;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Board extends Model
{
    protected $fillable = [
        'org_id', 'user_id', 'manager_id', 'title', 'color',
        'description', 'visibility', 'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Board $board) {
            $board->user_id ??= auth()->id();
            $board->org_id  ??= auth()->user()?->org_unit_id;
        });
    }

    public function lists(): HasMany
    {
        return $this->hasMany(BoardList::class)->orderBy('position');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function labels()
    {
        return $this->hasMany(BoardLabel::class)->orderBy('position');
    }
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'board_user')->withTimestamps();
    }
}
