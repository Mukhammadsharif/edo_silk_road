<?php

namespace App\Console\Commands;

use App\Models\Admin\StatusList;
use App\Models\Document\DocumentJournals;
use App\Services\Integration1C\OnecOrderCollector;
use App\Services\Integration1C\OnecOrderTypeResolver;
use Illuminate\Console\Command;

/**
 * Первичная загрузка: прогнать уже подписанные кадровые приказы через сборщик,
 * чтобы 1С могла забрать историю, а не только новые приказы.
 *
 *   php artisan onec:backfill-orders --dry-run
 *   php artisan onec:backfill-orders --from=2025-01-01
 *   php artisan onec:backfill-orders --org=3 --from=2025-01-01
 */
class OnecBackfillOrders extends Command
{
    protected $signature = 'onec:backfill-orders
        {--from= : Приказы, зарегистрированные с этой даты (Y-m-d)}
        {--org= : Ограничить одной организацией (document_journals.org_unit_id)}
        {--dry-run : Показать, что будет собрано, без записи}
        {--allow-unsigned : Захватить и документы с неподписанной резолюцией — только для отладки}';

    protected $description = 'Собрать структурированные данные подписанных кадровых приказов для выгрузки в 1С';

    /**
     * Статусы состоявшегося кадрового приказа.
     *
     * 9 — резолюция подписана: в этот момент формируется PDF приказа
     *     (GenerateHrOrderPdfJob). Дальше идёт исполнение.
     * 10 — исполнение закрыто, 11 — исполнение обеспечено.
     *
     * Подпись резолюции дополнительно проверяет OnecOrderCollector:
     * статус 9 ставится и при возврате документа на доработку.
     */
    private const SIGNED_STATUSES = [9, 10, 11];

    public function handle(OnecOrderCollector $collector, OnecOrderTypeResolver $resolver): int
    {
        $orgId = $this->option('org') ? (int) $this->option('org') : null;

        // Журналы, распознанные как кадровые приказы (по всем организациям)
        $journalTypeIds = $resolver->hrOrderJournals($orgId)
            ->filter(fn ($jt) => $resolver->resolve($jt) !== null)
            ->pluck('id')
            ->all();

        if (empty($journalTypeIds)) {
            $this->error('Не найдено кадровых журналов с распознанным типом приказа.');
            $this->line('Запустите сначала: php artisan onec:map-orders');

            return self::FAILURE;
        }

        $this->info('Кадровых журналов в работе: ' . count($journalTypeIds));

        $statuses = self::SIGNED_STATUSES;

        if ($this->option('allow-unsigned')) {
            $this->warn('Включены документы с неподписанной резолюцией — только для отладки!');
        }

        $query = DocumentJournals::whereIn('j_type', $journalTypeIds)
            ->whereIn('status', $statuses);

        if ($from = $this->option('from')) {
            $query->whereDate('j_date', '>=', $from);
        }

        if ($orgId) {
            $query->where('org_unit_id', $orgId);
        }

        $total = $query->count();

        // Всегда показываем расклад по статусам: иначе непонятно,
        // почему приказов найдено мало — их правда нет или фильтр не тот
        $this->showStatusBreakdown($journalTypeIds, $orgId, $total, $statuses);

        if ($total === 0) {
            $this->warn('Подходящих приказов не найдено.');

            return self::SUCCESS;
        }

        $this->info('К выгрузке готово (статусы ' . implode('/', $statuses) . "): {$total}");

        if ($this->option('dry-run')) {
            $this->warn('Режим --dry-run: запись не производится.');
        }

        $bar        = $this->output->createProgressBar($total);
        $captured   = 0;
        $skipped    = 0;
        $incomplete = 0;

        $query->orderBy('id')->chunkById(200, function ($chunk) use (
            $collector, $bar, &$captured, &$skipped, &$incomplete
        ) {
            foreach ($chunk as $journal) {
                if ($this->option('dry-run')) {
                    $captured++;
                    $bar->advance();
                    continue;
                }

                $payload = $collector->capture(
                    $journal->id,
                    [],
                    null,
                    (bool) $this->option('allow-unsigned')
                );

                if (!$payload) {
                    $skipped++;
                } else {
                    $captured++;

                    if (!empty($payload->missing_fields)) {
                        $incomplete++;
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Собрано: {$captured}");

        if ($skipped) {
            $this->warn("Пропущено: {$skipped}");
            $this->line('Обычно это документы, возвращённые на доработку:');
            $this->line('статус 9 ставится и при возврате, но резолюция там не подписана.');
        }

        if ($incomplete) {
            $this->warn("С неполными данными: {$incomplete}");
            $this->line('Список недостающих полей — это и есть план доработки формы приказа:');
            $this->line('  SELECT order_type, missing_fields, COUNT(*) FROM onec_order_payloads');
            $this->line('  WHERE jsonb_array_length(missing_fields) > 0 GROUP BY 1, 2 ORDER BY 3 DESC;');
        }

        return self::SUCCESS;
    }

    /**
     * Сколько документов лежит в кадровых журналах и в каких они статусах.
     *
     * Без этой картины непонятно, почему выгружается мало: приказов
     * действительно нет, они все черновики, или подписанные имеют
     * не тот статус, который мы считаем финальным.
     */
    private function showStatusBreakdown(array $journalTypeIds, ?int $orgId, int $ready, array $statuses): void
    {
        $counts = DocumentJournals::whereIn('j_type', $journalTypeIds)
            ->when($orgId, fn ($q) => $q->where('org_unit_id', $orgId))
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('cnt', 'status');

        $totalDocs = $counts->sum();

        $this->newLine();
        $this->info("Всего документов в кадровых журналах: {$totalDocs}");

        if ($totalDocs === 0) {
            $this->warn('В журналах приказов нет ни одного документа.');
            $this->line('Журналы заведены, но приказы через них ещё не проводились.');

            return;
        }

        $names = StatusList::where('type', 'work_document')->pluck('title', 'code');
        $rows  = [];

        foreach ($counts as $status => $cnt) {
            $isSigned = in_array((int) $status, $statuses, true);

            $rows[] = [
                $status,
                mb_substr((string) ($names[$status] ?? '—'), 0, 30),
                $cnt,
                $isSigned ? 'выгружается' : '',
            ];
        }

        $this->table(['Статус', 'Название', 'Кол-во', ''], $rows);

        if ($ready < $totalDocs) {
            $skipped = $totalDocs - $ready;
            $this->newLine();
            $this->line("Не выгружается {$skipped}: документы ещё не дошли до резолюции.");
            $this->line('Выгружаются статусы 9 (резолюция подписана), 10 и 11.');
        }
    }
}
