<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Api\DocumentFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Документы в мобильном приложении.
 *
 * Контроллер тонкий: вся логика в DocumentFeedService, который общий
 * с мини-аппом — так списки и карточки гарантированно одинаковые.
 * Здесь только авторизация (Sanctum через middleware) и язык ответа.
 */
class MobileDocumentController extends Controller
{
    public function __construct(private DocumentFeedService $feed) {}

    /** GET /api/v1/mobile/counts */
    public function counts(Request $request): JsonResponse
    {
        $this->applyLocale($request);

        return $this->feed->counts($request);
    }

    /** GET /api/v1/mobile/status-options */
    public function statusOptions(Request $request): JsonResponse
    {
        $this->applyLocale($request);

        return $this->feed->statusOptions($request);
    }

    /** GET /api/v1/mobile/documents */
    public function index(Request $request): JsonResponse
    {
        $this->applyLocale($request);

        return $this->feed->forSign($request);
    }

    /** GET /api/v1/mobile/documents/{j_id} */
    public function show(Request $request, int $jId): JsonResponse
    {
        $this->applyLocale($request);

        return $this->feed->show($request, $jId);
    }

    /** POST /api/v1/mobile/documents/{j_id}/sign-link */
    public function signLink(Request $request, int $jId): JsonResponse
    {
        return $this->feed->createSignToken($request, $jId);
    }

    /** GET /api/v1/mobile/my-documents */
    public function myIndex(Request $request): JsonResponse
    {
        $this->applyLocale($request);

        return $this->feed->myDocuments($request);
    }

    /** GET /api/v1/mobile/my-documents/{j_id} */
    public function myShow(Request $request, int $jId): JsonResponse
    {
        $this->applyLocale($request);

        return $this->feed->showMine($request, $jId);
    }

    /** POST /api/v1/mobile/my-documents/{j_id}/send */
    public function send(Request $request, int $jId): JsonResponse
    {
        return $this->feed->sendForSign($request, $jId);
    }

    /** DELETE /api/v1/mobile/my-documents/{j_id} */
    public function destroy(Request $request, int $jId): JsonResponse
    {
        return $this->feed->deleteOwn($request, $jId);
    }

    /**
     * Язык ответа из ?lang=.
     *
     * cyr трактуется как uz: на фронте это узбекская кириллица,
     * отдельной локали для неё на бэкенде нет.
     */
    private function applyLocale(Request $request): void
    {
        $lang = $request->query('lang', 'uz');

        if ($lang === 'cyr') {
            $lang = 'uz';
        }

        if (in_array($lang, ['uz', 'ru', 'en'], true)) {
            app()->setLocale($lang);
        }
    }
}
