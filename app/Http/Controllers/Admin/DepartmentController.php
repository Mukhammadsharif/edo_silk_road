<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Admin\UserWorkHistory;
use App\Models\Journal\JournalTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{

    public function index(Request $request)
    {
        //
        $s_text = $request->input('s_text');

        $parentDepartments = Department::where('parent_id', 0)->where('status', 1)->orderBy('order_number')->get();

        $search = Department::where('status', 1);

        if (trim(''.$s_text.'')) {
            $search->where(DB::raw("CONCAT(`name_uz`, ' ', `name_kr`, ' ', `name_ru`, ' ', `name_en`)"), 'LIKE', "%".$s_text."%");
        }

        $departments = $search->orderBy('order_number')->paginate(25);

        $departments->appends([
            's_text' => $s_text
        ]);

        return view('admin.departments.index', compact('parentDepartments', 'departments', 's_text'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function create()
    {
        //
        $parentDepartments = Department::where('parent_id', 0)->where('status', 1)->orderBy('order_number')->get();

        $departments = Department::where('status', 1)->orderBy('order_number')->get();

        return view('admin.departments.create', compact('parentDepartments', 'departments'));
    }

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'name_uz' => 'required',
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

    public function edit($id)
    {
        //
        $model = Department::findOrFail($id);

        $parentDepartments = Department::where('parent_id', 0)->where('status', 1)->orderBy('order_number')->get();

        $departments = Department::where('status', 1)->orderBy('order_number')->get();

        $organizations = OrganizationUnits::where('status', 1)
            ->orderBy('level')
            ->get();

        return view('admin.departments.edit', compact('model', 'departments', 'parentDepartments', 'organizations'));
    }

    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'name_uz' => 'required',
            'order_number' => 'required',
        ]);

        $model = Department::find($id);

        if ($model) {

            if ($request->parent_id != $model->parent_id) {
                $model->update([
                    'status' => 2
                ]);

                $level = 1;

                $parent_department = Department::find($request->parent_id);
                if ($parent_department) {
                    $level = $parent_department->level_number + 1;
                }

                $create = new Department();
                $create->name_uz = $request->name_uz;
                $create->name_kr = $request->name_kr;
                $create->name_ru = $request->name_ru;
                $create->name_en = $request->name_en;
                $create->parent_id = $request->parent_id;
                $create->status = 1;
                $create->level_number = $level;
                $create->order_number = $request->order_number;
                $create->save();

            } else {
                $model->update([
                    'name_uz' => $request->name_uz,
                    'name_kr' => $request->name_kr,
                    'name_ru' => $request->name_ru,
                    'name_en' => $request->name_en,
                    'order_number' => $request->order_number,
                    'status' => $request->status,
                    'org_unit_id' => $request->org_unit_id,
                ]);

                foreach ($model->childs as $child) {
                    $child->update([
                        'org_unit_id' => $request->org_unit_id,
                    ]);
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

//    public function orgStructureData()
//    {
//        $departments = Department::select('id', 'parent_id', 'name_uz', 'status')->where('status', '1')->get();
//
//        $nodes = $departments->map(function ($dept) {
//            $usersHtml = '<ul style="list-style: none;">'; // Remove default bullet points
//
//            $dep_head = $this->getDepHeadUser($dept->dep_id);
//            $first_user = $dept->activeWorkers->first();
//
//            foreach ($dept->activeWorkers as $worker) {
//                $fullName = optional($worker->personal)->fullName();
//                $positionName = optional($worker->position)->name_uz;
//
//                // Handle localization for position
//                $lang = session('locale', 'uz');
//                if ($worker->position) {
//                    if ($lang === 'ru') {
//                        $positionName = $worker->position->name_ru;
//                    } elseif ($lang === 'en') {
//                        $positionName = $worker->position->name_en;
//                    }
//                }
//
//                $usersHtml .= "<li><img src='" . url('/images/profile_image1.png') .
//                    "' style='width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;'> {$fullName} ({$positionName})</li>";
//            }
//
//            $usersHtml .= '</ul>';
//
//            return [
//                'id' => $dept->id,
//                'pid' => $dept->parent_id,
//                'name' => $dept->name_uz,
//                'users' => $usersHtml,
//                'first_user' => $dep_head ? $dep_head->personal->shortName() : $first_user->personal->shortName(),
//                'first_user_position' => $dep_head ? $this->getUserPosition($dep_head) : $this->getUserPosition($first_user),
//                'img' => url('/images/profile_image1.png')
//            ];
//        });
//
//        return response()->json($nodes);
//    }

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
}
