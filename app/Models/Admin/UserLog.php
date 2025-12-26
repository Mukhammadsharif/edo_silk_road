<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLog extends Model
{

    protected $fillable = ['action', 'user_id', 'modified_by', 'details'];

    protected $casts = [
        'details' => 'json',
    ];

    # Relationship method for the user work history
    public function userWorkHistory(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'modified_by');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRoleNames(array $roleIds)
    {
        if (empty($roleIds)) return '-';

        $roles = Role::whereIn('id', $roleIds)->pluck('name')->toArray();

        $role_names = '';
        foreach ($roles as $role) {
            $role_names = (strlen($role_names) > 0) ? $role_names .', '. $role:$role;
        }
        return  $role_names;
    }
}
