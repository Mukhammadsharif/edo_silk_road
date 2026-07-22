<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Localization;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('telegram')
                ->group(base_path('routes/telegram.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Define the 'api' middleware group
        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            SubstituteBindings::class,
        ]);

        // Append middleware to the 'web' group
        $middleware->web(append: [
            Localization::class,
        ]);

        // Настройка CSRF исключений (вместо VerifyCsrfToken в web group)
        $middleware->validateCsrfTokens(except: [
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
            '/frontend/mobile/upload'
        ]);

        // Define middleware aliases
        $middleware->alias([
            'telegram' => \App\Http\Middleware\MinimalMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Все ошибки на api/* отдаём как JSON, а не HTML / редирект на страницу логина.
        // Иначе при протухшем токене мобильное приложение получает HTML со статусом 200
        // (редирект на /login), который нельзя распарсить и по которому не понять,
        // что пора разлогинить пользователя.
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Единообразный JSON-ответ при ошибке аутентификации в API.
        // Статус 401 (Unauthenticated) — семантически верный для истёкшего/недействительного
        // токена. Если мобильное приложение завязано на 403 — поменяйте код здесь.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'code'    => 'token_invalid_or_expired',
                ], 401);
            }
        });
    })->create();
