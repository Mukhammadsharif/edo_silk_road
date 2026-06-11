<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnecTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public $expiresAt,
        public string $orgName = ''
    ) {}

    public function build()
    {
        return $this->subject('Токен интеграции с 1С (EDOLINE)')
            ->view('emails.onec_token');
    }
}
