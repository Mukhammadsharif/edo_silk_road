<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\Company;
use App\Models\BitrixHashes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class CompanyController extends Controller
{
    /**
     * @OA\Put(
     *     path="/api/main/api-1c/v1/companies/create_update",
     *     summary="Создание или обновление компании",
     *     description="Создает новую компанию или обновляет существующую по Bitrix ID",
     *     tags={"Bitrix"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ID","name"},
     *             @OA\Property(
     *                 property="ID",
     *                 type="string",
     *                 example="BITRIX_COMPANY_123"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="ООО Ромашка"
     *             ),
     *             @OA\Property(
     *                 property="isBranch",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="parentOrganizationID",
     *                 type="string",
     *                 nullable=true,
     *                 example="BITRIX_PARENT_001"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Компания успешно создана или обновлена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="company_id", type="string"),
     *             @OA\Property(property="local_id", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'ID' => 'required|string',
                'name' => 'required|string|max:255',
                'isBranch' => 'boolean',
                'parentOrganizationID' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $company = $request->all();
            Log::info('Processing company store request', [
                'company_id' => $company['ID'],
                'name' => $company['name'],
                'is_branch' => $company['isBranch'] ?? false
            ]);

            // Check for self-referencing
            $isSelfReferencing = isset($company['parentOrganizationID']) &&
                $company['parentOrganizationID'] === $company['ID'];
            if ($isSelfReferencing) {
                Log::warning('Self-referencing parent organization detected', [
                    'company_id' => $company['ID']
                ]);
                throw new Exception('Company cannot reference itself as parent');
            }

            $bitrixHash = BitrixHashes::where('type', 'C')
                ->where('hash', $company['ID'])
                ->first();

            if ($bitrixHash) {
                // Update existing company
                $companyModel = Company::find($bitrixHash->local_id);
                if (!$companyModel) {
                    Log::error('Company not found for existing Bitrix hash', [
                        'bitrix_hash_id' => $bitrixHash->id,
                        'company_id' => $company['ID']
                    ]);
                    throw new Exception('Company not found for existing Bitrix hash');
                }

                $companyModel->name = $company['name'];
                $companyModel->is_branch = $company['isBranch'] ? 1 : 0;
                $companyModel->status = 1;

                if (isset($company['parentOrganizationID'])) {
                    $parentBitrixHash = BitrixHashes::where('type', 'C')
                        ->where('hash', $company['parentOrganizationID'])
                        ->first();
                    if ($parentBitrixHash) {
                        $parentCompany = Company::find($parentBitrixHash->local_id);
                        $companyModel->parent_id = $parentCompany ? $parentBitrixHash->local_id : 0;
                    } else {
                        $companyModel->parent_id = 0;
                    }
                } else {
                    $companyModel->parent_id = 0;
                }

                $companyModel->save();
                Log::info('Company updated successfully', [
                    'company_id' => $company['ID'],
                    'local_id' => $companyModel->id
                ]);
            } else {
                // Create new company
                $companyModel = new Company();
                $companyModel->name = $company['name'];
                $companyModel->is_branch = $company['isBranch'] ? 1 : 0;
                $companyModel->status = 1;

                if (isset($company['parentOrganizationID'])) {
                    $parentBitrixHash = BitrixHashes::where('type', 'C')
                        ->where('hash', $company['parentOrganizationID'])
                        ->first();
                    if ($parentBitrixHash) {
                        $parentCompany = Company::find($parentBitrixHash->local_id);
                        $companyModel->parent_id = $parentCompany ? $parentBitrixHash->local_id : 0;
                    } else {
                        $companyModel->parent_id = 0;
                    }
                } else {
                    $companyModel->parent_id = 0;
                }

                $companyModel->save();

                $bitrixHash = new BitrixHashes();
                $bitrixHash->type = 'C';
                $bitrixHash->hash = $company['ID'];
                $bitrixHash->local_id = $companyModel->id;
                $bitrixHash->save();

                Log::info('Company created successfully', [
                    'company_id' => $company['ID'],
                    'local_id' => $companyModel->id,
                    'bitrix_hash_id' => $bitrixHash->id
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Company create/update successful',
                'company_id' => $company['ID'],
                'local_id' => $companyModel->id
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation failed for company store request', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing company store request', [
                'error' => $e->getMessage(),
                'company_id' => $company['ID'] ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error processing request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
