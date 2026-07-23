<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\HR\HrEmpExperience;
use App\Models\Admin\HR\HrEmpOrder;
use App\Models\Admin\Role;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserPosition;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\Protocol\ProtocolDocTypeUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrEmpOrderController extends Controller
{
    //
    /*
        Order Type
        1 - Ishlaydi
        2 - Mehnat tatili
        3 - Boshqa lavozimga utish
        4 - Ishdan buwatiw
        5 - Oquv tatili
        6 - Kasallik tatili
        7 - GPX
        8 - Ishga qabul qilish
        9 - Boshqalar
        * */

    public function store(Request $request)
    {
        //
        $request->validate([
            'ord_name' => 'required',
            'ord_code' => 'required',
            'ord_num' => 'required',
            'from_date' => 'required'
        ]);

        if ($request->ord_code == 3) {
            $request->validate([
                'dep_id' => 'required',
                'post_id' => 'required',
            ]);
        }

        // ════════════════════════════════════════════════════════════════
        // ЗАЩИТА ОТ ADMIN LOCKOUT при увольнении (ord_code == 4):
        // Если увольняемый сотрудник — единственный активный IT_Admin
        // в этой организации, блокируем приказ. Иначе организация
        // останется без IT-администратора, и никто не сможет управлять
        // ролями, пользователями и т.д.
        //
        // Проверяем ДО любых записей в БД, чтобы откат был не нужен.
        // ════════════════════════════════════════════════════════════════
        if ((int) $request->ord_code === 4) {
            $itAdminRole = Role::where('name', 'IT_Admin')->first();
            $targetUser  = User::find($request->user_id);

            if ($itAdminRole && $targetUser) {
                $isItAdmin = UserRole::where('model_id', $targetUser->id)
                    ->where('role_id', $itAdminRole->id)
                    ->exists();

                if ($isItAdmin) {
                    $orgUnitId = $this->organizationId();

                    // Другие активные IT_Admin в этой же org
                    $otherOrgUserIds = User::where('org_unit_id', $orgUnitId)
                        ->where('status', 1)
                        ->where('id', '!=', $targetUser->id)
                        ->pluck('id')
                        ->toArray();

                    $otherItAdminsExist = !empty($otherOrgUserIds) && UserRole::where('role_id', $itAdminRole->id)
                            ->whereIn('model_id', $otherOrgUserIds)
                            ->exists();

                    if (!$otherItAdminsExist) {
                        return back()
                            ->withInput()
                            ->with(
                                'error',
                                __('field.cannot_dismiss_last_it_admin')
                                ?? 'Невозможно уволить: этот сотрудник — единственный IT_Admin в организации. Сначала назначьте роль IT_Admin другому сотруднику.'
                            );
                    }
                }
            }
        }

        $to_date = null;
        if ($request->to_date) {
            $to_date = date('Y-m-d', strtotime($request->to_date));
        }

        /*$lastHrEmpOrder = HrEmpOrder::where('user_id', $request->user_id)->orderBy('id', 'desc')->first();

        if ($lastHrEmpOrder) {
            if (!$lastHrEmpOrder->to_date) {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $subDay = Carbon::parse($from_date)->subDays(1);
                $lastHrEmpOrder->update([
                    'to_date' => $subDay
                ]);
            }
        }*/

        $user = User::find($request->user_id);

        // work_history именно в ТЕКУЩЕЙ организации, а не произвольный последний.
        // Приказ создаётся админом конкретной org для сотрудника в этой же org.
        $currentWorkInOrg = UserWorkHistory::where('user_id', $user->id)
            ->where('org_id', $this->organizationId())
            ->where('status', 1)
            ->orderByDesc('id')
            ->first();

        $createEmpOrder = new HrEmpOrder();
        $createEmpOrder->user_id = $request->user_id;
        $createEmpOrder->w_user_id = $currentWorkInOrg->id ?? 0;
        $createEmpOrder->ord_code = $request->ord_code;
        $createEmpOrder->ord_name = $request->ord_name;
        $createEmpOrder->ord_num = $request->ord_num;
        $createEmpOrder->from_date = date('Y-m-d', strtotime($request->from_date));
        $createEmpOrder->to_date = $to_date;
        $createEmpOrder->created_id = $this->authActiveUser()->id;
        $createEmpOrder->save();

        if ($request->dep_id) {
            $nextYear = Carbon::now()->addYear()->format('Y-m-d');

            // Деактивируем только work_history В ТЕКУЩЕЙ организации.
            // Если юзер числится в нескольких org — другие НЕ должны отключаться.
            $deactivateLastWorkUser = UserWorkHistory::where('user_id', $request->user_id)
                ->where('org_id', $this->organizationId())
                ->where('status', 1)
                ->get();
            if ($deactivateLastWorkUser) {
                foreach ($deactivateLastWorkUser as $item) {
                    $item->update([
                        'status' => 2,
                        'to_date' => Carbon::now()
                    ]);
                    ProtocolDocTypeUser::where('user_id', $item->id)->delete();
                }
            }

            $createWork = new UserWorkHistory();
            $createWork->user_id = $request->user_id;
            $createWork->dep_id = $request->dep_id;
            $createWork->post_id = $request->post_id;
            $createWork->from_date = date('Y-m-d', strtotime($request->from_date));
            $createWork->to_date = $nextYear;
            $createWork->status = 1;
            $createWork->org_id = $this->orgIdForDepartment($request->dep_id);
            $createWork->save();

            $userDepartment = Department::find($request->dep_id);
            $userPosition = UserPosition::find($request->post_id);
            // Xodim lavozimlari
            $createEmpExp = new HrEmpExperience();
            $createEmpExp->user_id = $request->user_id;
            $createEmpExp->w_user_id = $createWork->id;
            $createEmpExp->exp_name = $userDepartment->name_uz??'-';
            $createEmpExp->post_name = $userPosition->name_uz??'-';
            $createEmpExp->is_org = 'Y';
            $createEmpExp->dep_id = $request->dep_id;
            $createEmpExp->post_id = $request->post_id;
            $createEmpExp->from_date = date('Y-m-d', strtotime($request->from_date));
            $createEmpExp->to_date = null;
            $createEmpExp->exp_descr = '-';
            $createEmpExp->hr_emp_order_id = $createEmpOrder->id;
            $createEmpExp->created_id = $this->authActiveUser()->id;
            $createEmpExp->save();
        }

        if ($request->ord_code == 4) {
            // ════════════════════════════════════════════════════════════════
            // УВОЛЬНЕНИЕ (с учётом мульти-org)
            // ════════════════════════════════════════════════════════════════
            //
            // 1) Деактивируем work_history именно в ТЕКУЩЕЙ организации
            //    (которая выпускает приказ об увольнении).
            // 2) Если у юзера остались активные work_history в ДРУГИХ org —
            //    юзера не отключаем; если текущая org была активной — переключаем
            //    users.org_unit_id на одну из оставшихся (чтобы юзер при
            //    следующем входе попал в работающую org, а не в "пустую").
            // 3) Если это была ПОСЛЕДНЯЯ организация — деактивируем юзера
            //    (User::status = 3) как и раньше.
            // ════════════════════════════════════════════════════════════════

            $currentOrgId = $this->organizationId();

            // Деактивируем все активные work_history юзера в этой org
            $worksInThisOrg = UserWorkHistory::where('user_id', $user->id)
                ->where('org_id', $currentOrgId)
                ->where('status', 1)
                ->get();

            foreach ($worksInThisOrg as $work) {
                $work->update([
                    'status'  => 2,
                    'to_date' => Carbon::now(),
                ]);
                ProtocolDocTypeUser::where('user_id', $work->id)->delete();
            }

            // Сколько активных work_history осталось у юзера в других org?
            $remainingActive = UserWorkHistory::where('user_id', $user->id)
                ->where('status', 1)
                ->orderByDesc('id')
                ->get();

            if ($remainingActive->isEmpty()) {
                // Это была последняя организация — отключаем юзера полностью
                $user->update(['status' => 3]);
            } else {
                // Есть ещё работа в других org — юзера оставляем активным.
                // Если текущая выбранная org была именно той, откуда уволили —
                // переключаем org_unit_id на одну из оставшихся.
                if ((int) $user->org_unit_id === (int) $currentOrgId) {
                    $user->update(['org_unit_id' => $remainingActive->first()->org_id]);
                }
            }
        }

        return redirect()->route('hr.emp.order.view', [$user->id, $user->user_hash])
            ->with('success', __('message.task_completed_successfully'));
    }

    public function show($id)
    {
        //
        $user = User::findOrFail($id);

        $ordTypes = StatusList::where('type', 'hr_emp_status')->whereNotIn('code', [8])->orderBy('code')->get();

        $departments = Department::where('org_unit_id', $this->organizationId())->where('status', 1)->where('parent_id', 0)->get();

        return view('hr.admin.emp-order.create', compact('user', 'id', 'ordTypes', 'departments'));
    }

    public function edit($id)
    {
        //
        $model = HrEmpOrder::findOrFail($id);

        $user = User::findOrFail($model->user_id);

        $ordTypes = StatusList::where('type', 'hr_emp_status')->orderBy('code')->get();

        $departments = Department::where('org_unit_id', $this->organizationId())->where('status', 1)->where('parent_id', 0)->get();

        return view('hr.admin.emp-order.edit',compact('model', 'user', 'ordTypes', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ord_name' => 'required',
            'ord_num' => 'required',
            'from_date' => 'required'
        ]);

        if (($request->from_date > $request->to_date) && $request->to_date) {
            return back()->withErrors(['msg' => 'Davr oralig`i to`g`ri emas!']);
        }

        $isActive = $request->isActive??'P';

        $order = HrEmpOrder::find($id);

        $user = User::findOrFail($order->user_id);

        if ($order) {
            $order->update([
                'ord_name' => $request->ord_name,
                'ord_num' => $request->ord_num,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'created_id' => $this->authActiveUser()->id,
                'isActive' => $isActive
            ]);

            if ($request->dep_id) {
                $userWork = UserWorkHistory::where('id', $order->w_user_id)->where('user_id', $order->user_id)->first();
                if ($userWork) {
                    $userWork->update([
                        'dep_id' => $request->dep_id,
                        'post_id' => $request->post_id,
                        'from_date' => $request->from_date,
                    ]);
                    ProtocolDocTypeUser::where('user_id', $userWork->id)->delete();
                }

                $hrEmpExperience = HrEmpExperience::where('user_id', $order->user_id)->where('hr_emp_order_id', $order->id)->first();
                if ($hrEmpExperience) {
                    $userDepartment = Department::find($request->dep_id);
                    $userPosition = UserPosition::find($request->post_id);
                    $hrEmpExperience->update([
                        'exp_name' => $userDepartment->name_uz??'-',
                        'post_name' => $userPosition->name_uz??'-',
                        'dep_id' => $request->dep_id,
                        'post_id' => $request->post_id,
                        'from_date' => $request->from_date,
                        'to_date' => $request->to_date,
                        'exp_descr' => $order->ord_name,
                        'isActive' => $isActive
                    ]);
                }
            }
            $hrEmpExperience = HrEmpExperience::where('user_id', $order->user_id)->where('hr_emp_order_id', $order->id)->first();
            if ($hrEmpExperience) {
                $hrEmpExperience->update([
                    'from_date' => $request->from_date,
                    'isActive' => $isActive
                ]);
            }
            $userWork = UserWorkHistory::where('id', $order->w_user_id)->where('user_id', $order->user_id)->first();
            if ($userWork) {
                $userWork->update([
                    'from_date' => $request->from_date,
                ]);
            }
        }

        return redirect()->route('hr.emp.order.view', [$user->id, $user->user_hash])
            ->with('success', __('task_completed_successfully'));
    }
}
