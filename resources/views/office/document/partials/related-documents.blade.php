@php
    $rdvLoc = session()->get('locale', app()->getLocale());
    $rdvT = [
        'uz' => ['title'=>'Bogʻliq hujjatlar', 'chain'=>'zanjir orqali'],
        'ru' => ['title'=>'Связанные документы', 'chain'=>'по цепочке'],
        'en' => ['title'=>'Related documents', 'chain'=>'via chain'],
    ];
    $RDV = $rdvT[$rdvLoc] ?? $rdvT['uz'];

    $rdService = new \App\Services\Documents\DocumentRelationService();
    $rdChain = isset($journal) ? $rdService->chain($journal->id) : collect();

    // Прямые связи — чтобы отличать их от транзитивных (по цепочке).
    $directIds = [];
    if (isset($journal)) {
        $directIds = \App\Models\Document\DocumentRelation::where('status', 1)
            ->where(function ($q) use ($journal) {
                $q->where('j_id', $journal->id)->orWhere('related_j_id', $journal->id);
            })
            ->get()
            ->flatMap(fn ($r) => [(int) $r->j_id, (int) $r->related_j_id])
            ->unique()->reject(fn ($id) => $id === (int) $journal->id)->values()->all();
    }
@endphp

@if($rdChain->count())
    <div class="related-docs-view">
        <div class="related-docs-view-head">
            <i data-lucide="link"></i>
            <span>{{ $RDV['title'] }}</span>
            <b>{{ $rdChain->count() }}</b>
        </div>
        <ul class="related-docs-view-list">
            @foreach($rdChain as $rel)
                <li>
                    <i data-lucide="file-text"></i>
                    <a href="{{ $rdService->viewUrl($rel) }}" target="_blank">{{ $rdService->label($rel) }}</a>
                    @if(!in_array((int) $rel->id, $directIds, true))
                        <span class="related-chain-tag">{{ $RDV['chain'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    <style>
        .related-docs-view { margin: 1.25rem 0; padding: 1rem 1.25rem; border: 1px solid #e6ddff; border-radius: 14px; background: #faf8ff; }
        .related-docs-view-head { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #5b34d6; margin-bottom: 12px; }
        .related-docs-view-head i { width: 18px; height: 18px; }
        .related-docs-view-head b { background: #efeaff; color: #6b40e6; border-radius: 999px; padding: 1px 9px; font-size: .78rem; }
        .related-docs-view-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
        .related-docs-view-list li { display: flex; align-items: center; gap: 9px; padding: 9px 12px; background: #fff; border: 1px solid #ece5ff; border-radius: 10px; }
        .related-docs-view-list li > i { width: 17px; height: 17px; color: #794DFD; flex: 0 0 auto; }
        .related-docs-view-list a { flex: 1 1 auto; color: #3a2f66; font-weight: 600; font-size: .88rem; text-decoration: none; }
        .related-docs-view-list a:hover { text-decoration: underline; }
        .related-chain-tag { font-size: .72rem; color: #9b8bd0; background: #f2ecff; border-radius: 999px; padding: 2px 9px; flex: 0 0 auto; }
    </style>
@endif
