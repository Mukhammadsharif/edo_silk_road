<?php

namespace App\Models;

use App\Models\Admin\UserWorkHistory;
use App\Models\Journal\JournalExecutionSteps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Цель «фиксированного маршрута» шага типа журнала (v61).
 * См. миграцию journal_execution_step_targets.
 */
class JournalExecutionStepTarget extends Model
{
    protected $table = 'journal_execution_step_targets';

    protected $fillable = ['j_exec_step_id', 'target_type', 'target_id'];

    /** Есть ли у типа хотя бы один шаг с целями = «фиксированный маршрут». */
    public static function typeHasTargets(int $journalTypeId): bool
    {
        $stepIds = JournalExecutionSteps::where('journal_type_id', $journalTypeId)->pluck('id');

        return $stepIds->isNotEmpty()
            && static::whereIn('j_exec_step_id', $stepIds)->exists();
    }

    /**
     * Резолв целей шага в СОТРУДНИКОВ организации.
     *
     * @return Collection<UserWorkHistory> активные рабочие места
     *         (to_user для document_execution_steps = work_history.id!)
     *         Пусто — если цели не резолвятся (вакантная должность,
     *         уволенный человек) — вызывающий код обязан это проверить.
     */
    public static function resolveStep(int $stepId, int $orgId): Collection
    {
        $targets = static::where('j_exec_step_id', $stepId)->get();

        if ($targets->isEmpty()) {
            return collect();
        }

        $postIds = $targets->where('target_type', 'post')->pluck('target_id');
        $userIds = $targets->where('target_type', 'user')->pluck('target_id');

        $query = UserWorkHistory::where('org_id', $orgId)
            ->where('status', 1)
            ->whereHas('user', fn ($q) => $q->where('status', 1))
            ->where(function ($q) use ($postIds, $userIds) {
                if ($postIds->isNotEmpty()) {
                    $q->whereIn('post_id', $postIds);
                }
                if ($userIds->isNotEmpty()) {
                    $postIds->isNotEmpty()
                        ? $q->orWhereIn('user_id', $userIds)
                        : $q->whereIn('user_id', $userIds);
                }
            });

        // Уникальность по человеку: должность + личное указание могут
        // дать одного и того же сотрудника дважды.
        return $query->get()->unique('id')->values();
    }
}
