<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

//Schedule::command('demo:cron')->everyMinute();
/*Schedule::command('app:db_back_up')->daily();
Schedule::command('qr_code_key_gen:cron')->dailyAt('22:50');
Schedule::command('mail_send:cron')->everyFiveMinutes();*/
