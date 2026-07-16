<?php

namespace App\Services\Integration1C;

use App\Models\Integration1C\OnecIntegrationToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Генерация, обновление и проверка токенов интеграции 1С, привязанных к организации.
 *
 * Схема (как в OAuth):
 *  - access-токен (token) — короткий, 60 минут, идёт в каждом запросе 1С (X-1C-Token);
 *  - refresh-токен (refresh_token) — долгий (30 дней), только для получения нового
 *    access-токена через POST /api/onec/token/refresh, без участия человека.
 *
 * Refresh ротируется: при каждом обмене выпускается НОВАЯ пара, старый refresh
 * помечается использованным. Повторное предъявление использованного refresh —
 * признак утечки: вся цепочка токенов организации гасится.
 */
class OnecTokenService
{
    /** Срок жизни access-токена (минут) */
    private const TTL_MINUTES = 60;

    /** Срок жизни refresh-токена (дней) */
    private const REFRESH_TTL_DAYS = 30;

    /**
     * Сгенерировать новую пару токенов для организации.
     * Старые активные токены этой организации помечаются истёкшими (один активный за раз).
     */
    public function generate(int $orgUnitId, ?int $createdBy = null): OnecIntegrationToken
    {
        $this->expireActiveTokens($orgUnitId);

        return OnecIntegrationToken::create([
            'token'              => 'onec_' . Str::random(48),
            'org_unit_id'        => $orgUnitId,
            'created_by'         => $createdBy,
            'expires_at'         => now()->addMinutes($this->ttlMinutes()),
            'used_count'         => 0,
            'refresh_token'      => 'onecr_' . Str::random(48),
            'refresh_expires_at' => now()->addDays($this->refreshTtlDays()),
        ]);
    }

    /**
     * Обменять refresh-токен на новую пару (ротация).
     *
     * @return OnecIntegrationToken|null null — refresh неизвестен, истёк или уже использован
     */
    public function refresh(string $refreshToken): ?OnecIntegrationToken
    {
        $record = OnecIntegrationToken::where('refresh_token', $refreshToken)->first();

        if (!$record) {
            return null;
        }

        // Повторное использование уже обменянного refresh — вероятная утечка:
        // гасим все активные токены организации, дальше нужен ручной выпуск.
        if ($record->isRefreshUsed()) {
            Log::channel('onec')->warning('[1C token] refresh reuse detected — revoking chain', [
                'org_unit_id' => $record->org_unit_id,
                'token_id'    => $record->id,
            ]);
            $this->revokeOrgTokens((int) $record->org_unit_id);

            return null;
        }

        if ($record->isRefreshExpired()) {
            return null;
        }

        // Ротация: гасим текущий access и выпускаем новую пару в той же цепочке.
        $record->forceFill([
            'expires_at'      => now(),
            'refresh_used_at' => now(),
        ])->save();

        $fresh = OnecIntegrationToken::create([
            'token'              => 'onec_' . Str::random(48),
            'org_unit_id'        => $record->org_unit_id,
            'created_by'         => $record->created_by,
            'parent_id'          => $record->id,
            'expires_at'         => now()->addMinutes($this->ttlMinutes()),
            'used_count'         => 0,
            'refresh_token'      => 'onecr_' . Str::random(48),
            // Окно обновления НЕ продлевается бесконечно: наследуем исходный срок,
            // чтобы раз в 30 дней ответственный подтверждал интеграцию вручную.
            'refresh_expires_at' => $record->refresh_expires_at,
        ]);

        Log::channel('onec')->info('[1C token] refreshed', [
            'org_unit_id' => $fresh->org_unit_id,
            'from_id'     => $record->id,
            'to_id'       => $fresh->id,
            'expires_at'  => $fresh->expires_at->toDateTimeString(),
        ]);

        return $fresh;
    }

    /**
     * Найти валидный (не истёкший) access-токен. Возвращает модель или null.
     */
    public function resolve(string $token): ?OnecIntegrationToken
    {
        $record = OnecIntegrationToken::where('token', $token)->first();

        if (!$record || $record->isExpired()) {
            return null;
        }

        return $record;
    }

    /**
     * Отметить использование токена.
     */
    public function markUsed(OnecIntegrationToken $token): void
    {
        $token->forceFill([
            'last_used_at' => now(),
            'used_count'   => $token->used_count + 1,
        ])->save();
    }

    /** Активный (не истёкший) токен организации, если есть */
    public function activeFor(int $orgUnitId): ?OnecIntegrationToken
    {
        return OnecIntegrationToken::where('org_unit_id', $orgUnitId)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /** Погасить все токены организации: и access, и возможность refresh */
    public function revokeOrgTokens(int $orgUnitId): void
    {
        OnecIntegrationToken::where('org_unit_id', $orgUnitId)
            ->whereNull('refresh_used_at')
            ->update([
                'expires_at'         => now(),
                'refresh_expires_at' => now(),
            ]);
    }

    public function ttlMinutes(): int
    {
        return (int) config('onec.token_ttl_minutes', self::TTL_MINUTES);
    }

    public function refreshTtlDays(): int
    {
        return (int) config('onec.refresh_ttl_days', self::REFRESH_TTL_DAYS);
    }

    /* ------------------------------------------------------------------ */

    /** Истечь предыдущие активные access-токены организации (один активный за раз) */
    private function expireActiveTokens(int $orgUnitId): void
    {
        OnecIntegrationToken::where('org_unit_id', $orgUnitId)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->update(['expires_at' => now()]);
    }
}
