<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\RequestForOrgs;
use App\Models\Admin\Role;
use App\Models\Admin\UserWorkHistory;
use App\Models\Journal\JournalTypes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{

    public function organizationsIndex(Request $request)
    {
        //
        if (!$this->isSuperUser()) {
            return  redirect()->route('departments.index', ['id' => $this->organizationId()]);
        }

        $s_text   = $request->input('s_text');
        $s_status = $request->input('s_status');

        $parentOrganizations = OrganizationUnits::where('parent_id', 0)->where('status', 1)->get();

        $search = OrganizationUnits::query();

        /* Статусы организации: 1 Faol, 2 Faol emas, 3 O'chirilgan.
         | По умолчанию показываем активные и неактивные — удалённые скрыты.
         | Явный выбор в фильтре имеет приоритет: выбрав «O'chirilgan»,
         | супер-админ увидит удалённые и сможет их восстановить. */
        if ($s_status) {
            $search->where('status', $s_status);
        } else {
            $search->whereIn('status', [1, 2]);
        }

        if (trim((string) $s_text)) {
            $search->where(function ($q) use ($s_text) {
                $q->where(
                    DB::raw("(name_uz || ' ' || name_kr || ' ' || name_ru || ' ' || name_en)"),
                    'ILIKE',
                    "%{$s_text}%"
                )
                    ->orWhereRaw('tax_number::text ILIKE ?', ["%{$s_text}%"]);
            });
        }

        $organizations = $search->paginate(25);

        $organizations->appends([
            's_text'   => $s_text,
            's_status' => $s_status,
        ]);

        return view('admin.organizations.index', compact('parentOrganizations', 'organizations', 's_text', 's_status'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function organizationsCreate()
    {
        //
        $parentOrganizations = OrganizationUnits::where('parent_id', 0)->where('status', 1)->get();

        $organizations = OrganizationUnits::where('status', 1)->get();

        $users = User::where('status', 1)->get();

        return view('admin.organizations.create', compact('organizations', 'parentOrganizations', 'users'));
    }

    public function organizationsStore(Request $request)
    {
        //
        $this->validate($request, [
            'name_uz' => 'required',
        ]);

        $parent_id = empty($request->parent_id) ? 0 : $request->parent_id;

        $level = 1;

        $parent_organization = OrganizationUnits::find($parent_id);
        if ($parent_organization) {
            $level = $parent_organization->level_number + 1;
        }

        OrganizationUnits::create([
            'code' => $this->uniqueCode(),
            'name_uz' => $request->name_uz,
            'name_kr' => $request->name_kr,
            'name_ru' => $request->name_ru,
            'name_en' => $request->name_en,
            'parent_id' => $parent_id??0,
            'tax_number' => $request->tax_number,
            'owner_user_id' => $request->owner_user_id??1,
            'level' => $level,
            'status' => 1
        ]);

        return redirect()->route('organizations.index')
            ->with('success', __('message.dep_updated'));

    }

    public function organizationsEdit($id)
    {
        //
        $model = OrganizationUnits::findOrFail($id);

        $parentOrganizations = OrganizationUnits::where('parent_id', 0)->where('status', 1)->get();

        $organizations = OrganizationUnits::where('status', 1)->where('id', '!=', $id)->get();

        $users = User::inOrganization($id)->where('status', 1)->get();

        return view('admin.organizations.edit', compact('model', 'organizations', 'parentOrganizations', 'users'));
    }

    public function organizationsUpdate(Request $request, $id)
    {
        //
        $this->validate($request, [
            'name_uz' => 'required',
            'tax_number' => 'required',
        ]);

        $model = OrganizationUnits::find($id);

        if ($model) {

            $level = 1;

            $parent_organization = OrganizationUnits::find($request->parent_id);
            if ($parent_organization) {
                $level = $parent_organization->level_number + 1;
            }

            $model->update([
                'name_uz' => $request->name_uz,
                'name_kr' => $request->name_kr,
                'name_ru' => $request->name_ru,
                'name_en' => $request->name_en,
                'status' => $request->status??1,
                'parent_id' => $request->parent_id??0,
                'tax_number' => $request->tax_number,
                'owner_user_id' => $request->owner_user_id??1,
                'level' => $level
            ]);

            if ($request->status == 2) {
                OrganizationUnits::where('parent_id', $id)->update(['status' => $request->status]);
            }

        }

        return redirect()->route('organizations.index')
            ->with('success', __('message.dep_updated'));
    }

    /**
     * DELETE /admin/organizations/destroy/{id} — удаление организации (только Super_Admin).
     *
     * Удаление МЯГКОЕ: status = 3 (O'chirilgan). Физически строку не трогаем —
     * на организацию завязаны документы, журналы, подписки, хранилище и токены
     * Didox, и каскадное удаление уничтожило бы историю документооборота.
     * Удалённую организацию видно через фильтр «O'chirilgan» и можно вернуть.
     *
     * Блокируем удаление, если внутри ещё есть активные сотрудники или живые
     * дочерние организации — иначе люди остались бы прикреплены к несуществующей
     * структуре. Сначала переведите их, потом удаляйте.
     */
    public function organizationsDestroy($id)
    {
        if (!$this->isSuperUser()) {
            return response()->json([
                'status'  => 403,
                'message' => "Ruxsat yo'q.",
            ], 403);
        }

        $model = OrganizationUnits::find($id);

        if (!$model) {
            return response()->json(['status' => 404, 'message' => __('field.not_found')], 404);
        }

        // Активные сотрудники организации.
        $usersCount = UserWorkHistory::where('org_id', $model->id)
            ->where('status', 1)
            ->distinct('user_id')
            ->count('user_id');

        if ($usersCount > 0) {
            return response()->json([
                'status'  => 422,
                'message' => "Tashkilotda {$usersCount} ta faol xodim bor. Avval ularni boshqa tashkilotga o'tkazing.",
            ], 422);
        }

        // Живые дочерние организации.
        $childrenCount = OrganizationUnits::where('parent_id', $model->id)
            ->whereIn('status', [1, 2])
            ->count();

        if ($childrenCount > 0) {
            return response()->json([
                'status'  => 422,
                'message' => "Tashkilotda {$childrenCount} ta quyi tashkilot bor. Avval ularni o'chiring.",
            ], 422);
        }

        $model->update(['status' => 3]);

        return response()->json([
            'status'  => 200,
            'message' => "Tashkilot o'chirildi.",
        ]);
    }

    /**
     * POST /admin/organizations/restore/{id} — вернуть удалённую организацию (Super_Admin).
     * Возвращает status = 1 (Faol). Дочерние не трогаем: они восстанавливаются
     * отдельно и осознанно.
     */
    public function organizationsRestore($id)
    {
        if (!$this->isSuperUser()) {
            return response()->json([
                'status'  => 403,
                'message' => "Ruxsat yo'q.",
            ], 403);
        }

        $model = OrganizationUnits::find($id);

        if (!$model) {
            return response()->json(['status' => 404, 'message' => __('field.not_found')], 404);
        }

        $model->update(['status' => 1]);

        return response()->json([
            'status'  => 200,
            'message' => 'Tashkilot tiklandi.',
        ]);
    }

    public function departmentsIndex(Request $request, $id)
    {
        //
        $s_text = $request->input('s_text');

        $parentDepartments = Department::where('org_unit_id', $id)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        $search = Department::where('org_unit_id', $id)->where('status', 1);

        if (trim(''.$s_text.'')) {
            $search->where(DB::raw("CONCAT(`name_uz`, ' ', `name_kr`, ' ', `name_ru`, ' ', `name_en`)"), 'ILIKE', "%".$s_text."%");
        }

        $departments = $search->orderBy('order_number')->paginate(25);

        $departments->appends([
            's_text' => $s_text
        ]);

        $organization_id = $id;

        return view('admin.departments.index', compact('parentDepartments', 'departments', 's_text', 'organization_id'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function departmentsCreate($id)
    {
        //
        $parentDepartments = Department::where('org_unit_id', $id)->where('parent_id', 0)->where('status', 1)->orderBy('order_number')->get();

        $departments = Department::where('org_unit_id', $id)->where('status', 1)->orderBy('order_number')->get();

        $organization_id = $id;

        return view('admin.departments.create', compact('parentDepartments', 'departments', 'organization_id'));
    }

    public function departmentsStore(Request $request)
    {
        //
        $this->validate($request, [
            'name_uz' => 'required',
            'organization_id' => 'required',
        ]);

        $parent_id = empty($request->parent_id) ? 0 : $request->parent_id;

        $level = 1;

        $parent_department = Department::find($parent_id);
        if ($parent_department) {
            $level = $parent_department->level_number + 1;
        }

        $ord_number = JournalTypes::where('parent_id', $request->parent_id)->orderBy('id', 'desc')->first();

        if ($ord_number) {
            $order = $ord_number->order_number + 1;
        }

        $last_number = JournalTypes::where('parent_id', 0)->orderBy('id', 'desc')->first();

        $new_ord = $last_number->order_number +1;

        Department::create([
            'code' => $this->uniqueCode(),
            'org_unit_id' => $request->organization_id,
            'name_uz' => $request->name_uz,
            'name_kr' => $request->name_kr,
            'name_ru' => $request->name_ru,
            'name_en' => $request->name_en,
            'parent_id' => $parent_id,
            'order_number' => $order??$new_ord,
            'level_number' => $level,
            'status' => 1
        ]);

        return redirect()->route('departments.index', ['id' => $request->organization_id])
            ->with('success', __('message.dep_updated'));

    }

    public function departmentsEdit($id)
    {
        //
        $model = Department::findOrFail($id);

        $parentDepartments = Department::where('org_unit_id', $model->org_unit_id)->where('parent_id', 0)->where('status', 1)->orderBy('order_number')->get();

        $departments = Department::where('org_unit_id', $model->org_unit_id)->where('status', 1)->orderBy('order_number')->get();

        if ($this->isSuperUser()) {
            $organizations = OrganizationUnits::where('status', 1)
                ->orderBy('level')
                ->get();
        } else {
            $organizations = OrganizationUnits::where('id', $this->organizationId())
                ->where('status', 1)
                ->orderBy('level')
                ->get();
        }

        return view('admin.departments.edit', compact('model', 'departments', 'parentDepartments', 'organizations'));
    }

    public function departmentsUpdate(Request $request, $id)
    {
        //
        $this->validate($request, [
            'name_uz' => 'required',
            'order_number' => 'required',
            'org_unit_id' => 'required',
        ]);

        $model = Department::find($id);

        if ($model) {

            $level = 1;

            $parent_department = Department::find($request->parent_id);

            if ($parent_department) {
                $level = $parent_department->level_number + 1;
            }

            // Что изменилось — фиксируем ДО обновления.
            $parentChanged = (int) $request->parent_id !== (int) $model->parent_id;
            $orgChanged    = (int) $request->org_unit_id !== (int) $model->org_unit_id;

            // Сохраняем всегда, а не только при смене родительского отдела —
            // иначе правки названий (uz/kr/ru/en), порядка и статуса молча терялись,
            // хотя пользователю показывалось сообщение об успехе.
            $model->update([
                'name_uz'      => $request->name_uz,
                'name_kr'      => $request->name_kr,
                'name_ru'      => $request->name_ru,
                'name_en'      => $request->name_en,
                'parent_id'    => $request->parent_id,
                'level_number' => $level,
                'order_number' => $request->order_number,
                'status'       => $request->status,
                'org_unit_id'  => $request->org_unit_id,
            ]);

            // Отдел переехал в другую организацию — переносим и дочерние отделы,
            // иначе поддерево осталось бы в старой организации.
            if ($orgChanged || $parentChanged) {
                foreach ($model->childs as $child) {
                    $child->update([
                        'org_unit_id' => $request->org_unit_id,
                    ]);
                }
            }

            // Отключение отдела гасит дочерние.
            if ($request->status == 2) {
                Department::where('parent_id', $id)->update(['status' => $request->status]);
            }
        }

        return redirect()->route('departments.index', ['id' => $request->org_unit_id])
            ->with('success', __('message.dep_updated'));
    }

    public function destroy($id)
    {
        //
    }

    public function sendRequest(Request $request)
    {
        $tax_number = $request->input('tax_number');

        $validator = Validator::make($request->all(), [
            'tax_number'=> 'required|max:191',
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages()
            ]);
        } else {

            $organization = OrganizationUnits::where('tax_number', $tax_number)->first();

            if (!$organization) {
                return response()->json([
                    'status' => 400,
                    'errors' => ['value' => __('field.org_with_tin_was_not_registered')]
                ]);
            }

            $userId = Auth::id();

            // Уже есть pending заявка в эту же организацию?
            $existingPending = RequestForOrgs::where('user_id', $userId)
                ->where('org_unit_id', $organization->id)
                ->where('status', 1)
                ->first();

            if ($existingPending) {
                return response()->json([
                    'status' => 400,
                    'errors' => ['value' => __('field.subscription_request_already_pending') ?? 'Заявка в эту организацию уже подана']
                ]);
            }

            // Юзер уже является активным членом этой организации?
            $alreadyMember = \App\Models\Admin\UserWorkHistory::where('user_id', $userId)
                ->where('org_id', $organization->id)
                ->where('status', 1)
                ->exists();

            if ($alreadyMember) {
                return response()->json([
                    'status' => 400,
                    'errors' => ['value' => __('field.already_member_of_this_org') ?? 'Вы уже являетесь сотрудником этой организации']
                ]);
            }

            // Удаляем старую rejected/accepted заявку в эту же org (если есть),
            // чтобы не плодить дубликаты. Заявки в ДРУГИЕ org не трогаем.
            RequestForOrgs::where('user_id', $userId)
                ->where('org_unit_id', $organization->id)
                ->delete();

            RequestForOrgs::create([
                'user_id'     => $userId,
                'org_unit_id' => $organization->id,
                'tax_number'  => $tax_number,
                'status'      => 1
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'Request Sent Successfully.'
            ]);
        }
    }

    public function cancelRequest(Request $request)
    {
        $userId = Auth::id();

        $requestForOrg = RequestForOrgs::where('user_id', $userId)
            ->where('status', 1) // только ожидающие
            ->first();

        if (!$requestForOrg) {
            return response()->json([
                'status' => 400,
                'errors' => ['error' => 'Faol so\'rov topilmadi.']
            ], 400);
        }

        $requestForOrg->delete();

        return response()->json([
            'status' => 200,
            'message' => 'So\'rov muvaffaqiyatli bekor qilindi.'
        ]);
    }

    public function orgStructure()
    {
        $isSuper = $this->isSuperUser();

        $organizations = $isSuper
            ? OrganizationUnits::where('status', 1)->orderBy('level')->orderBy('id')->get()
            : collect();

        $currentOrgId = (int) $this->organizationId();

        return view('admin.departments.org-structure', compact('organizations', 'isSuper', 'currentOrgId'));
    }

    public function orgStructureData(Request $request)
    {
        $lang = session('locale', 'uz');

        // Супер-админ выбирает организацию через селект (по умолчанию — своя).
        // Остальные всегда видят только свою организацию.
        if ($this->isSuperUser()) {
            $selectedOrg = (int) $request->input('org', 0);
            if (!$selectedOrg) {
                $selectedOrg = (int) $this->organizationId();
            }

            $organizations = OrganizationUnits::where('status', 1)
                ->where('id', $selectedOrg)->get();

            // Fallback на свою организацию, если выбранная недоступна.
            if ($organizations->isEmpty()) {
                $organizations = OrganizationUnits::where('status', 1)
                    ->where('id', $this->organizationId())->get();
            }
        } else {
            $organizations = OrganizationUnits::where('status', 1)
                ->where('id', $this->organizationId())->get();
        }

        $orgIds = $organizations->pluck('id')->map(fn ($v) => (int) $v)->all();

        $departments = Department::with(['activeWorkers.personal', 'activeWorkers.position'])
            ->where('status', 1)
            ->whereIn('org_unit_id', $orgIds)
            ->orderBy('order_number')
            ->get();

        // Множество присутствующих подразделений — чтобы «осиротевшие» дочерние
        // (чей родитель выключен/отсутствует) не превращались в отдельные корни.
        $presentDeptIds = $departments->pluck('id')->map(fn ($v) => (int) $v)->all();

        $localName = function ($nameUz, $nameRu, $nameEn) use ($lang) {
            if ($lang === 'ru') return $nameRu ?: $nameUz;
            if ($lang === 'en') return $nameEn ?: $nameUz;
            return $nameUz;
        };

        $nodeDataArray = [];

        // ── 1. Узлы-организации (корни дерева) ──
        // ВАЖНО: у корневого узла GoJS TreeModel ключ 'parent' должен ОТСУТСТВОВАТЬ.
        // Значение null недопустимо и приводит к ошибке "ParentKey ... is not a number or a string: null".
        foreach ($organizations as $org) {
            $nodeDataArray[] = [
                'key'       => 'o_' . $org->id,
                'name'      => '',
                'title'     => '',
                'dept'      => OrganizationUnits::lang($org->id),
                'pic'       => url('/images/profile_image1.png'),
                'users'     => '',
                'level'     => 0,
                'usersHtml' => '',
                'isOrg'     => true,
            ];
        }

        // ── 2. Узлы-подразделения ──
        foreach ($departments as $dept) {
            $usersList = [];
            $dep_head   = $this->getDepHeadUser($dept->id);
            $first_user = $dept->activeWorkers->first();

            $usersHtml = '<ul style="list-style: none; margin: 0; padding: 0; max-height: 50vh; overflow-y: auto">';

            foreach ($dept->activeWorkers as $worker) {
                $shortName = optional($worker->personal)->shortName() ?? '';
                $positionName = '';
                if ($worker->position) {
                    $positionName = $localName(
                        $worker->position->name_uz ?? '',
                        $worker->position->name_ru ?? '',
                        $worker->position->name_en ?? ''
                    );
                }

                $usersList[] = "{$shortName}{$positionName}";

                $usersHtml .= "<li style='display: flex; flex-direction: row; align-items: center'>
                                    <img src='" . url('/images/profile_image1.png') . "'
                                        style='width: 40px; height: 40px; vertical-align: middle; margin-right: 5px;'>
                                    <div>
                                        <span style='text-align: right'>{$shortName}</span>
                                        <br>
                                        <span style='text-align: right'>({$positionName})</span>
                                    </div>
                                </li>
                                <br>";
            }
            $usersHtml .= '</ul>';

            // Родитель: активное родительское подразделение, иначе — сама организация.
            if ($dept->parent_id && (int) $dept->parent_id !== 0
                && in_array((int) $dept->parent_id, $presentDeptIds, true)) {
                $parentKey = 'd_' . $dept->parent_id;
            } else {
                $parentKey = 'o_' . $dept->org_unit_id;
            }

            $headName  = optional(optional($dep_head)->personal)->shortName()
                ?? optional(optional($first_user)->personal)->shortName()
                ?? '';
            $headTitle = $dep_head ? $this->getUserPosition($dep_head)
                : ($first_user ? $this->getUserPosition($first_user) : '');

            $nodeDataArray[] = [
                'key'       => 'd_' . $dept->id,
                'name'      => $headName,
                'title'     => $headTitle,
                'dept'      => $localName($dept->name_uz, $dept->name_ru, $dept->name_en),
                'pic'       => url('/images/profile_image1.png'),
                'users'     => implode("\n", $usersList),
                'level'     => $dept->level_number,
                'parent'    => $parentKey,
                'usersHtml' => $usersHtml,
                'isOrg'     => false,
            ];
        }

        return response()->json([
            'class' => 'go.TreeModel',
            'nodeDataArray' => $nodeDataArray,
        ]);
    }
    public function getDepHeadUser($dep_id)
    {
        $dep_head_role = Role::whereIn('name', ['Dep_Head'])->first();
        return UserWorkHistory::whereHas('user', function ($query) use ($dep_head_role) {
            $query->whereHas('userRoles', function ($query2) use ($dep_head_role) {
                $query2->where('role_id', $dep_head_role->id);
            });
        })->where('dep_id', $dep_id)->where('status', 1)->first();
    }

    public function getUserPosition($worker)
    {
        $lang = session('locale', 'uz');

        if ($worker->position) {
            if ($lang === 'ru') {
                $positionName = $worker->position->name_ru;
            } elseif ($lang === 'en') {
                $positionName = $worker->position->name_en;
            } else {
                $positionName = $worker->position->name_uz;
            }
        }

        return $positionName??'';
    }

    public function uniqueCode(): string
    {
        return fake()->regexify('[A-Z]{2}').fake()->regexify('[0-9]{8}');
    }
}
