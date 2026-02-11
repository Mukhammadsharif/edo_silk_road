<?php

namespace App\Http\Controllers\Api\ECP;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Models\ECP\SignatureDocuments;
use App\Models\ECP\SignatureDocumentType;
use App\Models\ECP\UserSignatureKey;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SignDocImzoMobileApiController extends Controller
{
    public string $eriSrvIp;
    private string $siteId;
    private int $documentTtl = 300; // 5 minutes in seconds

    public function __construct()
    {
        $this->eriSrvIp = config('services.e-imzo-server.url');
        $this->siteId = config('services.e-imzo-mobile.site_id', '0000');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/auth",
     *     summary="Генерация challenge для идентификации через ID-Card",
     *     description="Создает случайное значение challenge для подписания пользователем через мобильное приложение E-IMZO ID-CARD",
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
     *     ),
     *
     *     @OA\Response(response=400, description="Ошибка в параметрах запроса"),
     *     @OA\Response(response=503, description="Ошибка сервера E-IMZO")
     * )
     */
    public function mobileAuth(): JsonResponse
    {
        try {
            // Generate random challenge (32 hex characters = 128 bits)
            $challenge = strtoupper(Str::random(32));

            // Generate unique document ID (8 hex characters)
            $documentId = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

            // Store in Redis with TTL
            $redisKey = "mobile:auth:{$documentId}";
            Redis::setex($redisKey, $this->documentTtl, json_encode([
                'challenge' => $challenge,
                'created_at' => now()->toIso8601String(),
                'type' => 'auth'
            ]));

            return response()->json([
                'status' => 1,
                'siteId' => $this->siteId,
                'documentId' => $documentId,
                'challenge' => $challenge,
                'message' => null
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in mobile auth', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => -1,
                'message' => 'Redis connection error or internal server error'
            ], 503);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/status",
     *     summary="Проверка статуса подписания",
     *     description="Проверяет загружен ли PKCS#7 документ от мобильного приложения",
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
     *             @OA\Property(property="status", type="integer", description="1=Success, 2=Not uploaded yet, negative=Error"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Ошибка в параметрах запроса")
     * )
     */
    public function mobileStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'documentId' => 'required|string'
            ]);

            $documentId = $request->documentId;
            $redisKey = "mobile:auth:{$documentId}";

            // Check if document exists in Redis
            if (!Redis::exists($redisKey)) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Document ID not found in Redis (possibly expired)'
                ]);
            }

            $data = json_decode(Redis::get($redisKey), true);

            // Check if PKCS7 has been uploaded
            if (isset($data['pkcs7']) && !empty($data['pkcs7'])) {
                return response()->json([
                    'status' => 1,
                    'message' => null
                ]);
            }

            // PKCS7 not yet uploaded
            return response()->json([
                'status' => 2,
                'message' => 'PKCS#7 not yet uploaded from E-IMZO Mobile'
            ]);

        } catch (\Throwable $e) {
            Log::error('Error checking mobile status', [
                'error' => $e->getMessage(),
                'documentId' => $request->documentId ?? null
            ]);

            return response()->json([
                'status' => -1,
                'message' => 'Redis connection error'
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/upload",
     *     summary="Endpoint для загрузки PKCS#7 от мобильного приложения",
     *     description="Принимает PKCS#7 документ от E-IMZO ID-CARD мобильного приложения",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId", "pkcs7"},
     *             @OA\Property(property="documentId", type="string", example="2944F1F2"),
     *             @OA\Property(property="pkcs7", type="string", description="Base64 encoded PKCS#7 document")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PKCS#7 успешно загружен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="PKCS#7 uploaded successfully")
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Ошибка в параметрах")
     * )
     */
    public function mobileUpload(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'documentId' => 'required|string',
                'pkcs7' => 'required|string'
            ]);

            $documentId = $request->documentId;
            $pkcs7 = $request->pkcs7;

            $redisKey = "mobile:auth:{$documentId}";

            // Check if document exists
            if (!Redis::exists($redisKey)) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Document ID not found in Redis'
                ], 400);
            }

            // Get existing data and add PKCS7
            $data = json_decode(Redis::get($redisKey), true);
            $data['pkcs7'] = $pkcs7;
            $data['uploaded_at'] = now()->toIso8601String();

            // Update Redis with new data, keeping the same TTL
            $ttl = Redis::ttl($redisKey);
            Redis::setex($redisKey, max($ttl, 60), json_encode($data));

            return response()->json([
                'status' => 1,
                'message' => 'PKCS#7 uploaded successfully'
            ]);

        } catch (\Throwable $e) {
            Log::error('Error uploading mobile PKCS7', [
                'error' => $e->getMessage(),
                'documentId' => $request->documentId ?? null
            ]);

            return response()->json([
                'status' => -1,
                'message' => 'Failed to upload PKCS#7'
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecp/mobile/authenticate/{documentId}",
     *     summary="Проверка результата идентификации пользователя",
     *     description="Проверяет и возвращает информацию о сертификате после успешной идентификации",
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
     *         description="Успешная идентификация",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(
     *                 property="subjectCertificateInfo",
     *                 type="object",
     *                 @OA\Property(property="serialNumber", type="string"),
     *                 @OA\Property(property="X500Name", type="string"),
     *                 @OA\Property(property="subjectName", type="object"),
     *                 @OA\Property(property="validFrom", type="string"),
     *                 @OA\Property(property="validTo", type="string")
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Ошибка проверки"),
     *     @OA\Response(response=503, description="Ошибка сервера")
     * )
     */
    public function mobileAuthenticate(Request $request, string $documentId): JsonResponse
    {
        try {
            $redisKey = "mobile:auth:{$documentId}";

            // Check if document exists
            if (!Redis::exists($redisKey)) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Document ID not found in Redis (possibly expired)'
                ], 400);
            }

            $data = json_decode(Redis::get($redisKey), true);

            // Check if PKCS7 exists
            if (!isset($data['pkcs7']) || empty($data['pkcs7'])) {
                return response()->json([
                    'status' => 2,
                    'message' => 'PKCS#7 not yet uploaded from E-IMZO Mobile'
                ], 400);
            }

            // Get client IP and host
            $clientIp = $request->ip();
            $host = $request->getHost();

            // Verify PKCS7 with E-IMZO server
            $verificationResult = $this->verifyPkcs7Auth($data['pkcs7'], $clientIp, $host);

            if ($verificationResult['status'] !== 1) {
                return response()->json($verificationResult, 400);
            }

            // Clean up Redis after successful authentication
            Redis::del($redisKey);

            return response()->json($verificationResult);

        } catch (\Throwable $e) {
            Log::error('Error in mobile authenticate', [
                'error' => $e->getMessage(),
                'documentId' => $documentId
            ]);

            return response()->json([
                'status' => -10,
                'message' => 'Internal server error. Check E-IMZO-SERVER logs'
            ], 503);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/sign",
     *     summary="Генерация документа для подписания",
     *     description="Создает DocumentID для подписания пользовательского документа",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="DocumentID создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="siteId", type="string", example="0000"),
     *             @OA\Property(property="documentId", type="string", example="850FF727"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=503, description="Ошибка сервера")
     * )
     */
    public function mobileSign(): JsonResponse
    {
        try {
            // Generate unique document ID for signing
            $documentId = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

            // Store in Redis with TTL
            $redisKey = "mobile:sign:{$documentId}";
            Redis::setex($redisKey, $this->documentTtl, json_encode([
                'created_at' => now()->toIso8601String(),
                'type' => 'sign',
                'user_id' => auth()->id()
            ]));

            return response()->json([
                'status' => 1,
                'siteId' => $this->siteId,
                'documentId' => $documentId,
                'message' => null
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in mobile sign', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => -1,
                'message' => 'Failed to generate document ID'
            ], 503);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/verify",
     *     summary="Проверка подписи документа",
     *     description="Проверяет подпись пользовательского документа",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId", "document"},
     *             @OA\Property(property="documentId", type="string", example="850FF727"),
     *             @OA\Property(property="document", type="string", description="Base64 encoded user document")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Подпись проверена",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="subjectCertificateInfo", type="object"),
     *             @OA\Property(property="verificationInfo", type="object"),
     *             @OA\Property(property="pkcs7Attached", type="string"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Ошибка проверки")
     * )
     */
    public function mobileVerify(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'documentId' => 'required|string',
                'document' => 'required|string'
            ]);

            $documentId = $request->documentId;
            $document = $request->document;

            $redisKey = "mobile:sign:{$documentId}";

            // Check if document exists
            if (!Redis::exists($redisKey)) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Document ID not found in Redis (possibly expired)'
                ], 400);
            }

            $data = json_decode(Redis::get($redisKey), true);

            // Check if PKCS7 exists
            if (!isset($data['pkcs7']) || empty($data['pkcs7'])) {
                return response()->json([
                    'status' => 2,
                    'message' => 'PKCS#7 not yet uploaded from E-IMZO Mobile'
                ], 400);
            }

            // Get client IP and host
            $clientIp = $request->ip();
            $host = $request->getHost();

            // Verify PKCS7 signature with E-IMZO server
            $verificationResult = $this->verifyPkcs7Sign($data['pkcs7'], $document, $clientIp, $host);

            if ($verificationResult['status'] !== 1) {
                return response()->json($verificationResult, 400);
            }

            // Clean up Redis after successful verification
            Redis::del($redisKey);

            return response()->json($verificationResult);

        } catch (\Throwable $e) {
            Log::error('Error in mobile verify', [
                'error' => $e->getMessage(),
                'documentId' => $request->documentId ?? null
            ]);

            return response()->json([
                'status' => -10,
                'message' => 'Internal server error. Check E-IMZO-SERVER logs'
            ], 400);
        }
    }

    /**
     * Verify PKCS7 for authentication
     */
    private function verifyPkcs7Auth(string $pkcs7, string $clientIp, string $host): array
    {
        try {
            $response = Http::withHeaders([
                'X-Real-IP' => $clientIp,
                'Host' => $host,
                'Content-Type' => 'application/json'
            ])->post($this->eriSrvIp . '/backend/auth', $pkcs7);

            if (!$response->successful()) {
                Log::error('E-IMZO server auth failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'status' => -10,
                    'message' => 'E-IMZO server authentication failed'
                ];
            }

            $result = $response->json();

            // Handle different status codes according to documentation
            if (isset($result['status'])) {
                $status = $result['status'];

                // Status code mapping from documentation
                $errorMessages = [
                    -3 => 'Check E-IMZO-SERVER logs',
                    -4 => 'Invalid PKCS#7 document structure',
                    -5 => 'Invalid PKCS#7 signature',
                    -6 => 'Invalid user certificate',
                    -7 => 'Certificate invalid at signing time',
                    -8 => 'Error checking certificate status',
                    -9 => 'Time window exceeded between signing and verification',
                    -10 => 'Check E-IMZO-SERVER logs',
                    -99 => 'Check E-IMZO-SERVER logs',
                    -100 => 'Check E-IMZO-SERVER logs'
                ];

                if ($status !== 1) {
                    return [
                        'status' => $status,
                        'message' => $errorMessages[$status] ?? 'Unknown error'
                    ];
                }
            }

            return $result;

        } catch (\Throwable $e) {
            Log::error('Error verifying PKCS7 auth', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => -10,
                'message' => 'Failed to verify PKCS#7 with E-IMZO server'
            ];
        }
    }

    /**
     * Verify PKCS7 for document signing
     */
    private function verifyPkcs7Sign(string $pkcs7, string $document, string $clientIp, string $host): array
    {
        try {
            $response = Http::withHeaders([
                'X-Real-IP' => $clientIp,
                'Host' => $host,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($this->eriSrvIp . '/backend/mobile/verify', [
                'pkcs7' => $pkcs7,
                'document' => $document
            ]);

            if (!$response->successful()) {
                Log::error('E-IMZO server verify failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'status' => -10,
                    'message' => 'E-IMZO server verification failed'
                ];
            }

            $result = $response->json();

            // Handle status codes
            if (isset($result['status']) && $result['status'] !== 1) {
                $errorMessages = [
                    -3 => 'Check E-IMZO-SERVER logs',
                    -4 => 'Invalid PKCS#7 document structure',
                    -5 => 'Invalid PKCS#7 signature',
                    -6 => 'Invalid user certificate',
                    -7 => 'Certificate invalid at signing time',
                    -8 => 'Error checking certificate status',
                    -9 => 'Time window exceeded',
                    -10 => 'Check E-IMZO-SERVER logs'
                ];

                return [
                    'status' => $result['status'],
                    'message' => $errorMessages[$result['status']] ?? 'Unknown error'
                ];
            }

            return $result;

        } catch (\Throwable $e) {
            Log::error('Error verifying PKCS7 sign', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => -10,
                'message' => 'Failed to verify signature with E-IMZO server'
            ];
        }
    }

    /**
     * Save signature data to database (helper for mobile signing)
     */
    public function saveMobileSignature(int $docId, string $docType, array $certInfo, array $verificationInfo): void
    {
        try {
            // Save or update user signature key
            $userKey = UserSignatureKey::where('user_id', auth()->id())
                ->where('serial_number', $certInfo['serialNumber'])
                ->firstOrNew([
                    'user_id' => auth()->id(),
                    'serial_number' => $certInfo['serialNumber']
                ]);

            $userKey->json_data = json_encode($certInfo);
            $userKey->save();

            // Get or create document type
            $docTypeModel = SignatureDocumentType::where('doc_code', $docType)
                ->firstOrNew(['doc_code' => $docType], ['id' => 1]);

            // Create signature record
            SignatureDocuments::create([
                'doc_id' => $docId,
                'doc_type' => $docTypeModel->id,
                'user_id' => auth()->id(),
                'key_id' => $userKey->id,
                'signing_time' => $verificationInfo['signingTime'] ?? now(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Error saving mobile signature', [
                'error' => $e->getMessage(),
                'doc_id' => $docId
            ]);
        }
    }
}
