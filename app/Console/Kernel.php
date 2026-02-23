<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    //
    protected $commands = [
        //
        Commands\DemoCron::class,
        Commands\MailSendListCron::class,
        Commands\DatabaseBackUp::class,
        Commands\QrCodeKeyGenCron::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        //Test demo
       //$schedule->command('demo:cron')->everyMinute();

        // Database Back UP
      /*  $schedule->command('app:db_back_up')->daily();

        // Key generate for Qr Code
        $schedule->command('qr_code_key_gen:cron')->dailyAt('22:50');*/

    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
