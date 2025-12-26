<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="UserRoleItem",
 *     type="object",
 *
 *     @OA\Property(property="role_id", type="integer", example=1),
 *     @OA\Property(property="model_type", type="string", example="App\Models\User"),
 *     @OA\Property(property="model_id", type="integer", example=1)
 * )
 */
class UserRoleItem {}
