<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\UserPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPostController extends Controller
{
    public function index(Request $request)
    {
        //
        if($this->isSuperUser()) {
            return $this->superUserIndex($request);
        } else {
            return $this->adminIndex($request);
        }
    }

    public function superUserIndex(Request $request)
    {
        $s_text = $request->input('s_text');

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

        return view('admin.user-posts.super-user-index', compact('organizations', 's_text'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function superUserPosts(Request $request, $orgId)
    {
        //
        $s_text = $request->input('s_text');

        $search = UserPosition::where('org_id', $orgId)->where('status', 1);

        if (trim(''.$s_text.'')) {
            $search->where(DB::raw("CONCAT(`name_uz`, ' ', `name_kr`, ' ', `name_ru`, ' ', `name_en`)"), 'LIKE', "%".$s_text."%");
        }

        $models = $search->orderBy('order_number')->paginate(25);

        $models->appends([
            's_text' => $s_text
        ]);

        $isSuperUser = $this->isSuperUser();

        return view('admin.user-posts.index', compact('models', 's_text', 'isSuperUser'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function adminIndex(Request $request)
    {
        //
        $s_text = $request->input('s_text');

        $search = UserPosition::where('org_id', $this->organizationId())->where('status', 1);

        if (trim(''.$s_text.'')) {
            $search->where(DB::raw("CONCAT(`name_uz`, ' ', `name_kr`, ' ', `name_ru`, ' ', `name_en`)"), 'LIKE', "%".$s_text."%");
        }

        $models = $search->orderBy('order_number')->paginate(25);

        $models->appends([
            's_text' => $s_text
        ]);

        $isSuperUser = $this->isSuperUser();

        return view('admin.user-posts.index', compact('models', 's_text', 'isSuperUser'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function create()
    {
        //
        if ($this->isSuperUser()) {
            $departments = collect();
        } else {
            $departments = Department::where('org_unit_id', $this->organizationId())->where('status', 1)->orderBy('order_number')->get();
        }

        $isSuperUser = $this->isSuperUser();

        $organizations = OrganizationUnits::where('status', 1)->get();

        return view('admin.user-posts.create', compact( 'departments', 'organizations', 'isSuperUser'));
    }

    public function getOrganizationDepartments($orgId)
    {

        return response()->json([
            'data' => Department::where('org_unit_id', $orgId)->where('status', 1)->get(),
            'status' => 200,
            'message' => 'success'
        ]);
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            'name_uz' => 'required|max:255',
            'dep_ids' => 'required',
            'name_kr' => 'max:255',
            'name_ru' => 'max:255',
            'name_en' => 'max:255',
        ]);

        $dep_ids = json_encode($request->dep_ids);

        $create = new UserPosition();
        $create->org_id = $request->org_id ?: $this->organizationId();
        $create->name_uz = $request->name_uz;
        $create->name_kr = $request->name_kr;
        $create->name_ru = $request->name_ru;
        $create->name_en = $request->name_en;
        $create->dep_ids = $dep_ids;
        $create->order_number = $request->order_number;
        $create->save();

        return redirect()->route('user-posts.index')
            ->with('success', __('message.task_completed_successfully'));
    }

    public function edit($id)
    {
        //
        $model = UserPosition::findOrFail($id);

        $dep_ids = [];
        if ($model->dep_ids && $model->dep_ids != 'null') {
            $dep_ids = json_decode($model->dep_ids, true);
        }

        $selected_deps = Department::whereIn('id', $dep_ids)->where('status', 1)->orderBy('order_number')->get();

        $un_selected_deps = Department::whereNotIn('id', $dep_ids)->where('status', 1)->orderBy('order_number')->get();

        return view('admin.user-posts.edit',compact('model', 'selected_deps', 'un_selected_deps'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'name_uz' => 'required|max:255',
            'dep_ids' => 'required',
            'name_kr' => 'max:255',
            'name_ru' => 'max:255',
            'name_en' => 'max:255',
        ]);

        $dep_ids = json_encode($request->dep_ids);

        $model = UserPosition::find($id);

        if ($model) {
            $model->update([
                'name_uz' => $request->name_uz,
                'name_kr' => $request->name_kr,
                'name_ru' => $request->name_ru,
                'name_en' => $request->name_en,
                'order_number' => $request->order_number,
                'dep_ids' => $dep_ids,
                'status' => $request->status
            ]);
        }


        return redirect()->route('user-posts.index')
            ->with('success', __('message.task_completed_successfully'));
    }

    public function destroy($id)
    {
        //
    }
}
