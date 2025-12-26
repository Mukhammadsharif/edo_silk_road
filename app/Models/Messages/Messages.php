<?php

namespace App\Models\Messages;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    //

    protected $fillable = [
        'from_w_user_id',
        'text',
        'subject',
        'reply_message_id',
        'hash_code'
    ];

    public function files()
    {
        return $this->hasMany(MessageFiles::class, 'message_id');
    }

    public function sender()
    {
        return $this->belongsTo(UserWorkHistory::class, 'from_w_user_id');
    }

    public function replyMessage()
    {
        return $this->belongsTo(Messages::class, 'reply_message_id', 'id');
    }
}
