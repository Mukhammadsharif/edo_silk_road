<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Admin\Subscriptions;
use App\Models\Admin\Tariffs;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionRequestController extends Controller
{
    public function index()
    {
        $pending = Subscriptions::with(['organization', 'tariff'])
            ->where('status', 2)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $active = Subscriptions::with(['organization', 'tariff'])
            ->where('status', 1)
            ->where('ends_at', '>=', now())
            ->orderBy('ends_at', 'desc')
            ->paginate(20);

        $expired = Subscriptions::with(['organization', 'tariff'])
            ->where('status', 1)
            ->where('ends_at', '<', now())
            ->orderBy('ends_at', 'desc')
            ->paginate(20);

        $rejected = Subscriptions::with(['organization', 'tariff'])
            ->where('status', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingCount  = Subscriptions::where('status', 2)->count();
        $activeCount   = Subscriptions::where('status', 1)->where('ends_at', '>=', now())->count();
        $expiredCount  = Subscriptions::where('status', 1)->where('ends_at', '<', now())->count();
        $rejectedCount = Subscriptions::where('status', 0)->count();

        $tariffs       = Tariffs::orderBy('name')->get();
        $organizations = OrganizationUnits::where('status', 1)->orderBy('name_uz')->get();

        return view('admin.subscriptions.requests', compact(
            'pending', 'active', 'expired', 'rejected',
            'pendingCount', 'activeCount', 'expiredCount', 'rejectedCount',
            'tariffs', 'organizations'
        ));
    }

    public function approve($id)
    {
        $subscription = Subscriptions::findOrFail($id);

        if ($subscription->status != 2) {
            return redirect()->back()->with('error', __('field.subscription_request_already_reviewed'));
        }

        Subscriptions::where('org_unit_id', $subscription->org_unit_id)
            ->where('status', 1)
            ->where('ends_at', '>=', now())
            ->update(['status' => 0]);

        $subscription->status = 1;
        $subscription->save();

        return redirect()->back()->with('success', __('field.subscription_approved_successfully'));
    }

    public function reject($id)
    {
        $subscription = Subscriptions::findOrFail($id);

        if ($subscription->status != 2) {
            return redirect()->back()->with('error', __('field.subscription_request_already_reviewed'));
        }

        $subscription->status = 0;
        $subscription->save();

        return redirect()->back()->with('success', __('field.subscription_rejected'));
    }

    /**
     * Return subscription data as JSON for the edit modal
     */
    public function edit($id)
    {
        $subscription = Subscriptions::with(['organization', 'tariff'])->findOrFail($id);

        $didoxService = new \App\Services\Didox\DidoxAccessService();

        return response()->json([
            'id'                => $subscription->id,
            'org_unit_id'       => $subscription->org_unit_id,
            'org_name'          => $subscription->organization->name_uz ?? 'Noma\'lum',
            'tariff_id'         => $subscription->tariff_id,
            'tariff_name'       => $subscription->tariff->name ?? '',
            'status'            => $subscription->status,
            'starts_at'         => $subscription->starts_at?->format('Y-m-d'),
            'ends_at'           => $subscription->ends_at?->format('Y-m-d'),
            'has_kanban_access' => $subscription->has_kanban_access,
            'has_didox_access'  => $didoxService->isEnabledForOrg($subscription->org_unit_id),
            'didox_user_ids'    => $didoxService->responsibleUserIds($subscription->org_unit_id),
        ]);
    }

    /**
     * Update subscription fields
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tariff_id'         => 'required|exists:tariffs,id',
            'status'            => 'required|in:0,1,2',
            'starts_at'         => 'nullable|date',
            'ends_at'           => 'nullable|date|after_or_equal:starts_at',
            'has_kanban_access' => 'nullable|boolean',
            'has_didox_access'  => 'nullable|boolean',
            'didox_user_ids'    => 'nullable|array',
            'didox_user_ids.*'  => 'integer',
        ]);

        $subscription = Subscriptions::findOrFail($id);
        $hasKanban    = $request->boolean('has_kanban_access');
        $hasDidox     = $request->boolean('has_didox_access');
        $didoxUserIds = $hasDidox ? (array) $request->input('didox_user_ids', []) : [];

        $subscription->tariff_id         = $request->tariff_id;
        $subscription->status            = $request->status;
        $subscription->starts_at         = $request->starts_at ?: null;
        $subscription->ends_at           = $request->ends_at ?: null;
        $subscription->has_kanban_access = $hasKanban;
        $subscription->save();

        $this->syncKanbanRole($subscription->org_unit_id, $hasKanban);
        // Didox — через роли/меню, назначаем конкретным сотрудникам.
        $this->syncDidoxRole($subscription->org_unit_id, $hasDidox, $didoxUserIds);

        return redirect()->back()->with('success', __('field.subscription_updated_successfully'));
    }

    /**
     * Admin creates a new subscription manually (directly active or pending)
     */
    public function store(Request $request)
    {
        $request->validate([
            'org_unit_id'       => 'required|exists:organization_units,id',
            'tariff_id'         => 'required|exists:tariffs,id',
            'status'            => 'required|in:0,1,2',
            'starts_at'         => 'required|date',
            'ends_at'           => 'required|date|after:starts_at',
            'has_kanban_access' => 'nullable|boolean',
            'has_didox_access'  => 'nullable|boolean',
            'didox_user_ids'    => 'nullable|array',
            'didox_user_ids.*'  => 'integer',
        ]);

        if ($request->status == 1) {
            Subscriptions::where('org_unit_id', $request->org_unit_id)
                ->where('status', 1)
                ->where('ends_at', '>=', now())
                ->update(['status' => 0]);
        }

        $hasKanban    = $request->boolean('has_kanban_access');
        $hasDidox     = $request->boolean('has_didox_access');
        $didoxUserIds = $hasDidox ? (array) $request->input('didox_user_ids', []) : [];

        $subscription = Subscriptions::create([
            'org_unit_id'       => $request->org_unit_id,
            'tariff_id'         => $request->tariff_id,
            'status'            => $request->status,
            'starts_at'         => $request->starts_at,
            'ends_at'           => $request->ends_at,
            'has_kanban_access' => $hasKanban,
        ]);

        $this->syncKanbanRole($request->org_unit_id, $hasKanban);
        // Didox — назначаем конкретным сотрудникам организации.
        $this->syncDidoxRole($request->org_unit_id, $hasDidox, $didoxUserIds);

        return redirect()->back()->with('success', __('field.subscription_created_successfully'));
    }

    /**
     * Активные сотрудники организации для мультиселекта «ответственные за Didox».
     */
    public function orgUsers($orgId)
    {
        $users = \App\Models\Admin\UserWorkHistory::where('org_id', (int) $orgId)
            ->where('status', 1)
            ->whereHas('user', function ($q) {
                $q->where('status', 1);
            })
            ->get()
            ->unique('user_id')
            ->map(function ($u) {
                return [
                    'user_id'  => $u->user_id,
                    'name'     => optional($u->personal)->fullName() ?? '-',
                    'position' => optional($u->userPost)->lang(optional($u->userPost)->id) ?? '',
                ];
            })->values();

        return response()->json(['success' => true, 'users' => $users]);
    }
    /**
     * Search organizations for Select2 (returns JSON)
     */
    public function searchOrganizations(Request $request)
    {
        $query = $request->get('q', '');

        $orgs = OrganizationUnits::where('status', 1)
            ->where(function ($q) use ($query) {
                $q->where('name_uz', 'like', "%{$query}%")
                    ->orWhere('name_ru', 'like', "%{$query}%")
                    ->orWhere('tax_number', 'like', "%{$query}%");
            })
            ->limit(30)
            ->get(['id', 'name_uz', 'tax_number']);

        return response()->json($orgs->map(fn($o) => [
            'id'   => $o->id,
            'text' => $o->name_uz . ($o->tax_number ? ' — ' . $o->tax_number : ''),
        ]));
    }

    private function syncKanbanRole(int $orgUnitId, bool $hasAccess): void
    {
        $roleId = config('kanban.role_id');

        if (! $roleId) {
            return; // роль не настроена — молча выходим
        }

        $role = Role::find($roleId);

        if (! $role) {
            return; // роли с таким ID нет
        }

        $users = User::query()
            ->where('org_unit_id', $orgUnitId)
            ->role(config('kanban.grant_to_roles')) // scope от Spatie
            ->get();

        foreach ($users as $user) {
            if ($hasAccess) {
                $user->assignRole($role);
            } else {
                $user->removeRole($role);
            }
        }
    }

    /**
     * Синхронизация доступа к Didox для организации (конкретные сотрудники).
     */
    private function syncDidoxRole(int $orgUnitId, bool $hasAccess, array $userIds = []): void
    {
        (new \App\Services\Didox\DidoxAccessService())
            ->syncForOrgUsers($orgUnitId, $hasAccess, $userIds);
    }
}
