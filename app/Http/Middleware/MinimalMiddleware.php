<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MinimalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request); // No session or CSRF
    }
}
