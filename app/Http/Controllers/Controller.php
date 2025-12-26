<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
use App\Models\Admin\DocLog;
use App\Models\Admin\Role;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\HrPorotocolDocTypes\ProtocolDocTypeUsers;
use App\Models\MailCronLists;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use App\Jobs\SendTelegramMessageJob;

#[
    OA\Info(version: "1.0.0", description: "Your API Description", title: "Your API Title"),
    OA\Server(url: 'http://localhost:8000', description: "Local server"),
]

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    const STATUS_ACTIVE = 1;
    const STATUS_PASSIVE = 2;
    const STATUS_DELETE = 3;
    const RESPONSE_SUCCESS = 200;
    const RESPONSE_ERROR = 201;
    const PAGINATION_LIMIT = 25;
    const SMS_AUTH = 1;
    const SMS_AUTH_PASS_FOR = 2;
    const SMS_AUTH_NEW_LOG_PASS = 3;
    const SMS_USER_VERIFY = 4;

    protected $admin = 'Super_Admin';
    protected $chancellery = 'Office_Emp';
    protected $chairmanUser = 'Super_Emp';

    protected $showSuccessToast = 'showSuccessToast()';
    protected $showInfoToast = 'showInfoToast()';
    protected $showWarningToast = 'showWarningToast()';
    protected $showDangerToast = 'showDangerToast()';

    protected $link_url = '#';

    public function userWork($id)
    {
        // return object
        return UserWorkHistory::find($id);
    }

    public function authActiveUser()
    {
        // return object
        return UserWorkHistory::where('user_id', Auth::id())->where('status', 1)->orderBy('id', 'DESC')->first();
    }

    public function authActiveUserIds()
    {
        // return array
        return UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();
    }

    public function getWorkUserIds($id)
    {
        // return array
        //$user =  UserWorkHistory::find($id);

        return UserWorkHistory::where('user_id', $id)->pluck('id')->toArray();
    }

    public function authActiveUserRoleNames()
    {
        // return array
        $auth_roles = array();

        foreach ($this->authActiveUserRoles() as $key => $item) {
            $auth_roles[$key] = $item->name;
        }

        return $auth_roles;
    }

    public function authActiveUserRoles()
    {
        // return object
        $role_ids = UserRole::where('model_id', $this->authActiveUser()->user_id??0)->pluck('role_id')->toArray();

        return Role::whereIn('id', $role_ids)->get();
    }

    public function userWorkRoleNames($user_id)
    {
        // return array
        $auth_roles = array();

        foreach ($this->userWorkRoles($user_id) as $key => $item) {
            $auth_roles[$key] = $item->role_code;
        }

        return $auth_roles;
    }

    public function userWorkRoles($user_id)
    {
        // return object
        $role_ids = UserRole::where('user_id', $user_id)->pluck('role_id')->toArray();

        return Role::whereIn('id', $role_ids)->get();
    }

    public function getResolutionRole()
    {
        return DocumentExecutionRoles::where('doc_code', 'RESOL')->where('status', 1)->first();
    }
    public function getSignerRole()
    {
        return DocumentExecutionRoles::where('doc_code', 'SIGN')->where('status', 1)->first();
    }
    public function getAgreementRole()
    {
        return DocumentExecutionRoles::where('doc_code', 'AGREE')->where('status', 1)->first();
    }
    public function getExecutiveRole()
    {
        return DocumentExecutionRoles::where('doc_code', 'EXEC')->where('status', 1)->first();
    }

    public function getDepHeads()
    {
        // return object

        return UserWorkHistory::all();
    }

    public function protocolMembers()
    {
        return ProtocolDocTypeUsers::all();
    }

    public function uniqueId(): string
    {
        return (string) Uuid::uuid4();
    }

    public function uniqueHash($length): string
    {
        return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length).date('s');
    }

    public function uniqueDocCode(): string
    {
        return fake()->regexify('[A-Z]{2}').fake()->regexify('[0-9]{8}');
    }

    public function createLog($doc_id, $act_id, $log_type, $doc_name): true
    {
        // create log
        $log = new DocLog();
        $log->user_id = $this->authActiveUser()->id;
        $log->doc_id = $doc_id;
        $log->act_id = $act_id;
        $log->act_date = now();
        $log->log_type = $log_type;
        $log->doc_name = $doc_name;
        $log->computer_id = $_SERVER['SERVER_ADDR']??0;
        $log->save();

        return true;

        /*
         * yaratish = 1
         * uzgartirish = 2
         * ochirish = 3
         * kelishuvchiga yuborish = 4
         * imzoga yuborish = 5
         * kelishuvchi imzolash = 6
         * kelishuvchi bekor qilish = 7
         * kelishuvchi tahrirlashga qaytarish = 8
         * direktor rezol yaratish/imzolamoq (Login) = 9
         * direktor rezol yaratish/imzolamoq (ERI) = 10
         * direktor rezol bekor qilish = 11
         * direktor rezol tahrirlash qaytarish = 12
         * Ijrochi javob qaytarish = 13
         * Ijrochi quwimcha ijrochi quwiw = 14
         * Direktor hujjat ijrosini tugatish = 15
         * Xodim xodimdan kelgan hujjatni imzolash = 16
         * Xodim xodimdan kelgan hujjatni bekor qilish = 17
         * Xodim xodimdan kelgan hujjatni tahrir qaytarish = 18
         * Xodim xodimdan kelgan hujjatni ijrosini tugatish = 19
         * Protokol imzolash = 20
         * Imzolovchi imzoladi = 21
         * Imzolovchi bekor qilish = 22
         * Imzolovchi tahrirlashga qaytarish = 23
         * Ijro nazorati rezolyutsiya yaratdi = 24
         * Ijro nazorati tahrirlashga qaytardi = 25
         * Xodim Javob xatini uchirdi = 26
         * Izoh qoldirildi = 27
         * Rezolyutsiyaga yuborish = 28
         * Rezolyutsiya tahrirlandi = 29
         * Ijrochi ijroni tasdqiladi = 30
        */
    }

    public function createMailCron($to_user, $journal_type, $from_user_name, $document_link, $doc_name): true
    {
        // create mail cron
        $mail = new MailCronLists();
        $mail->to_user = $to_user;
        $mail->journal_type = $journal_type;
        $mail->from_user_name = $from_user_name;
        $mail->document_link = $document_link;
        $mail->doc_name = $doc_name;
        $mail->save();

        $this->createTelegramCron($to_user, $doc_name, $document_link);

        return true;

        /*
         * Status
         * yangi = 1
         * yuborildi = 2
         * xatolik = -1
         *
        */
    }

    public function createTelegramCron($to_user, $doc_name, $document_link): true
    {
        $message = "EDO tizimidan ".$doc_name." hujjati yuborildi.";
        $workUser   = UserWorkHistory::find($to_user);
        $user       = User::find($workUser->user_id);
        $userEmail  = $user->email;
        $appUrl     = env('APP_URL', 'https://demoedo.uztest.uz');
        $documentLink = $appUrl . '/' . $document_link;

        SendTelegramMessageJob::dispatch($userEmail, $message, $documentLink)->onQueue('send_telegram');
        return true;
    }

    public function latinToCyrillic($latinString): array|string
    {
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
        ];
        $lat = [
            'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','x','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','X','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
        ];
        return str_replace($lat, $cyr, $latinString);
    }

    public function generateUniqueLangSlug($model, $string, $field = 'key') {
        $slug = Str::slug($string);
        $original = $slug;
        $i = 1;

        while ($model::where($field, $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function generateUniqueLoginSlug($model, $string, $field = 'username') {
        $slug = Str::slug($string);
        $original = $slug;
        $i = 1;

        while ($model::where($field, $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }

}
