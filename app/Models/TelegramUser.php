<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TelegramUser extends Model
{
    //
    use Notifiable;

    protected $fillable = ['telegram_user_id', 'telegram_username', 'user_identifier', 'status'];

    public function routeNotificationForTelegram()
    {
        return $this->telegram_user_id;
    }
}
