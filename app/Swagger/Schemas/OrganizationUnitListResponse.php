<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="OrganizationUnitListResponse",
 *     type="object",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Organization units retrieved successfully"),
 *
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *
 *         @OA\Property(
 *             property="data",
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/OrganizationUnitItem")
 *         ),
 *
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="per_page", type="integer", example=100),
 *         @OA\Property(property="total", type="integer", example=30),
 *         @OA\Property(property="last_page", type="integer", example=1)
 *     )
 * )
 */
class OrganizationUnitListResponse {}
