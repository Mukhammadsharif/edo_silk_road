<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Localization;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
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

        /*
         | Загрузка тяжелее post_max_size.
         |
         | PHP отбрасывает такой запрос ДО того, как до него доберётся Laravel:
         | $_POST и $_FILES приходят пустыми, поэтому обычная валидация
         | 'file|max:...' тут не срабатывает — пользователь видел голую
         | страницу «Content Too Large» и терял заполненную форму.
         |
         | Возвращаем его на ту же форму с понятным сообщением. Введённые
         | поля сохраняются через withInput(), кроме самих файлов —
         | их браузер приложить заново не может.
         */
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $limit = ini_get('post_max_size');

            $message = __('validate.upload_too_large', ['limit' => $limit]);

            // Строки перевода может не быть — тогда __() вернёт сам ключ
            if ($message === 'validate.upload_too_large') {
                $message = "Загрузка слишком большая. Общий размер файлов в одной "
                    . "форме не должен превышать {$limit}. Прикрепите файлы по частям "
                    . "или сожмите их.";
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'code'    => 'post_too_large',
                ], 413);
            }

            return redirect()->back()
                ->withInput($request->except(['doc_files', 'document_file', 'profile_img']))
                ->withErrors(['files' => $message]);
        });
    })->create();
