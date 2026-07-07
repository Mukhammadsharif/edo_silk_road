<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Группа согласующих. Привязана к организации (org_unit_id).
 * Участники хранятся в таблице approval_group_users по user_id.
 */
class ApprovalGroup extends Model
{
    protected $fillable = [
        'org_unit_id',
        'name',
        'created_by',
        'status',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(ApprovalGroupUser::class, 'group_id');
    }

    /**
     * Массив user_id участников группы.
     */
    public function memberUserIds(): array
    {
        return $this->members()->pluck('user_id')->toArray();
    }

    public function organization()
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }
}
