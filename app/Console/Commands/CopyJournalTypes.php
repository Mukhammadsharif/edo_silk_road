<?php

namespace App\Console\Commands;

use App\Models\Admin\OrganizationUnits;
use App\Models\Journal\JournalExecutionStepRoles;
use App\Models\Journal\JournalExecutionSteps;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Копирование видов документов (журналов) из одной организации в другую.
 *
 * ЧТО КОПИРУЕТСЯ
 *   journal_types                 — сами журналы, вместе с деревом разделов
 *   journal_execution_steps       — маршрут согласования журнала
 *   journal_execution_step_roles  — роли, назначенные на шаг
 *   journal_type_controls         — настройки контроля
 *
 * ЧТО НЕ КОПИРУЕТСЯ И ПОЧЕМУ
 *   document_journals и документы — копируем настройку, а не данные
 *   journal_attached_orgs         — ссылается на организации-корреспонденты
 *                                   организации-источника, в приёмнике они другие
 *   department_journals           — привязаны к подразделениям источника
 *
 * ПОЧЕМУ ЭТО ВООБЩЕ РАБОТАЕТ
 *   Роли (roles) и роли исполнения (document_execution_roles) в EDOLINE
 *   глобальные, без привязки к организации. Поэтому маршруты согласования
 *   переносятся как есть, без сопоставления идентификаторов.
 *
 * ДЕРЕВО
 *   Журналы вложены: раздел → подраздел → журнал. Копируем сверху вниз,
 *   запоминая соответствие старый id → новый, чтобы parent_id у потомков
 *   указывал на новые записи, а не на журналы организации-источника.
 *
 * ПРИМЕРЫ
 *   php artisan journals:copy 1 5 --dry-run
 *   php artisan journals:copy 1 5 --root=COMMAND
 *   php artisan journals:copy 1 5 --root=COMMAND --only=HR
 */
class CopyJournalTypes extends Command
{
    protected $signature = 'journals:copy
        {from : ID организации-источника}
        {to : ID организации-приёмника}
        {--root= : Копировать только поддерево раздела с этим doc_code (например COMMAND)}
        {--only= : Из поддерева взять только ветку, в названии которой есть эта строка}
        {--dry-run : Показать, что будет скопировано, без записи}';

    protected $description = 'Скопировать виды документов (журналы) со всеми настройками в другую организацию';

    /** Соответствие: id журнала в источнике → id созданной копии */
    private array $idMap = [];

    private int $createdJournals = 0;
    private int $createdSteps    = 0;
    private int $createdRoles    = 0;
    private int $createdControls = 0;
    private int $skipped         = 0;

    public function handle(): int
    {
        $fromOrg = (int) $this->argument('from');
        $toOrg   = (int) $this->argument('to');

        if ($fromOrg === $toOrg) {
            $this->error('Организация-источник и приёмник совпадают.');

            return self::FAILURE;
        }

        $source = OrganizationUnits::find($fromOrg);
        $target = OrganizationUnits::find($toOrg);

        if (!$source || !$target) {
            $this->error('Организация не найдена. Проверьте id.');

            return self::FAILURE;
        }

        $this->info("Источник: [{$source->id}] {$source->name_uz}");
        $this->info("Приёмник: [{$target->id}] {$target->name_uz}");
        $this->newLine();

        $journals = $this->collectJournals($fromOrg);

        if ($journals->isEmpty()) {
            $this->error('В организации-источнике не найдено журналов по заданным условиям.');

            return self::FAILURE;
        }

        $this->showPlan($journals, $toOrg);

        if ($this->option('dry-run')) {
            $this->warn('Режим --dry-run: ничего не записано.');

            return self::SUCCESS;
        }

        if (!$this->confirm('Копировать?', false)) {
            $this->line('Отменено.');

            return self::SUCCESS;
        }

        // Транзакция обязательна: при обрыве на середине останется
        // дерево с битыми parent_id, разбирать которое вручную тяжело
        DB::transaction(function () use ($journals, $toOrg) {
            foreach ($journals as $journal) {
                $this->copyJournal($journal, $toOrg);
            }
        });

        $this->newLine();
        $this->info("Журналов создано:     {$this->createdJournals}");
        $this->info("Шагов согласования:   {$this->createdSteps}");
        $this->info("Ролей на шагах:       {$this->createdRoles}");
        $this->info("Настроек контроля:    {$this->createdControls}");

        if ($this->skipped) {
            $this->warn("Пропущено (уже есть): {$this->skipped}");
        }

        $this->newLine();
        $this->line('Проверьте результат:');
        $this->line("  SELECT id, parent_id, doc_code, title FROM journal_types WHERE org_id = {$toOrg} ORDER BY id;");

        return self::SUCCESS;
    }

    /**
     * Журналы источника в порядке от корня к листьям.
     *
     * Порядок критичен: родитель должен быть скопирован раньше потомка,
     * иначе его новый id ещё неизвестен и parent_id проставить не из чего.
     */
    private function collectJournals(int $fromOrg)
    {
        $all = JournalTypes::where('org_id', $fromOrg)
            ->where('status', 1)
            ->get()
            ->keyBy('id');

        // Ограничение поддеревом раздела
        if ($rootCode = $this->option('root')) {
            $root = $all->firstWhere('doc_code', $rootCode);

            if (!$root) {
                $this->error("Раздел с doc_code = {$rootCode} не найден в организации {$fromOrg}.");

                return collect();
            }

            $all = $this->subtree($all, [(int) $root->id])->put($root->id, $root);
        }

        // Ограничение веткой по названию (например «HR»)
        if ($only = $this->option('only')) {
            $branches = $all->filter(fn ($j) => Str::contains(
                mb_strtolower((string) $j->title),
                mb_strtolower($only)
            ));

            if ($branches->isEmpty()) {
                $this->error("Ветка с «{$only}» в названии не найдена.");

                return collect();
            }

            $kept = $this->subtree($all, $branches->pluck('id')->map(fn ($i) => (int) $i)->all());

            foreach ($branches as $b) {
                $kept->put($b->id, $b);
            }

            // Родителей веток тоже надо взять — иначе некуда вешать копию
            foreach ($branches as $b) {
                $parentId = (int) $b->parent_id;

                while ($parentId !== 0 && $all->has($parentId)) {
                    $parent = $all->get($parentId);
                    $kept->put($parent->id, $parent);
                    $parentId = (int) $parent->parent_id;
                }
            }

            $all = $kept;
        }

        // Сортировка по уровню вложенности: родители раньше потомков
        return $all->sortBy([
            fn ($a, $b) => $this->depth($a, $all) <=> $this->depth($b, $all),
        ])->values();
    }

    /** Все потомки указанных журналов, на любую глубину. */
    private function subtree($all, array $parentIds)
    {
        $result = collect();
        $queue  = $parentIds;

        while ($queue) {
            $current  = array_shift($queue);
            $children = $all->where('parent_id', $current);

            foreach ($children as $child) {
                if ($result->has($child->id)) {
                    continue;
                }

                $result->put($child->id, $child);
                $queue[] = (int) $child->id;
            }
        }

        return $result;
    }

    /** Глубина журнала в дереве — для сортировки. */
    private function depth($journal, $all): int
    {
        $depth    = 0;
        $parentId = (int) $journal->parent_id;

        while ($parentId !== 0 && $all->has($parentId) && $depth < 10) {
            $parentId = (int) $all->get($parentId)->parent_id;
            $depth++;
        }

        return $depth;
    }

    private function showPlan($journals, int $toOrg): void
    {
        $rows = [];

        foreach ($journals as $j) {
            $exists = JournalTypes::where('org_id', $toOrg)
                ->where('title', $j->title)
                ->where('parent_id', '!=', 0)
                ->exists();

            $steps = JournalExecutionSteps::where('journal_type_id', $j->id)->count();

            $rows[] = [
                $j->id,
                $j->parent_id,
                mb_substr((string) $j->title, 0, 40),
                $j->doc_code,
                $steps,
                $exists ? 'уже есть' : '',
            ];
        }

        $this->table(['ID', 'Родитель', 'Название', 'doc_code', 'Шагов', ''], $rows);
        $this->info('Всего к копированию: ' . count($rows));
    }

    private function copyJournal(JournalTypes $source, int $toOrg): void
    {
        $parentId = 0;

        if ((int) $source->parent_id !== 0) {
            // Родитель уже скопирован на предыдущей итерации
            $parentId = $this->idMap[$source->parent_id] ?? null;

            if ($parentId === null) {
                // Родителя нет в выборке — ищем такой же раздел в приёмнике
                $sourceParent = JournalTypes::find($source->parent_id);

                $parentId = $sourceParent
                    ? (int) JournalTypes::where('org_id', $toOrg)
                        ->where('doc_code', $sourceParent->doc_code)
                        ->value('id')
                    : 0;

                if (!$parentId) {
                    $this->warn("Пропущен «{$source->title}»: не найден раздел-родитель в приёмнике");
                    $this->skipped++;

                    return;
                }
            }
        }

        // Повторный запуск не должен плодить дубли
        $existing = JournalTypes::where('org_id', $toOrg)
            ->where('parent_id', $parentId)
            ->where('title', $source->title)
            ->first();

        if ($existing) {
            $this->idMap[$source->id] = (int) $existing->id;
            $this->skipped++;

            return;
        }

        $copy = JournalTypes::create([
            'parent_id'    => $parentId,
            'org_id'       => $toOrg,
            'title'        => $source->title,
            'lang_id'      => $source->lang_id,
            // doc_code уникален в пределах организации и генерируется случайно
            // при создании журнала. Системные коды (BASE_*, MESS, COMMAND)
            // переносим как есть — на них завязана логика приложения.
            'doc_code'     => $this->docCodeFor($source, $toOrg),
            'order_number' => $source->order_number,
            'level_number' => $source->level_number,
            'status'       => 1,
        ]);

        $this->idMap[$source->id] = (int) $copy->id;
        $this->createdJournals++;

        $this->copySteps($source->id, (int) $copy->id);
        $this->copyControls($source->id, (int) $copy->id);
    }

    /**
     * Код для копии.
     *
     * Системные коды (их знает код приложения) сохраняем. Обычные —
     * генерируем заново, потому что старый уже занят в другой организации,
     * а поиск журналов местами идёт по doc_code без учёта org_id.
     */
    private function docCodeFor(JournalTypes $source, int $toOrg): string
    {
        $systemPrefixes = ['BASE_', 'MESS', 'APP', 'COMMAND', 'ORDER_F', 'REPORT',
            'STATEMENT', 'NOTIFICATION', 'OTHERS', 'HR_ORD'];

        foreach ($systemPrefixes as $prefix) {
            if (str_starts_with((string) $source->doc_code, $prefix)) {
                // Если такой код в приёмнике уже есть — оставляем его же,
                // дубль на уровне разных организаций допустим
                return $source->doc_code;
            }
        }

        do {
            $code = strtoupper(Str::random(4));
        } while (JournalTypes::where('doc_code', $code)->exists());

        return $code;
    }

    /** Маршрут согласования: шаги и назначенные на них роли. */
    private function copySteps(int $sourceId, int $newId): void
    {
        $steps = JournalExecutionSteps::where('journal_type_id', $sourceId)->get();

        foreach ($steps as $step) {
            $newStep = JournalExecutionSteps::create([
                'journal_type_id' => $newId,
                // exec_role_id ссылается на document_execution_roles —
                // справочник глобальный, сопоставление не требуется
                'exec_role_id'    => $step->exec_role_id,
                'exec_status'     => $step->exec_status,
            ]);

            $this->createdSteps++;

            $roleIds = JournalExecutionStepRoles::where('j_exec_step_id', $step->id)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                // roles тоже глобальные (Director, Dep_Head и т.д.)
                JournalExecutionStepRoles::create([
                    'j_exec_step_id' => $newStep->id,
                    'role_id'        => $roleId,
                ]);

                $this->createdRoles++;
            }
        }
    }

    private function copyControls(int $sourceId, int $newId): void
    {
        $controls = JournalTypeControls::where('journal_type_id', $sourceId)->get();

        foreach ($controls as $control) {
            JournalTypeControls::create([
                'journal_type_id' => $newId,
                'status'          => $control->status,
            ]);

            $this->createdControls++;
        }
    }
}
