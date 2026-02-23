<?php


namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="JournalListResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Journal types retrieved successfully"),
 *
 *     @OA\Property(
 *         property="data",
 *         ref="#/components/schemas/JournalListData"
 *     )
 * )
 */
class JournalListResponse
{
}
