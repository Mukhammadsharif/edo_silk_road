<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OnecTokenMail;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Admin\UserRole;
use App\Models\Integration1C\OnecCompany;
use App\Models\Integration1C\OnecDivision;
use App\Models\Integration1C\OnecEmployee;
use App\Models\Integration1C\OnecPerson;
use App\Models\Integration1C\OnecPosition;
use App\Models\Integration1C\OnecIntegrationToken;
use App\Services\Integration1C\OnecTokenService;
use App\Services\Integration1C\OnecPromoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Кабинет интеграции с 1С.
 *  - Директор / IT-админ организации: для своей организации (+ email с токеном)
 *  - Super_Admin: для любой организации (без email)
 */
class OnecIntegrationController extends Controller
{
    public function __construct(
        private OnecTokenService $tokens,
        private OnecPromoter $promoter
    ) {}

    /** Роли пользователя */
    private function roleNames(): array
    {
        $ids = UserRole::where('model_id', Auth::id())->pluck('role_id')->toArray();
        return Role::whereIn('id', $ids)->pluck('name')->toArray();
    }

    private function isSuperAdmin(): bool
    {
        return in_array('Super_Admin', $this->roleNames(), true);
    }

    /** Может ли управлять интеграцией (директор / IT-админ / суперадмин) */
    private function canManage(): bool
    {
        $roles = $this->roleNames();
        return in_array('Super_Admin', $roles, true)
            || in_array('Director', $roles, true)
            || in_array('IT_Admin', $roles, true);
    }

    /** Страница интеграции */
    public function index(Request $request)
    {
        abort_unless($this->canManage(), 403, 'Нет доступа к интеграции с 1С');

        $isSuper = $this->isSuperAdmin();

        if ($isSuper) {
            // Суперадмин видит все организации
            $organizations = OrganizationUnits::orderBy('name_ru')->get();
            $selectedOrgId = (int) ($request->get('org_unit_id') ?: ($organizations->first()->id ?? 0));
        } else {
            // Директор/IT-админ — только своя
            $selectedOrgId = (int) Auth::user()->org_unit_id;
            $organizations = OrganizationUnits::where('id', $selectedOrgId)->get();
        }

        // Активный токен организации
        $activeToken = OnecIntegrationToken::where('org_unit_id', $selectedOrgId)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        // Данные, пришедшие из 1С (для табов-таблиц)
        $incoming = $this->incomingData($selectedOrgId);

        return view('admin.onec.index', [
            'isSuper'        => $isSuper,
            'organizations'  => $organizations,
            'selectedOrgId'  => $selectedOrgId,
            'activeToken'    => $activeToken,
            'ttlMinutes'     => $this->tokens->ttlMinutes(),
            'incoming'       => $incoming,
        ]);
    }

    /** Сгенерировать токен */
    public function generate(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $isSuper = $this->isSuperAdmin();
        $orgUnitId = $isSuper
            ? (int) $request->input('org_unit_id')
            : (int) Auth::user()->org_unit_id;

        if (!$orgUnitId) {
            return back()->with('error', 'Не указана организация');
        }

        $token = $this->tokens->generate($orgUnitId, Auth::id());

        // Для НЕ суперадмина — отправляем токен на email сотрудника
        $emailed = false;
        if (!$isSuper && Auth::user()->email) {
            try {
                Mail::to(Auth::user()->email)->send(new OnecTokenMail(
                    $token->token,
                    $token->expires_at,
                    optional(OrganizationUnits::find($orgUnitId))->name_ru ?? ''
                ));
                $emailed = true;
            } catch (\Throwable $e) {
                Log::channel('onec')->error('[1C token email] ' . $e->getMessage());
            }
        }

        return back()->with([
            'onec_token'   => $token->token,
            'onec_expires' => $token->expires_at->toDateTimeString(),
            'onec_emailed' => $emailed,
            'org_unit_id'  => $orgUnitId,
        ]);
    }

    /** Сбор пришедших данных для табов (по выбранной организации) */
    private function incomingData(int $orgUnitId): array
    {
        $by = fn($q) => $q->where('org_unit_id', $orgUnitId)->latest()->limit(100)->get();

        // Проблемные записи (pending/error) по всем сущностям — для вкладки диагностики
        $problems = collect();
        $map = [
            'companies' => [OnecCompany::class, 'Компания'],
            'divisions' => [OnecDivision::class, 'Отдел'],
            'positions' => [OnecPosition::class, 'Должность'],
            'persons'   => [OnecPerson::class, 'Физлицо'],
            'employees' => [OnecEmployee::class, 'Сотрудник'],
        ];
        foreach ($map as $entity => [$class, $label]) {
            $rows = $class::where('org_unit_id', $orgUnitId)
                ->whereIn('sync_status', ['pending', 'error'])
                ->latest()->limit(100)->get();
            foreach ($rows as $r) {
                $name = $r->name ?? trim(($r->last_name ?? '') . ' ' . ($r->first_name ?? ''));
                $problems->push((object) [
                    'entity'     => $entity,
                    'label'      => $label,
                    'onec_id'    => $r->onec_id,
                    'name'       => $name ?: '—',
                    'status'     => $r->sync_status,
                    'error'      => $r->sync_error,
                    'attempts'   => $r->sync_attempts,
                    'updated_at' => $r->updated_at,
                ]);
            }
        }

        return [
            'companies' => $by(OnecCompany::query()),
            'divisions' => $by(OnecDivision::query()),
            'positions' => $by(OnecPosition::query()),
            'persons'   => $by(OnecPerson::query()),
            'employees' => $by(OnecEmployee::query()),
            'problems'  => $problems,
        ];
    }

    /** Повторить промоут всех проблемных записей организации */
    public function retry(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $isSuper = $this->isSuperAdmin();
        $orgUnitId = $isSuper
            ? (int) $request->input('org_unit_id')
            : (int) Auth::user()->org_unit_id;

        $result = $this->promoter->promoteAllPending($orgUnitId ?: null);

        return back()->with('onec_retry_result', $result);
    }
}
