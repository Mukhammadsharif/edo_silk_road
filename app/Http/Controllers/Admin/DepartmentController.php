<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Admin\UserWorkHistory;
use App\Models\Journal\JournalTypes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{

    public function organizationsIndex(Request $request)
    {
        //
        if (!$this->isSuperUser()) {
            return  redirect()->route('departments.index', ['id' => $this->organizationId()]);
        }

        $s_text = $request->input('s_text');

        $parentOrganizations = OrganizationUnits::where('parent_id', 0)->where('status', 1)->get();

        $search = OrganizationUnits::where('status', 1);

        if (trim(''.$s_text.'')) {
            $search->where(
                DB::raw("(name_uz || ' ' || name_kr || ' ' || name_ru || ' ' || name_en)"),
                'ILIKE',
                "%{$s_text}%"
            );
        }

        $organizations = $search->paginate(25);

        $organizations->appends([
            's_text' => $s_text
        ]);

        return view('admin.organizations.index', compact('parentOrganizations', 'organizations', 's_text'))
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
            'parent_id' => $parent_id,
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

        $users = User::where('status', 1)->get();

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
                'parent_id' => $request->parent_id,
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
            $search->where(DB::raw("CONCAT(`name_uz`, ' ', `name_kr`, ' ', `name_ru`, ' ', `name_en`)"), 'LIKE', "%".$s_text."%");
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
            'name_uz' => $request->name_uz,
            'name_kr' => $request->name_kr,
            'name_ru' => $request->name_ru,
            'name_en' => $request->name_en,
            'parent_id' => $parent_id,
            'order_number' => $order??$new_ord,
            'level_number' => $level,
            'status' => 1
        ]);

        return redirect()->route('departments.index')
            ->with('success', __('message.dep_updated'));

    }

    public function departmentsEdit($id)
    {
        //
        $model = Department::findOrFail($id);

        $parentDepartments = Department::where('org_unit_id', $model->org_unit_id)->where('parent_id', 0)->where('status', 1)->orderBy('order_number')->get();

        $departments = Department::where('org_unit_id', $model->org_unit_id)->where('status', 1)->orderBy('order_number')->get();

        $organizations = OrganizationUnits::where('status', 1)
            ->orderBy('level')
            ->get();

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


            if ($request->parent_id != $model->parent_id) {
                $model->update([
                    'name_uz' => $request->name_uz,
                    'name_kr' => $request->name_kr,
                    'name_ru' => $request->name_ru,
                    'name_en' => $request->name_en,
                    'parent_id' => $request->parent_id,
                    'level_number' => $level,
                    'order_number' => $request->order_number,
                    'status' => $request->status,
                    'org_unit_id' => $request->org_unit_id,
                ]);

                if ($model->childs) {
                    foreach ($model->childs as $child) {
                        $child->update([
                            'org_unit_id' => $request->org_unit_id,
                        ]);
                    }
                }
            }

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

    public function orgStructure()
    {
        return view('admin.departments.org-structure');
    }

    public function orgStructureData()
    {
        $departments = Department::select('id', 'parent_id', 'name_uz', 'status', 'level_number')->where('status', '1')->get();

        $nodeDataArray = $departments->map(function ($dept) {
            $usersList = [];

            $dep_head = $this->getDepHeadUser($dept->id);
            $first_user = $dept->activeWorkers->first();

            $usersHtml = '<ul style="list-style: none; margin: 0; padding: 0; max-height: 50vh; overflow-y: auto">'; // Remove default bullet points

            foreach ($dept->activeWorkers as $worker) {
                $shortName = optional($worker->personal)->shortName();
                $positionName = optional($worker->position)->name_uz;

                // Handle localization for position (as in your code)
                $lang = session('locale', 'uz');
                if ($worker->position) {
                    if ($lang === 'ru') {
                        $positionName = $worker->position->name_ru;
                    } elseif ($lang === 'en') {
                        $positionName = $worker->position->name_en;
                    }
                }

                $usersList[] = "{$shortName}"."{$positionName}";

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

            $node = [
                'key' => $dept->id,
                'name' => $dep_head ? $dep_head->personal->shortName() : ($first_user ? $first_user->personal->shortName() : ''),
                'title' => $dep_head ? $this->getUserPosition($dep_head) : ($first_user ? $this->getUserPosition($first_user) : ''),
                'dept' => $dept->name_uz,
                'pic' => url('/images/profile_image1.png'),
                'users' => implode("\n", $usersList),
                'level' => $dept->level_number,
                'parent' => $dept->parent_id??null,
                'usersHtml' => $usersHtml
            ];

            if ($dept->parent_id != 0) {
                $node['parent'] = $dept->parent_id;
            }

            return $node;
        });

        return response()->json([
            'class' => 'go.TreeModel',
            'nodeDataArray' => $nodeDataArray->toArray()
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
