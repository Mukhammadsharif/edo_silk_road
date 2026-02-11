<?php

namespace App\Http\Controllers\Api\ECP;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignDocImzoMobileApiController extends Controller
{
    public string $eriSrvIp;

    public function __construct()
    {
        $this->eriSrvIp = config('services.e-imzo-server.url');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/auth",
     *     summary="Генерация challenge для идентификации через ID-Card",
     *     description="Создает случайное значение challenge для подписания пользователем",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Challenge успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="siteId", type="string", example="0000"),
     *             @OA\Property(property="documentId", type="string", example="2944F1F2"),
     *             @OA\Property(property="challenge", type="string", example="F8D2181DC6C02EA819B88FF3EF49BE0C"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function mobileAuth(): JsonResponse
    {
        try {
            $path = '/frontend/mobile/auth';
            $response = $this->curlPost($path, null);

            $status = 0;
            $siteId = '';
            $documentId = '';
            $challenge = '';
            $message = null;

            if ($response) {
                $jsonDecode = json_decode($response, true);
                $status = $jsonDecode['status'] ?? 0;
                $siteId = $jsonDecode['siteId'] ?? '';
                $documentId = $jsonDecode['documentId'] ?? '';
                $challenge = $jsonDecode['challange'] ?? ''; // Note: API uses 'challange' with typo
                $message = $jsonDecode['message'] ?? null;
            }

            return response()->json([
                'status' => $status,
                'siteId' => $siteId,
                'documentId' => $documentId,
                'challenge' => $challenge,
                'message' => $message
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in mobile auth', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Server communication error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/status",
     *     summary="Проверка статуса подписания",
     *     description="Проверяет загружен ли PKCS#7 документ",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId"},
     *             @OA\Property(property="documentId", type="string", example="BBF3E8C3")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Статус получен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function mobileStatus(Request $request): JsonResponse
    {
        try {
            $documentId = $request->input('documentId');

            if (!$documentId) {
                Log::warning('Missing documentId in request', ['request' => $request->all()]);
                return response()->json([
                    'status' => 0,
                    'message' => 'Document ID is required'
                ], 400);
            }

            $path = '/frontend/mobile/status';
            $response = $this->curlPostForm($path, http_build_query(['documentId' => $documentId]));

            $status = 0;
            $message = 'Status retrieved';

            if ($response === false) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Server communication error'
                ], 500);
            }

            $jsonDecode = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $status = $jsonDecode['status'] ?? 0;
                $message = $jsonDecode['message'] ?? 'Status retrieved';
            } else {
                $message = 'Invalid response from server: ' . json_last_error_msg();
            }

            return response()->json([
                'status' => $status,
                'message' => $message
            ]);

        } catch (\Throwable $e) {
            Log::error('Error checking mobile status', [
                'error' => $e->getMessage(),
                'documentId' => $request->documentId ?? null
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/upload",
     *     summary="Endpoint для загрузки PKCS#7 от мобильного приложения",
     *     description="Принимает PKCS#7 документ от E-IMZO ID-CARD приложения",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId", "pkcs7"},
     *             @OA\Property(property="documentId", type="string"),
     *             @OA\Property(property="pkcs7", type="string"),
     *             @OA\Property(property="serialNumber", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PKCS#7 загружен"
     *     )
     * )
     */
    public function mobileUpload(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            $documentId = $data['document_id'];
            $pkcs7 = $data['pkcs7_b64'];
            $serialNumber = $data['serial_number'];

            $path = '/frontend/mobile/upload';
            $postData = http_build_query([
                'document_id' => $documentId,
                'pkcs7_b64' => $pkcs7,
                'serial_number' => $serialNumber
            ]);

            sleep(1);

            $this->mobileVerify($request, $data['document_id']);

            $this->curlPostForm($path, $postData);

            return response()->json([
                'status' => 1,
                'message' => 'PKCS#7 uploaded successfully'
            ]);

        } catch (\Throwable $e) {
            Log::error('Error uploading mobile PKCS7', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Upload failed'
            ], 500);
        }
    }

//    public function mobileUpload(Request $request): JsonResponse
//    {
//        try {
//            $documentId   = $request->input('documentId');
//            $pkcs7        = $request->input('pkcs7');
//            $serialNumber = $request->input('serialNumber', '');
//
//            $data = $request->all();
//
//            // ←─────────────────────────────── самое важное ────────────────────────────────
//            \Log::debug('mobileUpload called', [
//                'document_id' => $data['document_id'],
//                'pkcs7_b64' => $data['pkcs7_b64'],
//                'serial_number' => $data['serial_number'],
//                'documentId'   => $documentId,
//                'serialNumber' => $serialNumber,
//                'pkcs7_length' => strlen($pkcs7),
//                'pkcs7_preview'=> substr($pkcs7, 0, 100) . (strlen($pkcs7) > 100 ? '...' : ''),
//                'ip'           => $request->ip(),
//                'user_agent'   => $request->userAgent(),
//                'all_input'    => $request->except(['pkcs7']), // pkcs7 слишком большой — не пишем целиком
//            ]);
//            // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
//
//            $path = '/frontend/mobile/upload';
//            $postData = http_build_query([
//                'documentId'   => $documentId,
//                'pkcs7'        => $pkcs7,
//                'serialNumber' => $serialNumber
//            ]);
//
//            $this->curlPostForm($path, $postData);
//
//            return response()->json([
//                'status'  => 1,
//                'message' => 'PKCS#7 uploaded successfully'
//            ]);
//
//        } catch (\Throwable $e) {
//            \Log::error('mobileUpload failed', [
//                'message'      => $e->getMessage(),
//                'file'         => $e->getFile(),
//                'line'         => $e->getLine(),
//                'trace'        => $e->getTraceAsString(),
//                'documentId'   => $documentId   ?? 'not set',
//                'serialNumber' => $serialNumber ?? 'not set',
//                'pkcs7_length' => isset($pkcs7) ? strlen($pkcs7) : 'not set',
//                'request'      => [
//                    'ip'        => $request->ip(),
//                    'method'    => $request->method(),
//                    'url'       => $request->fullUrl(),
//                    'headers'   => $request->headers->all(),
//                    'all_input' => $request->except(['pkcs7']),
//                ]
//            ]);
//
//            return response()->json([
//                'status'  => 0,
//                'message' => 'Upload failed'
//            ], 500);
//        }
//    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecp/mobile/authenticate/{documentId}",
     *     summary="Проверка результата идентификации",
     *     description="Возвращает информацию о сертификате после идентификации",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\Parameter(
     *         name="documentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Информация о сертификате"
     *     )
     * )
     */
    public function mobileAuthenticate(Request $request, string $documentId): JsonResponse
    {
        try {
            $clientIp = $request->ip();
            $host = $request->getHost();

            $path = '/backend/mobile/authenticate/' . $documentId;
            $response = $this->curlGet($path, $clientIp, $host);

            if ($response === false) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Server communication error'
                ], 500);
            }

            $jsonData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid response from server'
                ], 500);
            }

            return response()->json($jsonData);

        } catch (\Throwable $e) {
            Log::error('Error in mobile authenticate', [
                'error' => $e->getMessage(),
                'documentId' => $documentId
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Authentication failed'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/sign",
     *     summary="Генерация документа для подписания",
     *     description="Создает DocumentID для подписания",
     *     tags={"ECP - E-IMZO Mobile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="DocumentID создан"
     *     )
     * )
     */
    public function mobileSign(): JsonResponse
    {
        try {
            $path = '/frontend/mobile/sign';
            $response = $this->curlPost($path, null);

            $status = 0;
            $siteId = '';
            $documentId = '';
            $message = null;

            if ($response) {
                $jsonDecode = json_decode($response, true);
                $status = $jsonDecode['status'] ?? 0;
                $siteId = $jsonDecode['siteId'] ?? '';
                $documentId = $jsonDecode['documentId'] ?? '';
                $message = $jsonDecode['message'] ?? null;
            }

            return response()->json([
                'status' => $status,
                'siteId' => $siteId,
                'documentId' => $documentId,
                'message' => $message
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in mobile sign', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Failed to generate document ID'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/verify",
     *     summary="Проверка подписи документа",
     *     description="Проверяет подпись документа",
     *     tags={"ECP - E-IMZO Mobile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId", "document"},
     *             @OA\Property(property="documentId", type="string"),
     *             @OA\Property(property="document", type="string", description="Base64 encoded document")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Результат проверки подписи"
     *     )
     * )
     */
    public function mobileVerify(Request $request, $documentId): JsonResponse
    {
        try {

            $clientIp = $request->ip();
            $host = $request->getHost();

            $path = '/backend/mobile/verify';
            $postData = 'documentId=' . urlencode($documentId) . '&document=' . urlencode(base64_encode("19001"));

            $response = $this->curlPostFormBack($path, $postData, $clientIp, $host);

            if ($response === false) {
                Log::error('cURL Request Failed', ['documentId' => $documentId]);
                return response()->json([
                    'status' => 0,
                    'message' => 'Server communication error'
                ], 500);
            }

            $jsonData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid response from server'
                ], 500);
            }

            Log::info('Mobile verify response', ['jsonData' => $jsonData]);

            return response()->json($jsonData);

        } catch (\Throwable $e) {
            Log::error('Error in mobile verify', [
                'error' => $e->getMessage(),
                'documentId' => $request->documentId ?? null
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Verification failed'
            ], 500);
        }
    }

    /**
     * POST request to E-IMZO server (для frontend методов)
     */
    private function curlPost(string $path, ?string $postData): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPost', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
        }

        curl_close($ch);

        return $response ?: null;
    }

    /**
     * POST request with form data (для frontend методов)
     */
    private function curlPostForm(string $path, string $postData): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPostForm', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $response ?: null;
    }

    /**
     * GET request to E-IMZO server (для backend методов)
     */
    private function curlGet(string $path, string $clientIp, string $host): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'X-Real-IP: ' . $clientIp,
                'Host: ' . $host,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlGet', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $response ?: null;
    }

    /**
     * POST request with form data to backend (для backend методов)
     */
    private function curlPostFormBack(string $path, string $postData, string $clientIp, string $host): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'X-Real-IP: ' . $clientIp,
                'Host: ' . $host,
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPostFormBack', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $response ?: null;
    }
}
