<?php

namespace App\Http\Controllers\Api\Bitrix;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserWorkHistory;
use App\Models\BitrixHashes;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    /**
     * @OA\Put(
     *     path="/api/main/api-1c/v1/employees/create_update_list",
     *     summary="Создание или обновление сотрудников",
     *     description="Создает или обновляет сотрудников (трудовую историю) по Bitrix ID",
     *     tags={"Bitrix"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"employees"},
     *             @OA\Property(
     *                 property="employees",
     *                 type="array",
     *                 @OA\Items(
     *                     required={
     *                         "ID","personID","divisionID","positionID","employmentDate"
     *                     },
     *                     @OA\Property(property="ID", type="string", example="BITRIX_E_001"),
     *                     @OA\Property(property="personID", type="string", example="BITRIX_P_001"),
     *                     @OA\Property(property="divisionID", type="string", example="BITRIX_D_001"),
     *                     @OA\Property(property="positionID", type="string", example="BITRIX_PS_001"),
     *                     @OA\Property(
     *                         property="employmentDate",
     *                         type="string",
     *                         format="date",
     *                         example="2024-01-15"
     *                     ),
     *                     @OA\Property(
     *                         property="dismissed",
     *                         type="boolean",
     *                         example=false
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Сотрудники успешно обработаны",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="processed_count", type="integer")
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
                'employees' => 'required|array',
                'employees.*.ID' => 'required|string',
                'employees.*.personID' => 'required|string',
                'employees.*.divisionID' => 'required|string',
                'employees.*.positionID' => 'required|string',
                'employees.*.employmentDate' => 'required|date',
                'employees.*.dismissed' => 'boolean',
            ]);

            DB::beginTransaction();

            $employeesArray = $request->employees;

            Log::info('Processing employees store request', [
                'employee_count' => count($employeesArray)
            ]);

            if (empty($employeesArray)) {
                throw new Exception('No employees provided in request');
            }

            foreach ($employeesArray as $employee) {
                Log::info('Processing employee', [
                    'employee_id' => $employee['ID'],
                    'person_id' => $employee['personID'],
                    'division_id' => $employee['divisionID'],
                    'position_id' => $employee['positionID']
                ]);

                $bitrixHash = BitrixHashes::where('type', 'E')->where('hash', $employee['ID'])->first();
                $bitrixUser = BitrixHashes::where('type', 'P')->where('hash', $employee['personID'])->first();
                $bitrixDepartment = BitrixHashes::where('type', 'D')->where('hash', $employee['divisionID'])->first();
                $bitrixPosition = BitrixHashes::where('type', 'PS')->where('hash', $employee['positionID'])->first();

                // Check if required entities exist
                if (!$bitrixUser) {
                    Log::error('User not found for employee', [
                        'employee_id' => $employee['ID'],
                        'person_id' => $employee['personID']
                    ]);
                    throw new Exception("User not found for person ID {$employee['personID']}");
                }

                if (!$bitrixDepartment) {
                    Log::error('Department not found for employee', [
                        'employee_id' => $employee['ID'],
                        'division_id' => $employee['divisionID']
                    ]);
                    throw new Exception("Department not found for division ID {$employee['divisionID']}");
                }

                if (!$bitrixPosition) {
                    Log::error('Position not found for employee', [
                        'employee_id' => $employee['ID'],
                        'position_id' => $employee['positionID']
                    ]);
                    throw new Exception("Position not found for position ID {$employee['positionID']}");
                }

                if ($bitrixHash) {
                    // Update existing employee
                    $workUser = UserWorkHistory::where('id', $bitrixHash->local_id)->first();
                    if (!$workUser) {
                        Log::error('Work history not found for existing Bitrix hash', [
                            'bitrix_hash_id' => $bitrixHash->id,
                            'employee_id' => $employee['ID']
                        ]);
                        throw new Exception("Work history not found for employee ID {$employee['ID']}");
                    }

                    $workUser->update([
                        'user_id' => $bitrixUser->local_id,
                        'dep_id' => $bitrixDepartment->local_id,
                        'post_id' => $bitrixPosition->local_id,
                        'from_date' => Carbon::parse($employee['employmentDate'])->format('Y-m-d'),
                        'to_date' => Carbon::parse($employee['employmentDate'])->addYear()->format('Y-m-d'),
                        'status' => $employee['dismissed'] ? 2 : 1,
                    ]);

                    Log::info('Employee updated successfully', [
                        'employee_id' => $employee['ID'],
                        'local_id' => $workUser->id
                    ]);
                } else {
                    // Create new employee
                    $workUser = new UserWorkHistory();
                    $workUser->user_id = $bitrixUser->local_id;
                    $workUser->dep_id = $bitrixDepartment->local_id;
                    $workUser->post_id = $bitrixPosition->local_id;
                    $workUser->from_date = Carbon::parse($employee['employmentDate'])->format('Y-m-d');
                    $workUser->to_date = Carbon::parse($employee['employmentDate'])->addYear()->format('Y-m-d');
                    $workUser->status = $employee['dismissed'] ? 2 : 1;
                    $workUser->save();

                    $bitrixHash = new BitrixHashes();
                    $bitrixHash->type = 'E';
                    $bitrixHash->hash = $employee['ID'];
                    $bitrixHash->local_id = $workUser->id;
                    $bitrixHash->save();

                    Log::info('Employee created successfully', [
                        'employee_id' => $employee['ID'],
                        'local_id' => $workUser->id,
                        'bitrix_hash_id' => $bitrixHash->id
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Employees create/update successful',
                'processed_count' => count($employeesArray)
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation failed for employees store request', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing employees store request', [
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
