<?php

namespace App\Console\Commands;

use App\Models\Document\DocumentJournals;
use App\Models\QrCodeKeys;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QrCodeKeyGenCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr_code_key_gen:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates random 4-digits keys for access via qr-code';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = Carbon::now()->subDay()->format('Y-m-d');

//        $cn_protocols = CnProtocol::where('status', 5)->get();
//
//        if ($cn_protocols->count()){
//            foreach ($cn_protocols as $protocol){
//                $check_protocol = QrCodeKeys::where('doc_id', $protocol->id)->where('doc_type', 'cn_protocol')->first();
//                if ($check_protocol){
//                    $check_protocol->update(['key_code' => rand(1000, 9999), 'key_date' => $date]);
//                } else {
//                    $qr_key = new QrCodeKeys();
//                    $qr_key->doc_id = $protocol->id;
//                    $qr_key->doc_type = 'cn_protocol';
//                    $qr_key->key_code = rand(1000, 9999);
//                    $qr_key->key_date = $date;
//                    $qr_key->save();
//                }
//            }
//        }
//
//        $com_protocols = ComProtocol::where('status', 5)->get();
//
//        if ($com_protocols->count()){
//            foreach ($com_protocols as $protocol){
//                $check_protocol = QrCodeKeys::where('doc_id', $protocol->id)->where('doc_type', 'com_protocol')->first();
//                if ($check_protocol){
//                    $check_protocol->update(['key_code' => rand(1000, 9999), 'key_date' => $date]);
//                } else {
//                    $qr_key = new QrCodeKeys();
//                    $qr_key->doc_id = $protocol->id;
//                    $qr_key->doc_type = 'com_protocol';
//                    $qr_key->key_code = rand(1000, 9999);
//                    $qr_key->key_date = $date;
//                    $qr_key->save();
//                }
//            }
//        }

        $out_docs = DocumentJournals::whereHas('journal', function ($query) {
            $query->where('doc_code', 'BASE_OUTGOING');
        })->whereIn('status', [9, 10])->get();

        if ($out_docs->count()){
            foreach ($out_docs as $doc){
                $check_doc = QrCodeKeys::where('doc_id', $doc->doc_id)->where('doc_type', 'out_doc')->first();
                if ($check_doc){
                    $check_doc->update(['key_code' => rand(1000, 9999), 'key_date' => $date]);
                } else {
                    $qr_key = new QrCodeKeys();
                    $qr_key->doc_id = $doc->doc_id;
                    $qr_key->doc_type = 'out_doc';
                    $qr_key->key_code = rand(1000, 9999);
                    $qr_key->key_date = $date;
                    $qr_key->save();
                }
            }
        }

        $work_docs = DocumentJournals::whereHas('journal', function ($query) {
            $query->whereNotIn('doc_code', ['MESS', 'BASE_OUTGOING']);
        })->whereIn('status', [9, 10])->get();

        if ($work_docs->count()){
            foreach ($work_docs as $doc){
                $check_doc = QrCodeKeys::where('doc_id', $doc->doc_id)->where('doc_type', 'work_doc')->first();
                if ($check_doc){
                    $check_doc->update(['key_code' => rand(1000, 9999), 'key_date' => $date]);
                } else {
                    $qr_key = new QrCodeKeys();
                    $qr_key->doc_id = $doc->doc_id;
                    $qr_key->doc_type = 'work_doc';
                    $qr_key->key_code = rand(1000, 9999);
                    $qr_key->key_date = $date;
                    $qr_key->save();
                }
            }
        }

        $this->info('Keys were generated successfully');

        return 1;
    }
}
