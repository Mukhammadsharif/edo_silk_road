<?php

namespace App\Swagger\Schemas;

/**
 * Родительская организация в сокращённом виде.
 *
 * Отдельная схема, а не ссылка на OrganizationUnitItem: иначе получилась бы
 * рекурсия, и Swagger UI разворачивал бы дерево до бесконечности.
 *
 * @OA\Schema(
 *     schema="OrganizationUnitParentItem",
 *     type="object",
 *     title="Родительская организация",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="ORG-001"),
 *     @OA\Property(property="name_uz", type="string", example="Bosh boshqarma"),
 *     @OA\Property(property="name_ru", type="string", nullable=true, example="Главное управление"),
 *     @OA\Property(property="parent_id", type="integer", example=0),
 *     @OA\Property(property="level", type="integer", example=1),
 *     @OA\Property(property="status", type="integer", example=1)
 * )
 */
class OrganizationUnitParentItem {}
