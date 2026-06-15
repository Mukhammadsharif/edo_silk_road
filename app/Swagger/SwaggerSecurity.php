<?php


namespace App\Swagger;

/**
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     in="header",
 *     name="Authorization"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="onec_token",
 *     type="apiKey",
 *     in="header",
 *     name="X-1C-Token",
 *     description="Статический токен интеграции 1С (из ONEC_INBOUND_TOKENS). Вводится в Authorize."
 * )
 */
class SwaggerSecurity
{
    // Пустой класс, только для аннотаций
}
