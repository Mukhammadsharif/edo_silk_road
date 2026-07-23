<?php

namespace App\Services\Didox;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Низкоуровневый HTTP-клиент Didox.
 * Только транспорт: заголовки, базовый URL, единая обработка ответа.
 * Бизнес-логику (логин, рефреш токена, маппинг) держим в сервисах выше.
 *
 * Каждый запрос уходит с Partner-Authorization; user-key проставляется
 * методом withUserKey() для эндпоинтов, требующих авторизации пользователя.
 */
class DidoxClient
{
    private string $baseUrl;
    private string $partnerToken;
    private int $timeout;
    private ?string $userKey = null;

    public function __construct()
    {
        $this->baseUrl      = rtrim((string) config('didox.base_url'), '/');
        $this->partnerToken = (string) config('didox.partner_token');
        $this->timeout      = (int) config('didox.timeout', 30);
    }

    /** Установить user-key (токен пользователя Didox) для последующих вызовов. */
    public function withUserKey(?string $token): self
    {
        $clone = clone $this;
        $clone->userKey = $token;
        return $clone;
    }

    private function http(): PendingRequest
    {
        $req = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson()
            ->withHeaders(array_filter([
                'Partner-Authorization' => $this->partnerToken,
                'user-key'              => $this->userKey,
            ]));

        return $req;
    }

    /* ==================================================================
     |  Auth / Registration
     * ================================================================== */

    /** POST /v1/auth/:taxId/token/:locale — логин по ЭЦП (signature = timeStampTokenB64). */
    public function loginByEcp(string $taxId, string $signature, ?string $locale = null): array
    {
        $locale = $locale ?: config('didox.locale', 'ru');
        return $this->handle(
            $this->http()->post("/v1/auth/{$taxId}/token/{$locale}", ['signature' => $signature]),
            'auth/token'
        );
    }

    /** POST /v1/auth/:taxId/password/:locale — логин по паролю регистрации. */
    public function loginByPassword(string $taxId, string $password, ?string $locale = null): array
    {
        $locale = $locale ?: config('didox.locale', 'ru');
        return $this->handle(
            $this->http()->post("/v1/auth/{$taxId}/password/{$locale}", ['password' => $password]),
            'auth/password'
        );
    }

    /** POST /v1/auth/company/:companyTaxId/login/:locale — вход в компанию под физлицом. */
    public function loginCompany(string $companyTaxId, string $userToken, ?string $locale = null): array
    {
        $locale = $locale ?: config('didox.locale', 'ru');
        return $this->handle(
            $this->withUserKey($userToken)->http()
                ->post("/v1/auth/company/{$companyTaxId}/login/{$locale}", []),
            'auth/company-login'
        );
    }

    /** POST /v1/auth/signup — регистрация по ЭЦП. */
    public function signup(array $payload): array
    {
        return $this->handle($this->http()->post('/v1/auth/signup', $payload), 'auth/signup');
    }

    /* ==================================================================
     |  Signature service
     * ================================================================== */

    /** POST /v1/dsvs/timestamp — прикрепить timestamp к подписи. */
    public function timestamp(string $pkcs7, string $signatureHex): array
    {
        return $this->handle(
            $this->http()->post('/v1/dsvs/timestamp', [
                'pkcs7'        => $pkcs7,
                'signatureHex' => $signatureHex,
            ]),
            'dsvs/timestamp'
        );
    }

    /**
     * POST /v1/dsvs/signature/join — объединение подписей (раздел 7 документации:
     * подписание входящего = подпись продавца (toSign) + подпись покупателя).
     */
    public function signatureJoin(string $signature1, string $signature2): array
    {
        return $this->handle(
            $this->http()->post('/v1/dsvs/signature/join', [
                'signature1' => $signature1,
                'signature2' => $signature2,
            ]),
            'dsvs/signature-join'
        );
    }

    /* ==================================================================
     |  Profile / Account
     * ================================================================== */

    /** GET /v1/account — данные текущего аккаунта. */
    public function account(): array
    {
        return $this->handle($this->http()->get('/v1/account'), 'account');
    }

    /** POST /v1/profile/update — обновить данные профиля. */
    public function updateProfile(array $payload): array
    {
        return $this->handle($this->http()->post('/v1/profile/update', $payload), 'profile/update');
    }

    /* ==================================================================
     |  Documents
     * ================================================================== */

    /** GET /v2/documents — список документов (фильтры в $query). */
    public function documents(array $query = []): array
    {
        return $this->handle($this->http()->get('/v2/documents', $query), 'documents/list');
    }

    /** GET /v2/documents/statistics/all — счётчики по статусам. */
    public function statistics(): array
    {
        return $this->handle($this->http()->get('/v2/documents/statistics/all'), 'documents/statistics');
    }

    /** GET /v1/documents/{id} — детали документа (owner: 1 исходящий / 0 входящий). */
    public function document(string $id, array $query = []): array
    {
        return $this->handle($this->http()->get("/v1/documents/{$id}", $query), 'documents/show');
    }

    /** GET /v1/documents/{id}/documentBase64 — base64 документа (для подписи входящего, E-IMZO ≥ 6.3.5). */
    public function documentBase64(string $id): array
    {
        return $this->handle($this->http()->get("/v1/documents/{$id}/documentBase64"), 'documents/base64');
    }

    /** GET /v1/documents/{id}/file/true — печатная форма документа (PDF) в base64. */
    public function documentPdfBase64(string $id): array
    {
        return $this->handle($this->http()->get("/v1/documents/{$id}/file/true"), 'documents/file');
    }

    /**
     * GET /v1/documents/{id}/attachment — возвращает подписанную ссылку
     * (storage-URL) на файл документа. По этой ссылке отдельным GET качается
     * бинарный PDF (см. fetchFile).
     */
    public function attachmentUrl(string $id): ?string
    {
        $resp = $this->handle($this->http()->get("/v1/documents/{$id}/attachment"), 'documents/attachment');
        if (! ($resp['success'] ?? false)) {
            return null;
        }

        $data = $resp['data'];
        $url  = is_string($data) ? $data : ($data['url'] ?? $data['data'] ?? $data['link'] ?? null);
        $url  = is_string($url) ? trim($url, " \t\n\r\0\x0B\"'") : null;

        return ($url && str_starts_with($url, 'http')) ? $url : null;
    }

    /**
     * Скачивает файл документа по полной ссылке (storage-URL или raw.json.url).
     * Возвращает бинарные данные PDF или null.
     */
    public function fetchFile(string $url): ?string
    {
        $resp = Http::timeout($this->timeout)
            ->withHeaders(array_filter([
                'Partner-Authorization' => $this->partnerToken,
                'user-key'              => $this->userKey,
            ]))
            ->get($url);

        return $resp->successful() ? $resp->body() : null;
    }

    /** POST /v1/documents/:docType/create/:locale — создать черновик. */
    public function createDocument(string $docType, array $payload, ?string $locale = null): array
    {
        $locale = $locale ?: config('didox.locale', 'ru');
        return $this->handle(
            $this->http()->post("/v1/documents/{$docType}/create/{$locale}", $payload),
            'documents/create'
        );
    }

    /** POST /v1/documents/:docId/update/:docType — обновить черновик. */
    public function updateDocument(string $docId, string $docType, array $payload): array
    {
        return $this->handle(
            $this->http()->post("/v1/documents/{$docId}/update/{$docType}", $payload),
            'documents/update'
        );
    }

    /** POST /v1/documents/:docId/sign — подписать (signature = timeStampTokenB64). */
    public function signDocument(string $docId, string $signature): array
    {
        return $this->handle(
            $this->http()->post("/v1/documents/{$docId}/sign", ['signature' => $signature]),
            'documents/sign'
        );
    }

    /**
     * POST /v1/documents/:docId/reject — отказ входящего документа.
     * По разделу 8: сначала tosign(action=reject, comment) → подписать data →
     * timestamp → сюда отправляется подпись + комментарий.
     */
    public function rejectDocument(string $docId, string $comment, ?string $signature = null): array
    {
        return $this->handle(
            $this->http()->post("/v1/documents/{$docId}/reject", array_filter([
                'comment'   => $comment,
                'signature' => $signature,
            ])),
            'documents/reject'
        );
    }

    /** POST /v1/documents/:docId/delete/draft — удалить черновик. */
    public function deleteDraft(string $docId): array
    {
        return $this->handle($this->http()->post("/v1/documents/{$docId}/delete/draft", []), 'documents/delete-draft');
    }

    /**
     * POST /v1/documents/:docId/delete — удаление (отмена) исходящего документа.
     * По разделу 9: tosign(action=cancel) → подписать ответ → timestamp → подпись сюда.
     */
    public function deleteDocument(string $docId, ?string $signature = null): array
    {
        return $this->handle(
            $this->http()->post("/v1/documents/{$docId}/delete", array_filter(['signature' => $signature])),
            'documents/delete'
        );
    }

    /** POST /v1/documents/:docId/tosign — данные к подписанию (action: accept, cancel, reject...). */
    public function toSign(string $docId, array $payload): array
    {
        return $this->handle($this->http()->post("/v1/documents/{$docId}/tosign", $payload), 'documents/tosign');
    }

    /** GET /v1/documents/view/:id/html/:locale — печатная форма HTML. */
    public function html(string $id, ?string $locale = null): array
    {
        $locale = $locale ?: config('didox.locale', 'ru');
        return $this->handle($this->http()->get("/v1/documents/view/{$id}/html/{$locale}"), 'documents/html');
    }

    /** GET /v1/documents/view/:id/pdf/:locale — печатная форма PDF (base64/бинарь). */
    public function pdf(string $id, ?string $locale = null): array
    {
        $locale = $locale ?: config('didox.locale', 'ru');
        return $this->handle($this->http()->get("/v1/documents/view/{$id}/pdf/{$locale}"), 'documents/pdf');
    }

    /** GET /v1/documents/:id/archive — zip-архив (подписи/pdf/json), ссылка живёт 5 мин. */
    public function archive(string $id): array
    {
        return $this->handle($this->http()->get("/v1/documents/{$id}/archive"), 'documents/archive');
    }

    /** GET /v1/documents/exchange — биржевые лоты. */
    public function exchange(array $query = []): array
    {
        return $this->handle($this->http()->get('/v1/documents/exchange', $query), 'documents/exchange');
    }

    /** GET /v1/documents/exchange/types — типы биржевых операций. */
    public function exchangeTypes(): array
    {
        return $this->handle($this->http()->get('/v1/documents/exchange/types'), 'documents/exchange-types');
    }

    /* ==================================================================
     |  Catalogs
     * ================================================================== */

    public function banks(): array     { return $this->handle($this->http()->get('/v1/banks/all'), 'catalogs/banks'); }
    public function measures(): array  { return $this->handle($this->http()->get('/v1/measures/all'), 'catalogs/measures'); }
    public function regions(): array   { return $this->handle($this->http()->get('/v1/regions/all'), 'catalogs/regions'); }
    public function districts(): array { return $this->handle($this->http()->get('/v1/districts/all'), 'catalogs/districts'); }

    /* ==================================================================
     |  Единая обработка ответа
     * ================================================================== */

    private function handle(Response $resp, string $endpoint): array
    {
        if (config('didox.log_requests')) {
            Log::channel('didox')->info("[Didox] {$endpoint}", [
                'status' => $resp->status(),
                'ok'     => $resp->successful(),
            ]);
        }

        if ($resp->failed()) {
            Log::channel('didox')->error("[Didox] {$endpoint} failed", [
                'status' => $resp->status(),
                'body'   => $resp->json() ?? $resp->body(),
            ]);

            return [
                'success' => false,
                'status'  => $resp->status(),
                'error'   => $resp->json() ?? $resp->body(),
            ];
        }

        return [
            'success' => true,
            'status'  => $resp->status(),
            'data'    => $resp->json() ?? $resp->body(),
        ];
    }
}
