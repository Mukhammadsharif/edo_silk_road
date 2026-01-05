<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Tariffs;
use App\Models\Admin\Subscriptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricingController extends Controller
{
    //
    public function index()
    {
        $tariffs = Tariffs::where('status', 1)->orderBy('price_monthly', 'asc')->get();

        $currentSubscription = Auth::user()->organization->currentSubscription ?? null;

        return view('pricing.index', compact('tariffs', 'currentSubscription'));
    }

    public function subscribe(Request $request, $tariffId)
    {
        $request->validate([
            'period' => 'required|in:month,year'
        ]);

        $tariff = Tariffs::findOrFail($tariffId);
        $orgId = Auth::user()->org_unit_id;
        $period = $request->input('period');

        $existing = Subscriptions::where('org_unit_id', $orgId)
            ->whereIn('status', [1, 2])
            ->where('ends_at', '>=', now())
            ->first();

        if ($existing) {
            return redirect()->back()->with('warning', __('field.subscription_already_exists'));
        }

        $startsAt = Carbon::today();
        $endsAt = $period === 'year'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();

        Subscriptions::create([
            'org_unit_id' => $orgId,
            'tariff_id'   => $tariff->id,
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
            'status'      => 2, // pending
        ]);

        $periodText = $period === 'year' ? 'yillik' : 'oylik';
        return redirect()->back()->with('success',
            "«{$tariff->name}» tarifiga {$periodText} obuna so'rovi muvaffaqiyatli yuborildi. Admin tasdiqlashini kuting."
        );
    }
}
