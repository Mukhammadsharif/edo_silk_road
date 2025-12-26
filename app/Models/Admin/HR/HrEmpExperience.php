<?php

namespace App\Models\Admin\HR;

use App\Models\Admin\Department;
use App\Models\Admin\UserPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmpExperience extends Model
{
    protected $fillable = [
        'user_id',
        'w_user_id',
        'exp_name',
        'post_name',
        'is_org',
        'dep_id',
        'post_id',
        'hr_emp_order_id',
        'from_date',
        'to_date',
        'exp_descr',
        'created_id',
        'isActive'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dep_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(UserPosition::class, 'post_id', 'id');
    }
}
