<?php

namespace App\Swagger\TelegramMiniApp;

/**
 * Swagger-описание профиля и мастера создания документа в Telegram Mini App.
 *
 * Класс пустой — носитель аннотаций, см. пояснение в AuthEndpoints.
 */
class ProfileAndCreateEndpoints
{
    /**
     * @OA\Get(
     *     path="/api/telegram/profile",
     *     summary="Карточка сотрудника",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/profile/avatar",
     *     summary="Загрузить аватар",
     *     description="Файл сохраняется в public/profile_img как img_{user_id}.png.
     *         К ссылке добавляется метка времени, чтобы браузер не показывал
     *         закэшированную старую картинку.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/profile/avatar",
     *     summary="Удалить аватар",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/profile/password",
     *     summary="Сменить пароль",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/profile/info",
     *     summary="Изменить личные данные",
     *     description="ВНИМАНИЕ: маршрут и метод контроллера существуют, но
     *         мини-апп их не вызывает — в src/shared/api/endpoints.js такого
     *         вызова нет. Либо функция не доведена до интерфейса, либо маршрут
     *         остался от прежней версии.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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

    /* ─────────────────── Мастер создания документа ─────────────────── */

    /**
     * @OA\Get(
     *     path="/api/telegram/create/init/{type}",
     *     summary="Справочники для формы создания",
     *     description="Первый шаг мастера: журналы, уровни конфиденциальности,
     *         подразделения, языки и признак активной подписки.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/create/journals/{j_type_id}",
     *     summary="Подтипы выбранного журнала",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/create/execution-users/{j_id}",
     *     summary="Участники маршрута для журнала",
     *     description="Состав ролей задаётся настройками журнала: кто может
     *         накладывать резолюцию, подписывать и согласовывать.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/create/doc-organizations/{j_id}",
     *     summary="Организации-корреспонденты",
     *     description="Нужны для входящих и исходящих документов.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/create/store",
     *     summary="Создать документ",
     *     description="Последний шаг мастера. Передаётся как multipart/form-data,
     *         потому что вместе с полями идут файлы. Требуется активная подписка
     *         у пользователя.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/create/web-editor",
     *     summary="Передать черновик в веб-редактор на компьютере",
     *     description="Заполненная в телефоне форма сохраняется в кэш на 10 минут,
     *         и возвращается ссылка для открытия на компьютере, где доступен
     *         OnlyOffice. Файлы не переносятся — их нужно приложить заново.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
