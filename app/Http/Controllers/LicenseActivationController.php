<?php

namespace App\Http\Controllers;

use App\Models\Admin\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Клиентская часть: ИТ-админ / директор организации активирует лицензию.
 */
class LicenseActivationController extends Controller
{
    private LicenseService $service;

    public function __construct(LicenseService $service)
    {
        $this->service = $service;
    }

    /** Страница активации в профиле */
    public function index()
    {
        $orgId = $this->organizationId();
        $organization = \App\Models\Admin\OrganizationUnits::find($orgId);

        // Текущая активная подписка (если есть)
        $currentSubscription = $organization?->currentSubscription;

        // История лицензий организации
        $licenses = License::where('org_unit_id', $orgId)
            ->orderByDesc('created_at')
            ->get();

        return view('license.activate', compact('organization', 'currentSubscription', 'licenses'));
    }

    /** Активировать загруженный .lic файл */
    public function activate(Request $request)
    {
        $request->validate([
            'license_file' => 'required|file|max:2048', // до 2MB
        ]);

        $orgId = $this->organizationId();

        try {
            $content = file_get_contents($request->file('license_file')->getRealPath());
            $subscription = $this->service->activate($content, (int) $orgId, Auth::id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Litsenziya muvaffaqiyatli faollashtirildi! Obuna faol.');
    }
}
