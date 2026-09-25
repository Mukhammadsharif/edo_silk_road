<?php

namespace App\Swagger\TelegramMiniApp;

/**
 * Схемы ответов Telegram Mini App и эндпоинты привязки E-IMZO ID-Card.
 *
 * Класс пустой — носитель аннотаций, см. пояснение в AuthEndpoints.
 *
 * @OA\Schema(
 *     schema="TelegramDocumentListItem",
 *     type="object",
 *     title="Документ в списке",
 *
 *     @OA\Property(property="id", type="integer", example=497, description="document_journals.id"),
 *     @OA\Property(property="doc_number", type="string", example="142/2026", description="Прочерк, если номера нет"),
 *     @OA\Property(property="doc_name", type="string", example="Ta'til berish to'g'risida"),
 *     @OA\Property(property="created_at", type="string", example="12.09.2026", description="Формат d.m.Y"),
 *     @OA\Property(property="role", type="string", enum={"RESOL","SIGN","AGREE"}, example="SIGN"),
 *     @OA\Property(property="role_label", type="string", example="Imzolash"),
 *     @OA\Property(property="step_status", type="integer", nullable=true, example=2),
 *     @OA\Property(property="status_label", type="string", example="Imzolashda")
 * )
 *
 * @OA\Schema(
 *     schema="TelegramDocumentDetail",
 *     type="object",
 *     title="Карточка входящего документа",
 *
 *     @OA\Property(property="id", type="integer", example=497),
 *     @OA\Property(property="doc_id", type="integer", example=1203),
 *     @OA\Property(property="doc_number", type="string", nullable=true, example="142/2026"),
 *     @OA\Property(property="doc_name", type="string", nullable=true),
 *     @OA\Property(property="doc_date", type="string", nullable=true, example="12.09.2026"),
 *     @OA\Property(property="doc_term", type="string", nullable=true, example="20.09.2026"),
 *     @OA\Property(property="doc_text", type="string", nullable=true),
 *     @OA\Property(property="doc_hash", type="string", nullable=true, example="a1b2c3d4"),
 *     @OA\Property(property="journal", type="string", nullable=true, example="Ta'til"),
 *     @OA\Property(property="status", type="string", nullable=true, example="Imzolashda"),
 *     @OA\Property(property="role", type="string", nullable=true, enum={"RESOL","SIGN","AGREE"}),
 *     @OA\Property(property="files", type="array", @OA\Items(ref="#/components/schemas/TelegramDocumentFile"))
 * )
 *
 * @OA\Schema(
 *     schema="TelegramMyDocumentDetail",
 *     type="object",
 *     title="Карточка своего документа",
 *     description="Отличается от входящего тем, что вместо роли в маршруте
 *         показывается состояние отправки: черновик можно отправить или удалить",
 *
 *     @OA\Property(property="id", type="integer", example=498),
 *     @OA\Property(property="doc_id", type="integer", example=1204),
 *     @OA\Property(property="doc_number", type="string", nullable=true),
 *     @OA\Property(property="doc_name", type="string", nullable=true),
 *     @OA\Property(property="doc_date", type="string", nullable=true, example="15.09.2026"),
 *     @OA\Property(property="doc_term", type="string", nullable=true),
 *     @OA\Property(property="doc_text", type="string", nullable=true),
 *     @OA\Property(property="doc_hash", type="string", nullable=true),
 *     @OA\Property(property="journal", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", nullable=true, example="Yaratilgan"),
 *     @OA\Property(property="files", type="array", @OA\Items(ref="#/components/schemas/TelegramDocumentFile"))
 * )
 *
 * @OA\Schema(
 *     schema="TelegramDocumentFile",
 *     type="object",
 *     title="Вложение документа",
 *
 *     @OA\Property(property="name", type="string", example="prikaz.pdf"),
 *     @OA\Property(property="url", type="string", example="https://edoline.uz/..."),
 *     @OA\Property(property="size", type="integer", nullable=true, example=184320),
 *     @OA\Property(property="ext", type="string", nullable=true, example="pdf")
 * )
 *
 * @OA\Schema(
 *     schema="TelegramJournalOption",
 *     type="object",
 *     title="Журнал в выпадающем списке",
 *
 *     @OA\Property(property="id", type="integer", example=202),
 *     @OA\Property(property="title", type="string", example="Ishga qabul qilish"),
 *     @OA\Property(property="doc_code", type="string", example="PIGL")
 * )
 */
class Schemas
{
    /**
     * @OA\Post(
     *     path="/api/telegram/eimzo-link/auth-init",
     *     summary="Начать привязку аккаунта по ID-Card",
     *     description="Часть сценария привязки Telegram-аккаунта через E-IMZO:
     *         вызывается со страницы привязки, открытой по одноразовой ссылке
     *         от бота, а не из самого мини-аппа. Поэтому авторизация здесь
     *         не по Bearer, а по link_token.",
     *     tags={"Telegram Mini App"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"link_token"},
     *             @OA\Property(property="link_token", type="string", description="Токен из ссылки, выданной ботом")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Челлендж создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="documentId", type="string", example="doc-abc-123"),
     *             @OA\Property(property="challenge", type="string", example="5f2a...")
     *         )
     *     ),
     *     @OA\Response(response=401, description="link_token неверный или истёк"),
     *     @OA\Response(response=422, description="link_token не передан")
     * )
     */
    public function eimzoAuthInit() {}

    /**
     * @OA\Post(
     *     path="/api/telegram/eimzo-link/status",
     *     summary="Опрос состояния подписи ID-Card",
     *     description="Страница привязки периодически спрашивает, подписал ли
     *         пользователь челлендж в приложении E-IMZO.",
     *     tags={"Telegram Mini App"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"link_token","documentId"},
     *             @OA\Property(property="link_token", type="string"),
     *             @OA\Property(property="documentId", type="string", example="doc-abc-123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Текущее состояние",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="pending", description="pending | signed | expired")
     *         )
     *     ),
     *     @OA\Response(response=401, description="link_token неверный или истёк"),
     *     @OA\Response(response=422, description="Не переданы обязательные поля")
     * )
     */
    public function eimzoStatus() {}

    /**
     * @OA\Post(
     *     path="/api/telegram/eimzo-link/check-auth",
     *     summary="Завершить привязку после подписи",
     *     description="Проверяет подписанный челлендж, определяет пользователя
     *         EDOLINE по ПИНФЛ из сертификата и связывает его с Telegram-аккаунтом.",
     *     tags={"Telegram Mini App"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"link_token","documentId"},
     *             @OA\Property(property="link_token", type="string"),
     *             @OA\Property(property="documentId", type="string", example="doc-abc-123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200, description="Аккаунт привязан",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))
     *     ),
     *     @OA\Response(response=401, description="link_token неверный, истёк или подпись не подтверждена"),
     *     @OA\Response(response=404, description="Пользователь с таким ПИНФЛ не найден"),
     *     @OA\Response(response=422, description="Не переданы обязательные поля")
     * )
     */
    public function eimzoCheckAuth() {}
}
