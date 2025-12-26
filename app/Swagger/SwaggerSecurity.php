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
 */
class SwaggerSecurity
{
    // Пустой класс, только для аннотаций
}
