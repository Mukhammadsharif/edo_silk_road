<?php

namespace App\Console\Commands;

use App\Mail\DocSendMail;
use App\Models\Admin\UserWorkHistory;
use App\Models\MailCronLists;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailSendListCron extends Command
{
    protected $signature = 'mail_send:cron';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $mail_lists = MailCronLists::where('status', 1)->whereDate('created_at', Carbon::today())->limit(100)->get();
        $cc = 0;
        if (count($mail_lists) > 0) {
            foreach ($mail_lists as $mail_list) {
                $cc+= 1;
                $title      = $mail_list->journal_type;
                $workUser   = UserWorkHistory::find($mail_list->to_user);
                $user       = User::find($workUser->user_id);
                $toEmail    = $user->email;
                $body       = 'Sizga EDO tizimidan "' . $mail_list->journal_type . '" hujjati yuborildi.';
                $toUserName = ($user->userPersonal->l_name ?? '') . ' ' . ( $user->userPersonal->f_name ?? '');
                $appUrl     = env('APP_URL', 'https://edoline.uz');
                $documentLink = $appUrl . '/' . $mail_list->document_link;
                $linkName   = $mail_list->doc_name ?? 'havolaga o`ting...';

                $details = collect([
                    'doc_type'      => $mail_list->doc_type,
                    'title'         => $title,
                    'body'          => $body,
                    'from_user_name'=> $mail_list->from_user_name,
                    'to_user_name'  => $toUserName,
                    'link_url'      => $documentLink,
                    'link_name'     => $linkName,
                ]);

                // # To test. You can change 'example@gmail.com' into yours and
                // # document links will be sent to your email address
                //$toEmail = 'mukhammadsharif7@gmail.com';
                try {
                    Mail::to($toEmail)->send(new DocSendMail($details));
                } catch (\Exception $e) {
                    Log::error("Failed to send email to {$toEmail}: {$e->getMessage()}");
                }

//
//                $telegramUsers = TelegramUser::where('user_identifier', $user->email)
//                    ->whereNotNull('telegram_user_id')
//                    ->get();
//
//                if ($telegramUsers->isNotEmpty()) {
//                    // Trim title to remove whitespace
//                    $title = trim($title);
//                    $telegramMessage = "EDO tizimidan '$title' hujjati yuborildi.";
//
//                    try {
//                        Notification::send($telegramUsers, new TelegramNotification($telegramMessage, $documentLink));
//                        Log::info("Telegram notification queued for {$user->email} for document: {$title}", [
//                            'chat_ids' => $telegramUsers->pluck('telegram_user_id')->toArray(),
//                            'document_link' => $documentLink,
//                        ]);
//                    } catch (\Exception $e) {
//                        Log::error("Failed to queue Telegram notification for {$user->email}: {$e->getMessage()}", [
//                            'exception' => $e->getTraceAsString(),
//                        ]);
//                    }
//                } else {
//                    Log::info("No Telegram account linked for user: {$user->email}");
//                }

                $mail_list->update([
                    'status' => 2
                ]);
            }
        }

        $deletedSendMessages = MailCronLists::where('status', 2)->where('created_at', '<', Carbon::now()->subDays(15))->get();
        if ($deletedSendMessages) {
            MailCronLists::where('status', 2)->where('created_at', '<', Carbon::now()->subDays(15))->delete();
        }

        $this->info('Message Send'.count($mail_lists). '/'.$cc);

        return 0;
    }
}
