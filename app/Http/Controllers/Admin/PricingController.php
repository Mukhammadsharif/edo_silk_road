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
        // На этой странице тарифы для выбора больше не показываем —
        // только активную подписку организации, и только для Director / IT_Admin.
        $roleNames = $this->authActiveUserRoleNames();
        $canViewSubscription = in_array('Director', $roleNames, true)
            || in_array('IT_Admin', $roleNames, true);

        $tariffs = collect();

        $currentSubscription = $canViewSubscription
            ? Auth::user()->organization?->currentSubscription
            : null;

        $pendingSubscription = $canViewSubscription
            ? Auth::user()->organization?->pendingSubscription
            : null;

        return view('pricing.index', compact('tariffs', 'currentSubscription', 'pendingSubscription'));
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
            ->where('status', 2)
            ->where('ends_at', '>=', now())
            ->first();

        $startsAt = Carbon::today();
        $endsAt = $period === 'year'
            ? $startsAt->copy()->addYear()
            : $startsAt->copy()->addMonth();


        if ($existing) {

            $existing->update([
                'tariff_id'   => $tariff->id,
                'starts_at'   => $startsAt,
                'ends_at'     => $endsAt,
                'status' => 2
            ]);

        } else {

            Subscriptions::create([
                'org_unit_id' => $orgId,
                'tariff_id'   => $tariff->id,
                'starts_at'   => $startsAt,
                'ends_at'     => $endsAt,
                'status'      => 2, // pending
            ]);
        }

        $periodText = $period === 'year' ? 'yillik' : 'oylik';
        return redirect()->back()->with('success',
            "«{$tariff->name}» tarifiga {$periodText} obuna so'rovi muvaffaqiyatli yuborildi. Admin tasdiqlashini kuting."
        );
    }
}
