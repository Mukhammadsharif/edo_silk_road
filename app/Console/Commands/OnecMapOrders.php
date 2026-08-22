<?php

namespace App\Console\Commands;

use App\Models\Integration1C\OnecOrderTypeBinding;
use App\Models\Journal\JournalTypes;
use App\Services\Integration1C\OnecOrderTypeClassifier;
use App\Services\Integration1C\OnecOrderTypeResolver;
use Illuminate\Console\Command;

/**
 * Разбор кадровых журналов всех организаций и автоматическая простановка
 * соответствия «журнал → тип приказа 1С».
 *
 * Запускать не обязательно: соответствие создаётся само при первом приказе.
 * Команда нужна, чтобы увидеть картину заранее и поправить спорное до того,
 * как приказы начнут уходить в 1С.
 *
 *   php artisan onec:map-orders            — показать, что определилось
 *   php artisan onec:map-orders --review   — только требующие проверки
 *   php artisan onec:map-orders --org=3    — по одной организации
 */
class OnecMapOrders extends Command
{
    protected $signature = 'onec:map-orders
        {--org= : Только одна организация (journal_types.org_id)}
        {--review : Показать только требующие проверки человеком}
        {--refresh : Пересоздать автоматические соответствия (ручные не трогает)}
        {--parent= : Взять всех детей раздела с этим doc_code}
        {--dry : Ничего не сохранять, только показать разбор названий}
        {--scan : Разведка: разобрать ВСЕ журналы и показать похожие на кадровые приказы}';

    protected $description = 'Определить соответствие кадровых журналов типам приказов 1С';

    public function handle(OnecOrderTypeResolver $resolver): int
    {
        $orgId  = $this->option('org') ? (int) $this->option('org') : null;

        if ($this->option('scan')) {
            return $this->scan($orgId);
        }

        $parent = $this->option('parent');

        $journals = $resolver->hrOrderJournals($orgId, $parent);

        if ($journals->isEmpty()) {
            return $this->explainNothingFound($parent ?: OnecOrderTypeResolver::HR_ORDER_CODE_MARKER, $orgId);
        }

        // Быстрый разбор названий без записи в БД
        if ($this->option('dry')) {
            return $this->dryRun($journals);
        }

        if ($this->option('refresh')) {
            $ids     = $journals->pluck('id');
            $deleted = OnecOrderTypeBinding::whereIn('journal_type_id', $ids)
                ->where('source', OnecOrderTypeBinding::SOURCE_AUTO)
                ->delete();

            $resolver->resetCache();
            $this->warn("Сброшено автоматических соответствий: {$deleted}");
        }

        $rows       = [];
        $review     = 0;
        $recognized = 0;

        foreach ($journals as $journal) {
            $binding = $resolver->binding($journal);

            if (!$binding) {
                continue;
            }

            $needsReview = $binding->needsReview();

            if ($needsReview) {
                $review++;
            }

            if ($binding->order_type) {
                $recognized++;
            }

            if ($this->option('review') && !$needsReview) {
                continue;
            }

            $subtype = $binding->subtype
                ? implode(', ', array_map(
                    fn ($k, $v) => "{$k}={$v}",
                    array_keys($binding->subtype),
                    $binding->subtype
                ))
                : '';

            $rows[] = [
                $journal->org_id,
                $journal->id,
                mb_substr((string) $journal->title, 0, 42),
                $binding->order_type ?? '— НЕ РАСПОЗНАН —',
                $subtype,
                $binding->confidence,
                $binding->source === OnecOrderTypeBinding::SOURCE_MANUAL ? 'вручную' : 'авто',
                $needsReview ? 'ПРОВЕРИТЬ' : '',
            ];
        }

        if ($rows) {
            $this->table(
                ['Орг', 'ID', 'Название журнала', 'Тип приказа', 'Подтип', 'Увер.', 'Источник', ''],
                $rows
            );
        }

        $total = $journals->count();

        $this->newLine();
        $this->info("Всего кадровых журналов: {$total}");
        $this->info("Распознано: {$recognized}");

        if ($review) {
            $this->warn("Требуют проверки: {$review}");
            $this->line('Причина — тип не определён либо уверенность ниже '
                . OnecOrderTypeClassifier::CONFIDENCE_THRESHOLD . '.');
            $this->line('Поправить: раздел «Интеграция 1С → Соответствие приказов» в админке');
            $this->line('или POST /admin/onec/order-mapping/update.');
        } else {
            $this->info('Спорных соответствий нет — можно выгружать.');
        }

        return self::SUCCESS;
    }

    /**
     * Журналов не найдено — объясняем почему и показываем, что есть в базе.
     * Иначе непонятно, ошибка это, пустая база или другое наименование кодов.
     */
    private function explainNothingFound(string $marker, ?int $orgId): int
    {
        $this->error("Журналов кадровых приказов не найдено (искали doc_code с «{$marker}»).");
        $this->newLine();

        $total = JournalTypes::where('parent_id', '!=', 0)->where('status', 1)->count();

        if ($total === 0) {
            $this->warn('В базе нет ни одного дочернего журнала — похоже, база пустая.');

            return self::FAILURE;
        }

        // Показываем разделы, внутри которых обычно живут приказы
        $roots = JournalTypes::where('parent_id', 0)
            ->when($orgId, fn ($q) => $q->where('org_id', $orgId))
            ->whereIn('doc_code', ['COMMAND', 'ORDER_F', 'APP'])
            ->orderBy('org_id')
            ->get(['id', 'org_id', 'doc_code', 'title']);

        if ($roots->isNotEmpty()) {
            $this->line('Разделы, в которых обычно лежат приказы:');
            $this->table(
                ['ID', 'Орг', 'doc_code', 'Название'],
                $roots->take(15)->map(fn ($r) => [
                    $r->id,
                    $r->org_id,
                    $r->doc_code,
                    mb_substr((string) $r->title, 0, 34),
                ])->all()
            );
        }

        $this->newLine();
        $this->warn('Что делать:');
        $this->line('  1. Разведка — посмотреть, какие журналы похожи на кадровые приказы:');
        $this->line('     php artisan onec:map-orders --scan');
        $this->line('  2. Если приказы лежат в разделе Buyruq (COMMAND) — взять его детей:');
        $this->line('     php artisan onec:map-orders --parent=COMMAND --dry');
        $this->line('  3. Если журналы приказов ещё не заведены — создать их в кабинете.');
        $this->newLine();
        $this->line('Важно: журналы заявлений (APP_MT_*) называются так же, как приказы');
        $this->line('(«Mehnat ta\'tili»), поэтому по названию их не различить — только по doc_code.');

        return self::FAILURE;
    }

    /**
     * Разведка: пройти по ВСЕМ дочерним журналам и показать те, что классификатор
     * опознал как кадровые. Нужна, чтобы понять, как у вас названы коды,
     * когда стандартный признак HR_ORD не находится.
     */
    private function scan(?int $orgId): int
    {
        $classifier = app(OnecOrderTypeClassifier::class);

        $journals = JournalTypes::where('parent_id', '!=', 0)
            ->where('status', 1)
            ->when($orgId, fn ($q) => $q->where('org_id', $orgId))
            ->orderBy('org_id')->orderBy('id')
            ->get();

        $this->info('Просмотрено дочерних журналов: ' . $journals->count());
        $this->newLine();

        // Группируем по префиксу кода: так сразу видно, какие семейства кодов есть
        $byPrefix = [];
        $rows     = [];

        foreach ($journals as $j) {
            $result = $classifier->classify((string) $j->title, $j->doc_code);

            if (!$result['order_type']) {
                continue;
            }

            $prefix = explode('_', (string) $j->doc_code)[0] ?: '(пусто)';
            $byPrefix[$prefix] = ($byPrefix[$prefix] ?? 0) + 1;

            $rows[] = [
                $j->org_id,
                $j->id,
                $j->doc_code,
                mb_substr((string) $j->title, 0, 32),
                $result['order_type'],
                $result['confidence'],
            ];
        }

        if (empty($rows)) {
            $this->warn('Ни один журнал не опознан как кадровый приказ.');

            return self::SUCCESS;
        }

        $this->line('Семейства кодов среди опознанных:');

        foreach ($byPrefix as $prefix => $count) {
            $hint = $prefix === 'APP' ? '  ← это ЗАЯВЛЕНИЯ, не приказы' : '';
            $this->line(sprintf('  %-14s %3d журн.%s', $prefix, $count, $hint));
        }

        $this->newLine();
        $this->table(['Орг', 'ID', 'doc_code', 'Название', 'Тип приказа', 'Увер.'], array_slice($rows, 0, 60));

        if (count($rows) > 60) {
            $this->line('… показаны первые 60 из ' . count($rows));
        }

        $this->newLine();
        $this->warn('Это только разведка, ничего не сохранено.');
        $this->line('Найдите в списке семейство кодов, которым названы именно ПРИКАЗЫ,');
        $this->line('и запустите разбор по нему: php artisan onec:map-orders --parent=КОД_РАЗДЕЛА --dry');

        return self::SUCCESS;
    }

    /** Показать разбор названий без записи соответствий в БД. */
    private function dryRun($journals): int
    {
        $classifier = app(OnecOrderTypeClassifier::class);
        $rows = [];

        foreach ($journals as $journal) {
            $r = $classifier->classify((string) $journal->title, $journal->doc_code);

            $rows[] = [
                $journal->org_id,
                $journal->id,
                mb_substr((string) $journal->title, 0, 40),
                $r['order_type'] ?? '— НЕ РАСПОЗНАН —',
                $r['subtype'] ? json_encode($r['subtype'], JSON_UNESCAPED_UNICODE) : '',
                $r['confidence'],
                mb_substr((string) $r['matched_by'], 0, 30),
            ];
        }

        $this->table(['Орг', 'ID', 'Название', 'Тип', 'Подтип', 'Увер.', 'Совпало по'], $rows);
        $this->warn('Режим --dry: соответствия не сохранены.');

        return self::SUCCESS;
    }
}
