<?php

namespace App\Models\Messages;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;

class MessageForwards extends Model
{
    //

    protected $fillable = [
        'message_id',
        'from_w_user_id',
        'to_w_user_id',
        'status',
        'read_date',
    ];

    public function toUser()
    {
        return $this->belongsTo(UserWorkHistory::class, 'to_w_user_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(UserWorkHistory::class, 'from_w_user_id');
    }

    public function user()
    {
        return $this->belongsTo(UserWorkHistory::class, 'to_w_user_id');
    }
}
