<?php

namespace App\Services\Didox;

use App\Models\Admin\OrganizationUnits;
use App\Models\Didox\DidoxToken;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Управление user-токеном Didox с привязкой к организации EDOLINE.
 *
 * Система мультиорганизационная: у каждой организации свой ИНН
 * (organization_units.tax_number). Токен хранится на пару (user, org),
 * ИНН для логина в Didox всегда берётся из tax_number активной
 * организации — пользователь его не вводит.
 *
 * Токен Didox живёт 360 минут; повторный логин требует участия
 * пользователя (ЭЦП/пароль) и не может выполняться из крона.
 */
class DidoxTokenService
{
    public function __construct(private DidoxClient $client) {}

    /** ИНН организации из справочника. Бросает исключение, если не заполнен. */
    public function resolveOrgTin(int $orgUnitId): string
    {
        $org = OrganizationUnits::find($orgUnitId);

        if (!$org) {
            throw new RuntimeException("Организация #{$orgUnitId} не найдена.");
        }

        $tin = trim((string) $org->tax_number);

        if ($tin === '') {
            throw new RuntimeException("У организации «{$org->name_ru}» не заполнен ИНН (tax_number).");
        }

        return $tin;
    }

    /** Живой токен для пары (user, org) или исключение — нужна переавторизация. */
    public function getFreshToken(int $userId, int $orgUnitId): string
    {
        $row = DidoxToken::where('user_id', $userId)
            ->where('org_unit_id', $orgUnitId)
            ->first();

        if ($row && $row->isFresh()) {
            $row->markUsed();
            return $row->token;
        }

        throw new RuntimeException('Didox token expired or missing: re-authentication required.');
    }

    /** Есть ли живой токен (без исключения) — для UI. */
    public function hasFreshToken(int $userId, int $orgUnitId): bool
    {
        $row = DidoxToken::where('user_id', $userId)
            ->where('org_unit_id', $orgUnitId)
            ->first();

        return (bool) ($row && $row->isFresh());
    }

    /**
     * Логин по ЭЦП. $signature — timeStampTokenB64 (подписан ИНН организации,
     * E-IMZO + timestamp выполняются на клиенте).
     */
    public function loginByEcp(int $userId, int $orgUnitId, string $signature, ?string $locale = null): string
    {
        $tin   = $this->resolveOrgTin($orgUnitId);
        $resp  = $this->client->loginByEcp($tin, $signature, $locale);
        $token = $this->extractToken($resp, 'ecp');

        return $this->store($userId, $orgUnitId, $tin, $token, 'ecp');
    }

    /** Логин по паролю регистрации Didox (пароль организации). */
    public function loginByPassword(int $userId, int $orgUnitId, string $password, ?string $locale = null): string
    {
        $tin   = $this->resolveOrgTin($orgUnitId);
        $resp  = $this->client->loginByPassword($tin, $password, $locale);
        $token = $this->extractToken($resp, 'password');

        return $this->store($userId, $orgUnitId, $tin, $token, 'password');
    }

    /**
     * Вход в компанию под физлицом: токен физлица обменивается на токен
     * компании текущей организации.
     */
    public function loginCompany(int $userId, int $orgUnitId, string $userToken, ?string $locale = null): string
    {
        $tin   = $this->resolveOrgTin($orgUnitId);
        $resp  = $this->client->loginCompany($tin, $userToken, $locale);
        $token = $this->extractToken($resp, 'company');

        return $this->store($userId, $orgUnitId, $tin, $token, 'company');
    }

    /** Инвалидировать локальный токен (например, при 401 от Didox). */
    public function forget(int $userId, int $orgUnitId): void
    {
        DidoxToken::where('user_id', $userId)->where('org_unit_id', $orgUnitId)->delete();
    }

    /* ------------------------------------------------------------------ */

    private function extractToken(array $resp, string $context): string
    {
        if (empty($resp['success'])) {
            Log::channel('didox')->error("[Didox] login failed ({$context})", ['resp' => $resp['error'] ?? null]);
            throw new RuntimeException('Didox login failed: ' . json_encode($resp['error'] ?? 'unknown'));
        }

        $token = $resp['data']['token'] ?? null;

        if (!$token) {
            throw new RuntimeException('Didox login: token missing in response.');
        }

        return $token;
    }

    private function store(int $userId, int $orgUnitId, string $tin, string $token, string $method): string
    {
        $ttl = (int) config('didox.token_ttl_minutes', 360);

        DidoxToken::updateOrCreate(
            ['user_id' => $userId, 'org_unit_id' => $orgUnitId],
            [
                'tin'         => $tin,
                'token'       => $token,
                'auth_method' => $method,
                'expires_at'  => now()->addMinutes($ttl),
            ]
        );

        return $token;
    }
}
