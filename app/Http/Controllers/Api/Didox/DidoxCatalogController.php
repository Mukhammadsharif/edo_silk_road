<?php

namespace App\Http\Controllers\Api\Didox;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Справочники Didox: банки, единицы измерения, регионы, районы.
 * Меняются редко → кэшируем на сутки, чтобы не дёргать Didox на каждый запрос.
 */
class DidoxCatalogController extends Controller
{
    use ResolvesDidoxClient;

    private const TTL = 86400; // 24 ч

    public function banks(Request $request): JsonResponse     { return $this->cached($request, 'banks',     fn ($c) => $c->banks()); }
    public function measures(Request $request): JsonResponse  { return $this->cached($request, 'measures',  fn ($c) => $c->measures()); }
    public function regions(Request $request): JsonResponse   { return $this->cached($request, 'regions',   fn ($c) => $c->regions()); }
    public function districts(Request $request): JsonResponse { return $this->cached($request, 'districts', fn ($c) => $c->districts()); }

    private function cached(Request $request, string $key, callable $call): JsonResponse
    {
        try {
            $client   = $this->authorizedClient($request);
            $cacheKey = "didox:catalog:{$key}";

            $resp = Cache::get($cacheKey);

            if (!is_array($resp) || empty($resp['success'])) {
                $resp = $call($client);
                // Кэшируем ТОЛЬКО успешный ответ — иначе ошибка залипнет на сутки.
                if (!empty($resp['success'])) {
                    Cache::put($cacheKey, $resp, self::TTL);
                }
            }

            return response()->json($resp, ($resp['success'] ?? false) ? 200 : ($resp['status'] ?? 422));
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
