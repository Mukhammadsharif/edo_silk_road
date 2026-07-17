<?php

namespace App\Http\Controllers\Api\Integration1C;

use App\Http\Controllers\Controller;
use App\Mail\OnecTokenMail;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Admin\UserRole;
use App\Models\Integration1C\OnecIntegrationToken;
use App\Services\Integration1C\OnecTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * API-аналог кабинета интеграции с 1С (веб: admin.onec.generate).
 *
 * Доступ — аутентифицированный пользователь (sanctum) с ролью
 * Super_Admin / Director / IT_Admin, как и в веб-кабинете:
 *  - Director / IT_Admin — токен только для своей организации (+ письмо на email);
 *  - Super_Admin — для любой организации (org_unit_id в теле), без письма.
 *
 * @OA\Tag(name="1C Token", description="Выпуск токена интеграции 1С через API")
 */
class OnecTokenController extends Controller
{
    public function __construct(private OnecTokenService $tokens) {}

    /** Имена ролей текущего пользователя */
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

    /**
     * @OA\Post(
     *     path="/api/onec/token/generate",
     *     tags={"1C Token"},
     *     summary="Сгенерировать токен интеграции 1С",
     *     description="Выпускает новый токен интеграции 1С для организации и помечает истёкшими предыдущие активные токены этой организации (один активный за раз). Токен живёт 60 минут. Director / IT_Admin получают токен только для своей организации и дополнительно письмом на email. Super_Admin может указать org_unit_id любой организации; без указания берётся его активная организация.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="org_unit_id",
     *                 type="integer",
     *                 example=1,
     *                 description="Только для Super_Admin. Для остальных ролей игнорируется — токен всегда для своей организации."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Токен выпущен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="onec_9ZqK3v..."),
     *             @OA\Property(property="expires_at", type="string", example="2026-07-15 13:45:00"),
     *             @OA\Property(property="ttl_minutes", type="integer", example=60),
     *             @OA\Property(property="refresh_token", type="string", example="onecr_8Kd2p...",
     *                 description="Долгоживущий ключ: 1С обновляет им access-токен без участия человека"),
     *             @OA\Property(property="refresh_expires_at", type="string", example="2026-08-14 13:45:00"),
     *             @OA\Property(property="org_unit_id", type="integer", example=1),
     *             @OA\Property(property="org_name", type="string", example="ДЕМО Организация"),
     *             @OA\Property(property="emailed", type="boolean", example=true,
     *                 description="Отправлено ли письмо с токеном на email пользователя (только не для Super_Admin)")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не аутентифицирован"),
     *     @OA\Response(response=403, description="Нет доступа к интеграции с 1С"),
     *     @OA\Response(response=422, description="Организация не определена или org_unit_id не существует")
     * )
     */
    public function generate(Request $request): JsonResponse
    {
        if (!$this->canManage()) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к интеграции с 1С.',
            ], 403);
        }

        $data = $request->validate([
            'org_unit_id' => ['nullable', 'integer', 'exists:organization_units,id'],
        ]);

        $isSuper = $this->isSuperAdmin();

        // Суперадмин может выпустить токен для любой организации; остальные — только для своей.
        $orgUnitId = $isSuper
            ? (int) ($data['org_unit_id'] ?? Auth::user()->org_unit_id)
            : (int) Auth::user()->org_unit_id;

        if (!$orgUnitId) {
            return response()->json([
                'success' => false,
                'message' => 'Не указана организация.',
            ], 422);
        }

        $token = $this->tokens->generate($orgUnitId, Auth::id());
        $org   = OrganizationUnits::find($orgUnitId);

        // Не суперадмину дублируем токен письмом — как в веб-кабинете.
        $emailed = false;
        if (!$isSuper && Auth::user()->email) {
            try {
                Mail::to(Auth::user()->email)->send(new OnecTokenMail(
                    $token->token,
                    $token->expires_at,
                    $org->name_ru ?? ''
                ));
                $emailed = true;
            } catch (Throwable $e) {
                Log::channel('onec')->error('[1C token email][api] ' . $e->getMessage());
            }
        }

        Log::channel('onec')->info('[1C token][api] issued', [
            'org_unit_id' => $orgUnitId,
            'user_id'     => Auth::id(),
            'expires_at'  => $token->expires_at->toDateTimeString(),
        ]);

        return response()->json([
            'success'            => true,
            'token'              => $token->token,
            'expires_at'         => $token->expires_at->toDateTimeString(),
            'ttl_minutes'        => $this->tokens->ttlMinutes(),
            'refresh_token'      => $token->refresh_token,
            'refresh_expires_at' => $token->refresh_expires_at->toDateTimeString(),
            'org_unit_id'        => $orgUnitId,
            'org_name'           => $org->name_ru ?? null,
            'emailed'            => $emailed,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/onec/token/refresh",
     *     tags={"1C Token"},
     *     summary="Обновить токен интеграции 1С по refresh-токену",
     *     description="Обменивает refresh-токен на новую пару access+refresh. Аутентификация — сам refresh-токен, сессия пользователя НЕ нужна: 1С продлевает доступ самостоятельно, без участия человека. Refresh ротируется: старый становится недействительным сразу после обмена, повторное предъявление гасит все токены организации (защита от утечки). Окно обновления не продлевается — по истечении refresh_expires_at нужен ручной выпуск в кабинете.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="onecr_8Kd2p...",
     *                 description="Refresh-токен, полученный при выпуске или прошлом обновлении")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Выпущена новая пара токенов",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="onec_9ZqK3v..."),
     *             @OA\Property(property="expires_at", type="string", example="2026-07-15 14:45:00"),
     *             @OA\Property(property="ttl_minutes", type="integer", example=60),
     *             @OA\Property(property="refresh_token", type="string", example="onecr_Lm4Xt...",
     *                 description="НОВЫЙ refresh-токен: сохраните его, прежний уже недействителен"),
     *             @OA\Property(property="refresh_expires_at", type="string", example="2026-08-14 13:45:00"),
     *             @OA\Property(property="org_unit_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Refresh-токен неизвестен, истёк или уже использован — нужен новый выпуск в кабинете"),
     *     @OA\Response(response=422, description="refresh_token не передан"),
     *     @OA\Response(response=429, description="Слишком много попыток обновления")
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string', 'max:80'],
        ]);

        $fresh = $this->tokens->refresh($data['refresh_token']);

        if (!$fresh) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh-токен недействителен, истёк или уже был использован. '
                    . 'Выпустите новый токен в кабинете интеграции 1С.',
            ], 401);
        }

        return response()->json([
            'success'            => true,
            'token'              => $fresh->token,
            'expires_at'         => $fresh->expires_at->toDateTimeString(),
            'ttl_minutes'        => $this->tokens->ttlMinutes(),
            'refresh_token'      => $fresh->refresh_token,
            'refresh_expires_at' => $fresh->refresh_expires_at->toDateTimeString(),
            'org_unit_id'        => (int) $fresh->org_unit_id,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/onec/token/current",
     *     tags={"1C Token"},
     *     summary="Текущий активный токен интеграции 1С",
     *     description="Возвращает активный (не истёкший) токен организации, если он есть. Полезно, чтобы не выпускать новый токен без необходимости.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="org_unit_id", in="query", required=false, @OA\Schema(type="integer"),
     *         description="Только для Super_Admin. Для остальных — своя организация."),
     *     @OA\Response(
     *         response=200,
     *         description="Состояние токена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="has_active", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="onec_9ZqK3v..."),
     *             @OA\Property(property="expires_at", type="string", example="2026-07-15 13:45:00"),
     *             @OA\Property(property="refresh_expires_at", type="string", example="2026-08-14 13:45:00"),
     *             @OA\Property(property="refreshable", type="boolean", example=true,
     *                 description="Можно ли продлить доступ по refresh-токену без ручного выпуска"),
     *             @OA\Property(property="org_unit_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не аутентифицирован"),
     *     @OA\Response(response=403, description="Нет доступа к интеграции с 1С"),
     *     @OA\Response(response=422, description="Организация не определена")
     * )
     */
    public function current(Request $request): JsonResponse
    {
        if (!$this->canManage()) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступа к интеграции с 1С.',
            ], 403);
        }

        $isSuper   = $this->isSuperAdmin();
        $orgUnitId = $isSuper
            ? (int) ($request->query('org_unit_id') ?: Auth::user()->org_unit_id)
            : (int) Auth::user()->org_unit_id;

        if (!$orgUnitId) {
            return response()->json([
                'success' => false,
                'message' => 'Не указана организация.',
            ], 422);
        }

        $active = OnecIntegrationToken::where('org_unit_id', $orgUnitId)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        return response()->json([
            'success'            => true,
            'has_active'         => (bool) $active,
            'token'              => $active?->token,
            'expires_at'         => optional($active?->expires_at)->toDateTimeString(),
            'refresh_expires_at' => optional($active?->refresh_expires_at)->toDateTimeString(),
            'refreshable'        => (bool) $active?->isRefreshUsable(),
            'org_unit_id'        => $orgUnitId,
        ]);
    }
}
