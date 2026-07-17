<?php

namespace App\Http\Middleware;

use App\Services\Integration1C\OnecLogger;
use App\Services\Integration1C\OnecTokenService;
use Closure;
use Illuminate\Http\Request;

/**
 * Авторизация входящих запросов 1С + определение организации по токену.
 *
 * Логика:
 *  1) Берём токен из заголовка (X-1C-Token или Authorization: Bearer).
 *  2) Сначала пробуем org-токен (привязан к организации, живёт 1 час) →
 *     определяем org_unit_id и кладём в request->attributes('onec_org_unit_id').
 *  3) Если это не org-токен — пробуем статический токен из ONEC_INBOUND_TOKENS (legacy/dev).
 *  4) Нет валидного токена → чистый JSON 401.
 */
class OnecAuth
{
    public function __construct(
        private OnecLogger $logger,
        private OnecTokenService $tokens
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Всегда отвечаем JSON-ом (на случай Accept: */*)
        $request->headers->set('Accept', 'application/json');

        $provided = $request->bearerToken() ?: $request->header('X-1C-Token');

        if (!$provided) {
            return $this->unauthorized($request, 'no_token');
        }

        // 1) Org-токен — определяем организацию
        $orgToken = $this->tokens->resolve($provided);
        if ($orgToken) {
            $this->tokens->markUsed($orgToken);
            // Кладём org_unit_id для использования в синке
            $request->attributes->set('onec_org_unit_id', (int) $orgToken->org_unit_id);
            $request->attributes->set('onec_token_id', $orgToken->id);
            return $next($request);
        }

        // 2) Статический токен (ONEC_INBOUND_TOKENS) — без привязки к организации
        $configured = array_filter(array_map('trim', explode(',', (string) config('onec.inbound_tokens'))));
        if (!empty($configured) && in_array($provided, $configured, true)) {
            return $next($request);
        }

        // 3) Если статические токены не настроены И это был похоже на просроченный org-токен
        if (str_starts_with($provided, 'onec_')) {
            return $this->unauthorized($request, 'expired_or_invalid_org_token');
        }

        // 4) dev-режим: статические токены не настроены вообще → пропускаем
        if (empty($configured)) {
            return $next($request);
        }

        return $this->unauthorized($request, 'invalid_token');
    }

    private function unauthorized(Request $request, string $reason)
    {
        $this->logger->event('AUTH FAILED (inbound)', [
            'path'   => $request->path(),
            'reason' => $reason,
            'ip'     => $request->ip(),
        ], 'warning');

        $msg = $reason === 'expired_or_invalid_org_token'
            ? 'Токен организации недействителен или истёк (срок жизни 1 час). Обновите его: POST /api/onec/token/refresh с refresh_token. Если refresh истёк — выпустите новый токен в кабинете.'
            : 'Неавторизованный запрос. Передайте токен в заголовке X-1C-Token или Authorization: Bearer {token}';

        return response()->json(['success' => false, 'message' => $msg], 401);
    }
}
