<?php

namespace App\Services\Documents;

use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentRelation;
use App\Models\Document\Documents;
use Illuminate\Support\Collection;

/**
 * Связанные документы.
 *
 * Связь хранится как ребро графа (j_id <-> related_j_id). Отображение цепочки —
 * обход графа в ширину в обе стороны, поэтому связь транзитивна: если документ 1
 * связан с 2, а затем 3 связывают со 2, то в карточке документа 3 в цепочке
 * появится и документ 1 (через 2).
 */
class DocumentRelationService
{
    /**
     * Распознаёт вставленную ссылку на документ и возвращает его журнал.
     * Ссылка вида .../view-work-doc/{id}/{hash} (или inbox/sent/sign и т.п.).
     */
    public function resolveLink(string $link, ?int $orgId = null): ?DocumentJournals
    {
        $link = trim($link);
        if ($link === '') {
            return null;
        }

        $journal = null;

        // Основной случай: пара "/{id}/{hash}" (hash — буквенно-цифровой, ≥6 символов).
        if (preg_match('~/(\d+)/([A-Za-z0-9]{6,})(?:[/?#]|$)~', $link, $m)) {
            $journal = DocumentJournals::find((int) $m[1]);
            $hash = $m[2];
            if ($journal) {
                $doc = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->first();
                if (!$doc) {
                    $journal = null; // hash не совпал — ссылка недействительна
                }
            }
        }

        // Фолбэк: ссылка без hash — берём последнее число как id журнала.
        if (!$journal && preg_match('~(\d+)(?!.*\d)~', $link, $m2)) {
            $journal = DocumentJournals::find((int) $m2[1]);
        }

        if (!$journal) {
            return null;
        }

        if ($orgId && (int) $journal->org_unit_id !== (int) $orgId) {
            return null;
        }

        return $journal;
    }

    /**
     * Сохраняет связи документа со списком связанных журналов.
     * Дубликаты и самоссылка отбрасываются.
     *
     * @param  array<int|string>  $relatedIds
     */
    public function saveRelations(int $journalId, array $relatedIds, ?int $userId = null): void
    {
        foreach (array_unique($relatedIds) as $relatedId) {
            $relatedId = (int) $relatedId;
            if (!$relatedId || $relatedId === $journalId) {
                continue;
            }
            if (!DocumentJournals::whereKey($relatedId)->exists()) {
                continue;
            }

            DocumentRelation::firstOrCreate(
                ['j_id' => $journalId, 'related_j_id' => $relatedId],
                ['created_by' => $userId, 'status' => 1]
            );
        }
    }

    /**
     * Полная цепочка связанных документов (транзитивно, в обе стороны),
     * исключая сам документ. Возвращает коллекцию DocumentJournals.
     */
    public function chain(int $journalId): Collection
    {
        $visited = [$journalId => true];
        $queue = [$journalId];

        while ($queue) {
            $current = array_shift($queue);

            $neighbours = DocumentRelation::where('status', 1)
                ->where(function ($q) use ($current) {
                    $q->where('j_id', $current)->orWhere('related_j_id', $current);
                })
                ->get();

            foreach ($neighbours as $rel) {
                foreach ([(int) $rel->j_id, (int) $rel->related_j_id] as $node) {
                    if (!isset($visited[$node])) {
                        $visited[$node] = true;
                        $queue[] = $node;
                    }
                }
            }
        }

        unset($visited[$journalId]);
        $ids = array_keys($visited);

        if (!$ids) {
            return collect();
        }

        return DocumentJournals::whereIn('id', $ids)
            ->whereNotIn('status', [4]) // без удалённых
            ->get()
            ->sortByDesc('id')
            ->values();
    }

    /**
     * Ссылка на страницу просмотра документа.
     */
    public function viewUrl(DocumentJournals $journal): string
    {
        $hash = $journal->doc?->doc_hash ?? '';
        $code = $journal->journal->doc_code ?? '';

        $route = match ($code) {
            'BASE_OUTGOING' => 'office.view-sent-doc',
            'BASE_INCOMING' => 'office.view-inbox-doc',
            default         => 'office.view-work-doc',
        };

        return route($route, [$journal->id, $hash]);
    }

    /**
     * Человеко-читаемая подпись документа для цепочки.
     */
    public function label(DocumentJournals $journal): string
    {
        $doc = $journal->doc;
        $name = $doc?->doc_name ?: ($journal->journal->title ?? '');
        $number = $doc?->doc_number ? ('№ ' . $doc->doc_number . ' — ') : '';

        return trim($number . $name) ?: ('#' . $journal->id);
    }
}
