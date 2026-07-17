<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Участник группы согласующих (связка группа ↔ пользователь).
 */
class ApprovalGroupUser extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ApprovalGroup::class, 'group_id');
    }
}
