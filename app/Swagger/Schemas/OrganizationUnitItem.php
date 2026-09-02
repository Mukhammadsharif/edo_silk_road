<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="OrganizationUnitItem",
 *     type="object",
 *     title="Организация",
 *     description="Запись справочника organization_units со всеми полями таблицы",
 *
 *     @OA\Property(property="id", type="integer", example=16),
 *     @OA\Property(property="code", type="string", maxLength=50, example="ORG-016", description="Уникальный код организации"),
 *
 *     @OA\Property(property="name_uz", type="string", maxLength=100, example="Adliya vazirligi", description="Название на узбекской латинице (обязательное)"),
 *     @OA\Property(property="name_kr", type="string", maxLength=100, nullable=true, example="Адлия вазирлиги", description="Название на узбекской кириллице"),
 *     @OA\Property(property="name_ru", type="string", maxLength=100, nullable=true, example="Министерство юстиции"),
 *     @OA\Property(property="name_en", type="string", maxLength=100, nullable=true, example="Ministry of Justice"),
 *
 *     @OA\Property(property="tax_number", type="string", maxLength=20, nullable=true, example="201122334", description="ИНН"),
 *
 *     @OA\Property(property="owner_user_id", type="integer", example=42, description="Владелец организации, users.id. 0 — не назначен"),
 *     @OA\Property(property="parent_id", type="integer", example=0, description="Родительская организация. 0 — корневая"),
 *     @OA\Property(property="level", type="integer", example=1, description="Уровень вложенности, начиная с 1"),
 *     @OA\Property(property="status", type="integer", example=1, description="1 — активна, иначе отключена"),
 *
 *     @OA\Property(
 *         property="meta_data",
 *         type="object",
 *         nullable=true,
 *         description="Произвольные дополнительные данные организации (jsonb)",
 *         example={"address": "Toshkent sh.", "phone": "+998712000000"}
 *     ),
 *
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true, example="2026-01-15T09:30:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, example="2026-08-27T14:05:00.000000Z"),
 *
 *     @OA\Property(
 *         property="parent",
 *         nullable=true,
 *         description="Родительская организация. Приходит только при with_parent=true",
 *         ref="#/components/schemas/OrganizationUnitParentItem"
 *     )
 * )
 */
class OrganizationUnitItem {}
