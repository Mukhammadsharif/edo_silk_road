<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorkHistory extends Model
{
    protected $fillable = [
        'user_id',
        'org_id',
        'dep_id',
        'post_id',
        'from_date',
        'to_date',
        'status',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dep_id', 'id');
    }
    public function position(): BelongsTo
    {
        return $this->belongsTo(UserPosition::class, 'post_id', 'id');
    }
    public function personal(): BelongsTo
    {
        return $this->belongsTo(UserPersonalInfo::class, 'user_id', 'user_id');
    }
    public function userPost()
    {
        return $this->belongsTo(UserPosition::class, 'post_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
