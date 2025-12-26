<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="StatusListItem",
 *     type="object",
 *     title="Status list item",
 *     description="Элемент справочника статусов документов",
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         example=1
 *     ),
 *
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         example="doc_exec",
 *         description="Тип статуса"
 *     ),
 *
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         example=2,
 *         description="Код статуса"
 *     ),
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="В работе",
 *         description="Название статуса"
 *     ),
 *
 *     @OA\Property(
 *         property="order_number",
 *         type="integer",
 *         example=1,
 *         description="Порядок сортировки"
 *     ),
 *
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-01T10:00:00Z"
 *     ),
 *
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-01T10:00:00Z"
 *     )
 * )
 */
class StatusListItem
{
}
