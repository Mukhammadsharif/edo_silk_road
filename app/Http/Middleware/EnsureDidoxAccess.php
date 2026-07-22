<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Пропускает к разделу Didox (/external-docs/*) только если у организации
 * пользователя включён Didox в подписке (has_didox_access). Иначе 403.
 */
class EnsureDidoxAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->hasRole('Super_Admin') && ! $user->hasDidoxAccess())) {
            abort(403, 'Didox moduli tashkilotingiz uchun yoqilmagan.');
        }

        return $next($request);
    }
}
