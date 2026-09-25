<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Api\UserProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Карточка сотрудника в мобильном приложении.
 * Логика — в общем UserProfileService.
 */
class MobileProfileController extends Controller
{
    public function __construct(private UserProfileService $profiles) {}

    /** GET /api/v1/mobile/profile */
    public function show(Request $request): JsonResponse
    {
        return $this->profiles->show($request);
    }

    /** POST /api/v1/mobile/profile/avatar */
    public function updateAvatar(Request $request): JsonResponse
    {
        return $this->profiles->updateAvatar($request);
    }

    /** DELETE /api/v1/mobile/profile/avatar */
    public function deleteAvatar(Request $request): JsonResponse
    {
        return $this->profiles->deleteAvatar($request);
    }

    /** POST /api/v1/mobile/profile/password */
    public function updatePassword(Request $request): JsonResponse
    {
        return $this->profiles->updatePassword($request);
    }

    /** POST /api/v1/mobile/profile/info */
    public function updateInfo(Request $request): JsonResponse
    {
        return $this->profiles->updatePersonalInfo($request);
    }
}
