@php
    $rdLoc = session()->get('locale', app()->getLocale());
    $rdT = [
        'uz' => ['title'=>'Bogʻliq hujjatlar','hint'=>'Tayyor hujjat havolasini qoʻshing','ph'=>'Hujjat havolasini joylashtiring...','add'=>'Qoʻshish','empty'=>'Bogʻliq hujjatlar yoʻq','bad'=>'Havola boʻyicha hujjat topilmadi','dup'=>'Bu hujjat allaqachon qoʻshilgan'],
        'ru' => ['title'=>'Связанные документы','hint'=>'Вставьте ссылку на готовый документ','ph'=>'Вставьте ссылку на документ...','add'=>'Добавить','empty'=>'Связанных документов нет','bad'=>'Документ по ссылке не найден','dup'=>'Этот документ уже добавлен'],
        'en' => ['title'=>'Related documents','hint'=>'Paste a link to an existing document','ph'=>'Paste a document link...','add'=>'Add','empty'=>'No related documents','bad'=>'No document found for this link','dup'=>'This document is already added'],
    ];
    $RD = $rdT[$rdLoc] ?? $rdT['uz'];
    // Предзаполнение при редактировании (передаётся из вью как $relatedPreset — массив [id,label,url]).
    $relatedPreset = $relatedPreset ?? [];
@endphp

<div class="form-section related-docs-section">
    <div class="section-header">
        <i data-lucide="link"></i>
        <h3>{{ $RD['title'] }}</h3>
    </div>

    <p class="related-docs-hint">{{ $RD['hint'] }}</p>

    <div class="related-docs-input">
        <input type="text" id="relatedDocLink" class="form-control-modern" placeholder="{{ $RD['ph'] }}">
        <button type="button" id="relatedDocAdd" class="btn-modern btn-primary" style="width:auto;">
            <i data-lucide="plus"></i> {{ $RD['add'] }}
        </button>
    </div>
    <p class="related-docs-error" id="relatedDocError" style="display:none;"></p>

    <div class="related-docs-list" id="relatedDocsList">
        @forelse($relatedPreset as $item)
            <div class="related-doc-chip" data-id="{{ $item['id'] }}">
                <input type="hidden" name="related_docs[]" value="{{ $item['id'] }}">
                <i data-lucide="file-text"></i>
                <a href="{{ $item['url'] }}" target="_blank" class="related-doc-name">{{ $item['label'] }}</a>
                <button type="button" class="related-doc-remove" title="×"><i data-lucide="x"></i></button>
            </div>
        @empty
        @endforelse
    </div>
    <div class="related-docs-empty" id="relatedDocsEmpty" @if(count($relatedPreset)) style="display:none;" @endif>
        {{ $RD['empty'] }}
    </div>
</div>

<style>
    .related-docs-section { margin-top: 1rem; }
    .related-docs-hint { font-size: .82rem; color: #8a90a6; margin: 0 0 12px; }
    .related-docs-input { display: flex; gap: 10px; align-items: stretch; }
    .related-docs-input .form-control-modern { flex: 1 1 auto; }
    .related-docs-error { color: #ef4444; font-size: .82rem; margin: 8px 0 0; }
    .related-docs-list { display: flex; flex-direction: column; gap: 8px; margin-top: 14px; }
    .related-doc-chip { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1.5px solid #e6ddff; background: #f7f4ff; border-radius: 10px; }
    .related-doc-chip > i { width: 18px; height: 18px; color: #794DFD; flex: 0 0 auto; }
    .related-doc-name { flex: 1 1 auto; color: #3a2f66; font-weight: 600; font-size: .88rem; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .related-doc-name:hover { text-decoration: underline; }
    .related-doc-remove { width: 26px; height: 26px; border: none; background: transparent; color: #9b8bd0; cursor: pointer; border-radius: 7px; display: grid; place-items: center; flex: 0 0 auto; }
    .related-doc-remove:hover { background: #ece5ff; color: #794DFD; }
    .related-doc-remove i { width: 15px; height: 15px; }
    .related-docs-empty { color: #b3b8c9; font-size: .84rem; padding: 6px 0; }
</style>

<script>
    (function () {
        window.__rdLabels = @json($RD);

        function initRelatedDocs() {
            const input = document.getElementById('relatedDocLink');
            const addBtn = document.getElementById('relatedDocAdd');
            const list = document.getElementById('relatedDocsList');
            const emptyEl = document.getElementById('relatedDocsEmpty');
            const errEl = document.getElementById('relatedDocError');
            if (!input || !addBtn || !list) return;

            const L = window.__rdLabels || {};

            function refreshEmpty() {
                const has = list.querySelector('.related-doc-chip');
                if (emptyEl) emptyEl.style.display = has ? 'none' : 'block';
            }
            function showError(msg) {
                if (!errEl) return;
                errEl.textContent = msg || '';
                errEl.style.display = msg ? 'block' : 'none';
            }
            function hasId(id) {
                return !!list.querySelector('.related-doc-chip[data-id="' + id + '"]');
            }
            function addChip(doc) {
                const chip = document.createElement('div');
                chip.className = 'related-doc-chip';
                chip.setAttribute('data-id', doc.id);
                chip.innerHTML =
                    '<input type="hidden" name="related_docs[]" value="' + doc.id + '">' +
                    '<i data-lucide="file-text"></i>' +
                    '<a href="' + doc.url + '" target="_blank" class="related-doc-name"></a>' +
                    '<button type="button" class="related-doc-remove"><i data-lucide="x"></i></button>';
                chip.querySelector('.related-doc-name').textContent = doc.label;
                chip.querySelector('.related-doc-remove').addEventListener('click', function () {
                    chip.remove(); refreshEmpty();
                });
                list.appendChild(chip);
                if (window.lucide) lucide.createIcons();
                refreshEmpty();
            }

            function resolve() {
                const link = (input.value || '').trim();
                showError('');
                if (!link) return;

                const token = document.querySelector('meta[name="csrf-token"]');
                addBtn.disabled = true;

                fetch("{{ route('office.doc.resolve-related-link') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ link: link })
                })
                    .then(r => r.json())
                    .then(function (res) {
                        addBtn.disabled = false;
                        if (!res || !res.ok) { showError(L.bad || 'Not found'); return; }
                        if (hasId(res.id)) { showError(L.dup || 'Already added'); return; }
                        addChip(res);
                        input.value = '';
                    })
                    .catch(function () { addBtn.disabled = false; showError(L.bad || 'Error'); });
            }

            addBtn.addEventListener('click', resolve);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); resolve(); }
            });

            // remove-хендлеры для предзаполненных чипов
            list.querySelectorAll('.related-doc-remove').forEach(function (btn) {
                btn.addEventListener('click', function () { btn.closest('.related-doc-chip').remove(); refreshEmpty(); });
            });

            refreshEmpty();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRelatedDocs);
        } else { initRelatedDocs(); }
    })();
</script>
