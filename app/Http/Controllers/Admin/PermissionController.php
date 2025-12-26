<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use App\Models\Admin\Permission;

class PermissionController extends Controller
{
    //
    public function __construct(protected LanguageService $languageService)
    {
        /*$this->middleware('permission:permission-list', ['only' => ['index']]);
        $this->middleware('permission:permission-create', ['only' => ['create','store']]);
        $this->middleware('permission:permission-edit', ['only' => ['update','edit']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);*/
    }

    public function index(Request $request)
    {
        $models = Permission::orderBy('id','DESC')->paginate(25);

        return view('admin.permission.index', compact('models'))->with('i', ($request->input('page', 1) - 1) * 25);
    }

    public function create()
    {
        //
        $languages = $this->languageService->getList();

        $parents = Permission::where('parent_id', 0)->get();

        return view('admin.permission.create', compact('parents', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:permissions,name'
            ]
        ]);

        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'permission_name' => $request->permission_name,
            'parent_id' => $request->parent_id
        ]);

        return redirect('admin/permissions')->with('status','Permission Created Successfully');
    }

    public function edit(Permission $permission)
    {
        //
        $languages = $this->languageService->getList();

        $parents = Permission::where('parent_id', 0)->get();

        return view('admin.permission.edit', compact('permission', 'parents', 'languages'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:permissions,name,'.$permission->id
            ]
        ]);

        $permission->update([
            'name' => $request->name,
            'permission_name' => $request->permission_name,
            'parent_id' => $request->parent_id
        ]);

        return redirect('admin/permissions')->with('status','Permission Updated Successfully');
    }

    public function destroy($id)
    {
        $model = Permission::find($id);
        $status = self::RESPONSE_ERROR;
        if ($model) {
            $status = self::RESPONSE_SUCCESS;
            $childs = Permission::where('parent_id', $id)->get();
            if ($childs) {
                foreach ($childs as $item) {
                    $item->delete();
                }
            }
            $model->delete();
        }

        return response()->json([
            'status' => $status,
            'message' => 'Success',
        ]);
    }
}
