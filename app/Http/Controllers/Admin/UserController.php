<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\RequestForOrgs;
use App\Models\Admin\Role;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserPosition;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\User;
use App\Services\UserLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    //
    public function __construct(protected UserLogService $userLogService)
    {
        /*$this->middleware('permission:role-list', ['only' => ['index']]);
        $this->middleware('permission:role-create', ['only' => ['create','store','addPermissionToRole','givePermissionToRole']]);
        $this->middleware('permission:role-edit', ['only' => ['update','edit']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);*/
    }

    public function index(Request $request)
    {
        //
        $s_text = $request->input('s_text');

        $s_status = $request->input('s_status');

        $s_role = $request->input('s_role');

        $s_org = $request->input('s_org');

        $isSuperUser = $this->isSuperUser();

        if ($isSuperUser) {
            $search = User::orderBy('id', 'DESC');
        } else {
            $search = User::where('org_unit_id', $this->organizationId())->orderBy('id', 'DESC');
        }

        if (!in_array($this->admin, $this->authActiveUserRoleNames())) {
            $search->where('username', '!=', 'muksid');
        }

        if ($s_org){
            $search->where('org_unit_id', $s_org);
        }

        if (trim(''.$s_text.'')) {
            /*$s_text1 = $s_text;
            if (preg_match('/^[\p{Latin}\s0-9]+$/u', $s_text)) {
                $s_text1 = $this->latinToCyrillic($s_text);
            }*/
            $search->whereHas('userPersonal', function ($query) use ($s_text) {
                $query->where(DB::raw("CONCAT_WS(' ', l_name, f_name, m_name)"), 'like', '%'.$s_text.'%');
            });
        }

        if ($s_status){
            $search->where('status', $s_status);
        }

        if ($s_role){
            $user_role_ids = UserRole::where('role_id', $s_role)->pluck('model_id')->unique()->toArray();

            $search->whereIn('id', $user_role_ids);
        }

        $models = $search->paginate(25);

        $models->appends([
            's_text' => $s_text,
            's_status' => $s_status,
            's_role' => $s_role,
            's_org' => $s_org,
        ]);

        $statusList = StatusList::where('type', 'user_status')->get();

        $userRoles = Role::where('status', 1)->get();

        $organizations = OrganizationUnits::orderBy('id', 'ASC')->get();

        $pendingRequests = RequestForOrgs::where('org_unit_id', $this->organizationId())
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->get();

        $departments = Department::where('org_unit_id', $this->organizationId())->where('status', 1)->get();

        return view('admin.users.index', compact('models', 's_text', 's_status',
            's_role', 'statusList', 'userRoles', 'organizations', 's_org', 'isSuperUser', 'pendingRequests', 'departments'));
    }

//    public function acceptRequest(Request $request, $id)
//    {
//        $request->validate([
//            'dep_id' => 'required|exists:departments,id',
//            'post_id' => 'nullable|exists:user_positions,id'
//        ]);
//
//        $requestOrg = RequestForOrgs::findOrFail($id);
//
//        if ($requestOrg->org_unit_id !== $this->organizationId()) {
//            return redirect()->back()->with('error', __('field.unauthorized_action'));
//        }
//
//        if ($requestOrg->status !== 1) {
//            return redirect()->back()->with('error', 'field.subscription_request_already_reviewed');
//        }
//
//        $user = User::findOrFail($requestOrg->user_id);
//
//        $user->org_unit_id = $requestOrg->org_unit_id;
//        $user->save();
//
//        $nextYear = Carbon::now()->addYear()->format('Y-m-d');
//
//        $oldUserWorkHistory = UserWorkHistory::where('user_id', $user->id)->first();
//
//        if ($oldUserWorkHistory) $oldUserWorkHistory->update(['status' => 2]);
//
//        UserWorkHistory::create([
//            'user_id' => $user->id,
//            'org_id' => $this->organizationId(),
//            'dep_id' => $request->dep_id,
//            'post_id' => $request->post_id ?? 3,
//            'from_date' => Carbon::now()->format('Y-m-d'),
//            'to_date' => $nextYear,
//            'status' => 1
//        ]);
//
//        $requestOrg->status = 2;
//        $requestOrg->save();
//
//
//        return redirect()->route('users.index')->with('success', __('field.user_accepted_and_assigned'));
//    }

    public function acceptRequest(Request $request, $id)
    {
        $request->validate([
            'dep_id' => 'required|exists:departments,id',
            'post_id' => 'nullable|exists:user_positions,id'
        ]);

        $requestOrg = RequestForOrgs::findOrFail($id);

        if ($requestOrg->org_unit_id !== $this->organizationId()) {
            return redirect()->back()->with('error', __('field.unauthorized_action'));
        }

        if ($requestOrg->status !== 1) {
            return redirect()->back()->with('error', 'field.subscription_request_already_reviewed');
        }

        $organization = OrganizationUnits::find($this->organizationId());

        if (!$organization->currentSubscription) {
            return redirect()->back()->with('error', __('field.no_active_subscription'));
        }

        if (!$organization->canAddUser()) {
            return redirect()->back()->with('error', __('field.user_limit_reached'));
        }

        $user = User::findOrFail($requestOrg->user_id);

        $user->org_unit_id = $requestOrg->org_unit_id;
        $user->save();

        $nextYear = Carbon::now()->addYear()->format('Y-m-d');

        $oldUserWorkHistory = UserWorkHistory::where('user_id', $user->id)->first();

        if ($oldUserWorkHistory) $oldUserWorkHistory->update(['status' => 2]);

        UserWorkHistory::create([
            'user_id' => $user->id,
            'org_id' => $this->organizationId(),
            'dep_id' => $request->dep_id,
            'post_id' => $request->post_id ?? 3,
            'from_date' => Carbon::now()->format('Y-m-d'),
            'to_date' => $nextYear,
            'status' => 1
        ]);

        $requestOrg->status = 2;
        $requestOrg->save();

        return redirect()->route('users.index')->with('success', __('field.user_accepted_and_assigned'));
    }

    public function rejectRequest($id)
    {
        $requestOrg = RequestForOrgs::findOrFail($id);

        if ($requestOrg->org_unit_id !== $this->organizationId()) {
            return redirect()->back()->with('error',  __('field.unauthorized_action'));
        }

        $requestOrg->status = 3; // Rejected
        $requestOrg->save();

        return redirect()->route('users.index')->with('success', __('field.request_rejected_successfully'));
    }

    public function create()
    {
        //
        $departments = Department::where('status', 1)->where('parent_id', 0)->get();

        return view('admin.users.create',
            compact('departments', 'departments'));
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            'email' => 'required|email|unique:users',
            'l_name' => 'required|max:50',
            'f_name' => 'required|max:50',
            'dep_id' => 'required',
            'post_id' => 'required',
        ]);

        $createUser = new User();
        $createUser->username = 'login'.random_int(100000, 999999);
        $createUser->email = $request->email;
        $createUser->user_hash = $this->uniqueId();
        $createUser->password = Hash::make(123456);
        $createUser->status = 1;
        $createUser->save();

        if ($createUser) {
            $createPersonal = new UserPersonalInfo();
            $createPersonal->user_id = $createUser->id;
            $createPersonal->l_name = $request->l_name;
            $createPersonal->f_name = $request->f_name;
            $createPersonal->save();

            $nextYear = Carbon::now()->addYear()->format('Y-m-d');

            $createWork = new UserWorkHistory();
            $createWork->user_id = $createUser->id;
            $createWork->dep_id = $request->dep_id;
            $createWork->post_id = $request->post_id;
            $createWork->from_date = Carbon::now();
            $createWork->to_date = $nextYear;
            $createWork->status = 1;
            $createWork->save();

            $this->userLogService->createUserLog($createUser->id, ['comment' => 'Created user']);
        }

        return Redirect::route('users.index')->with('success', __('field.user_created_successfully'));
    }

    public function show($hash)
    {
        //
        $user = User::where('user_hash', $hash)->firstOrFail();

        $userWorking = UserWorkHistory::where('user_id', $user->id)->get();

        $userRoles = UserRole::where('model_id', $user->id)->get();

        $userWorkingHistories = UserWorkHistory::where('user_id', $user->id)->get();

        $statusList = StatusList::where('type', 'user_status')->get();

        return view('admin.users.show',
            compact('user', 'userWorking', 'userRoles', 'userWorkingHistories', 'statusList'));
    }

    public function edit($hash)
    {
        $user = User::where('user_hash', $hash)->firstOrFail();

        $personal = UserPersonalInfo::where('user_id', $user->id)->first();

        $departments = Department::where('status', 1)->where('parent_id', 0)->get();

        $roleIf = Role::where('status', 1);

        if (!in_array($this->admin, $this->authActiveUserRoleNames())) {
            $roleIf->where('name', '!=', 'Super_Admin');
        }

        $roles = $roleIf->orderBy('name')->get();

        $userRoles = UserRole::where('model_id', $user->id)->pluck('role_id')->toArray();

        return view('admin.users.edit',
            compact('user', 'personal', 'departments', 'roles', 'userRoles'));
    }

    public function editPersonalInfo(Request $request, $id, $hash)
    {
        //
        $request->validate([
            'l_name' => 'required|max:50',
            'f_name' => 'required|max:50',
            'm_name' => 'max:50',
            'doc_pin' => 'max:14',
            'doc_number' => 'max:9',
            'phone' => 'max:9',
        ]);

        $user = User::where('id', $id)->where('user_hash', $hash)->first();
        if ($user) {
            $userPersonalInfo = UserPersonalInfo::where('user_id', $id)->first();
            if ($userPersonalInfo) {
                $userPersonalInfo->update([
                    'l_name' => $request->l_name,
                    'f_name' => $request->f_name,
                    'm_name' => $request->m_name,
                    'doc_pin' => $request->doc_pin,
                    'doc_number' => $request->doc_number,
                    'doc_type' => $request->doc_type,
                    'birthday' => $request->birthday,
                    'gender' => $request->gender,
                    'phone' => $request->phone
                ]);
            }

            $this->userLogService->editUserLog($id, ['comment' => 'Updated personal info']);
        }

        return back()->with('success', 'User personal info successfully updated');
    }

    public function editUserRoles(Request $request, $id, $hash)
    {
        //
        $user = User::where('id', $id)->where('user_hash', $hash)->first();
        if ($user) {

            UserRole::where('model_id', $id)->delete();

            if ($request->role_ids) {

                $roles = Role::whereIn('id', $request->role_ids)->get();

                if (count($roles) > 0) {

                    foreach ($roles as $role) {
                        $user->assignRole([$role->id]);
                    }
                }
            }

            $this->userLogService->editUserLog($id, ['comment' => 'Updated user roles', 'roles' => $request->role_ids]);

            Cache::forget("user_{$user->id}_menus");
        }

        return back()->with('success', 'User role assign successfully');
    }

    public function editUserDepartmentPosition(Request $request, $id, $hash)
    {
        //
        $request->validate([
            'dep_id' => 'required',
            'post_id' => 'required',
            'user_position_from_date' => 'required'
        ]);

        $user = User::where('id', $id)->where('user_hash', $hash)->first();
        if ($user) {

            UserWorkHistory::where('user_id', $id)->where('status', 1)->update(['status' => 2]);

            UserWorkHistory::where('user_id', $id)->orderBy('id', 'DESC')->take(1)->update(['to_date' => date('Y-m-d', strtotime(now()))]);

            $nextYear = Carbon::now()->addYear()->format('Y-m-d');

            $createWork = new UserWorkHistory();
            $createWork->user_id = $id;
            $createWork->dep_id = $request->dep_id;
            $createWork->post_id = $request->post_id;
            $createWork->from_date = $request->user_position_from_date;
            $createWork->to_date = $nextYear;
            $createWork->status = 1;
            $createWork->save();

            $this->userLogService->editUserLog($id, [
                'comment' => 'Updated user position',
                'dep_id' => $request->dep_id,
                'post_id' => $request->post_id
            ]);
        }

        return back()->with('success', 'User role assign successfully');
    }

    public function editUserLoginPassword(Request $request, $id, $hash)
    {
        //
        $this->validate($request, [
            'username' => 'min:3|unique:users,username,'.$id,
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)]
        ],[
            'email.unique' => __('validate.unique_email'),
        ]);

        $user = User::where('id', $id)->where('user_hash', $hash)->first();

        if ($user) {
            if (trim(''.$request->password.'')) {
                $this->validate($request, [
                    'password' => 'required|confirmed|min:6',
                ]);
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
            }
            $user->update([
                'username' => $request->username,
                'email' => $request->email
            ]);

            $this->userLogService->editUserLog($id, ['comment' => 'Updated user login password', 'username' => $request->username]);
        }

        return back()->with('success', 'User password successfully updated');
    }

    public function editUserStatus(Request $request, $id, $hash)
    {
        //
        $user = User::where('id', $id)->where('user_hash', $hash)->first();

        if ($user) {
            $user->update([
                'status' => $request->user_status
            ]);

            $this->userLogService->editUserLog($id, ['comment' => 'Updated user status', 'status' => $request->user_status]);
        }

        $personal = UserPersonalInfo::where('user_id', $id)->first();

        if ($personal) {
            $personal->update([
                'sort' => $request->user_sort
            ]);
        }

        return back()->with('success', 'User status successfully updated');
    }

    public function editEcpStatus(Request $request, $id, $hash): RedirectResponse
    {
        //

        $user = User::where('id', $id)->where('user_hash', $hash)->first();

        $personal = UserPersonalInfo::where('user_id', $user->id)->first();

        $status = $request->status ? 1 : 2;

        if ($personal) {
            $personal->update([
                'doc_sign_type' => $status
            ]);
        }

        return back()->with('success', 'User status successfully updated');
    }

    public function getDepPos($id)
    {
        //
        $current_dep = Department::find($id);

        $lang = session()->get('locale');

        $dep_name = 'name_uz as name';
        if ($lang == 'ru') {
            $dep_name = 'name_ru as name';
        } elseif ($lang == 'en') {
            $dep_name = 'name_en as name';
        }

        $departments = Department::select('id', $dep_name)->where('parent_id', $id)->where('status', 1)->orderBy('order_number')->get();

        $last_level = Department::orderBy('level_number', 'DESC')->first();

        $positions = UserPosition::select('id', $dep_name)->where('status', 1)->orderBy('order_number')->get();

        return response()->json([
            'current_dep' => $current_dep,
            'departments' => $departments,
            'last_level' => $last_level->level_number??0,
            'positions' => $positions,
        ]);
    }

    public function checkEmail($str)
    {
        //
        $message = false;
        $user = User::where('email', $str)->first();

        if ($user) {
            $message = true;
        }

        return response()->json([
            'message' => $message,
        ]);
    }

    public function getPositions($depId)
    {
        $lang = session()->get('locale', 'uz');

        $nameColumn = $lang === 'ru' ? 'name_ru' : ($lang === 'en' ? 'name_en' : 'name_uz');

        $positions = UserPosition::select('id', DB::raw("COALESCE($nameColumn, name_uz) as name"))
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        return response()->json([
            'positions' => $positions
        ]);
    }

}
