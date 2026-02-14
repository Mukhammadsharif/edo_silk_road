<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DemoCron extends Command
{

    protected $signature = 'demo:cron';

    protected $description = 'Command description';

    public function handle()
    {
        //
        Log::info('demo:ten executed at ' . now()->toDateTimeString());

        $this->info('demo:ten executed at ' . now()->toDateTimeString());

        return true;
    }
}
