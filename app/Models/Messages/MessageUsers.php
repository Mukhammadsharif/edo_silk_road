<?php

namespace App\Models\Messages;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;

class MessageUsers extends Model
{
    //
    protected $fillable = [
        'message_id',
        'to_w_user_id',
        'status',
        'read_date'
    ];

    public function user()
    {
        return $this->belongsTo(UserWorkHistory::class, 'to_w_user_id');
    }
}
