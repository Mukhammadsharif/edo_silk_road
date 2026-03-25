<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Subscriptions;
use App\Models\Admin\Tariffs;
use Illuminate\Http\Request;

class SubscriptionRequestController extends Controller
{
    //
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

        $pendingCount = Subscriptions::where('status', 2)->count();
        $activeCount = Subscriptions::where('status', 1)->where('ends_at', '>=', now())->count();
        $expiredCount = Subscriptions::where('status', 1)->where('ends_at', '<', now())->count();
        $rejectedCount = Subscriptions::where('status', 0)->count();

        $tariffs = Tariffs::orderBy('name')->get();

        return view('admin.subscriptions.requests', compact(
            'pending', 'active', 'expired', 'rejected',
            'pendingCount', 'activeCount', 'expiredCount', 'rejectedCount', 'tariffs'
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
            'id'          => $subscription->id,
            'org_name'    => $subscription->organization->name_uz ?? 'Noma\'lum',
            'tariff_id'   => $subscription->tariff_id,
            'tariff_name' => $subscription->tariff->name ?? '',
            'status'      => $subscription->status,
            'starts_at'   => $subscription->starts_at?->format('Y-m-d'),
            'ends_at'     => $subscription->ends_at?->format('Y-m-d'),
        ]);
    }

    /**
     * Update subscription fields
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tariff_id'  => 'required|exists:tariffs,id',
            'status'     => 'required|in:0,1,2',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);

        $subscription = Subscriptions::findOrFail($id);

        $subscription->tariff_id  = $request->tariff_id;
        $subscription->status     = $request->status;
        $subscription->starts_at  = $request->starts_at ?: null;
        $subscription->ends_at    = $request->ends_at ?: null;
        $subscription->save();

        return redirect()->back()->with('success', __('field.subscription_updated_successfully'));
    }
}
