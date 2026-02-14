<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserRole;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\Role;
use App\Models\Admin\Permission;
use Spatie\TranslationLoader\LanguageLine;

class RoleController extends Controller
{
    //
    public function __construct(protected LanguageService $languageService)
    {
        /*$this->middleware('permission:role-list', ['only' => ['index']]);
        $this->middleware('permission:role-create', ['only' => ['create','store','addPermissionToRole','givePermissionToRole']]);
        $this->middleware('permission:role-edit', ['only' => ['update','edit']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);*/
    }

    public function index()
    {
        //
        $models = Role::where('status', 1)->orderBy('status')->orderBy('name')->paginate(100);

        return view('admin.roles.index', compact('models'))
            ->with('i', (request()->input('page', 1) - 1) * 100);
    }

    public function create()
    {
        //
        $languages = $this->languageService->getList();

        return view('admin.roles.create', compact('languages'));
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            'name_uz' => [
                'required',
                'string',
                'unique:roles,name'
            ]
        ]);

        $lang_id = null;

        $lang_key = $this->generateUniqueLangSlug(LanguageLine::class, $request->name_uz);

        if (trim($request->name_uz)) {

            $lang =  LanguageLine::create([
                'group' => 'role',
                'key' => $lang_key,
                'text' => [
                    'uz' => $request->name_uz,
                    'ru' => $request->name_ru??$request->name_uz.' RU',
                    'en' => $request->name_en??$request->name_uz.' EN',
                ],
            ]);

            $lang_id = $lang->id;
        }

        Role::create([
            'name' => $request->name_uz,
            'lang_id' => $lang_id,
            'guard_name' => 'web',
            'status' => 1
        ]);

        return redirect()->route('roles.index')
            ->with('success', __('message.doc_created_success_message'));
    }

    public function edit(Role $role)
    {
        //
        $languages = $this->languageService->getList();

        return view('admin.roles.edit', compact('role', 'languages'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:roles,name,'.$role->id
            ]
        ]);

        $isActive = self::STATUS_PASSIVE;

        $status = $request->status;

        if ($status) {
            $isActive = self::STATUS_ACTIVE;
        }

        $role->update([
            'name' => $request->name,
            'lang_id' => $request->lang_id,
            'status' => $isActive
        ]);

        return redirect('admin/roles')->with('success', __('message.doc_updated_success_message'));
    }

    public function destroy($id)
    {
        //print_r($id); die;
        $model = Role::find($id);
        $status = self::RESPONSE_ERROR;
        $message = 'Xatolik mavjud!';
        if ($model) {
            $checkUserRole = UserRole::where('role_id', $id)->first();
            $message = 'Ushbu role Xodimda mavjud! ';
            if (!$checkUserRole) {
                $status = self::RESPONSE_SUCCESS;
                $model->update([
                    'status' => 2
                ]);
            }
        }

        return response()->json([
            'status' => $status,
            'success' => $message,
        ]);
    }

    public function addPermissionToRole($roleId)
    {
        //
        $parentPermissions = Permission::where('parent_id', 0)->get();
        $permissions = Permission::get();
        $role = Role::findOrFail($roleId);
        $rolePermissions = DB::table('role_has_permissions')
            ->where('role_has_permissions.role_id', $role->id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        return view('admin.roles.add-permissions', [
            'role' => $role,
            'parentPermissions' => $parentPermissions,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function givePermissionToRole(Request $request, $roleId)
    {
        $request->validate([
            'permission' => 'required'
        ]);

        $role = Role::findOrFail($roleId);
        $role->syncPermissions($request->permission);

        return redirect()->back()->with('success', 'Permissions added to role');
    }
}
