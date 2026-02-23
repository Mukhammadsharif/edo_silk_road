<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackUp extends Command
{
    //
    protected $signature = 'app:db_back_up';

    protected $description = 'Database backup';

    public function handle()
    {
        //
        $filename = "db_" . Carbon::now()->format('dmY') . ".gz";

        $command = "mysqldump --user=root --password='Edoeai123!@#' --host=127.0.0.1 medo_euro_asia  | gzip > "
            . storage_path() . "/app/backup/" . $filename;

        $returnVar = NULL;
        $output  = NULL;

        exec($command, $output, $returnVar);
        $this->info('Back Up 1');
        $today = Carbon::today();
        $year = $today->year;
        $month = $today->format('m');
        $day = $today->format('d');

        $file_get_real_path = "/var/www/html/medo_euro_asia/storage/app/backup/".$filename;

        Storage::disk('ftp_13')->put('db_backup/'.$year.'/'.$month.'/'.'medo_'.$day.'.gz', file_get_contents($file_get_real_path));

        unlink($file_get_real_path);

        $this->info('Back Up 2');

        return true;
    }
}
