<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Subscriptions;
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

        return view('admin.subscriptions.requests', compact(
            'pending', 'active', 'expired', 'rejected',
            'pendingCount', 'activeCount', 'expiredCount', 'rejectedCount'
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
}
