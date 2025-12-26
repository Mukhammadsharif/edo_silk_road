<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="UserItem",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="username", type="string", example="user"),
 *     @OA\Property(property="email", type="string", example="user@example.com"),
 *     @OA\Property(property="status", type="integer", example=1),
 *
 *     @OA\Property(property="user_personal", ref="#/components/schemas/UserPersonalItem"),
 *     @OA\Property(property="current_work", ref="#/components/schemas/UserWorkItem"),
 *
 *     @OA\Property(
 *         property="user_roles",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/UserRoleItem")
 *     )
 * )
 */
class UserItem {}
