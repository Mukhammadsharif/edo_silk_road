<?php

namespace App\Models\Protocol;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolDocTypeUser extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'doc_type_id',
        'user_id',
        'order_number',
        'role_id',
        'status',
    ];

    public function userWork(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class,'user_id');
    }
}
