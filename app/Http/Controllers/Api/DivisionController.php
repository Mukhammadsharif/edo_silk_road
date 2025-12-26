<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\Company;
use App\Models\BitrixHashes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class DivisionController extends Controller
{
    /**
     * @OA\Put(
     *     path="/api/main/api-1c/v1/divisions/create_update_list",
     *     summary="Создание или обновление подразделений",
     *     description="Создает или обновляет подразделения с иерархией (parentDivisionID) по Bitrix ID",
     *     tags={"Bitrix"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"divisions"},
     *             @OA\Property(
     *                 property="divisions",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"ID","name","companyID"},
     *                     @OA\Property(property="ID", type="string", example="BITRIX_D_001"),
     *                     @OA\Property(property="name", type="string", example="Отдел кадров"),
     *                     @OA\Property(property="companyID", type="string", example="BITRIX_C_001"),
     *                     @OA\Property(
     *                         property="parentDivisionID",
     *                         type="string",
     *                         nullable=true,
     *                         example="BITRIX_D_PARENT_001"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Подразделения успешно обработаны",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="processed_count", type="integer"),
     *             @OA\Property(property="pending_unresolved", type="integer")
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
                'divisions' => 'required|array',
                'divisions.*.ID' => 'required|string',
                'divisions.*.name' => 'required|string|max:255',
                'divisions.*.companyID' => 'required|string',
                'divisions.*.parentDivisionID' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $divisionsArray = $request->divisions;
            $pendingDivisions = [];

            Log::info('Processing divisions store request', [
                'division_count' => count($divisionsArray)
            ]);

            if (empty($divisionsArray)) {
                throw new Exception('No divisions provided in request');
            }

            // First pass: Process all divisions
            foreach ($divisionsArray as $key => $division) {
                Log::info('Processing division', [
                    'division_id' => $division['ID'],
                    'name' => $division['name'],
                    'company_id' => $division['companyID']
                ]);

                // Prevent self-referencing parent
                $isSelfReferencing = $division['parentDivisionID'] === $division['ID'];
                if ($isSelfReferencing) {
                    Log::warning('Self-referencing parent division detected', [
                        'division_id' => $division['ID']
                    ]);
                    throw new Exception("Division {$division['ID']} cannot reference itself as parent");
                }

                $bitrixHash = BitrixHashes::where('type', 'D')->where('hash', $division['ID'])->first();
                $bitrixCompany = BitrixHashes::where('type', 'C')->where('hash', $division['companyID'])->first();

                if (!$bitrixCompany) {
                    Log::error('Company not found for division', [
                        'division_id' => $division['ID'],
                        'company_id' => $division['companyID']
                    ]);
                    throw new Exception("Company not found for ID {$division['companyID']}");
                }

                $company = Company::find($bitrixCompany->local_id);
                if (!$company) {
                    Log::error('Company record not found', [
                        'division_id' => $division['ID'],
                        'company_id' => $division['companyID']
                    ]);
                    throw new Exception("Company record not found for ID {$division['companyID']}");
                }

                if ($bitrixHash) {
                    // Update existing department
                    $department = Department::find($bitrixHash->local_id);
                    if (!$department) {
                        Log::error('Department not found for existing Bitrix hash', [
                            'bitrix_hash_id' => $bitrixHash->id,
                            'division_id' => $division['ID']
                        ]);
                        throw new Exception("Department not found for division ID {$division['ID']}");
                    }

                    $department->name_uz = $division['name'];
                    if ($division['parentDivisionID']) {
                        $parentBitrixHash = BitrixHashes::where('type', 'D')->where('hash', $division['parentDivisionID'])->first();
                        if ($parentBitrixHash) {
                            $parentDepartment = Department::find($parentBitrixHash->local_id);
                            if ($parentDepartment) {
                                $department->parent_id = $parentBitrixHash->local_id;
                                $department->level_number = $parentDepartment->level_number + 1;
                            } else {
                                $department->parent_id = 0;
                                $department->level_number = 1;
                                $pendingDivisions[] = ['division' => $division, 'key' => $key];
                            }
                        } else {
                            $department->parent_id = 0;
                            $department->level_number = 1;
                            $pendingDivisions[] = ['division' => $division, 'key' => $key];
                        }
                    } else {
                        $department->parent_id = 0;
                        $department->level_number = 1;
                    }
                    $department->order_number = $key + 1;
                    $department->company_id = $company->id;
                    $department->save();

                    Log::info('Department updated successfully', [
                        'division_id' => $division['ID'],
                        'local_id' => $department->id
                    ]);
                } else {
                    // Create new department
                    $department = new Department();
                    $department->name_uz = $division['name'];
                    $department->order_number = $key + 1;
                    $department->company_id = $company->id;

                    if ($division['parentDivisionID']) {
                        $parentBitrixHash = BitrixHashes::where('type', 'D')->where('hash', $division['parentDivisionID'])->first();
                        if ($parentBitrixHash) {
                            $parentDepartment = Department::find($parentBitrixHash->local_id);
                            if ($parentDepartment) {
                                $department->parent_id = $parentBitrixHash->local_id;
                                $department->level_number = $parentDepartment->level_number + 1;
                            } else {
                                $department->parent_id = 0;
                                $department->level_number = 1;
                                $pendingDivisions[] = ['division' => $division, 'key' => $key];
                            }
                        } else {
                            $department->parent_id = 0;
                            $department->level_number = 1;
                            $pendingDivisions[] = ['division' => $division, 'key' => $key];
                        }
                    } else {
                        $department->parent_id = 0;
                        $department->level_number = 1;
                    }

                    $department->save();

                    $bitrixHash = new BitrixHashes();
                    $bitrixHash->type = 'D';
                    $bitrixHash->hash = $division['ID'];
                    $bitrixHash->local_id = $department->id;
                    $bitrixHash->save();

                    Log::info('Department created successfully', [
                        'division_id' => $division['ID'],
                        'local_id' => $department->id,
                        'bitrix_hash_id' => $bitrixHash->id
                    ]);
                }
            }

            // Second pass: Reprocess pending divisions
            $maxAttempts = count($divisionsArray);
            $attempt = 0;
            while (count($pendingDivisions) > 0 && $attempt < $maxAttempts) {
                $stillPending = [];
                foreach ($pendingDivisions as $pending) {
                    $division = $pending['division'];
                    $key = $pending['key'];

                    $bitrixHash = BitrixHashes::where('type', 'D')->where('hash', $division['ID'])->first();
                    if ($bitrixHash) {
                        $department = Department::find($bitrixHash->local_id);
                        if ($department) {
                            $parentBitrixHash = BitrixHashes::where('type', 'D')->where('hash', $division['parentDivisionID'])->first();
                            if ($parentBitrixHash) {
                                $parentDepartment = Department::find($parentBitrixHash->local_id);
                                if ($parentDepartment) {
                                    $department->name_uz = $division['name'];
                                    $department->parent_id = $parentBitrixHash->local_id;
                                    $department->level_number = $parentDepartment->level_number + 1;
                                    $department->order_number = $key + 1;
                                    $department->company_id = $company->id;
                                    $department->save();

                                    Log::info('Pending department parent updated', [
                                        'division_id' => $division['ID'],
                                        'local_id' => $department->id,
                                        'parent_id' => $department->parent_id
                                    ]);
                                } else {
                                    $stillPending[] = $pending;
                                }
                            } else {
                                $stillPending[] = $pending;
                            }
                        }
                    }
                }

                $pendingDivisions = $stillPending;
                $attempt++;
                if (count($stillPending) > 0 && $attempt >= $maxAttempts) {
                    Log::warning('Unresolved parent divisions after max attempts', [
                        'pending_count' => count($stillPending),
                        'pending_divisions' => array_map(fn($p) => $p['division']['ID'], $stillPending)
                    ]);
                    throw new Exception('Unable to resolve some parent division references');
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Divisions create/update successful',
                'processed_count' => count($divisionsArray),
                'pending_unresolved' => count($pendingDivisions)
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation failed for divisions store request', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing divisions store request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error processing request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
