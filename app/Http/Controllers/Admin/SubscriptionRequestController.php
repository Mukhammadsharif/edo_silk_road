<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Subscriptions;
use App\Models\Admin\Tariffs;
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

        return response()->json([
            'id'                => $subscription->id,
            'org_name'          => $subscription->organization->name_uz ?? 'Noma\'lum',
            'tariff_id'         => $subscription->tariff_id,
            'tariff_name'       => $subscription->tariff->name ?? '',
            'status'            => $subscription->status,
            'starts_at'         => $subscription->starts_at?->format('Y-m-d'),
            'ends_at'           => $subscription->ends_at?->format('Y-m-d'),
            'has_kanban_access' => $subscription->has_kanban_access,
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
        ]);

        $subscription = Subscriptions::findOrFail($id);

        $subscription->tariff_id         = $request->tariff_id;
        $subscription->status            = $request->status;
        $subscription->starts_at         = $request->starts_at ?: null;
        $subscription->ends_at           = $request->ends_at ?: null;
        $subscription->has_kanban_access = $request->boolean('has_kanban_access');
        $subscription->save();

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
        ]);

        // If activating directly — deactivate old active subscriptions for that org
        if ($request->status == 1) {
            Subscriptions::where('org_unit_id', $request->org_unit_id)
                ->where('status', 1)
                ->where('ends_at', '>=', now())
                ->update(['status' => 0]);
        }

        Subscriptions::create([
            'org_unit_id'       => $request->org_unit_id,
            'tariff_id'         => $request->tariff_id,
            'status'            => $request->status,
            'starts_at'         => $request->starts_at,
            'ends_at'           => $request->ends_at,
            'has_kanban_access' => $request->boolean('has_kanban_access'),
        ]);

        return redirect()->back()->with('success', __('field.subscription_created_successfully'));
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
}
