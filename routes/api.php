<?php

use App\Http\Controllers\Api\Admin\DictionaryApiController;
use App\Http\Controllers\Api\Bitrix\CompanyController;
use App\Http\Controllers\Api\Bitrix\DataController;
use App\Http\Controllers\Api\Bitrix\DivisionController;
use App\Http\Controllers\Api\Bitrix\EmployeeController;
use App\Http\Controllers\Api\Bitrix\PersonController;
use App\Http\Controllers\Api\Bitrix\PositionController;
use App\Http\Controllers\Api\ECP\SignDocImzoApiController;
use App\Http\Controllers\Api\ECP\SignDocImzoMobileApiController;
use App\Http\Controllers\Api\ECP\SignLoginApiController;
use App\Http\Controllers\Api\Messages\MessageApiController;
//use App\Http\Controllers\Api\Office\DocApiController;
use App\Http\Controllers\Api\Office\ExecutionApiController;
use App\Http\Controllers\Api\Office\JournalApiController;
use App\Http\Controllers\Api\Protocol\ProtocolApiCommentController;
use App\Http\Controllers\Api\Protocol\ProtocolApiEmpHrOrdersController;
use App\Http\Controllers\Api\Protocol\ProtocolsApiController;
use App\Http\Controllers\Api\Telegram\TelegramDocumentController;
use App\Http\Controllers\Api\Telegram\TelegramDocumentCreateController;
use App\Http\Controllers\Api\Telegram\TelegramEimzoLinkController;
use App\Http\Controllers\Api\Telegram\TelegramMiniAppAuthController;
use App\Http\Controllers\Api\Telegram\TelegramProfileController;
use App\Http\Controllers\Api\Didox\DidoxAuthController;
use App\Http\Controllers\Api\Didox\DidoxProfileController;
use App\Http\Controllers\Api\Didox\DidoxDocumentController;
use App\Http\Controllers\Api\Didox\DidoxCatalogController;
use App\Http\Controllers\Api\Didox\DidoxServiceController;
use App\Http\Controllers\Api\Integration1C\OnecInboundController;
use App\Http\Controllers\Api\Integration1C\OnecOutboundController;
use App\Http\Controllers\Api\User\ProfileApiController;
use App\Http\Controllers\Api\User\UserApiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\OnecAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('/callback', function (Request $request) {

    //
    $token = $request->bearerToken();
    $data = json_decode($request->getContent(), true);

    $key = $data['key'] ?? null;
    if (!$key) {
        Log::warning("Key not found in callback.");
        return response()->json(['error' => 1]);
    }

    $status = $data['status'] ?? null;
    if (in_array($status, [1, 4])) {
        return response()->json(['error' => 0]);
    }

    if (!in_array($status, [2, 6, 7])) {
        Log::info('Callback status not handled', ['status' => $status]);
        return response()->json(['error' => 0]);
    }

    $downloadUrl = $data['url'] ?? null;
    if (!$downloadUrl) {
        Log::error('No download URL in callback.');
        return response()->json(['error' => 1]);
    }

    // Replace external IP with internal IP
    $onlyOfficeExternalUrl = config('only-office.ONLY_OFFICE_EXTERNAL_URL');
    $onlyOfficeInternalUrl = config('only-office.ONLY_OFFICE_INTERNAL_URL');
    $downloadUrl = str_replace(
        $onlyOfficeExternalUrl,
        $onlyOfficeInternalUrl,
        $downloadUrl
    );

    try {
        $options = ['timeout' => 60];
        if (config('only-office.HTTP_PROXY')) {
            $options['proxy'] = config('only-office.HTTP_PROXY');
        }

        $response = Http::withOptions($options)
            ->withHeaders(['Authorization' => "Bearer $token"])
            ->get($downloadUrl);

        if (!$response->successful()) {
            Log::error('Failed to download file', ['url' => $downloadUrl]);
            return response()->json(['error' => 1]);
        }

        $today = Carbon::today();
        $year = $today->year;
        $month = $today->month;
        $day = $today->day;
        $ood_path = 'ood/' . $year . '/' . $month . '/' . $day . '/';

        $filename = $key . '.docx';
        $path = $ood_path . $filename;

        Storage::disk('ftp')->put($path, $response->body());

        Log::info('callback saved docx', [
            'key' => $key,
            'status' => $status,
            'path' => $path,
            'size' => strlen($response->body()),
        ]);

        return response()->json(['error' => 0]);

    } catch (\Exception $e) {
        Log::error('Exception saving file', ['message' => $e->getMessage()]);
        return response()->json(['error' => 1]);
    }

});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('main/api-1c/v1')->middleware(OnecAuth::class)->group(function () {

    // Все сущности 1С → generic-приёмник (буфер onec_* + синк в рабочие таблицы)
    foreach ([
                 'companies', 'divisions', 'positions', 'stafflist_positions',
                 'persons', 'employees', 'vocations', 'users',
             ] as $entity) {
        Route::put("/{$entity}/create_update",      [OnecInboundController::class, 'createUpdate'])->defaults('entity', $entity);
        Route::put("/{$entity}/create_update_list", [OnecInboundController::class, 'createUpdateList'])->defaults('entity', $entity);
        Route::delete("/{$entity}/delete",          [OnecInboundController::class, 'delete'])->defaults('entity', $entity);
    }
});


Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::get('/hr-orders', [DataController::class, 'hrOrders']);

    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/roles', [DictionaryApiController::class, 'roleList']);
    Route::get('/languages', [DictionaryApiController::class, 'languageList']);

    // Profile routes
    Route::get('/profile', [ProfileApiController::class, 'profile']);
    Route::put('/profile/personal', [ProfileApiController::class, 'personalUpdate']);
    Route::put('/profile/password', [ProfileApiController::class, 'passwordUpdate']);
    Route::post('/profile/image', [ProfileApiController::class, 'updateImage']);
    Route::delete('/profile/image', [ProfileApiController::class, 'deleteProfileImage']);

    // Execution routes
    Route::prefix('executions')->group(function () {
        Route::get('/for-execution', [ExecutionApiController::class, 'forExecutionIndex']);
        Route::get('/for-sign', [ExecutionApiController::class, 'forSignIndex']);
        Route::get('/for-approval', [ExecutionApiController::class, 'forApprovalIndex']);
        Route::get('/for-confirm', [ExecutionApiController::class, 'forConfirmIndex']);
        Route::get('/for-control', [ExecutionApiController::class, 'forControlIndex']);

        Route::get('/document/{j_id}/execution', [ExecutionApiController::class, 'viewExecDocument']);
        Route::get('/document/{j_id}/sign', [ExecutionApiController::class, 'viewSignDocument']);
        Route::get('/document/{j_id}/approval', [ExecutionApiController::class, 'viewApprovalDocument']);
        Route::get('/document/{j_id}/confirm', [ExecutionApiController::class, 'viewConfirmDocument']);
        Route::get('/document/{j_id}/control', [ExecutionApiController::class, 'viewControlDocument']);

        Route::get('/document/{j_id}', [ExecutionApiController::class, 'viewDocument']);


        Route::post('/document/{j_id}/sign', [ExecutionApiController::class, 'signDocument']);
        Route::post('/document/{j_id}/approve', [ExecutionApiController::class, 'approveDocument']);
        Route::post('/document/{j_id}/reject', [ExecutionApiController::class, 'rejectDocument']);
        Route::post('/document/{step_id}/reply', [ExecutionApiController::class, 'replyDocument']);
        Route::post('/document/{j_id}/execute', [ExecutionApiController::class, 'executeDocument']);

        Route::get('/status-list', [ExecutionApiController::class, 'statusList']);
        Route::get('/journal-list', [ExecutionApiController::class, 'journalList']);
    });

    Route::prefix('protocol')->group(function () {
        Route::get('/protocols', [ProtocolsApiController::class, 'index']);
        Route::get('/protocols/{id}/{doc_hash}', [ProtocolsApiController::class, 'view']);
        Route::post('/protocols/{id}/{doc_hash}/sign', [ProtocolsApiController::class, 'sign']);

        Route::get('/hr-orders', [ProtocolApiEmpHrOrdersController::class, 'index']);
        Route::get('/hr-orders/{id}/{doc_hash}', [ProtocolApiEmpHrOrdersController::class, 'view']);
        Route::post('/hr-orders/{id}/{doc_hash}/sign', [ProtocolApiEmpHrOrdersController::class, 'sign']);

        Route::get('/{doc_hash}/comments', [ProtocolApiCommentController::class, 'index']);
        Route::get('/protocol-comments/{comment_id}', [ProtocolApiCommentController::class, 'show']);
    });

    Route::get('/work-documents', [JournalApiController::class, 'indexWork']);
    Route::get('/work-documents/{id}/{hash}', [JournalApiController::class, 'viewWork']);
    Route::get('/inbox-documents', [JournalApiController::class, 'indexInbox']);
    Route::get('/inbox-documents/{id}/{hash}', [JournalApiController::class, 'viewInbox']);
    Route::get('/outgoing-documents', [JournalApiController::class, 'indexOutgoing']);
    Route::get('/outgoing-documents/{id}/{hash}', [JournalApiController::class, 'viewOutgoing']);

    //    // Message routes
//    Route::prefix('messages')->group(function () {
//        Route::get('/{type}', [MessageApiController::class, 'list']);
//        Route::get('/view/{id}', [MessageApiController::class, 'view']);
//        Route::delete('/{id}', [MessageApiController::class, 'delete']);
//    });

//    Route::prefix('doc-controller')->group(function () {
//        Route::get('/documents/work-docs', [DocApiController::class, 'workDocsIndex']);
//        Route::get('/documents/{id}/view/{doc_hash}', [DocApiController::class, 'workView']);
//        Route::get('/public-protocols', [DocApiController::class, 'publicProtocolsIndex']);
//        Route::get('/protocols', [DocApiController::class, 'protocolsIndex']);
//        Route::get('/protocol/{id}/{doc_hash}', [DocApiController::class, 'protocolView']);
//        Route::delete('/work-documents', [DocApiController::class, 'delWorkDoc']);
//        Route::delete('/protocols', [DocApiController::class, 'delProtocol']);
//    });
});

// ECP Routes - E-IMZO (Electronic Digital Signature)
Route::prefix('v1/ecp')->group(function () {

    // Public routes (no authentication required)
    Route::get('/frontend-challenge', [SignDocImzoApiController::class, 'frontChallenge']);
    Route::post('/backend-auth-login', [SignDocImzoApiController::class, 'backendAuthLogin']);

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/sign-documents', [SignDocImzoApiController::class, 'signDocuments']);
    });
});

// ECP Routes - Login Sign (Simple authentication-based signing)
Route::prefix('v1/login-sign')->middleware('auth:sanctum')->group(function () {

    // Sign documents
    Route::post('/sign-documents', [SignLoginApiController::class, 'signDocuments']);

    // Get document information
    Route::get('/document-types', [SignLoginApiController::class, 'getDocumentTypes']);
    Route::get('/pending-documents', [SignLoginApiController::class, 'getPendingDocuments']);
    Route::get('/document/{doc_id}/details', [SignLoginApiController::class, 'getDocumentDetails']);
});

// ECP Routes - E-IMZO Mobile (ID-Card)
Route::prefix('v1/ecp/mobile')->group(function () {
    /**
     * Endpoint для получения challenge для мобильной идентификации
     * Возвращает: documentId и challenge для подписания
     */
    Route::post('/auth', [SignDocImzoMobileApiController::class, 'mobileAuth']);

    /**
     * Endpoint для проверки статуса (загружен ли PKCS7)
     * Принимает: documentId
     * Возвращает: status (1=готово, 2=ожидание, negative=ошибка)
     */
    Route::post('/status', [SignDocImzoMobileApiController::class, 'mobileStatus']);

    /**
     * Endpoint для загрузки PKCS7 от мобильного приложения E-IMZO ID-CARD
     * Этот URL должен быть указан в настройках как UPLOAD URL
     * Принимает: documentId, pkcs7
     */
    Route::post('/upload', [SignDocImzoMobileApiController::class, 'mobileUpload']);

    /**
     * Endpoint для проверки результата идентификации
     * Принимает: documentId (в URL)
     * Возвращает: информацию о сертификате пользователя
     */
    Route::get('/authenticate/{documentId}', [SignDocImzoMobileApiController::class, 'mobileAuthenticate']);

    /**
     * Endpoint для генерации documentId для подписания документа
     * Возвращает: documentId для использования в deeplink
     */
    Route::post('/sign', [SignDocImzoMobileApiController::class, 'mobileSign'])
        ->middleware('auth:sanctum');

    /**
     * Endpoint для проверки подписи документа
     * Принимает: documentId, document (base64)
     * Возвращает: результат проверки подписи и информацию о сертификате
     */
    Route::post('/verify', [SignDocImzoMobileApiController::class, 'mobileVerify'])
        ->middleware('auth:sanctum');
});

Route::prefix('telegram')->group(function () {

    // Авторизация через Telegram WebApp initData (без auth middleware)
    Route::post('/auth', [TelegramMiniAppAuthController::class, 'auth']);

    // Получить Bearer token по sign token (для mobile sign page)
    Route::post('/resolve-token', function (\Illuminate\Http\Request $request) {
        $signToken = $request->input('sign_token');
        $record = \App\Models\TelegramMiniAppToken::findValidSign($signToken);
        if (!$record) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }
        return response()->json(['token' => $record->auth_token]);
    });

    // Документы (используют собственную auth через Bearer token)
    Route::get('/counts', [TelegramDocumentController::class, 'counts']);
    Route::get('/status-options', [TelegramDocumentController::class, 'statusOptions']);
    Route::get('/documents', [TelegramDocumentController::class, 'forSign']);
    Route::get('/documents/{j_id}', [TelegramDocumentController::class, 'show'])->whereNumber('j_id');
    Route::post('/documents/{j_id}/sign-token', [TelegramDocumentController::class, 'createSignToken'])->whereNumber('j_id');

    // Mening hujjatlarim — документы созданные текущим юзером
    Route::get('/my-documents', [TelegramDocumentController::class, 'myDocuments']);
    Route::get('/my-documents/{j_id}', [TelegramDocumentController::class, 'showMine'])->whereNumber('j_id');
    Route::post('/my-documents/{j_id}/send', [TelegramDocumentController::class, 'sendForSign'])->whereNumber('j_id');
    Route::delete('/my-documents/{j_id}', [TelegramDocumentController::class, 'deleteOwn'])->whereNumber('j_id');

    // Карточка сотрудника
    Route::get('/profile', [TelegramProfileController::class, 'show']);
    Route::post('/profile/avatar',   [TelegramProfileController::class, 'updateAvatar']);
    Route::delete('/profile/avatar', [TelegramProfileController::class, 'deleteAvatar']);
    Route::post('/profile/password', [TelegramProfileController::class, 'updatePassword']);
    Route::post('/profile/info',     [TelegramProfileController::class, 'updatePersonalInfo']);

    // Создание документа (wizard в Mini App). Все защищены через Bearer.
    Route::get('/create/init/{type}',              [TelegramDocumentCreateController::class, 'init']);
    Route::get('/create/journals/{j_type_id}',     [TelegramDocumentCreateController::class, 'journals'])->whereNumber('j_type_id');
    Route::get('/create/execution-users/{j_id}',   [TelegramDocumentCreateController::class, 'executionUsers'])->whereNumber('j_id');
    Route::get('/create/doc-organizations/{j_id}', [TelegramDocumentCreateController::class, 'docOrganizations'])->whereNumber('j_id');
    Route::post('/create/store',                   [TelegramDocumentCreateController::class, 'store']);
    Route::post('/create/web-editor',              [TelegramDocumentCreateController::class, 'webEditor']);

    // E-IMZO ID-Card привязка аккаунта (без auth middleware — используем link_token)
    Route::post('/eimzo-link/auth-init',  [TelegramEimzoLinkController::class, 'authInit']);
    Route::post('/eimzo-link/status',     [TelegramEimzoLinkController::class, 'pollStatus']);
    Route::post('/eimzo-link/check-auth', [TelegramEimzoLinkController::class, 'checkAuth']);
});


/*
|--------------------------------------------------------------------------
| Didox integration API (EDOLINE ↔ Didox)
|--------------------------------------------------------------------------
| Все эндпоинты защищены sanctum. Авторизация в самом Didox — через ЭЦП
| (authId → pkcs7 → login), токен Didox хранится в didox_tokens.
*/
//Route::prefix('didox')->middleware('auth:sanctum')->group(function () {
//
//    // ── User Authorization ──
//    Route::prefix('auth')->group(function () {
//        Route::get('/authId/{serialNumber}', [DidoxAuthController::class, 'authId']);
//        Route::post('/login',                [DidoxAuthController::class, 'login']);
//        Route::post('/register',             [DidoxAuthController::class, 'register']);
//        Route::get('/token',                 [DidoxAuthController::class, 'refresh']);
//    });
//
//    // ── Profile ──
//    Route::prefix('profile')->group(function () {
//        Route::get('/',                          [DidoxProfileController::class, 'show']);
//        Route::post('/update',                   [DidoxProfileController::class, 'update']);
//        Route::get('/by-tin/{taxId}',            [DidoxProfileController::class, 'byTin']);
//        Route::get('/operators',                 [DidoxProfileController::class, 'operators']);
//        Route::post('/operators',                [DidoxProfileController::class, 'updateOperators']);
//        Route::get('/branches',                  [DidoxProfileController::class, 'branches']);
//        Route::get('/product-classes/search',    [DidoxProfileController::class, 'searchProductClasses']);
//        Route::get('/product-classes',           [DidoxProfileController::class, 'productClasses']);
//        Route::post('/product-classes',          [DidoxProfileController::class, 'attachProductClass']);
//        Route::put('/product-classes',           [DidoxProfileController::class, 'updateProductClasses']);
//        Route::delete('/product-classes/{classCode}', [DidoxProfileController::class, 'detachProductClass']);
//    });
//
//    // ── Documents ──
//    Route::prefix('documents')->group(function () {
//        Route::get('/',                       [DidoxDocumentController::class, 'index']);
//        Route::get('/statistics',             [DidoxDocumentController::class, 'statistics']);
//        Route::get('/exchange',               [DidoxDocumentController::class, 'exchange']);
//        Route::get('/exchange/types',         [DidoxDocumentController::class, 'exchangeTypes']);
//        Route::post('/sync/pull',             [DidoxDocumentController::class, 'syncPull']);
//        Route::post('/sync/push',             [DidoxDocumentController::class, 'syncPush']);
//        Route::get('/{id}',                   [DidoxDocumentController::class, 'show']);
//        Route::post('/{docType}/create',      [DidoxDocumentController::class, 'store']);
//        Route::post('/{id}/update/{docType}', [DidoxDocumentController::class, 'updateDraft']);
//        Route::post('/{id}/sign',             [DidoxDocumentController::class, 'sign']);
//        Route::post('/{id}/reject',           [DidoxDocumentController::class, 'reject']);
//        Route::post('/{id}/delete/draft',     [DidoxDocumentController::class, 'destroyDraft']);
//        Route::post('/{id}/delete',           [DidoxDocumentController::class, 'destroy']);
//        Route::get('/{id}/tosign',            [DidoxDocumentController::class, 'toSign']);
//        Route::get('/{id}/html/{locale}',     [DidoxDocumentController::class, 'html']);
//        Route::get('/{id}/pdf/{locale}',      [DidoxDocumentController::class, 'pdf']);
//        Route::get('/{id}/downloadrequest',   [DidoxDocumentController::class, 'downloadRequest']);
//    });
//
//    // ── Catalogs ──
//    Route::prefix('catalogs')->group(function () {
//        Route::get('/banks',     [DidoxCatalogController::class, 'banks']);
//        Route::get('/measures',  [DidoxCatalogController::class, 'measures']);
//        Route::get('/regions',   [DidoxCatalogController::class, 'regions']);
//        Route::get('/districts', [DidoxCatalogController::class, 'districts']);
//    });
//
//    // ── Services ──
//    Route::post('/services/timestamp', [DidoxServiceController::class, 'timestamp']);
//});


/*
|--------------------------------------------------------------------------
| 1C Integration (employee-cabinet) — двусторонняя
|--------------------------------------------------------------------------
| INBOUND (1С → EDOLINE): 1С шлёт push на /main/api-1c/v1/{entity}/...
|   защита токеном через middleware onec.auth
| OUTBOUND (EDOLINE → 1С): /api/onec/push/... под sanctum
*/

// --- OUTBOUND: EDOLINE отправляет в 1С ---
Route::prefix('onec')->middleware('auth:sanctum')->group(function () {
    Route::post('/push/{entity}',      [OnecOutboundController::class, 'push']);
    Route::post('/push-list/{entity}', [OnecOutboundController::class, 'pushList']);
    Route::delete('/push/{entity}',    [OnecOutboundController::class, 'pushDelete']);
    Route::post('/export/persons',     [OnecOutboundController::class, 'exportPersons']);
});
