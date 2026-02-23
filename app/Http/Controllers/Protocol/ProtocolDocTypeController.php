<?php

namespace App\Http\Controllers\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Admin\MenuList;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use App\Models\Admin\RoleMenu;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use App\Models\Protocol\ProtocolDocConfigs;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocTypeUser;
use App\Models\User;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\TranslationLoader\LanguageLine;

class ProtocolDocTypeController extends Controller
{
    public function __construct(protected LanguageService $languageService){}

    public function index(Request $request)
    {
        //
        if($this->isSuperUser()) {
            return $this->superUserIndex($request);
        } else {
            return $this->adminIndex();
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

        return view('document-settings.protocol.super-user-index', compact('organizations', 's_text'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function superUserOrgDocs(Request $request, $orgId)
    {
        $parentDocTypes = ProtocolDocType::where('org_id', $orgId)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        $models = ProtocolDocType::where('org_id', $orgId)
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->paginate(25);

        return view('document-settings.protocol.index', compact('models', 'parentDocTypes'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function adminIndex()
    {
        $parentDocTypes = ProtocolDocType::where('org_id', $this->organizationId())
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        $models = ProtocolDocType::where('org_id', $this->organizationId())
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->paginate(25);

        return view('document-settings.protocol.index', compact('models', 'parentDocTypes'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function create()
    {
        $languages = $this->languageService->getList();

        $isSuperUser = $this->isSuperUser();

        if ($isSuperUser){
            $docTypes = collect();
        } else {
            $docTypes = ProtocolDocType::where('status', 1)
                ->orderBy('order_number')
                ->get();
        }

        $organizations = OrganizationUnits::where('status', 1)->get();

        return view('document-settings.protocol.create', compact('languages', 'docTypes', 'organizations', 'isSuperUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_uz' => 'required|max:100',
        ]);

        try {
            DB::beginTransaction();

            $parent_id = $request->parent_id ?? 0;
            $level = 1;

            if ($parent_id) {
                $parent_department = ProtocolDocType::findOrFail($parent_id);
                $level = $parent_department->level_number + 1;
            }

            $lang_key = $this->generateUniqueLangSlug(LanguageLine::class, $request->name_uz);
            $lang = LanguageLine::create([
                'group' => 'document',
                'key' => $lang_key,
                'text' => [
                    'uz' => $request->name_uz,
                    'ru' => $request->name_ru ?? $request->name_uz . ' RU',
                    'en' => $request->name_en ?? $request->name_uz . ' EN',
                ],
            ]);

            $doc_code = 'PRT_' . $this->uniqueHash(4);

            // Create main role for protocol type
            $role = new Role();
            $role->guard_name = 'web';
            $role->name = $request->name_uz . ' Protocol';
            $role->lang_id = $lang->id;
            $role->status = 1;
            $role->save();

            $permission = Permission::where('name', 'Permissions')->first();

            // Create permissions
            Permission::create([
                'name' => 'secretary_access_' . $doc_code,
                'guard_name' => 'web',
                'parent_id' => $permission ? $permission->id : 0,
            ]);

            Permission::create([
                'name' => 'member_access_' . $doc_code,
                'guard_name' => 'web',
                'parent_id' => $permission ? $permission->id : 0,
            ]);
            // Create document type
            $docType = new ProtocolDocType();
            $docType->org_id = $request->org_id ?: $this->organizationId();
            $docType->parent_id = $parent_id;
            $docType->title = $request->name_uz;
            $docType->lang_id = $lang->id;
            $docType->doc_code = $doc_code;
            $docType->doc_hash = $this->uniqueHash(10);
            $docType->order_number = 50;
            $docType->level_number = $level;
            $docType->role_id = $role->id;
            $docType->secretary_role_id = 0;
            $docType->member_role_id = 0;
            $docType->status = 1;
            $docType->save();

            // Create secretary menu
            $menuList = new MenuList();
            $menuList->title = $request->name_uz . ' (Secretary)';
            $menuList->url_path = '/protocol/' . $docType->doc_hash;
            $menuList->code = $doc_code;
            $menuList->parent_id = 0;
            $menuList->lang_id = $lang->id;
            $menuList->icon_id = 29;
            $menuList->count = 1;
            $menuList->save();

            $roleMenu = new RoleMenu();
            $roleMenu->role_id = $role->id;
            $roleMenu->menu_id = $menuList->id;
            $roleMenu->menu_type = 1;
            $roleMenu->parent_id = 0;
            $roleMenu->order_number = 50;
            $roleMenu->status = 1;
            $roleMenu->save();

            DB::commit();

            return redirect()->route('protocol-types.edit', $docType->doc_hash)
                ->with('success', __('message.doc_created_success_message'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create protocol doc type: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => __('message.error_message')]);
        }
    }

    public function edit($doc_hash)
    {
        $model = ProtocolDocType::where('doc_hash', $doc_hash)->firstOrFail();

        $docTypes = ProtocolDocType::where('org_id', $model->org_id)
            ->where('id', '<>', $model->id)
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        $users = UserWorkHistory::with('position')
            ->join('user_positions', 'user_work_histories.post_id', '=', 'user_positions.id')
            ->where('user_work_histories.status', 1)
            ->join('users', 'user_work_histories.user_id', '=', 'users.id')
            ->where('users.org_unit_id', $model->org_id)
            ->orderBy('user_positions.order_number')
            ->select('user_work_histories.*')
            ->get();

        $languages = $this->languageService->getList();

        $selectedUsers = ProtocolDocTypeUser::where('doc_type_id', $model->id)
            ->pluck('role_id', 'user_id')
            ->toArray();

        $secretaryUserIds = $this->findSecretaryUserIds($model);

        $user_roles = StatusList::where('type', 'prt_permission_type')->orderBy('code')->get();

        $signRoleTypes = StatusList::where('type', 'prt_sign_type')->orderBy('code')->get();
        $protocolSignRoles = ProtocolDocConfigs::where('protocol_type_id', $model->id)->get();

        $savedCodes = $protocolSignRoles->pluck('role_id')->toArray();
        $orderMap = $protocolSignRoles->pluck('order', 'role_id');
        $signRoleTypes = $signRoleTypes->sortBy(function ($type) use ($orderMap) {
            return $orderMap[$type->code] ?? 9999;
        });

        $listGroupItemColors = ['list-group-item-primary', 'list-group-item-warning', 'list-group-item-success',
            'list-group-item-info', 'list-group-item-danger'];

        return view('document-settings.protocol.edit', compact('model', 'docTypes', 'users', 'languages', 'selectedUsers',
            'secretaryUserIds', 'user_roles', 'signRoleTypes',
            'protocolSignRoles', 'listGroupItemColors', 'savedCodes'));
    }
    private function findSecretaryUserIds(ProtocolDocType $model): array
    {
        $secretaryUsers = User::whereHas('permissions', function ($query) use ($model) {
            $query->where('name', "secretary_access_{$model->doc_code}");
        })->get();

        $secretaryUserIds = [];
        foreach ($secretaryUsers as $secretaryUser) {
            $userWorkHistory = UserWorkHistory::where('user_id', $secretaryUser->id)
                ->where('status', 1)
                ->first();
            if ($userWorkHistory) {
                $secretaryUserIds[] = $userWorkHistory->id;
            }
        }

        return $secretaryUserIds;
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'title' => 'required|max:100',
            'order_number' => 'required|integer',
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:user_work_histories,id',
        ]);

        try {
            DB::beginTransaction();

            $docType = ProtocolDocType::findOrFail($id);
            $newDocCode = $request->doc_code ?? $docType->doc_code;
            $parentId = $request->parent_id ?? 0;
            $level = $this->calculateLevel($parentId);

            $this->revokeExistingPermissions($docType->doc_code);

            $this->updateLanguageLine($docType->lang_id, $request->title, $request->name_ru, $request->name_en);

            $role = $this->updateOrCreateRole($docType->role_id, $request->title . ' Protocol', $docType->lang_id, $request->status);

            $this->updatePermissions($docType->doc_code, $newDocCode);

            $this->updateMenu($docType, $request->title, $newDocCode, $request->status);

            $docType->update([
                'parent_id' => $parentId,
                'title' => $request->title,
                'doc_code' => $newDocCode,
                'order_number' => $request->order_number,
                'level_number' => $level,
                'secretary_role_id' => 0, // Keep as 0 to align with store
                'member_role_id' => 0,   // Keep as 0 to align with store
                'status' => $request->status,
            ]);

            $secretaries = [];
            $members = [];
            $secretaryMembers = [];

            if ($request->has('user_id') && $request->has('user_role_id')) {
                foreach ($request->user_id as $userId) {
                    $roleId = $request->user_role_id[$userId] ?? null;

                    if ($roleId == 1) {
                        $secretaries[] = $userId;
                    } elseif ($roleId == 2) {
                        $members[] = $userId;
                    } elseif ($roleId == 3) {
                        $secretaryMembers[] = $userId;
                    }
                }
            }

            $this->assignUsers($docType, $members, $role->id, $newDocCode);
            $this->assignSecretaries($docType, $secretaries, $role->id, $newDocCode);
            $this->assignSecretaryMembers($docType, $secretaryMembers, $role->id, $newDocCode);

            DB::commit();

            return $request->status == 2
                ? redirect()->route('protocol-types.index')->with('success', __('message.prt_doc_type_updated'))
                : redirect()->back()->with('success', __('message.prt_doc_type_updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update protocol doc type: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => __('message.error_message')]);
        }
    }

    private function revokeExistingPermissions(string $docCode): void
    {
        $permissions = ['secretary_access_' . $docCode, 'member_access_' . $docCode];
        $users = User::whereHas('permissions', function ($query) use ($permissions) {
            $query->whereIn('name', $permissions);
        })->get();

        foreach ($users as $user) {
            foreach ($permissions as $permission) {
                $user->revokePermissionTo($permission);
            }
        }
    }
    private function calculateLevel($parentId): int
    {
        if (!$parentId) {
            return 1;
        }

        $parentDepartment = ProtocolDocType::findOrFail($parentId);
        return $parentDepartment->level_number + 1;
    }

    private function updateLanguageLine(?int $langId, string $title, ?string $nameRu, ?string $nameEn): void
    {
        $lang = LanguageLine::findOrFail($langId);
        $lang->update([
            'text' => [
                'uz' => $title,
                'ru' => $nameRu ?? $title . ' RU',
                'en' => $nameEn ?? $title . ' EN',
            ],
        ]);
    }

    private function updateOrCreateRole(?int $roleId, string $name, ?int $langId, int $status): \Spatie\Permission\Contracts\Role|\Spatie\Permission\Models\Role
    {
        $roleData = [
            'name' => $name,
            'guard_name' => 'web',
            'lang_id' => $langId,
            'status' => $status,
        ];

        if ($roleId && $role = Role::find($roleId)) {
            $role->update($roleData);
            return $role;
        }

        return Role::create($roleData);
    }

    private function updatePermissions(string $oldDocCode, string $newDocCode): void
    {
        $permission = Permission::where('name', 'Permissions')->first();

        $secretaryPermission = Permission::where('name', "secretary_access_{$oldDocCode}")->first();
        if ($secretaryPermission) {
            $secretaryPermission->update(['name' => "secretary_access_{$newDocCode}"]);
        } else {
            Permission::create([
                'name' => "secretary_access_{$newDocCode}",
                'guard_name' => 'web',
                'parent_id' => $permission ? $permission->id : 0,
            ]);
        }

        $memberPermission = Permission::where('name', "member_access_{$oldDocCode}")->first();
        if ($memberPermission) {
            $memberPermission->update(['name' => "member_access_{$newDocCode}"]);
        } else {
            Permission::create([
                'name' => "member_access_{$newDocCode}",
                'guard_name' => 'web',
                'parent_id' => $permission ? $permission->id : 0,
            ]);
        }
    }

    private function updateMenu(ProtocolDocType $docType, string $title, string $newDocCode, int $status): void
    {
        $menuList = MenuList::where('code', $docType->doc_code)->first();
        if ($menuList) {
            $menuList->update([
                'title' => $title,
                'lang_id' => $docType->lang_id,
                'code' => $newDocCode,
                'url_path' => '/protocol/' . $docType->doc_hash,
                'status' => $status,
            ]);

            $roleMenu = RoleMenu::where('menu_id', $menuList->id)->first();
            if ($roleMenu && $roleMenu->role_id != $docType->role_id) {
                $roleMenu->update(['role_id' => $docType->role_id]);
            }
        }
    }

    private function assignUsers(ProtocolDocType $docType, ?array $userIds, int $roleId, string $docCode): void
    {
        ProtocolDocTypeUser::where('doc_type_id', $docType->id)->delete();

        if ($userIds && is_array($userIds)) {
            foreach ($userIds as $key => $userId) {
                ProtocolDocTypeUser::create([
                    'doc_type_id' => $docType->id,
                    'user_id' => $userId,
                    'order_number' => $key,
                    'role_id' => 2,
                    'status' => 1,
                ]);

                $userWork = UserWorkHistory::findOrFail($userId);
                $user = User::where('id', $userWork->user_id)->firstOrFail();

                $user->assignRole($roleId);
                $user->givePermissionTo("member_access_{$docCode}");

                // User Menus Cache clear
                Cache::forget("user_{$user->id}_menus");
            }
        }
    }

    private function assignSecretaries(ProtocolDocType $docType, array $secretaryUserIds, int $roleId, string $docCode): void
    {
        foreach ($secretaryUserIds as $key => $secretaryUserId) {
            $userWork = UserWorkHistory::findOrFail($secretaryUserId);
            $user = User::where('id', $userWork->user_id)->firstOrFail();

            ProtocolDocTypeUser::create([
                'doc_type_id' => $docType->id,
                'user_id' => $secretaryUserId,
                'order_number' => $key,
                'role_id' => 1,
                'status' => 1,
            ]);

            $user->assignRole($roleId);
            $user->givePermissionTo("secretary_access_{$docCode}");

            // User Menus Cache clear
            Cache::forget("user_{$user->id}_menus");
        }
    }

    private function assignSecretaryMembers(ProtocolDocType $docType, array $secretaryMembers, int $roleId, string $docCode): void
    {
        foreach ($secretaryMembers as $key => $secretaryMemberUserId) {
            $userWork = UserWorkHistory::findOrFail($secretaryMemberUserId);
            $user = User::where('id', $userWork->user_id)->firstOrFail();

            ProtocolDocTypeUser::create([
                'doc_type_id' => $docType->id,
                'user_id' => $secretaryMemberUserId,
                'order_number' => $key,
                'role_id' => 3,
                'status' => 1,
            ]);

            $user->assignRole($roleId);
            $user->givePermissionTo("secretary_access_{$docCode}");
            $user->givePermissionTo("member_access_{$docCode}");

            // User Menus Cache clear
            Cache::forget("user_{$user->id}_menus");
        }
    }

    public function signRoles(Request $request)
    {
        $rolesArray = $request->roles;
        $protocolTypeId = $request->protocol_type_id;
        $signType = $request->sign_type;

        ProtocolDocConfigs::where('protocol_type_id', $protocolTypeId)->delete();

        if (count($rolesArray)) {
            foreach ($rolesArray as $key => $role) {
                ProtocolDocConfigs::create([
                    'protocol_type_id' => $protocolTypeId,
                    'role_id' => $role,
                    'order' => ++$key,
                    'status' => 1,
                ]);
            }
        }

        $protocolType = ProtocolDocType::find($protocolTypeId);

        $protocolType->update(['sign_type' => $signType]);

        return redirect()->back()->with('success', __('message.prt_sign_roles_updated'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $docType = ProtocolDocType::findOrFail($id);
            $role = Role::find($docType->role_id);
            $doc_code = $docType->doc_code;

            ProtocolDocTypeUser::where('doc_type_id', $id)->delete();

            MenuList::where('code', $doc_code)->orWhere('code', $doc_code . '_S')->delete();

            Permission::where('name', 'secretary_access_' . $doc_code)
                ->orWhere('name', 'member_access_' . $doc_code)
                ->delete();

            if ($role) {
                RoleMenu::where('role_id', $role->id)->delete();
                $role->delete();
            }

            if ($docType->lang_id) {
                LanguageLine::where('id', $docType->lang_id)->delete();
            }

            $this->revokeExistingPermissions($docType->doc_code);

            $docType->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('message.task_completed_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete protocol doc type: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('message.error_message'),
            ], 500);
        }
    }
}
