<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\HR\HrEmpEducation;
use App\Models\Admin\HR\HrEmpExperience;
use App\Models\Admin\HR\HrEmpFamily;
use App\Models\Admin\HR\HrEmpOrder;
use App\Models\Admin\Role;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserPersonalNames;
use App\Models\Admin\UserPosition;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\User;
use App\Services\UserLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class HrEmpController extends Controller
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

        $search = User::where('org_unit_id', $this->organizationId())->orderBy('id', 'DESC');

        if (trim(''.$s_text.'')) {
            $search->whereHas('userPersonal', function ($query) use ($s_text) {
                $query->where(DB::raw("CONCAT_WS(' ', l_name, f_name, m_name)"), 'ILIKE', '%'.$s_text.'%');
            })->orWhere(function ($query1) use ($s_text) {
                $query1->where('email', 'ILIKE', '%'.$s_text.'%')->orWhere('username', 'ILIKE', '%'.$s_text.'%');
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
            's_role' => $s_role
        ]);

        $statusList = StatusList::where('type', 'user_status')->get();

        $userRoles = Role::where('status', 1)->orderBy('name')->get();

        return view('hr.admin.emp.index', compact('models', 's_text', 's_status',
            's_role', 'statusList', 'userRoles'));
    }

    public function create()
    {
        //
        $departments = Department::where('org_unit_id', $this->organizationId())->where('status', 1)->where('parent_id', 0)->get();

        return view('hr.admin.emp.create', compact('departments'));
    }

    public function store(Request $request)
    {
        //

        $request->validate([
            'email' => 'required|email|unique:users',
            'l_name_uz' => 'required|max:50',
            'f_name_uz' => 'required|max:50',
            'dep_id' => 'required',
            'post_id' => 'required',
        ],[
            'email.required' =>  __('validate.email_mandatory'),
            'l_name_uz.required' => __('validate.l_name_mandatory'),
            'f_name_uz.required' => __('validate.f_name_mandatory'),
            'dep_id.required' => __('validate.dep_id_mandatory'),
            'post_id.required' => 'validate.post_id_mandatory',
        ]);
        $username = $this->generateUniqueLoginSlug(User::class, $request->f_name_uz);

        $createUser = new User();
        $createUser->username = $username;
        $createUser->email = $request->email;
        $createUser->user_hash = $this->uniqueId();
        $createUser->password = Hash::make('123456');
        $createUser->status = 1;
        $createUser->org_unit_id = $this->organizationId();
        $createUser->save();

        $itAdminRole = Role::where('name', 'Dep_Emp')->first();
        if ($itAdminRole) {
            $createUser->assignRole([$itAdminRole->id]);
        }

        if ($createUser) {
            $createPersonal = new UserPersonalInfo();
            $createPersonal->user_id = $createUser->id;
            $createPersonal->l_name = $request->l_name_uz;
            $createPersonal->f_name = $request->f_name_uz;
            $createPersonal->m_name = $request->m_name_uz;
            $createPersonal->phone = $request->phone;
            $createPersonal->address = $request->address;
            $createPersonal->save();

            $personalName = new UserPersonalNames();
            $personalName->user_id = $createUser->id;
            $personalName->l_name_kr = $request->l_name_kr;
            $personalName->f_name_kr = $request->f_name_kr;
            $personalName->m_name_kr = $request->m_name_kr;
            $personalName->l_name_ru = $request->l_name_ru;
            $personalName->f_name_ru = $request->f_name_ru;
            $personalName->m_name_ru = $request->m_name_ru;
            $personalName->l_name_en = $request->l_name_en;
            $personalName->f_name_en = $request->f_name_en;
            $personalName->m_name_en = $request->m_name_en;
            $personalName->save();

            $nextYear = Carbon::now()->addYear()->format('Y-m-d');

            $createWork = new UserWorkHistory();
            $createWork->user_id = $createUser->id;
            $createWork->dep_id = $request->dep_id;
            $createWork->post_id = $request->post_id;
            $createWork->from_date = Carbon::now();
            $createWork->to_date = $nextYear;
            $createWork->status = 1;
            $createWork->org_id = $this->orgIdForDepartment($request->dep_id);
            $createWork->save();

            // Xodim buyruqlari
            $createEmpOrder = new HrEmpOrder();
            $createEmpOrder->user_id = $createUser->id;
            $createEmpOrder->w_user_id = $createWork->id;
            $createEmpOrder->ord_name = 'Ishga qabul qilish';
            $createEmpOrder->ord_num = 1;
            $createEmpOrder->ord_code = 8;
            $createEmpOrder->from_date = date('Y-m-d', strtotime(now()));
            $createEmpOrder->to_date = null;
            $createEmpOrder->created_id = $this->authActiveUser()->id;
            $createEmpOrder->save();

            $userDepartment = Department::find($request->dep_id);
            $userPosition = UserPosition::find($request->post_id);
            // Xodim lavozimlari
            $createEmpExp = new HrEmpExperience();
            $createEmpExp->user_id = $createUser->id;
            $createEmpExp->w_user_id = $createWork->id;
            $createEmpExp->exp_name = $userDepartment->name_uz??'-';
            $createEmpExp->post_name = $userPosition->name_uz??'-';
            $createEmpExp->is_org = 'Y';
            $createEmpExp->dep_id = $request->dep_id;
            $createEmpExp->post_id = $request->post_id;
            $createEmpExp->from_date = date('Y-m-d', strtotime(now()));
            $createEmpExp->to_date = null;
            $createEmpExp->exp_descr = 'Ishga qabul qilish';
            $createEmpExp->hr_emp_order_id = $createEmpOrder->id;
            $createEmpExp->created_id = $this->authActiveUser()->id;
            $createEmpExp->save();

            $this->userLogService->createUserLog($createUser->id, ['comment' => 'Created user']);
        }

        return Redirect::route('hr.emp.view', [$createUser->id, $createUser->user_hash])->with('success', __('message.task_completed_successfully'));
    }

    public function view($id, $user_hash)
    {
        $user = User::where('id', $id)->where('user_hash', $user_hash)->firstOrFail();

        $userWork = UserWorkHistory::where('user_id', $user->id)->orderBy('id', 'desc')->first();

        $personal = UserPersonalInfo::where('user_id', $user->id)->first();

        $hrEmpOrder = HrEmpOrder::where('user_id', $user->id)->where('isActive', 'A')->orderBy('from_date', 'DESC')->first();

        $hrEmpOrders = HrEmpOrder::where('user_id', $user->id)->where('isActive', 'A')->orderBy('from_date')->get();

        $departments = Department::where('org_unit_id', $this->organizationId())->where('parent_id', 0)->where('status', 1)->get();

        $activeRoles = UserRole::where('model_id', $user->currentWork->id??0)->get();

        $userExp = HrEmpExperience::where('user_id', $user->id)->where('isActive', 'A')->orderBy('from_date', 'ASC')->get();

        return view('hr.admin.emp.view', compact('user', 'userWork','personal', 'hrEmpOrder', 'hrEmpOrders', 'departments', 'activeRoles', 'userExp'));
    }

    public function infoView($id)
    {
        $user = User::findOrFail($id);

        $personal = UserPersonalInfo::where('user_id', $id)->firstOrFail();

        return view('hr.admin.emp.info-view',
            compact('user', 'personal'));
    }

    public function infoUpdate(Request $request, $id, $hash)
    {
        //
        $request->validate([
            'l_name_uz' => 'required|max:50',
            'f_name_uz' => 'required|max:50',
            'm_name_uz' => 'max:50',
            'doc_pin' => 'max:14',
            'doc_number' => 'max:9',
            'phone' => 'max:9',
        ],[
            'l_name_uz.required'=>'validate.l_name_mandatory',
            'f_name_uz.required'=>'validate.f_name_mandatory',
            'm_name_uz.required'=>'validate.m_name_mandatory',
            'doc_pin.required'=>'validate.doc_pin_mandatory',
            'doc_number.required'=>'validate.doc_number_mandatory',
            'phone.required'=>'validate.phone_mandatory'
        ]);

        $user = User::where('id', $id)->where('user_hash', $hash)->first();
        if ($user) {
            $userPersonalInfo = UserPersonalInfo::where('user_id', $id)->first();
            if ($userPersonalInfo) {
                $userPersonalInfo->update([
                    'l_name' => $request->l_name_uz,
                    'f_name' => $request->f_name_uz,
                    'm_name' => $request->m_name_uz,
                    'doc_pin' => $request->doc_pin,
                    'doc_number' => $request->doc_number,
                    'doc_type' => $request->doc_type,
                    'birthday' => $request->birthday,
                    'gender' => $request->gender,
                    'phone' => $request->phone,
                    'address' => $request->address
                ]);
            }

            $personalName = UserPersonalNames::where('user_id', $id)->first();
            if ($personalName) {
                $personalName->update([
                    'l_name_kr' => $request->l_name_kr,
                    'f_name_kr' => $request->f_name_kr,
                    'm_name_kr' => $request->m_name_kr,
                    'l_name_ru' => $request->l_name_ru,
                    'f_name_ru' => $request->f_name_ru,
                    'm_name_ru' => $request->m_name_ru,
                    'l_name_en' => $request->l_name_en,
                    'f_name_en' => $request->f_name_en,
                    'm_name_en' => $request->m_name_en,
                ]);
            } else {
                $personalName = new UserPersonalNames();
                $personalName->user_id = $id;
                $personalName->l_name_kr = $request->l_name_kr;
                $personalName->f_name_kr = $request->f_name_kr;
                $personalName->m_name_kr = $request->m_name_kr;
                $personalName->l_name_ru = $request->l_name_ru;
                $personalName->f_name_ru = $request->f_name_ru;
                $personalName->m_name_ru = $request->m_name_ru;
                $personalName->l_name_en = $request->l_name_en;
                $personalName->f_name_en = $request->f_name_en;
                $personalName->m_name_en = $request->m_name_en;
                $personalName->save();
            }

            $this->userLogService->editUserLog($id, ['comment' => 'Updated personal info']);
        }

        return back()->with('success', __('message.success_message'));
    }

    public function orderView($id)
    {
        $user = User::findOrFail($id);

        $models = HrEmpOrder::where('user_id', $id)->where('isActive', 'A')->orderBy('from_date')->get();

        return view('hr.admin.emp.order-view',
            compact('user', 'models'));
    }

    public function eduView($id)
    {
        $user = User::findOrFail($id);

        $models = HrEmpEducation::where('user_id', $id)->where('isActive', 'A')->orderBy('from_date')->get();

        return view('hr.admin.emp.edu-view',
            compact('user', 'models'));
    }

    public function famView($id)
    {
        $user = User::findOrFail($id);

        $models = HrEmpFamily::where('user_id', $id)->where('isActive', 'A')->get();

        return view('hr.admin.emp.fam-view',
            compact('user', 'models'));
    }

    public function expView($id)
    {
        $user = User::findOrFail($id);

        $models = HrEmpExperience::where('user_id', $id)->where('isActive', 'A')->orderBy('from_date')->get();

        return view('hr.admin.emp.exp-view',
            compact('user', 'models'));
    }
}
