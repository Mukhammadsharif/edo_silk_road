<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="RoleListResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Roles retrieved successfully"),
 *
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *
 *         @OA\Property(
 *             property="data",
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/RoleItem")
 *         ),
 *
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="per_page", type="integer", example=100),
 *         @OA\Property(property="total", type="integer", example=25)
 *     )
 * )
 */
class RoleListResponse {}
