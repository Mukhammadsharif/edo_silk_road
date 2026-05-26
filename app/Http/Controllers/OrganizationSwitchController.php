<?php

namespace App\Http\Controllers;

use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\UserWorkHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Управление переключением активной организации пользователя.
 *
 * Источник истины — users.org_unit_id (текущая выбранная org).
 * Доступ к организации = существование UserWorkHistory с status=1.
 */
class OrganizationSwitchController extends Controller
{
    /**
     * POST /switch-organization/{org_id}
     *
     * Переключить активную организацию пользователя на $orgId.
     * Юзер должен иметь активный UserWorkHistory в этой организации.
     */
    public function switch(Request $request, $orgId)
    {
        $orgId = (int) $orgId;
        $user  = Auth::user();

        if (!$user) {
            return $this->respond($request, 401, 'Unauthorized', null);
        }

        // Уже на этой организации — нечего делать
        if ((int) $user->org_unit_id === $orgId) {
            return $this->respond($request, 200, __('field.organization_already_active') ?? 'Эта организация уже активна', null);
        }

        // Проверка доступа: должен быть активный work_history
        $hasAccess = UserWorkHistory::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->where('status', 1)
            ->exists();

        if (!$hasAccess) {
            Log::warning('Organization switch denied — no active work history', [
                'user_id'  => $user->id,
                'org_id'   => $orgId,
                'ip'       => $request->ip(),
            ]);

            return $this->respond($request, 403, __('field.access_denied') ?? 'Нет доступа к этой организации', null);
        }

        // Проверим что организация активна
        $organization = OrganizationUnits::where('id', $orgId)
            ->where('status', 1)
            ->first();

        if (!$organization) {
            return $this->respond($request, 404, __('field.organization_not_found') ?? 'Организация не найдена', null);
        }

        // Сохраняем
        $user->org_unit_id = $orgId;
        $user->save();

        // Чистим кеш сайдбара (счётчики документов в меню зависят от текущей org)
        Cache::forget("user_{$user->id}_menus");

        Log::info('Organization switched', [
            'user_id' => $user->id,
            'old_org' => $request->session()->get('previous_org_unit_id'),
            'new_org' => $orgId,
        ]);

        return $this->respond(
            $request,
            200,
            __('field.organization_switched') ?? 'Организация изменена',
            [
                'org_id'   => $organization->id,
                'org_name' => $organization->name_uz,
            ]
        );
    }

    /**
     * Унифицированный ответ — JSON для AJAX, redirect для обычной формы.
     */
    private function respond(Request $request, int $status, string $message, ?array $data): mixed
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => $status,
                'message' => $message,
                'data'    => $data,
            ], $status === 200 ? 200 : $status);
        }

        if ($status === 200) {
            return redirect()->to('/edo')->with('success', $message);
        }

        return redirect()->back()->with('error', $message);
    }
}
