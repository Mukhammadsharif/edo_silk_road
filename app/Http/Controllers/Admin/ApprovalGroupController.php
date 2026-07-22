<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ApprovalGroup;
use App\Models\Admin\ApprovalGroupUser;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\UserWorkHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Управление группами согласующих.
 * Доступ: Super_Admin (по всем организациям, с выбором) и IT_Admin (только своя org).
 */
class ApprovalGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Проверка прав доступа к разделу. */
    private function ensureAccess(): void
    {
        $roles = $this->authActiveUserRoleNames();
        if (!in_array('Super_Admin', $roles) && !in_array('IT_Admin', $roles)) {
            abort(403);
        }
    }

    /** Может ли текущий пользователь выбирать организацию (только Super_Admin). */
    private function canPickOrg(): bool
    {
        return in_array('Super_Admin', $this->authActiveUserRoleNames());
    }

    /** Определить рабочую организацию: для IT_Admin — своя, для Super_Admin — выбранная (?org). */
    private function resolveOrgId(Request $request): ?int
    {
        if ($this->canPickOrg()) {
            $org = $request->input('org') ?? $request->input('org_unit_id');
            return $org ? (int) $org : (int) $this->organizationId();
        }
        return (int) $this->organizationId();
    }

    /** Активные пользователи организации (для выбора участников группы). */
    private function orgUsers(?int $orgId)
    {
        if (!$orgId) return collect();

        return UserWorkHistory::where('user_work_histories.org_id', $orgId)
            ->where('user_work_histories.status', 1)
            ->whereHas('user', function ($q) {
                $q->where('status', 1);
            })
            ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
            ->orderBy('user_personal_infos.sort')
            ->select('user_work_histories.*')
            ->get()
            ->unique('user_id')
            ->values();
    }

    public function index(Request $request)
    {
        $this->ensureAccess();

        $orgId = $this->resolveOrgId($request);

        $search = ApprovalGroup::where('status', 1);
        if (!$this->canPickOrg()) {
            $search->where('org_unit_id', $orgId);
        } elseif ($orgId) {
            $search->where('org_unit_id', $orgId);
        }

        $s_text = $request->input('s_text');
        if (trim('' . $s_text . '')) {
            $search->where('name', 'ILIKE', '%' . $s_text . '%');
        }

        $models = $search->withCount('members')->orderBy('id', 'DESC')->paginate(25);
        $models->appends(['s_text' => $s_text, 'org' => $orgId]);

        $organizations = $this->canPickOrg()
            ? OrganizationUnits::where('status', 1)->get()
            : collect();

        return view('admin.approval-groups.index', compact('models', 's_text', 'organizations', 'orgId'))
            ->with('canPickOrg', $this->canPickOrg())
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function create(Request $request)
    {
        $this->ensureAccess();

        $orgId = $this->resolveOrgId($request);
        $organizations = $this->canPickOrg()
            ? OrganizationUnits::where('status', 1)->get()
            : collect();

        $orgUsers = $this->orgUsers($orgId);

        return view('admin.approval-groups.create', compact('organizations', 'orgUsers', 'orgId'))
            ->with('canPickOrg', $this->canPickOrg());
    }

    /** AJAX: список пользователей выбранной организации (для Super_Admin при смене org). */
    public function getOrgUsers(Request $request, $orgId)
    {
        $this->ensureAccess();

        // IT_Admin не может смотреть чужие организации
        if (!$this->canPickOrg() && (int) $orgId !== (int) $this->organizationId()) {
            abort(403);
        }

        $users = $this->orgUsers((int) $orgId)->map(function ($u) {
            return [
                'user_id'  => $u->user_id,
                'name'     => optional($u->personal)->fullName() ?? '-',
                'position' => optional($u->userPost)->lang(optional($u->userPost)->id) ?? '',
            ];
        })->values();

        return response()->json(['success' => true, 'users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAccess();

        $request->validate([
            'name'    => 'required|string|max:255',
            'users'   => 'required|array|min:1',
            'users.*' => 'integer',
        ], [
            'name.required'  => __('field.name_required'),
            'users.required' => __('field.select_at_least_one_user'),
        ]);

        $orgId = $this->resolveOrgId($request);
        if (!$orgId) {
            $loc = session()->get('locale', app()->getLocale());
            $msg = $loc === 'ru' ? 'Укажите организацию' : ($loc === 'en' ? 'Organization is required' : 'Tashkilotni tanlang');
            return back()->withInput()->with('danger', $msg);
        }

        DB::transaction(function () use ($request, $orgId) {
            $group = ApprovalGroup::create([
                'org_unit_id' => $orgId,
                'name'        => $request->name,
                'created_by'  => optional($this->authActiveUser())->id,
                'status'      => 1,
            ]);

            $this->syncMembers($group, $orgId, $request->users);
        });

        return redirect()->route('approval-groups.index', ['org' => $orgId])
            ->with('success', __('message.saved_successfully'));
    }

    public function edit(Request $request, $id)
    {
        $this->ensureAccess();

        $group = ApprovalGroup::where('status', 1)->findOrFail($id);

        // IT_Admin — только своя организация
        if (!$this->canPickOrg() && (int) $group->org_unit_id !== (int) $this->organizationId()) {
            abort(403);
        }

        $orgId = (int) $group->org_unit_id;
        $organizations = $this->canPickOrg()
            ? OrganizationUnits::where('status', 1)->get()
            : collect();

        $orgUsers = $this->orgUsers($orgId);
        $selectedUserIds = $group->memberUserIds();

        return view('admin.approval-groups.edit', compact('group', 'organizations', 'orgUsers', 'orgId', 'selectedUserIds'))
            ->with('canPickOrg', $this->canPickOrg());
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $this->ensureAccess();

        $group = ApprovalGroup::where('status', 1)->findOrFail($id);

        if (!$this->canPickOrg() && (int) $group->org_unit_id !== (int) $this->organizationId()) {
            abort(403);
        }

        $request->validate([
            'name'    => 'required|string|max:255',
            'users'   => 'required|array|min:1',
            'users.*' => 'integer',
        ], [
            'name.required'  => __('field.name_required'),
            'users.required' => __('field.select_at_least_one_user'),
        ]);

        $orgId = (int) $group->org_unit_id;

        DB::transaction(function () use ($request, $group, $orgId) {
            $group->update(['name' => $request->name]);
            $this->syncMembers($group, $orgId, $request->users, true);
        });

        return redirect()->route('approval-groups.index', ['org' => $orgId])
            ->with('success', __('message.saved_successfully'));
    }

    public function destroy($id)
    {
        $this->ensureAccess();

        $group = ApprovalGroup::where('status', 1)->find($id);

        $status  = self::RESPONSE_ERROR;
        $message = __('message.deleted_successfully');

        if ($group) {
            // IT_Admin — только своя организация
            if (!$this->canPickOrg() && (int) $group->org_unit_id !== (int) $this->organizationId()) {
                abort(403);
            }
            $group->update(['status' => 3]);
            $status = self::RESPONSE_SUCCESS;
        }

        return response()->json([
            'status'  => $status,
            'success' => $message,
        ]);
    }

    /**
     * Синхронизировать участников. Принимаются только user_id, реально
     * работающие в данной организации (защита от подмены).
     */
    private function syncMembers(ApprovalGroup $group, int $orgId, array $userIds, bool $replace = false): void
    {
        $validUserIds = UserWorkHistory::where('org_id', $orgId)
            ->where('status', 1)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->toArray();

        if ($replace) {
            ApprovalGroupUser::where('group_id', $group->id)->delete();
        }

        foreach ($validUserIds as $uid) {
            ApprovalGroupUser::firstOrCreate([
                'group_id' => $group->id,
                'user_id'  => $uid,
            ]);
        }
    }
}
