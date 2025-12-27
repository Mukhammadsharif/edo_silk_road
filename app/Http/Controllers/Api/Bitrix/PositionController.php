<?php

namespace App\Http\Controllers\Api\Bitrix;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserPosition;
use App\Models\BitrixHashes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PositionController extends Controller
{
    /**
     * @OA\Put(
     *     path="/api/main/api-1c/v1/positions/create_update_list",
     *     summary="Создание или обновление должностей",
     *     description="Создает или обновляет должности пользователей по Bitrix ID",
     *     tags={"Bitrix"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"positions"},
     *             @OA\Property(
     *                 property="positions",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"ID","name"},
     *                     @OA\Property(property="ID", type="string", example="BITRIX_PS_001"),
     *                     @OA\Property(property="name", type="string", example="Менеджер по персоналу")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Должности успешно обработаны",
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
                'positions' => 'required|array',
                'positions.*.ID' => 'required|string',
                'positions.*.name' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            $positionsArray = $request->positions;

            Log::info('Processing positions store request', [
                'position_count' => count($positionsArray)
            ]);

            if (empty($positionsArray)) {
                throw new Exception('No positions provided in request');
            }

            foreach ($positionsArray as $positionData) {
                Log::info('Processing position', [
                    'position_id' => $positionData['ID'],
                    'name' => $positionData['name']
                ]);

                $bitrixHash = BitrixHashes::where('type', 'PS')->where('hash', $positionData['ID'])->first();

                if ($bitrixHash) {
                    // Update existing position
                    $position = UserPosition::where('id', $bitrixHash->local_id)->first();
                    if (!$position) {
                        Log::error('UserPosition not found for existing Bitrix hash', [
                            'bitrix_hash_id' => $bitrixHash->id,
                            'position_id' => $positionData['ID']
                        ]);
                        throw new Exception("UserPosition not found for position ID {$positionData['ID']}");
                    }

                    $position->update([
                        'name_uz' => $positionData['name'],
                    ]);

                    Log::info('Position updated successfully', [
                        'position_id' => $positionData['ID'],
                        'local_id' => $position->id
                    ]);
                } else {
                    // Create new position
                    $position = new UserPosition();
                    $position->name_uz = $positionData['name'];
                    $position->save();

                    $bitrixHash = new BitrixHashes();
                    $bitrixHash->type = 'PS';
                    $bitrixHash->hash = $positionData['ID'];
                    $bitrixHash->local_id = $position->id;
                    $bitrixHash->save();

                    Log::info('Position created successfully', [
                        'position_id' => $positionData['ID'],
                        'local_id' => $position->id,
                        'bitrix_hash_id' => $bitrixHash->id
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Positions create/update successful',
                'processed_count' => count($positionsArray)
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation failed for positions store request', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing positions store request', [
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
