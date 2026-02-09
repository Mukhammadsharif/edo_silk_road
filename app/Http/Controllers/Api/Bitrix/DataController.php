<?php

namespace App\Http\Controllers\Api\Bitrix;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateHrOrderPdfJob;
use App\Models\BitrixHashes;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use App\Models\Journal\JournalTypes;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DataController extends Controller
{

    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }
    //

    /**
     * @OA\Get(
     *     path="/api/v1/hr-orders",
     *     summary="Получение HR приказов",
     *     description="Возвращает список HR приказов с пагинацией и статусом PDF",
     *     tags={"HR Orders"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Количество записей на страницу",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Список HR приказов",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="document_name", type="string"),
     *                     @OA\Property(property="document_type", type="string"),
     *                     @OA\Property(property="app_start_date", type="string", example="2024-01-01"),
     *                     @OA\Property(property="app_end_date", type="string", example="2024-01-15"),
     *                     @OA\Property(property="created_date", type="string", example="2024-01-10 14:32:00"),
     *                     @OA\Property(property="sign_date", type="string", example="2024-01-11 09:00:00"),
     *                     @OA\Property(property="document_id", type="string", example="HR_ORD"),
     *                     @OA\Property(property="document_url", type="string"),
     *                     @OA\Property(property="pdf_url", type="string", nullable=true),
     *                     @OA\Property(
     *                         property="pdf_status",
     *                         type="string",
     *                         enum={"ready","generating","not_generated"}
     *                     ),
     *                     @OA\Property(property="emp_name", type="string"),
     *                     @OA\Property(property="emp_id", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="count", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="HR тип документов не найден"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */

    public function hrOrders()
    {
        try {
            $hrOrderType = JournalTypes::where('org_id', $this->organizationId())
                ->where('doc_code', 'HR_ORD')
                ->where('status', 1)
                ->first();

            if (!$hrOrderType) {
                return response()->json([
                    'error' => 'HR Order Type not found'
                ], 404);
            }

            // Fetch HR Order Journal Types
            $hrOrderJournalTypes = JournalTypes::where('org_id', $this->organizationId())
                ->where('parent_id', $hrOrderType->id)
                ->where('status', 1)
                ->pluck('id')
                ->toArray();

            if (empty($hrOrderJournalTypes)) {
                return response()->json([
                    'error' => 'No active HR Order Journal Types found'
                ], 404);
            }

            $perPage = request('per_page', 20);
            $documents = DocumentJournals::with(['doc', 'journal', 'fromUser.personal'])
                ->whereIn('j_type', $hrOrderJournalTypes)
                ->whereIn('status', [9, 10, 11])
                ->paginate($perPage);

            if ($documents->isEmpty()) {
                return response()->json([
                    'data' => [], 'count' => 0, 'message' => 'No documents found'
                ], 200);
            }

            $formattedDocuments = $documents->getCollection()->map(function ($document) {
                $cacheKey = "hr_order_pdf_path:{$document->id}";
                $path = Cache::get($cacheKey);
                $pdfUrl = null;
                $pdfStatus = 'not_generated';

                if ($path && Storage::exists('public/' . $path)) {
                    $pdfUrl = Storage::url($path);
                    $pdfStatus = 'ready';
                } else {
                    // Запускаем генерацию
                    GenerateHrOrderPdfJob::dispatch($document->id)->onQueue('pdf');
                    $pdfStatus = 'generating';
                }

                // === Остальные данные ===
                $metaData = DocumentTypeTempMetaData::where('doc_id', $document->doc_id ?? 0)->first();
                $json_data = $metaData ? json_decode($metaData->meta_data, true) : [];
                $app_id = $json_data['DOCUMENT_ID'] ?? '';
                $app_from_date = $json_data['FROM_DATE_A'] ?? '';
                $app_to_date = $json_data['TO_DATE_A'] ?? '';

                $appDocumentJournal = DocumentJournals::where('doc_id', $app_id)->first();
                $createdUserFullName = $appDocumentJournal && $appDocumentJournal->fromUser?->personal
                    ? $appDocumentJournal->fromUser->personal->fullName()
                    : 'Unknown';

                $createdUserBitrixId = BitrixHashes::where('type', 'P')
                    ->where('local_id', $appDocumentJournal->created_user ?? 0)
                    ->first();

                $directorDocument = DocumentExecutionSteps::where('j_id', $document->id)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'RESOL')->where('status', 1);
                })->first();

                return [
                    'document_name' => $document->doc->doc_name ?? 'N/A',
                    'document_type' => $document->journal->title ?? 'N/A',
                    'app_start_date' => $app_from_date,
                    'app_end_date' => $app_to_date,
                    'created_date' => $document->created_at?->toDateTimeString() ?? 'N/A',
                    'sign_date' => $directorDocument?->updated_at?->toDateTimeString() ?? 'N/A',
                    'document_id' => $document->doc->doc_code ?? 'N/A',
                    'document_url' => env('APP_URL', 'https://demoedo.uztest.uz').'/'. ($document->doc->doc_code ?? ''),
                    'pdf_url' => env('APP_URL', 'https://demoedo.uztest.uz').$pdfUrl,
                    'pdf_status' => $pdfStatus,
                    'emp_name' => $createdUserFullName,
                    'emp_id' => $createdUserBitrixId?->hash ?? ($appDocumentJournal->created_user ?? 'N/A')
                ];
            });

            return response()->json([
                'data' => $formattedDocuments->values(),
                'count' => $formattedDocuments->count(),
                'total' => $documents->total(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'message' => 'Documents retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('hrOrders error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

}
