<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'telegram/webhook',
	    'callback',
        '/callback',
        'api/callback',
        '/api/callback',
        'login',
        'api/*',
        'e-imzo/*',
        '/e-imzo/*',
        'e-imzo/backend-auth',
    ];

//    public function handle($request, \Closure $next)
//    {
//        \Log::info('CSRF Middleware - Request Path: ' . $request->path());
//        return parent::handle($request, $next);
//    }
}
