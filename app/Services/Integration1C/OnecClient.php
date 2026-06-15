<?php

namespace App\Services\Integration1C;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Клиентская сторона: EDOLINE → 1С.
 * EDOLINE отправляет свои данные в API 1С (тот же формат create_update/delete).
 */
class OnecClient
{
    private string $baseUrl;
    private int $timeout;
    private ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('onec.base_url'), '/');
        $this->timeout = (int) config('onec.timeout', 30);
    }

    /** Авторизоваться в 1С (login → JWT) */
    public function login(): ?string
    {
        $resp = Http::baseUrl($this->baseUrl)->timeout($this->timeout)->asJson()
            ->post('/auth/auth/login', [
                'username' => config('onec.username'),
                'password' => config('onec.password'),
            ]);

        if ($resp->failed()) {
            Log::channel('onec')->error('[1C] login failed', ['status' => $resp->status()]);
            return null;
        }
        $this->token = $resp->json('token') ?? $resp->json('access_token') ?? $resp->json('accessToken');
        return $this->token;
    }

    public function withToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    private function http(): PendingRequest
    {
        $req = Http::baseUrl($this->baseUrl)->timeout($this->timeout)->acceptJson()->asJson();
        if ($this->token) $req = $req->withToken($this->token);
        return $req;
    }

    /** PUT /main/api-1c/v1/{entity}/create_update — отправить одну запись в 1С */
    public function createUpdate(string $entity, array $payload): array
    {
        $resp = $this->http()->put("/main/api-1c/v1/{$entity}/create_update/", $payload);
        return $this->handle($resp, "{$entity}/create_update");
    }

    /** PUT .../create_update_list — пакет */
    public function createUpdateList(string $entity, string $wrapperKey, array $items): array
    {
        $resp = $this->http()->put("/main/api-1c/v1/{$entity}/create_update_list/", [
            $wrapperKey => $items,
        ]);
        return $this->handle($resp, "{$entity}/create_update_list");
    }

    /** DELETE .../delete/?{param}={value} */
    public function delete(string $entity, array $query): array
    {
        $resp = $this->http()->delete("/main/api-1c/v1/{$entity}/delete/", $query);
        return $this->handle($resp, "{$entity}/delete");
    }

    private function handle(\Illuminate\Http\Client\Response $resp, string $endpoint): array
    {
        if (config('onec.log_requests')) {
            Log::channel('onec')->info('[1C client] ' . $endpoint, ['status' => $resp->status()]);
        }
        if ($resp->failed()) {
            Log::channel('onec')->error('[1C client] FAILED ' . $endpoint, [
                'status' => $resp->status(),
                'body'   => mb_substr($resp->body(), 0, 1000),
            ]);
            throw new \RuntimeException("1C API error ({$endpoint}): " . $resp->status() . ' ' . $resp->body());
        }
        $json = $resp->json();
        return is_array($json) ? $json : ['raw' => $resp->body()];
    }
}
