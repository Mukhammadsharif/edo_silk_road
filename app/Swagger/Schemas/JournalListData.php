<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="JournalListData",
 *     type="object",
 *
 *     @OA\Property(
 *         property="execution_journal_types",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/JournalTypeItem")
 *     ),
 *
 *     @OA\Property(
 *         property="sign_journal_types",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/JournalTypeItem")
 *     ),
 *
 *     @OA\Property(
 *         property="approval_journal_types",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/JournalTypeItem")
 *     ),
 *
 *     @OA\Property(
 *         property="confirm_journal_types",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/JournalTypeItem")
 *     ),
 *
 *     @OA\Property(
 *         property="control_journal_types",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/JournalTypeItem")
 *     )
 * )
 */
class JournalListData {}
