<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="LanguageItem",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="group", type="string", example="auth"),
 *     @OA\Property(property="key", type="string", example="login"),
 *     @OA\Property(property="text", type="object", example={"ru":"Вход","en":"Login"})
 * )
 */
class LanguageItem {}
