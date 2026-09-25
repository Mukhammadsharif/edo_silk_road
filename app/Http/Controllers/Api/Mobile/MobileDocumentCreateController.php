<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Api\DocumentCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Мастер создания документа в мобильном приложении.
 * Логика — в общем DocumentCreationService.
 */
class MobileDocumentCreateController extends Controller
{
    public function __construct(private DocumentCreationService $creation) {}

    /** GET /api/v1/mobile/create/init/{type} */
    public function init(Request $request, string $type): JsonResponse
    {
        return $this->creation->init($request, $type);
    }

    /** GET /api/v1/mobile/create/journals/{j_type_id} */
    public function journals(Request $request, int $jTypeId): JsonResponse
    {
        return $this->creation->journals($request, $jTypeId);
    }

    /** GET /api/v1/mobile/create/execution-users/{j_id} */
    public function executionUsers(Request $request, int $jId): JsonResponse
    {
        return $this->creation->executionUsers($request, $jId);
    }

    /** GET /api/v1/mobile/create/doc-organizations/{j_id} */
    public function docOrganizations(Request $request, int $jId): JsonResponse
    {
        return $this->creation->docOrganizations($request, $jId);
    }

    /** POST /api/v1/mobile/create/store */
    public function store(Request $request): JsonResponse
    {
        return $this->creation->store($request);
    }

    /** POST /api/v1/mobile/create/web-editor */
    public function webEditor(Request $request): JsonResponse
    {
        return $this->creation->webEditor($request);
    }
}
