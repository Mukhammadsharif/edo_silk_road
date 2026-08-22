<?php

namespace App\Services\Integration1C;

use App\Models\Integration1C\OnecOrderTypeBinding;
use App\Models\Journal\JournalTypes;
use Illuminate\Support\Collection;

/**
 * Определение типа приказа 1С для журнала EDOLINE.
 *
 * Порядок:
 *   1. Есть запись в onec_order_type_bindings → берём её.
 *      Ручная (source = manual) не пересматривается никогда.
 *      Автоматическая пересматривается, если название журнала изменилось.
 *   2. Записи нет → классифицируем по названию и сразу сохраняем.
 *
 * Благодаря шагу 2 интеграция работает на новой организации без настройки:
 * первый же подписанный приказ создаёт соответствие сам.
 * Администратор правит только то, что классификатор не разобрал.
 */
class OnecOrderTypeResolver
{
    /**
     * Признак кадрового приказа — подстрока в СОБСТВЕННОМ doc_code журнала.
     *
     * Так же его определяет сам EDOLINE: DocumentService::getHtmlDoc() проверяет
     * str_contains($journal->journal->doc_code, 'HR_ORD'), а журналы называются
     * HR_ORD_MT и подобным образом. Родитель у них при этом COMMAND (Buyruq),
     * а не отдельный раздел HR_ORD — раздела с таким кодом в базе нет вовсе.
     *
     * Проверка по doc_code принципиальна: заявления APP_MT_* называются так же
     * («Mehnat ta'tili»), и по одному названию приказ от заявления не отличить.
     * Без этого фильтра в 1С уехали бы заявления вместо приказов.
     */
    public const HR_ORDER_CODE_MARKER = 'HR_ORD';

    /** Раздел-родитель с таким кодом, если организация его завела. */
    public const HR_PARENT_DOC_CODE = 'HR_ORD';

    /**
     * Корневые разделы, внутри которых лежат ПРИКАЗЫ.
     * COMMAND — «Buyruq» (приказ), присутствует во всех организациях.
     */
    public const ORDER_ROOT_CODES = ['COMMAND'];

    /**
     * Корневые разделы, которые надо ИСКЛЮЧИТЬ.
     *
     * APP — «Ariza» (заявление). Критично: журналы заявлений называются
     * ровно так же, как приказы («Mehnat ta'tili»), и по названию их
     * не различить. Без этого исключения в 1С уехали бы заявления
     * вместо приказов — сотрудникам начислялись бы отпуска по заявлениям.
     */
    public const EXCLUDED_ROOT_CODES = ['APP'];

    /** Кэш на время запроса: journal_type_id → binding|null */
    private array $cache = [];

    public function __construct(
        private OnecOrderTypeClassifier $classifier,
        private OnecLogger $logger
    ) {}

    /**
     * Тип приказа для журнала.
     *
     * @return array{order_type: string, subtype: array}|null
     *         null — журнал не кадровый, не распознан или исключён вручную
     */
    public function resolve(?JournalTypes $journalType): ?array
    {
        if (!$journalType) {
            return null;
        }

        $binding = $this->binding($journalType);

        if (!$binding || !$binding->isUsable()) {
            return null;
        }

        return [
            'order_type' => $binding->order_type,
            'subtype'    => $binding->subtype ?? [],
        ];
    }

    /**
     * Запись соответствия для журнала: из БД либо созданная классификатором.
     * Возвращает null, если журнал вообще не относится к кадровым приказам.
     */
    public function binding(JournalTypes $journalType): ?OnecOrderTypeBinding
    {
        $id = (int) $journalType->id;

        if (array_key_exists($id, $this->cache)) {
            return $this->cache[$id];
        }

        $binding = OnecOrderTypeBinding::where('journal_type_id', $id)->first();

        if ($binding) {
            // Название журнала переименовали — пересматриваем автоопределение.
            // Ручную правку не трогаем: человек знает лучше классификатора.
            if ($binding->source === OnecOrderTypeBinding::SOURCE_AUTO
                && $binding->title !== $journalType->title) {
                $binding = $this->reclassify($binding, $journalType);
            }

            return $this->cache[$id] = $binding;
        }

        if (!$this->isHrOrderJournal($journalType)) {
            return $this->cache[$id] = null;
        }

        return $this->cache[$id] = $this->createAuto($journalType);
    }

    /**
     * Журнал относится к кадровым приказам?
     * Признак — родитель с doc_code = HR_ORD (так же определяет DataController).
     */
    public function isHrOrderJournal(JournalTypes $journalType): bool
    {
        $docCode = (string) $journalType->doc_code;

        // Сам раздел — не приказ
        if ($docCode === self::HR_PARENT_DOC_CODE
            || in_array($docCode, self::ORDER_ROOT_CODES, true)) {
            return false;
        }

        // Явный маркер в собственном коде (HR_ORD_MT и подобные)
        if (str_contains($docCode, self::HR_ORDER_CODE_MARKER)) {
            return true;
        }

        // Иначе смотрим, в каком разделе журнал лежит.
        // Коды журналов генерируются случайно (PIGL, ZYPQ, ICNK…),
        // поэтому опознать приказ можно только по дереву разделов.
        $root = $this->rootDocCode($journalType);

        if ($root === null || in_array($root, self::EXCLUDED_ROOT_CODES, true)) {
            return false;
        }

        return in_array($root, self::ORDER_ROOT_CODES, true)
            || str_starts_with($root, self::HR_PARENT_DOC_CODE);
    }

    /**
     * doc_code корневого раздела журнала.
     *
     * Дерево бывает трёхуровневым: COMMAND → «HR buyruqlari» → сам журнал приказа.
     * Ограничение по глубине защищает от зацикливания на битых parent_id.
     */
    private function rootDocCode(JournalTypes $journalType): ?string
    {
        $current = $journalType;

        for ($depth = 0; $depth < 10; $depth++) {
            $parentId = (int) $current->parent_id;

            if ($parentId === 0) {
                return (string) $current->doc_code;
            }

            $parent = JournalTypes::find($parentId);

            if (!$parent || (int) $parent->id === (int) $current->id) {
                return (string) $current->doc_code;
            }

            $current = $parent;
        }

        return null;
    }

    /**
     * Все кадровые журналы всех организаций (или одной).
     *
     * $parentDocCode позволяет указать другой код родителя: HR_ORD заводит
     * администратор в кабинете, и в отдельной организации он может называться иначе.
     */
    public function hrOrderJournals(?int $orgId = null, ?string $parentDocCode = null): Collection
    {
        // Явно указанный раздел — берём всё его поддерево
        if ($parentDocCode) {
            $rootIds = JournalTypes::where('doc_code', $parentDocCode)->pluck('id')->all();

            return $this->descendantsOf($rootIds, $orgId);
        }

        $all = JournalTypes::where('status', 1)
            ->get(['id', 'parent_id', 'org_id', 'doc_code', 'title', 'status']);

        // id → запись, чтобы обходить дерево в памяти, а не запросами
        $byId = $all->keyBy('id');

        return $all
            ->filter(function ($journal) use ($byId, $orgId) {
                if ((int) $journal->parent_id === 0) {
                    return false; // раздел, а не журнал
                }

                if ($orgId && (int) $journal->org_id !== $orgId) {
                    return false;
                }

                $docCode = (string) $journal->doc_code;

                if (str_contains($docCode, self::HR_ORDER_CODE_MARKER)) {
                    return true;
                }

                $root = $this->rootCodeFromMap($journal, $byId);

                if ($root === null || in_array($root, self::EXCLUDED_ROOT_CODES, true)) {
                    return false;
                }

                return in_array($root, self::ORDER_ROOT_CODES, true)
                    || str_starts_with($root, self::HR_PARENT_DOC_CODE);
            })
            ->sortBy([['org_id', 'asc'], ['id', 'asc']])
            ->values();
    }

    /** Корневой doc_code по карте id → запись (без обращений к БД). */
    private function rootCodeFromMap($journal, $byId): ?string
    {
        $current = $journal;

        for ($depth = 0; $depth < 10; $depth++) {
            $parentId = (int) $current->parent_id;

            if ($parentId === 0) {
                return (string) $current->doc_code;
            }

            $parent = $byId->get($parentId);

            if (!$parent || (int) $parent->id === (int) $current->id) {
                return (string) $current->doc_code;
            }

            $current = $parent;
        }

        return null;
    }

    /** Всё поддерево указанных разделов, на любую глубину. */
    private function descendantsOf(array $rootIds, ?int $orgId): Collection
    {
        if (empty($rootIds)) {
            return collect();
        }

        $all  = JournalTypes::where('status', 1)
            ->get(['id', 'parent_id', 'org_id', 'doc_code', 'title', 'status']);
        $byId = $all->keyBy('id');

        return $all
            ->filter(function ($journal) use ($byId, $rootIds, $orgId) {
                if ((int) $journal->parent_id === 0) {
                    return false;
                }

                if ($orgId && (int) $journal->org_id !== $orgId) {
                    return false;
                }

                $current = $journal;

                for ($depth = 0; $depth < 10; $depth++) {
                    $parentId = (int) $current->parent_id;

                    if ($parentId === 0) {
                        return false;
                    }

                    if (in_array($parentId, $rootIds, true)) {
                        return true;
                    }

                    $parent = $byId->get($parentId);

                    if (!$parent) {
                        return false;
                    }

                    $current = $parent;
                }

                return false;
            })
            ->sortBy([['org_id', 'asc'], ['id', 'asc']])
            ->values();
    }

    /** Создать соответствие по результату классификации. */
    public function createAuto(JournalTypes $journalType): OnecOrderTypeBinding
    {
        $result = $this->classifier->classify(
            (string) $journalType->title,
            $journalType->doc_code
        );

        // Разделы (у журнала есть дочерние) и документы вне контракта
        // сразу помечаем «не выгружать» — иначе они висят в списке спорных
        $isSection = JournalTypes::where('parent_id', $journalType->id)->exists();
        $ignore    = ($result['ignored'] ?? false) || $isSection;

        if ($isSection && empty($result['matched_by'])) {
            $result['matched_by'] = 'раздел журналов, а не журнал приказа';
        }

        $binding = OnecOrderTypeBinding::create([
            'journal_type_id' => $journalType->id,
            'org_id'          => $journalType->org_id,
            'doc_code'        => $journalType->doc_code,
            'title'           => $journalType->title,
            'order_type'      => $result['order_type'],
            'subtype'         => $result['subtype'] ?: null,
            'source'          => OnecOrderTypeBinding::SOURCE_AUTO,
            'confidence'      => $result['confidence'],
            'matched_by'      => $result['matched_by'],
            'status'          => $ignore
                ? OnecOrderTypeBinding::STATUS_IGNORED
                : OnecOrderTypeBinding::STATUS_ACTIVE,
        ]);

        $this->logger->event('ORDER TYPE AUTO-MAPPED', [
            'journal_type_id' => $journalType->id,
            'org_id'          => $journalType->org_id,
            'title'           => $journalType->title,
            'order_type'      => $result['order_type'] ?? 'НЕ РАСПОЗНАН',
            'confidence'      => $result['confidence'],
        ], $result['order_type'] ? 'info' : 'warning');

        return $binding;
    }

    /** Переопределить автоматическое соответствие после переименования журнала. */
    private function reclassify(OnecOrderTypeBinding $binding, JournalTypes $journalType): OnecOrderTypeBinding
    {
        $result = $this->classifier->classify(
            (string) $journalType->title,
            $journalType->doc_code
        );

        $binding->update([
            'doc_code'   => $journalType->doc_code,
            'title'      => $journalType->title,
            'order_type' => $result['order_type'],
            'subtype'    => $result['subtype'] ?: null,
            'confidence' => $result['confidence'],
            'matched_by' => $result['matched_by'],
        ]);

        $this->logger->event('ORDER TYPE RECLASSIFIED', [
            'journal_type_id' => $journalType->id,
            'new_title'       => $journalType->title,
            'order_type'      => $result['order_type'] ?? 'НЕ РАСПОЗНАН',
        ]);

        return $binding->refresh();
    }

    /**
     * Ручная установка типа администратором.
     * После неё классификатор к этой записи не возвращается.
     */
    public function setManual(
        int $journalTypeId,
        ?string $orderType,
        array $subtype = [],
        string $status = OnecOrderTypeBinding::STATUS_ACTIVE
    ): OnecOrderTypeBinding {
        $journalType = JournalTypes::findOrFail($journalTypeId);

        $binding = OnecOrderTypeBinding::updateOrCreate(
            ['journal_type_id' => $journalTypeId],
            [
                'org_id'     => $journalType->org_id,
                'doc_code'   => $journalType->doc_code,
                'title'      => $journalType->title,
                'order_type' => $orderType,
                'subtype'    => $subtype ?: null,
                'source'     => OnecOrderTypeBinding::SOURCE_MANUAL,
                'confidence' => 100,
                'matched_by' => 'установлено вручную',
                'status'     => $status,
            ]
        );

        unset($this->cache[$journalTypeId]);

        $this->logger->event('ORDER TYPE SET MANUALLY', [
            'journal_type_id' => $journalTypeId,
            'order_type'      => $orderType,
            'status'          => $status,
        ]);

        return $binding;
    }

    public function resetCache(): void
    {
        $this->cache = [];
    }
}
