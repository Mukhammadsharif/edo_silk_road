<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\License;
use App\Models\Admin\OrganizationUnits;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicenseController extends Controller
{
    private LicenseService $service;

    public function __construct(LicenseService $service)
    {
        $this->service = $service;
    }

    /** Список выпущенных лицензий */
    public function index()
    {
        $licenses = License::with(['organization', 'subscription'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.licenses.index', compact('licenses'));
    }

    /** Форма генерации */
    public function create()
    {
        $organizations = OrganizationUnits::orderBy('id')->get();
        return view('admin.licenses.create', compact('organizations'));
    }

    /** Сгенерировать лицензию + сразу отдать .lic файл на скачивание */
    public function store(Request $request)
    {
        $data = $request->validate([
            'org_unit_id'   => 'required|integer|exists:organization_units,id',
            'max_users'     => 'required|integer|min:1',
            'max_documents' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1',
        ]);

        $result = $this->service->generate(
            (int) $data['org_unit_id'],
            (int) $data['max_users'],
            (int) $data['max_documents'],
            (int) $data['duration_days'],
            Auth::id()
        );

        // Сразу отдаём файл на скачивание
        return response($result['file_content'], 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $result['file_name'] . '"',
        ]);
    }

    /** Повторно скачать файл лицензии (пересобираем из БД) */
    public function download($id)
    {
        $license = License::findOrFail($id);

        $payload = [
            'key'           => $license->license_key,
            'org_unit_id'   => $license->org_unit_id,
            'max_users'     => $license->max_users,
            'max_documents' => $license->max_documents,
            'duration_days' => $license->duration_days,
            'issued_at'     => $license->created_at?->toIso8601String(),
            'v'             => 1,
        ];

        $fileContent = $this->service->buildFile($payload, $license->signature);
        $fileName    = 'license_' . $license->license_key . '.lic';

        return response($fileContent, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /** Отозвать лицензию */
    public function revoke($id)
    {
        $license = License::findOrFail($id);
        $license->update(['status' => 0]);

        // Если была активирована — деактивируем связанную подписку
        if ($license->subscription) {
            $license->subscription->update(['status' => 0]);
        }

        return back()->with('success', 'Litsenziya bekor qilindi.');
    }
}
