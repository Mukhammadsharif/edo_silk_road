<?php

namespace App\Http\Controllers\Api\Didox;

use App\Services\Didox\DidoxClient;
use App\Services\Didox\DidoxTokenService;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Общая логика контроллеров, ходящих в Didox от имени пользователя.
 *
 * Мультиорганизационность: ИНН НЕ передаётся в запросе. Организация
 * берётся из активной организации пользователя (users.org_unit_id),
 * её ИНН — из organization_units.tax_number. Каждая организация
 * работает только со своими документами.
 */
trait ResolvesDidoxClient
{
    /**
     * @throws RuntimeException если нет свежего токена (нужна повторная авторизация).
     */
    protected function authorizedClient(Request $request): DidoxClient
    {
        /** @var DidoxTokenService $tokens */
        $tokens = app(DidoxTokenService::class);
        /** @var DidoxClient $client */
        $client = app(DidoxClient::class);

        $token = $tokens->getFreshToken($request->user()->id, $this->currentOrgId($request));

        return $client->withUserKey($token);
    }

    /** Активная организация пользователя. */
    protected function currentOrgId(Request $request): int
    {
        $orgId = (int) ($request->user()->org_unit_id ?? 0);

        if ($orgId <= 0) {
            throw new RuntimeException('У пользователя не выбрана активная организация.');
        }

        return $orgId;
    }
}
