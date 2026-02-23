<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BypassSessionAndCsrf
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('telegram/webhook')) {
            return $next($request); // Bypass session and CSRF
        }
        return $next($request); // Continue with other middleware
    }
}
