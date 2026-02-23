<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\HR\HrEmpExperience;
use App\Models\Admin\HR\HrEmpOrder;
use App\Models\Admin\UserAuthHistory;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    //
    public function profile()
    {
        $user = User::findOrFail(Auth::id());

        $activeRoles = UserRole::where('model_id', Auth::id())->get();

        $hrEmpOrders = HrEmpOrder::where('user_id', $user->id)->where('isActive', 'A')->orderBy('from_date')->get();

        $workHistories = UserWorkHistory::where('user_id', $user->id)->orderBy('id', 'DESC')->get();

        $authHistories = UserAuthHistory::where('user_id', $user->id)->orderBy('id', 'DESC')->limit(100)->get();

        $userExp = HrEmpExperience::where('user_id', $user->id)->where('isActive', 'A')->orderBy('from_date', 'ASC')->get();

        return view('admin.users.profile',
            compact('user', 'activeRoles', 'workHistories', 'authHistories', 'hrEmpOrders', 'userExp'));
    }

    public function personalUpdate(Request $request)
    {
        //
        $request->validate([
            'l_name' => 'required|max:100',
            'f_name' => 'required|max:100',
            'm_name' => 'max:100',
        ]);

        $model = UserPersonalInfo::where('user_id', Auth::id())->firstOrFail();

        $model->update([
            'l_name' => $request->l_name,
            'f_name' => $request->f_name,
            'm_name' => $request->m_name
        ]);

        return back()->with('success','User Info updated successfully');
    }

    public function passUpdate(Request $request)
    {
        //
        $this->validate($request, [
            'password' => 'required|min:6',
            'passwordConf' => 'required|min:6',
        ]);

        $password = $request->input('password');

        $passwordConf = $request->input('passwordConf');

        $user = User::findOrFail(Auth::id());

        if ($password != $passwordConf ) {

            $this->validate($request, [
                'password'  => 'confirmed|min:6',
            ]);

        } else {

            $this->validate($request, [
                'password' => 'required|min:6',
                'passwordConf' => 'required|min:6'
            ]);

            $password = Hash::make($password);

            $user->update([
                $user->password = $password,
            ]);

            return back()->with('success', 'Account updated successfully');
        }

        //return 0;

        return back()->with('success','Error!');
    }

    public function updateImage(Request $request){

        //print_r($request->all()); die;
        $user_id = $this->authActiveUser()->user_id;
        $img_name = 'img_'.$user_id.'.png';

        request()->validate([
            'profile_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $request->profile_img->move(public_path('profile_img/'), $img_name);

        return back();
    }

    public function deleteProfileImage($user_id)
    {
        $image_name = 'img_'.$user_id.'.png';
        $image_path = public_path('profile_img/'.$image_name);
        if(File::exists($image_path)) {
            File::delete($image_path);
        }
        return true;
    }
}
