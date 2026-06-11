<?php

namespace App\Services\Integration1C;

use App\Models\Integration1C\OnecIntegrationToken;
use Illuminate\Support\Str;

/**
 * Генерация и проверка токенов интеграции 1С, привязанных к организации.
 * Токен живёт 1 час. По токену определяется org_unit_id.
 */
class OnecTokenService
{
    /** Срок жизни токена (минут) */
    private const TTL_MINUTES = 60;

    /**
     * Сгенерировать новый токен для организации.
     * Старые активные токены этой организации помечаются истёкшими (один активный за раз).
     */
    public function generate(int $orgUnitId, ?int $createdBy = null): OnecIntegrationToken
    {
        // Истечь предыдущие активные токены этой организации
        OnecIntegrationToken::where('org_unit_id', $orgUnitId)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->update(['expires_at' => now()]);

        return OnecIntegrationToken::create([
            'token'      => 'onec_' . Str::random(48),
            'org_unit_id'=> $orgUnitId,
            'created_by' => $createdBy,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'used_count' => 0,
        ]);
    }

    /**
     * Найти валидный (не истёкший) токен. Возвращает модель или null.
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

    public function ttlMinutes(): int
    {
        return self::TTL_MINUTES;
    }
}
