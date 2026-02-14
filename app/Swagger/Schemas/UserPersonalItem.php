<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="UserPersonalItem",
 *     type="object",
 *
 *     @OA\Property(property="user_id", type="integer", example="1"),
 *     @OA\Property(property="doc_pin", type="string", example="12345678901234"),
 *     @OA\Property(property="doc_number", type="string", example="123456789"),
 *     @OA\Property(property="doc_type", type="integer", example="2"),
 *     @OA\Property(property="birthday", type="string", example="2025-01-15"),
 *     @OA\Property(property="gender", type="string", example="1"),
 *     @OA\Property(property="phone", type="string", example="991234567"),
 *     @OA\Property(property="f_name", type="string", example="Ivan"),
 *     @OA\Property(property="l_name", type="string", example="Ivanov"),
 *     @OA\Property(property="m_name", type="string", example="Ivanovich"),
 *     @OA\Property(property="doc_sign_type", type="integer", example="2"),
 *     @OA\Property(property="sort", type="integer", example="1"),
 * )
 */
class UserPersonalItem {}
