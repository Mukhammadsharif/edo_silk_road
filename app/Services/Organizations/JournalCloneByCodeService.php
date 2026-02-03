<?php

namespace App\Services\Organizations;

use App\Models\Journal\JournalTypes;
use App\Models\Journal\JournalExecutionSteps;
use App\Models\Journal\JournalExecutionStepRoles;
use App\Models\Journal\JournalTypeControls;
use App\Models\Document\DocumentOrganizations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalCloneByCodeService
{
    public function cloneByCodes(
        int $fromOrgId,
        int $toOrgId,
        array $codes
    ): void {
        Log::info('[JournalClone] START', compact('fromOrgId', 'toOrgId', 'codes'));

        DB::transaction(function () use ($fromOrgId, $toOrgId, $codes) {

            $typeMap = [];

            $types = JournalTypes::where('org_id', $fromOrgId)
                ->whereIn('doc_code', $codes)
                ->orderBy('id')
                ->get();

            foreach ($types as $type) {
                $newType = $type->replicate();
                $newType->org_id = $toOrgId;
                $newType->parent_id = 0;
                $newType->save();

                $typeMap[$type->id] = $newType->id;

                Log::info('[JournalClone] JournalType cloned (pass1)', [
                    'old' => $type->id,
                    'new' => $newType->id,
                ]);
            }

            if (empty($typeMap)) {
                Log::warning('[JournalClone] STOP: no JournalTypes');
                return;
            }

            foreach ($types as $type) {
                if ($type->parent_id && isset($typeMap[$type->parent_id])) {
                    JournalTypes::where('id', $typeMap[$type->id])
                        ->update([
                            'parent_id' => $typeMap[$type->parent_id],
                        ]);

                    Log::info('[JournalClone] parent_id restored', [
                        'child_old' => $type->id,
                        'child_new' => $typeMap[$type->id],
                        'parent_old' => $type->parent_id,
                        'parent_new' => $typeMap[$type->parent_id],
                    ]);
                }
            }


            $stepMap = [];

            $steps = JournalExecutionSteps::whereIn(
                'journal_type_id',
                array_keys($typeMap)
            )->get();

            foreach ($steps as $step) {
                $newStep = $step->replicate();
                $newStep->journal_type_id = $typeMap[$step->journal_type_id];
                $newStep->save();

                $stepMap[$step->id] = $newStep->id;
            }

            /* =====================================================
             * 3. JournalExecutionStepRoles
             * ===================================================== */
            if (!empty($stepMap)) {
                $roles = JournalExecutionStepRoles::whereIn(
                    'j_exec_step_id',
                    array_keys($stepMap)
                )->get();

                foreach ($roles as $role) {
                    $newRole = $role->replicate();
                    $newRole->j_exec_step_id = $stepMap[$role->j_exec_step_id];
                    $newRole->save();
                }
            }

            /* =====================================================
             * 4. JournalTypeControls
             * ===================================================== */
            $controls = JournalTypeControls::whereIn(
                'journal_type_id',
                array_keys($typeMap)
            )->get();

            foreach ($controls as $control) {
                $newControl = $control->replicate();
                $newControl->journal_type_id = $typeMap[$control->journal_type_id];
                $newControl->save();
            }

            /* =====================================================
             * 5. DocumentOrganizations
             * for_doc_type = doc_code (STRING!)
             * ===================================================== */
            $orgs = DocumentOrganizations::whereIn(
                'for_doc_type',
                array_keys($typeMap) // 🔥 старые journal_types.id
            )->get();

            Log::info('[JournalClone] DocumentOrganizations found', [
                'count' => $orgs->count(),
            ]);

            foreach ($orgs as $org) {
                $newOrg = $org->replicate();

                // 🔥 перепривязываем на НОВЫЙ journal_type_id
                $newOrg->for_doc_type = $typeMap[$org->for_doc_type];

                $newOrg->save();

                Log::info('[JournalClone] DocumentOrganization cloned', [
                    'old_id' => $org->id,
                    'new_id' => $newOrg->id,
                    'old_type' => $org->for_doc_type,
                    'new_type' => $newOrg->for_doc_type,
                ]);
            }
        });

        Log::info('[JournalClone] FINISH');
    }
}
