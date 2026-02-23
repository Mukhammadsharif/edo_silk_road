<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="UserWorkItem",
 *     type="object",
 *
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="dep_id", type="integer", example=3),
 *     @OA\Property(property="post_id", type="integer", example="1"),
 *     @OA\Property(property="from_date", type="string", example="2025-01-15"),
 *     @OA\Property(property="to_date", type="string", example="2025-01-15"),
 *     @OA\Property(property="status", type="integer", example=1),
 * )
 */
class UserWorkItem {}
