<?php

namespace App\Swagger\MobileApi;

/**
 * Swagger-описание API мобильного приложения EDOLINE.
 *
 * Эндпоинты те же, что у Telegram Mini App, но авторизация другая —
 * Sanctum-токен из POST /api/auth/login, как в остальном API. Контроллеры
 * общие: внутри они достают пользователя через трейт ResolvesApiUser,
 * который принимает оба типа токена.
 *
 * Описания вынесены отдельным классом-носителем (l5-swagger сканирует весь
 * каталог app/), чтобы правка документации не трогала рабочий код.
 * Схемы ответов переиспользуются из раздела Telegram Mini App —
 * см. App\Swagger\TelegramMiniApp\Schemas.
 *
 * @OA\Tag(
 *     name="Mobile API",
 *     description="API мобильного приложения. Авторизация: POST /api/v1/mobile/login
 *         возвращает Sanctum-токен, он передаётся как Authorization: Bearer.
 *         Набор эндпоинтов повторяет Telegram Mini App — отличается только
 *         способом авторизации и префиксом пути (/api/v1/mobile вместо
 *         /api/telegram)."
 * )
 *
 * Схема безопасности sanctum здесь не объявляется — она уже описана
 * в App\Swagger\SwaggerSecurity, а повторное объявление ломает генерацию.
 * Ниже на неё только ссылаются.
 *
 * Упоминать имя аннотации в тексте комментария тоже нельзя: swagger-php
 * разбирает весь докблок целиком и принимает такое упоминание за настоящее
 * объявление без обязательных полей.
 */
class MobileEndpoints
{

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/login",
     *     summary="Вход в мобильное приложение",
     *     description="Возвращает Sanctum-токен со сроком жизни 30 дней.
     *         В отличие от /api/auth/login не гасит токены других устройств:
     *         токен именуется по device_name, поэтому телефон и планшет
     *         работают одновременно.
     *
     *         Побочный эффект: если org_unit_id пользователя указывает на
     *         организацию без активной записи работы, он переключается на
     *         организацию с активной записью — иначе списки документов
     *         пришли бы пустыми.",
     *     tags={"Mobile API"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="i.shunkorov"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="device_name", type="string", maxLength=100, example="iPhone 15", description="Имя устройства; по умолчанию mobile")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Токен выдан",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=191),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="org_unit_id", type="integer", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401, description="Неверный логин или пароль",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_credentials"),
     *             @OA\Property(property="message", type="string", example="Login yoki parol noto'g'ri")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403, description="Учётная запись отключена",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="inactive"))
     *     ),
     *     @OA\Response(response=422, description="Не переданы обязательные поля"),
     *     @OA\Response(response=429, description="Слишком много попыток входа")
     * )
     */
    public function login() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/logout",
     *     summary="Выход с текущего устройства",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Токен погашен", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function logout() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/logout-all",
     *     summary="Выход со всех устройств",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200, description="Все токены погашены",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="revoked", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function logoutAll() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/me",
     *     summary="Текущий пользователь",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Данные пользователя", @OA\JsonContent(@OA\Property(property="user", type="object"))),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function me() {}
    /**
     * @OA\Get(
     *     path="/api/v1/mobile/translations",
     *     summary="Переводы интерфейса мини-аппа",
     *     description="В мобильной группе, в отличие от мини-аппа, требует
     *         авторизации: вся группа /api/v1/mobile закрыта middleware
     *         auth:sanctum.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Словарь переводов по языкам",
     *         @OA\JsonContent(type="object", example={"uz": {"login": "Kirish"}, "ru": {"login": "Вход"}})
     *     )
     * )
     */
    public function translations() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/counts",
     *     summary="Счётчики на главном экране",
     *     description="Считаются по ВСЕМ записям user_work_histories пользователя,
     *         без фильтра по организации — намеренно, чтобы числа совпадали
     *         с веб-версией (menuCount).
     *
     *         Счётчика по исполнению (роль EXEC) здесь нет: этот раздел
     *         в мобильном API не реализован.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Количество документов по разделам",
     *         @OA\JsonContent(
     *             @OA\Property(property="for_resolution", type="integer", example=3, description="Роль RESOL, статус шага 2 или 6"),
     *             @OA\Property(property="for_sign", type="integer", example=5, description="Роль SIGN, статус шага 2"),
     *             @OA\Property(property="for_approval", type="integer", example=2, description="Роль AGREE, статус шага 2"),
     *             @OA\Property(property="my_docs", type="integer", example=12, description="Свои документы, кроме статусов 2 и 13")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function counts() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/status-options",
     *     summary="Варианты фильтра по статусу шага",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="category", in="query", required=false,
     *         description="Роль в маршруте документа",
     *         @OA\Schema(type="string", enum={"RESOL","SIGN","AGREE"}, example="SIGN")
     *     ),
     *     @OA\Parameter(
     *         name="lang", in="query", required=false,
     *         description="Язык подписей. Значение cyr трактуется как uz",
     *         @OA\Schema(type="string", enum={"uz","ru","en","cyr"}, example="uz")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Список статусов",
     *         @OA\JsonContent(
     *             @OA\Property(property="category", type="string", example="SIGN"),
     *             @OA\Property(property="type", type="string", example="doc_signer"),
     *             @OA\Property(property="default", type="string", example="2"),
     *             @OA\Property(
     *                 property="options", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="code", type="string", example="2"),
     *                     @OA\Property(property="title", type="string", example="Imzolashda")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function statusOptions() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/documents",
     *     summary="Документы, пришедшие пользователю",
     *     description="Без параметра category возвращаются все три роли сразу.
     *
     *         ВАЖНО: роль EXEC (исполнение резолюции) в выдачу НЕ входит —
     *         ни здесь, ни в счётчиках, ни в карточке документа. Эти три роли
     *         завершаются подписью через E-IMZO, а исполнение требует другого
     *         действия — отчёта о выполнении, которого в мобильном API пока нет.
     *         В веб-версии исполнение живёт в отдельном разделе.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="category", in="query", required=false,
     *         @OA\Schema(type="string", enum={"RESOL","SIGN","AGREE"}, example="SIGN")
     *     ),
     *     @OA\Parameter(
     *         name="step_status", in="query", required=false,
     *         description="Код статуса шага из /status-options",
     *         @OA\Schema(type="string", example="2")
     *     ),
     *     @OA\Parameter(
     *         name="lang", in="query", required=false,
     *         @OA\Schema(type="string", enum={"uz","ru","en","cyr"}, example="uz")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Список документов",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer", example=4),
     *             @OA\Property(
     *                 property="documents", type="array",
     *                 @OA\Items(ref="#/components/schemas/TelegramDocumentListItem")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function list() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/documents/{j_id}",
     *     summary="Карточка входящего документа",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id", in="path", required=true,
     *         description="document_journals.id",
     *         @OA\Schema(type="integer", example=497)
     *     ),
     *     @OA\Parameter(
     *         name="lang", in="query", required=false,
     *         @OA\Schema(type="string", enum={"uz","ru","en","cyr"}, example="uz")
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Карточка документа",
     *         @OA\JsonContent(ref="#/components/schemas/TelegramDocumentDetail")
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=403, description="Документ не адресован пользователю"),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function detail() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/documents/{j_id}/sign-link",
     *     summary="Одноразовая ссылка на страницу подписи E-IMZO",
     *     description="Подпись происходит вне приложения: ссылка открывается во
     *         внешнем браузере, где работает E-IMZO. Страница своя
     *         (/mobile/app/sign/{token}), токены — в таблице mobile_sign_tokens,
     *         поэтому вход в Telegram Mini App на ссылку не влияет.
     *         Ссылка одноразовая и живёт 15 минут.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="j_id", in="path", required=true, @OA\Schema(type="integer", example=497)),
     *
     *     @OA\Response(
     *         response=200, description="Ссылка создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="sign_url", type="string", example="https://edoline.uz/mobile/app/sign/s1gn70k3n"),
     *             @OA\Property(property="sign_type", type="string", example="sign_resolution"),
     *             @OA\Property(property="expires_in", type="integer", example=900, description="Секунд до истечения")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(
     *         response=403, description="Нет активного шага на этом пользователе",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Document not available"))
     *     ),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function signToken() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/my-documents",
     *     summary="Документы, созданные пользователем",
     *     description="Базовые журналы входящих и исходящих исключены —
     *         это раздел собственных рабочих документов.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(
     *         name="status", in="query", required=false,
     *         description="Код статуса документа",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Parameter(name="lang", in="query", required=false, @OA\Schema(type="string", enum={"uz","ru","en","cyr"})),
     *
     *     @OA\Response(
     *         response=200, description="Страница списка",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TelegramDocumentListItem")),
     *             @OA\Property(
     *                 property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42),
     *                 @OA\Property(property="has_more", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function myList() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/my-documents/{j_id}",
     *     summary="Карточка своего документа",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="j_id", in="path", required=true, @OA\Schema(type="integer", example=497)),
     *
     *     @OA\Response(
     *         response=200, description="Карточка документа",
     *         @OA\JsonContent(ref="#/components/schemas/TelegramMyDocumentDetail")
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=403, description="Документ создан не этим пользователем"),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function myDetail() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/my-documents/{j_id}/send",
     *     summary="Отправить свой документ по маршруту",
     *     description="Отправка требует подписи автора, поэтому возвращается
     *         ссылка на страницу E-IMZO, а не результат отправки. Документ
     *         должен быть в статусе 1 (черновик).",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="j_id", in="path", required=true, @OA\Schema(type="integer", example=497)),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sign_type"},
     *             @OA\Property(property="sign_type", type="string", example="from_signer_to_ag")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Ссылка на подпись создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="sign_url", type="string", example="https://edoline.uz/mobile/app/sign/s1gn70k3n"),
     *             @OA\Property(property="sign_type", type="string", example="from_signer_to_ag"),
     *             @OA\Property(property="expires_in", type="integer", example=900)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=403, description="Документ создан не этим пользователем"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(
     *         response=422, description="Документ не в статусе черновика",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_status"),
     *             @OA\Property(property="message", type="string", example="Hujjat status bilan bu amalni bajarib bo'lmaydi")
     *         )
     *     )
     * )
     */
    public function send() {}

    /**
     * @OA\Delete(
     *     path="/api/v1/mobile/my-documents/{j_id}",
     *     summary="Удалить свой документ",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="j_id", in="path", required=true, @OA\Schema(type="integer", example=497)),
     *
     *     @OA\Response(
     *         response=200, description="Документ удалён",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=403, description="Документ создан не этим пользователем"),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function remove() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/profile",
     *     summary="Карточка сотрудника",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200, description="Данные профиля",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="profile", type="object",
     *                 @OA\Property(property="id", type="integer", example=191),
     *                 @OA\Property(property="username", type="string", example="i.shunkorov"),
     *                 @OA\Property(property="email", type="string", example="i.shunkorov@edoline.uz"),
     *                 @OA\Property(property="full_name", type="string", example="Шункоров Икром"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="901234567"),
     *                 @OA\Property(property="birthday", type="string", nullable=true, format="date", example="1990-05-12"),
     *                 @OA\Property(property="avatar_url", type="string", nullable=true, example="https://edoline.uz/profile_img/img_191.png"),
     *                 @OA\Property(
     *                     property="organization", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=28),
     *                     @OA\Property(property="name_uz", type="string", example="ООО ATLAS GROUP"),
     *                     @OA\Property(property="name_ru", type="string", nullable=true),
     *                     @OA\Property(property="name_en", type="string", nullable=true),
     *                     @OA\Property(property="tax_number", type="string", nullable=true, example="201122334")
     *                 ),
     *                 @OA\Property(property="department", type="string", nullable=true, example="IT bo'limi")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=404, description="Пользователь не найден")
     * )
     */
    public function profile() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/profile/avatar",
     *     summary="Загрузить аватар",
     *     description="Файл сохраняется в public/profile_img как img_{user_id}.png.
     *         К ссылке добавляется метка времени, чтобы браузер не показывал
     *         закэшированную старую картинку.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"avatar"},
     *                 @OA\Property(property="avatar", type="string", format="binary", description="Изображение, до 5 МБ")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Аватар обновлён",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="avatar_url", type="string", example="https://edoline.uz/profile_img/img_191.png?v=1757000000")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=422, description="Файл не изображение или больше 5 МБ"),
     *     @OA\Response(
     *         response=500, description="Не удалось записать файл",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="upload_failed"),
     *             @OA\Property(property="message", type="string", example="Yuklash xatosi")
     *         )
     *     )
     * )
     */
    public function uploadAvatar() {}

    /**
     * @OA\Delete(
     *     path="/api/v1/mobile/profile/avatar",
     *     summary="Удалить аватар",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200, description="Аватар удалён",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function deleteAvatar() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/profile/password",
     *     summary="Сменить пароль",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password"),
     *             @OA\Property(property="new_password", type="string", format="password", minLength=6),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Пароль изменён",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=422, description="Текущий пароль неверен либо новый не прошёл проверку")
     * )
     */
    public function changePassword() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/profile/info",
     *     summary="Изменить личные данные",
     *     description="ВНИМАНИЕ: маршрут и метод контроллера существуют, но
     *         мини-апп их не вызывает — в src/shared/api/endpoints.js такого
     *         вызова нет. Либо функция не доведена до интерфейса, либо маршрут
     *         остался от прежней версии.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"l_name","f_name"},
     *             @OA\Property(property="l_name", type="string", maxLength=100, example="Шункоров"),
     *             @OA\Property(property="f_name", type="string", maxLength=100, example="Икром"),
     *             @OA\Property(property="m_name", type="string", maxLength=100, nullable=true, example="Анварович")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Данные обновлены"),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function updateInfo() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/create/init/{type}",
     *     summary="Справочники для формы создания",
     *     description="Первый шаг мастера: журналы, уровни конфиденциальности,
     *         подразделения, языки и признак активной подписки.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="type", in="path", required=true,
     *         description="Тип документа",
     *         @OA\Schema(type="string", enum={"incoming","outgoing","internal"}, example="internal")
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Справочники",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="internal"),
     *             @OA\Property(property="has_subscription", type="boolean", example=true),
     *             @OA\Property(property="journals", type="array", @OA\Items(ref="#/components/schemas/TelegramJournalOption")),
     *             @OA\Property(
     *                 property="confidences", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Oddiy")
     *                 )
     *             ),
     *             @OA\Property(property="departments", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="languages", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(
     *         response=422, description="Неизвестный тип документа",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="invalid_type"))
     *     )
     * )
     */
    public function createInit() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/create/journals/{j_type_id}",
     *     summary="Подтипы выбранного журнала",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="j_type_id", in="path", required=true, @OA\Schema(type="integer", example=9)),
     *
     *     @OA\Response(
     *         response=200, description="Вложенные журналы",
     *         @OA\JsonContent(
     *             @OA\Property(property="journals", type="array", @OA\Items(ref="#/components/schemas/TelegramJournalOption"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function createJournals() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/create/execution-users/{j_id}",
     *     summary="Участники маршрута для журнала",
     *     description="Состав ролей задаётся настройками журнала: кто может
     *         накладывать резолюцию, подписывать и согласовывать.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="j_id", in="path", required=true, @OA\Schema(type="integer", example=202)),
     *
     *     @OA\Response(
     *         response=200, description="Пользователи по ролям",
     *         @OA\JsonContent(
     *             @OA\Property(property="resolution_users", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="signatory_users", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="agreement_users", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function executionUsers() {}

    /**
     * @OA\Get(
     *     path="/api/v1/mobile/create/doc-organizations/{j_id}",
     *     summary="Организации-корреспонденты",
     *     description="Нужны для входящих и исходящих документов.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="j_id", in="path", required=true, @OA\Schema(type="integer", example=202)),
     *
     *     @OA\Response(
     *         response=200, description="Список организаций",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="organizations", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="title", type="string", example="Adliya vazirligi")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function docOrganizations() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/create/store",
     *     summary="Создать документ",
     *     description="Последний шаг мастера. Передаётся как multipart/form-data,
     *         потому что вместе с полями идут файлы. Требуется активная подписка
     *         у пользователя.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"journal_type","resolution_user"},
     *                 @OA\Property(property="journal_type", type="integer", example=9, description="journal_types.id"),
     *                 @OA\Property(property="journal_type2", type="integer", nullable=true, description="Подтип журнала"),
     *                 @OA\Property(property="doc_name", type="string", maxLength=500),
     *                 @OA\Property(property="doc_number", type="string", maxLength=100),
     *                 @OA\Property(property="doc_date", type="string", maxLength=20, example="15.09.2026"),
     *                 @OA\Property(property="doc_text", type="string"),
     *                 @OA\Property(property="doc_lang", type="string", enum={"uz","kr","ru","en"}),
     *                 @OA\Property(property="confidence", type="integer"),
     *                 @OA\Property(property="signing_term", type="string", maxLength=50),
     *                 @OA\Property(property="j_number", type="string", maxLength=50),
     *                 @OA\Property(property="resolution_user", type="integer", description="Обязателен: кто накладывает резолюцию"),
     *                 @OA\Property(property="to_emp", type="integer", nullable=true),
     *                 @OA\Property(property="to_agree", type="array", @OA\Items(type="integer"), description="Согласующие"),
     *                 @OA\Property(property="doc_organization", type="integer", nullable=true),
     *                 @OA\Property(property="main_file", type="string", format="binary", description="Текст документа, до 50 МБ"),
     *                 @OA\Property(property="doc_files", type="array", @OA\Items(type="string", format="binary"), description="Вложения, по 50 МБ")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Документ создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="journal_id", type="integer", example=498),
     *             @OA\Property(property="doc_hash", type="string", example="a1b2c3d4"),
     *             @OA\Property(property="view_url", type="string", example="https://edoline.uz/office/view-work-doc/498/a1b2c3d4"),
     *             @OA\Property(property="message", type="string", example="Hujjat muvaffaqiyatli yaratildi")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен"),
     *     @OA\Response(
     *         response=422, description="Ошибка валидации или нет активной подписки",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="validation"),
     *             @OA\Property(property="messages", type="object", example={"resolution_user": {"Rezolyutsiya uchun foydalanuvchi tanlang"}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500, description="Ошибка при сохранении",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="server_error"),
     *             @OA\Property(property="message", type="string", example="Не удалось создать документ. Попробуйте ещё раз.")
     *         )
     *     )
     * )
     */
    public function createStore() {}

    /**
     * @OA\Post(
     *     path="/api/v1/mobile/create/web-editor",
     *     summary="Передать черновик в веб-редактор на компьютере",
     *     description="Заполненная в телефоне форма сохраняется в кэш на 10 минут,
     *         и возвращается ссылка для открытия на компьютере, где доступен
     *         OnlyOffice. Файлы не переносятся — их нужно приложить заново.",
     *     tags={"Mobile API"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Те же поля, что и у /create/store, кроме файлов",
     *         @OA\JsonContent(type="object")
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Ссылка создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="url", type="string", example="https://edoline.uz/telegram/handoff-create?draft=abc123"),
     *             @OA\Property(property="expires", type="string", format="date-time", example="2026-09-16T12:10:00+05:00")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Нет или истёк токен")
     * )
     */
    public function webEditor() {}
}
