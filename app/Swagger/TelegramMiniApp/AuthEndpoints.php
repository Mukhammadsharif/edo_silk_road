<?php

namespace App\Swagger\TelegramMiniApp;

/**
 * Swagger-описание публичных эндпоинтов Telegram Mini App.
 *
 * ПОЧЕМУ ОТДЕЛЬНЫМ ФАЙЛОМ, А НЕ В КОНТРОЛЛЕРЕ.
 * l5-swagger сканирует весь каталог app/ (см. config/l5-swagger.php →
 * annotations), поэтому аннотации могут жить где угодно внутри app/.
 * Держим их отдельно, чтобы:
 *   - не раздувать контроллеры на сотни строк комментариев;
 *   - не трогать рабочий код при правке документации;
 *   - обновление контроллера не затирало описание и наоборот.
 *
 * Класс пустой — нужен только как носитель аннотаций.
 *
 * @OA\Tag(
 *     name="Telegram Mini App",
 *     description="API мини-приложения EDOLINE внутри Telegram. Авторизация —
 *         собственная: Bearer-токен выдаётся эндпоинтом /api/telegram/auth
 *         по подписанному initData от Telegram WebApp, живёт ограниченное
 *         время. Это НЕ Sanctum-токен из /api/auth/login."
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="telegramBearer",
 *     type="http",
 *     scheme="bearer",
 *     description="Токен из POST /api/telegram/auth, передаётся как
 *         Authorization: Bearer {token}"
 * )
 */
class AuthEndpoints
{
    /**
     * @OA\Post(
     *     path="/api/telegram/auth",
     *     summary="Авторизация по Telegram initData",
     *     description="Проверяет HMAC-подпись initData от Telegram WebApp и выдаёт
     *         Bearer-токен. Telegram-аккаунт должен быть заранее привязан к
     *         пользователю EDOLINE через команду /start у бота — иначе 403 not_linked.
     *
     *         Побочный эффект: если у пользователя org_unit_id указывает на
     *         организацию без активной записи в user_work_histories, он
     *         автоматически переключается на организацию с активной записью.
     *         Без этого все списки документов приходили бы пустыми, а
     *         переключателя организаций в мини-аппе нет.",
     *     tags={"Telegram Mini App"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"init_data"},
     *             @OA\Property(
     *                 property="init_data",
     *                 type="string",
     *                 description="Строка window.Telegram.WebApp.initData как есть, URL-encoded",
     *                 example="query_id=AAH...&user=%7B%22id%22%3A123456789%7D&auth_date=1757000000&hash=abc123"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Токен выдан",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="a1b2c3d4e5f6..."),
     *             @OA\Property(property="expires_at", type="string", format="date-time", example="2026-09-16T12:00:00+05:00"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="i.shunkorov"),
     *                 @OA\Property(property="email", type="string", example="i.shunkorov@edoline.uz")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="В initData нет данных пользователя",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="User data not found in initData"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Подпись Telegram не прошла проверку",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Invalid Telegram signature"))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Telegram-аккаунт не привязан к пользователю EDOLINE",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="not_linked"),
     *             @OA\Property(property="message", type="string", example="Telegram аккаунт не привязан. Отправьте /start боту для привязки.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Привязка есть, но пользователь EDOLINE не найден",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="User not found"))
     *     )
     * )
     */
    public function auth() {}

    /**
     * @OA\Post(
     *     path="/api/telegram/resolve-token",
     *     summary="Обменять sign-token на Bearer-токен",
     *     description="Используется страницей подписи E-IMZO (/mobile/sign/{token}),
     *         а не самим мини-аппом: одноразовая ссылка открывается во внешнем
     *         браузере, где Bearer-токена ещё нет.",
     *     tags={"Telegram Mini App"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sign_token"},
     *             @OA\Property(property="sign_token", type="string", example="s1gn70k3n...")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Токен найден",
     *         @OA\JsonContent(@OA\Property(property="token", type="string", example="a1b2c3d4e5f6..."))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Токен неверный или истёк",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Invalid or expired token"))
     *     )
     * )
     */
    public function resolveToken() {}

    /**
     * @OA\Get(
     *     path="/api/telegram/translations",
     *     summary="Переводы интерфейса мини-аппа",
     *     description="Публичный: нужен до авторизации, чтобы экраны загрузки
     *         и ошибок были уже локализованы.",
     *     tags={"Telegram Mini App"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Словарь переводов по языкам",
     *         @OA\JsonContent(type="object", example={"uz": {"login": "Kirish"}, "ru": {"login": "Вход"}})
     *     )
     * )
     */
    public function translations() {}
}
