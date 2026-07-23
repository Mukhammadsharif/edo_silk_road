<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Чинит записи трудовой истории без org_id.
 *
 * Причина: при смене должности через админку новая запись user_work_histories
 * создавалась без org_id. Из-за этого authActiveUser() не находил активную
 * запись (фильтр по org_id) и страницы падали с
 * "Attempt to read property user on null".
 *
 * Заполняем org_id:
 *   1) из отдела (departments.org_unit_id по dep_id) — основной источник;
 *   2) из самого пользователя (users.org_unit_id) — если отдел не найден.
 *
 * SQL написан коррелированными подзапросами — работает и на PostgreSQL,
 * и на MySQL (UPDATE ... JOIN между ними не портируется).
 *
 * Запуск:
 *   php artisan app:fix-work-history-org --dry-run   # только показать
 *   php artisan app:fix-work-history-org             # исправить
 */
class FixWorkHistoryOrgId extends Command
{
    protected $signature = 'app:fix-work-history-org {--dry-run : Показать, что будет исправлено, без изменений}';

    protected $description = 'Заполняет пустой org_id в user_work_histories (отдел → пользователь)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $total = $this->brokenQuery()->count();

        if ($total === 0) {
            $this->info('Записей без org_id не найдено — чинить нечего.');
            return self::SUCCESS;
        }

        $this->warn("Найдено записей без org_id: {$total}");

        $byDept = $this->brokenQuery()
            ->join('departments', 'departments.id', '=', 'user_work_histories.dep_id')
            ->whereNotNull('departments.org_unit_id')
            ->count();

        $this->line("  • можно взять из отдела (departments.org_unit_id): {$byDept}");
        $this->line('  • остальные попробуем восстановить по users.org_unit_id');

        if ($dry) {
            $this->comment('Режим --dry-run: изменения НЕ применены.');
            $this->showSamples();
            return self::SUCCESS;
        }

        if (! $this->confirm("Обновить {$total} записей?", true)) {
            $this->comment('Отменено.');
            return self::SUCCESS;
        }

        $fixedByDept = 0;
        $fixedByUser = 0;

        DB::transaction(function () use (&$fixedByDept, &$fixedByUser) {
            // 1) org_id из отдела
            $fixedByDept = DB::update("
                UPDATE user_work_histories
                   SET org_id = (
                        SELECT d.org_unit_id FROM departments d
                         WHERE d.id = user_work_histories.dep_id
                   )
                 WHERE (org_id IS NULL OR org_id = 0)
                   AND EXISTS (
                        SELECT 1 FROM departments d
                         WHERE d.id = user_work_histories.dep_id
                           AND d.org_unit_id IS NOT NULL
                   )
            ");

            // 2) остаток — org_id из пользователя
            $fixedByUser = DB::update("
                UPDATE user_work_histories
                   SET org_id = (
                        SELECT u.org_unit_id FROM users u
                         WHERE u.id = user_work_histories.user_id
                   )
                 WHERE (org_id IS NULL OR org_id = 0)
                   AND EXISTS (
                        SELECT 1 FROM users u
                         WHERE u.id = user_work_histories.user_id
                           AND u.org_unit_id IS NOT NULL
                   )
            ");
        });

        $remaining = $this->brokenQuery()->count();

        $this->info("Исправлено по отделу: {$fixedByDept}");
        $this->info("Исправлено по пользователю: {$fixedByUser}");

        if ($remaining > 0) {
            $this->warn("Осталось нерешённых записей: {$remaining} (нет ни отдела, ни org у пользователя)");
            $this->showSamples();
        } else {
            $this->info('Все записи исправлены.');
        }

        return self::SUCCESS;
    }

    /** Записи с пустым org_id (условие сгруппировано, чтобы не ломать другие where). */
    private function brokenQuery()
    {
        return DB::table('user_work_histories')->where(function ($q) {
            $q->whereNull('user_work_histories.org_id')
                ->orWhere('user_work_histories.org_id', 0);
        });
    }

    /** Показать несколько проблемных записей для диагностики. */
    private function showSamples(): void
    {
        $rows = $this->brokenQuery()
            ->select('id', 'user_id', 'org_id', 'dep_id', 'post_id', 'status')
            ->limit(10)->get();

        if ($rows->isEmpty()) {
            return;
        }

        $this->table(
            ['id', 'user_id', 'org_id', 'dep_id', 'post_id', 'status'],
            $rows->map(fn ($r) => (array) $r)->all()
        );
    }
}
