<?php

namespace App\Swagger\TelegramMiniApp;

/**
 * Swagger-описание эндпоинтов работы с документами в Telegram Mini App.
 *
 * Все они требуют Bearer-токен из /api/telegram/auth. Авторизация проверяется
 * не middleware, а вручную внутри контроллера (resolveUser), поэтому при
 * отсутствии или истечении токена приходит 401 с телом {"error":"Unauthorized"}.
 *
 * Класс пустой — носитель аннотаций, см. пояснение в AuthEndpoints.
 */
class DocumentEndpoints
{
    /**
     * @OA\Get(
     *     path="/api/telegram/counts",
     *     summary="Счётчики на главном экране",
     *     description="Считаются по ВСЕМ записям user_work_histories пользователя,
     *         без фильтра по организации — намеренно, чтобы числа совпадали
     *         с веб-версией (menuCount).",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/status-options",
     *     summary="Варианты фильтра по статусу шага",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/documents",
     *     summary="Документы, пришедшие пользователю",
     *     description="Без параметра category возвращаются все три роли сразу —
     *         так было до появления фильтра, поведение сохранено.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/documents/{j_id}",
     *     summary="Карточка входящего документа",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/documents/{j_id}/sign-token",
     *     summary="Одноразовая ссылка на страницу подписи E-IMZO",
     *     description="Подпись происходит вне мини-аппа: ссылка открывается во
     *         внешнем браузере, где работает E-IMZO. Тип подписи определяется
     *         ролью пользователя в маршруте документа. Срок жизни — 15 минут.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
     *
     *     @OA\Parameter(name="j_id", in="path", required=true, @OA\Schema(type="integer", example=497)),
     *
     *     @OA\Response(
     *         response=200, description="Ссылка создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="sign_url", type="string", example="https://edoline.uz/mobile/sign/s1gn70k3n"),
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
     *     path="/api/telegram/my-documents",
     *     summary="Документы, созданные пользователем",
     *     description="Базовые журналы входящих и исходящих исключены —
     *         это раздел собственных рабочих документов.",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/my-documents/{j_id}",
     *     summary="Карточка своего документа",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *     path="/api/telegram/my-documents/{j_id}/send",
     *     summary="Отправить свой документ по маршруту",
     *     description="Отправка требует подписи автора, поэтому возвращается
     *         ссылка на страницу E-IMZO, а не результат отправки. Документ
     *         должен быть в статусе 1 (черновик).",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
     *             @OA\Property(property="sign_url", type="string", example="https://edoline.uz/mobile/sign/s1gn70k3n"),
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
     *     path="/api/telegram/my-documents/{j_id}",
     *     summary="Удалить свой документ",
     *     tags={"Telegram Mini App"},
     *     security={{"telegramBearer":{}}},
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
}
