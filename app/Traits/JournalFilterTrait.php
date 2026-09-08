<?php

namespace App\Traits;

use App\Models\Journal\JournalTypes;
use Illuminate\Support\Collection;

/**
 * Двухэтапный фильтр по журналам: сначала раздел, потом вид документа.
 *
 * ЗАЧЕМ.
 * Раньше в выпадающем списке был плоский перечень листовых журналов всей
 * организации. Названия там повторяются: «Mehnat ta'tili» есть и среди
 * заявлений (Ariza), и среди приказов (Buyruq), а «Ta'til» может лежать
 * сразу в нескольких разделах. Пользователь видел два одинаковых пункта
 * и не мог понять, какой из них выбрать.
 *
 * Решение из двух частей:
 *   1. Отдельный список разделов. Выбрал раздел — во втором списке
 *      остались только его журналы.
 *   2. Пока раздел не выбран, к названию журнала спереди подставляется
 *      название его раздела: «Buyruq → Ta'til». Даже без фильтрации
 *      одинаковые пункты становятся различимы.
 *
 * Дерево бывает трёхуровневым (Buyruq → HR buyruqlari → сам журнал),
 * поэтому потомки собираются рекурсивно, а не только прямые дети.
 */
trait JournalFilterTrait
{
    /**
     * Первый уровень фильтра — верхние журналы организации (parent_id = 0).
     *
     * Сюда попадают и разделы с подтипами (Buyruq, Ariza), и самостоятельные
     * виды документов без подтипов (Bildirishnoma, Hisobot, Talabnoma).
     * Не попадают сами подтипы — они живут во втором списке.
     */
    protected function journalSections(): Collection
    {
        return JournalTypes::where('org_id', $this->organizationId())
            ->where('status', 1)
            ->where('parent_id', 0)
            ->orderBy('order_number')
            ->get();
    }

    /**
     * Листовые журналы для второго списка.
     *
     * @param  int|string|null  $sectionId  Выбранный раздел; пусто — все журналы
     * @param  array  $excludeDocCodes  Служебные коды, которые в этом разделе
     *                                  системы показывать не нужно
     * @param  array|null  $onlyIds  Дополнительное ограничение по id
     *                               (используется на странице контроля)
     */
    protected function journalLeaves(
        $sectionId = null,
        array $excludeDocCodes = [],
        ?array $onlyIds = null
    ): Collection {
        $query = JournalTypes::whereDoesntHave('childs')
            ->where('org_id', $this->organizationId())
            ->where('status', 1);

        if ($excludeDocCodes) {
            $query->whereNotIn('doc_code', $excludeDocCodes);
        }

        if ($onlyIds !== null) {
            $query->whereIn('id', $onlyIds);
        }

        if ($sectionId && $sectionId !== 'all') {
            $query->whereIn('parent_id', $this->sectionBranchIds((int) $sectionId));
        }

        return $query->with('parent')->orderBy('order_number')->get();
    }

    /**
     * Сам раздел и все вложенные в него разделы.
     *
     * Нужно потому, что дерево трёхуровневое: выбрав «Buyruq», пользователь
     * ожидает увидеть и журналы из вложенного «HR buyruqlari», а их
     * parent_id указывает не на «Buyruq», а на промежуточный узел.
     *
     * @return int[]
     */
    protected function sectionBranchIds(int $sectionId): array
    {
        $ids   = [$sectionId];
        $queue = [$sectionId];
        $depth = 0;

        // Ограничение по глубине защищает от зацикливания на битых parent_id
        while ($queue && $depth < 10) {
            $children = JournalTypes::whereIn('parent_id', $queue)
                ->where('status', 1)
                ->pluck('id')
                ->all();

            if (!$children) {
                break;
            }

            $ids   = array_merge($ids, $children);
            $queue = $children;
            $depth++;
        }

        return array_unique($ids);
    }

    /**
     * Подпись журнала в списке.
     *
     * Когда раздел не выбран, спереди идёт его название — иначе одинаковые
     * пункты из разных разделов неразличимы.
     */
    protected function journalOptionLabel(JournalTypes $journal, $sectionId = null): string
    {
        $title = $this->journalTitle($journal);

        if ($sectionId && $sectionId !== 'all') {
            return $title;
        }

        $parent = $journal->parent;

        if (!$parent) {
            return $title;
        }

        return $this->journalTitle($parent) . ' → ' . $title;
    }

    /**
     * Идентификаторы журналов, по которым фильтровать документы,
     * когда выбран только верхний уровень, без подтипа.
     *
     * Если у выбранного журнала есть подтипы — берём их все, чтобы список
     * документов совпадал с содержимым второго выпадающего списка.
     * Если подтипов нет — документы лежат в самом журнале.
     *
     * @param  Collection  $leaves  Подтипы, уже отфильтрованные под текущую страницу
     * @return int[]
     */
    protected function sectionFilterIds($sectionId, Collection $leaves): array
    {
        if ($leaves->isNotEmpty()) {
            return $leaves->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return [(int) $sectionId];
    }

    /**
     * Название журнала на языке интерфейса.
     *
     * JournalTypes::lang() при отсутствии перевода возвращает строку-заглушку
     * 'Lang', а не пустое значение — поэтому проверяем именно её, иначе
     * в списке появились бы пункты с названием «Lang».
     */
    protected function journalTitle(JournalTypes $journal): string
    {
        $translated = JournalTypes::lang($journal->lang_id);

        if (!$translated || $translated === 'Lang') {
            return (string) $journal->title;
        }

        return $translated;
    }
}
