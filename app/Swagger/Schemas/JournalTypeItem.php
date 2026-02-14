<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="JournalTypeItem",
 *     type="object",
 *     title="Journal type item",
 *     description="Элемент справочника типов журналов",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="parent_id", type="integer", example=4),
 *     @OA\Property(property="title", type="string", example="Внутренний документ"),
 *     @OA\Property(property="lang_id", type="integer", example=10),
 *     @OA\Property(property="doc_code", type="string", example="BASE_INTERNAL"),
 *     @OA\Property(property="order_number", type="integer", example=10),
 *     @OA\Property(property="level_number", type="integer", example=2),
 *     @OA\Property(property="status", type="integer", example=1),
 * )
 */
class JournalTypeItem {}
