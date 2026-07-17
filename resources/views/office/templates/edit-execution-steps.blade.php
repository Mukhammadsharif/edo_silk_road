<div class="form-section-modern">
    @if($signatory_users->count())
        <div class="section-divider"></div>
        <div class="section-title-modern">
            <i data-lucide="pen-tool"></i>
            <span>{{ __('execution.signatory') }}</span>
        </div>
        <div class="search-box-modern">
            <i data-lucide="search"></i>
            <input type="text" class="form-control-modern border-none" id="allUserToSearch" onkeyup="searchToAllUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
        <div class="users-list-modern">
            @foreach($signatory_users as $key => $signatory_user)
                <div class="select-to-all-user">
                    <label class="user-radio-card">
                        <input type="radio" name="to_emp" value="{{ $signatory_user->id }}"
                            {{ $signer && $signer->to_user === $signatory_user->id ? 'checked' : ''}}>
                        <div class="user-card-content">
                            <div class="user-avatar">
                                <i data-lucide="user"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ $signatory_user->personal->fullName()??'-' }}</span>
                                <span class="user-position">{{ $signatory_user->userPost->lang($signatory_user->userPost->id)??'' }}</span>
                            </div>
                            <div class="radio-indicator"></div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    @endif

    @php $approval_groups = $approval_groups ?? []; @endphp
    @if($agreement_users->count() || count($approval_groups))
        <div class="section-divider"></div>
        <div class="section-title-modern text-success">
            <i data-lucide="check-circle"></i>
            <span>{{ __('execution.agreement') }}</span>
        </div>

        @php
            $seqLoc = session()->get('locale', app()->getLocale());
            $seqLabels = [
                'ru' => ['mode'=>'Порядок согласования','parallel'=>'Параллельно','parallelHint'=>'все одновременно','sequential'=>'Последовательно','sequentialHint'=>'по очереди, по уровням','order'=>'Очерёдность подписания','empty'=>'Отметьте согласующих — они появятся здесь для настройки очереди'],
                'en' => ['mode'=>'Approval order','parallel'=>'Parallel','parallelHint'=>'all at once','sequential'=>'Sequential','sequentialHint'=>'one by one, by levels','order'=>'Signing order','empty'=>'Select approvers — they will appear here to arrange the order'],
                'uz' => ['mode'=>'Kelishuv tartibi','parallel'=>'Parallel','parallelHint'=>'bir vaqtda','sequential'=>'Ketma-ket','sequentialHint'=>'navbat bilan, bosqichma-bosqich','order'=>'Imzolash navbati','empty'=>'Kelishuvchilarni belgilang — ular navbatni sozlash uchun shu yerda chiqadi'],
            ];
            $SL = $seqLabels[$seqLoc] ?? $seqLabels['uz'];
            $curType = $agreement_type ?? 'parallel';
        @endphp
        <div class="agreement-mode">
            <div class="agreement-mode-label"><i data-lucide="git-branch"></i> {{ $SL['mode'] }}</div>
            <div class="agreement-mode-options">
                <label class="agreement-mode-opt">
                    <input type="radio" name="agreement_type" value="parallel" {{ $curType !== 'sequential' ? 'checked' : '' }} onchange="window.AgreementSeq && AgreementSeq.setMode('parallel')">
                    <span class="amo-box"><i data-lucide="git-commit-horizontal"></i></span>
                    <span class="amo-text"><b>{{ $SL['parallel'] }}</b><small>{{ $SL['parallelHint'] }}</small></span>
                </label>
                <label class="agreement-mode-opt">
                    <input type="radio" name="agreement_type" value="sequential" {{ $curType === 'sequential' ? 'checked' : '' }} onchange="window.AgreementSeq && AgreementSeq.setMode('sequential')">
                    <span class="amo-box"><i data-lucide="list-ordered"></i></span>
                    <span class="amo-text"><b>{{ $SL['sequential'] }}</b><small>{{ $SL['sequentialHint'] }}</small></span>
                </label>
            </div>
        </div>
        <script>
            window.__SL = @json($SL);
            window.__seqPreset = @json($agreement_sequence ?? []);
            window.__seqModePreset = @json($curType);
        </script>

        {{-- Группы согласующих: выбор группы отмечает всех её участников --}}
        @if(count($approval_groups))
            @php $loc = session()->get('locale', app()->getLocale()); @endphp
            <div class="approval-groups-box">
                <div class="approval-groups-label">
                    <i data-lucide="users-round"></i>
                    <span>{{ $loc === 'ru' ? 'Группы согласующих' : ($loc === 'en' ? 'Approval groups' : 'Kelishuvchilar guruhlari') }}</span>
                </div>
                <div class="approval-groups-chips">
                    @foreach($approval_groups as $g)
                        <label class="approval-group-chip">
                            <input type="checkbox" class="approval-group-cb"
                                   value="{{ $g['id'] }}"
                                   onchange="toggleApprovalGroup('{{ $g['id'] }}', this.checked)">
                            <i data-lucide="users"></i>
                            <span>{{ $g['name'] }}</span>
                            <b>{{ count($g['members']) }}</b>
                        </label>
                    @endforeach
                </div>
            </div>
            <script>window.__approvalGroups = @json($approval_groups);</script>
        @endif

        <div class="search-box-modern">
            <i data-lucide="search"></i>
            <input type="text" class="form-control-modern border-none" id="userSearch" onkeyup="searchUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
        <div class="users-list-modern" id="agreementUsersList">
            @foreach($agreement_users as $key => $agreement_user)
                <div class="select-user">
                    <label class="user-checkbox-card">
                        <input type="checkbox" name="to_agree[]" value="{{ $agreement_user->id }}"
                            {{ $agreements && $agreements->count() &&  in_array($agreement_user->id, $agreements->pluck('to_user')->toArray()) ? 'checked' : ''}}>
                        <div class="user-card-content">
                            <div class="user-avatar bg-success">
                                <i data-lucide="user-check"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ optional($agreement_user->personal)->fullName() ?? '-' }}</span>
                                <span class="user-position">{{ optional($agreement_user->userPost)->lang(optional($agreement_user->userPost)->id) ?? '' }}</span>
                            </div>
                            <div class="checkbox-indicator">
                                <i data-lucide="check"></i>
                            </div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>

        {{-- Панель очерёдности (только для последовательного согласования) --}}
        <input type="hidden" name="agreement_sequence" id="agreementSequence" value="">
        <div class="agreement-sequence" id="agreementSequencePanel" style="display:none;">
            <div class="agreement-sequence-label"><i data-lucide="list-ordered"></i> {{ $SL['order'] }}</div>
            <ol class="sequence-list" id="sequenceList"></ol>
            <div class="sequence-empty" id="sequenceEmpty">{{ $SL['empty'] }}</div>
        </div>
    @endif

    <div class="section-divider"></div>
    <div class="section-title-modern">
        <i data-lucide="file-check"></i>
        <span>{{ __('execution.resolution') }}</span>
    </div>
    <div class="search-box-modern">
        <i data-lucide="search"></i>
        <input type="text" class="form-control-modern border-none" id="userToSearch" onkeyup="searchToUserFunction()" placeholder="{{ __('action.search') }}...">
    </div>
    <div class="users-list-modern">
        @foreach($resolution_users as $key => $resolution_user)
            <div class="select-to-user">
                <label class="user-radio-card">
                    <input type="radio" name="resolution_user" value="{{ $resolution_user->id }}"
                        {{ $verifier && $verifier->to_user === $resolution_user->id ? 'checked' : ''}}>
                    <div class="user-card-content">
                        <div class="user-avatar">
                            <i data-lucide="user"></i>
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ $resolution_user->personal->fullName()??'-' }}</span>
                            <span class="user-position">{{ $resolution_user->userPost->lang($resolution_user->userPost->id)??'' }}</span>
                        </div>
                        <div class="radio-indicator"></div>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/templates/edit-execution-steps.css') }}">

<script>
    function searchToUserFunction() {
        let input = document.getElementById("userToSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-to-user');
        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function searchUserFunction() {
        let input = document.getElementById("userSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-user');

        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function searchCorUserFunction() {
        let input = document.getElementById("corUserSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-cor-user');

        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function searchToAllUserFunction() {
        let input = document.getElementById("allUserToSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-to-all-user');

        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }
</script>

<script>window.__agAssigned = @json(isset($agreements) ? $agreements->pluck('to_user')->values() : []);</script>

<script>
    /**
     * Выбор группы согласующих: отмечает всех участников группы в списке to_agree[].
     * Если участника нет в текущем списке — добавляет карточку с чекбоксом.
     * Снятие группы снимает её участников, если они не входят в другую выбранную группу.
     */
    (function () {
        const selectedGroups = new Set();
        const initialChecked = new Set();

        // Запоминаем изначально отмеченных согласующих (режим редактирования),
        // чтобы снятие группы не убирало уже назначенных вручную людей.
        document.querySelectorAll('#agreementUsersList input[name="to_agree[]"]:checked')
            .forEach(function (cb) { initialChecked.add(String(cb.value)); });

        // Принудительно отмечаем всех УЖЕ назначенных согласующих (в т.ч. добавленных
        // ранее через группу), чтобы они гарантированно попали в to_agree[] и не
        // потерялись при сохранении (update() пересоздаёт согласующих только из to_agree[]).
        (window.__agAssigned || []).forEach(function (v) {
            const cb = document.querySelector('#agreementUsersList input[name="to_agree[]"][value="' + v + '"]');
            if (cb) { cb.checked = true; initialChecked.add(String(v)); }
        });

        function groupsData() { return window.__approvalGroups || []; }

        function findGroup(id) {
            return groupsData().find(g => String(g.id) === String(id));
        }

        function memberInOtherSelected(memberId, exceptGroupId) {
            return groupsData().some(g =>
                String(g.id) !== String(exceptGroupId) &&
                selectedGroups.has(String(g.id)) &&
                (g.members || []).some(m => String(m.id) === String(memberId))
            );
        }

        function ensureCheckbox(m) {
            const list = document.getElementById('agreementUsersList');
            if (!list) return null;
            let cb = list.querySelector('input[name="to_agree[]"][value="' + m.id + '"]');
            if (cb) return cb;

            const wrap = document.createElement('div');
            wrap.className = 'select-user approval-added';
            wrap.innerHTML =
                '<label class="user-checkbox-card">' +
                '<input type="checkbox" name="to_agree[]" value="' + m.id + '">' +
                '<div class="user-card-content">' +
                '<div class="user-avatar bg-success"><i data-lucide="user-check"></i></div>' +
                '<div class="user-info">' +
                '<span class="user-name">' + (m.name || '-') + '</span>' +
                '<span class="user-position">' + (m.position || '') + '</span>' +
                '</div>' +
                '<div class="checkbox-indicator"><i data-lucide="check"></i></div>' +
                '</div></label>';
            list.appendChild(wrap);
            if (window.lucide) lucide.createIcons();
            return wrap.querySelector('input');
        }

        window.toggleApprovalGroup = function (groupId, checked) {
            const g = findGroup(groupId);
            if (!g) return;
            if (checked) selectedGroups.add(String(groupId));
            else selectedGroups.delete(String(groupId));

            (g.members || []).forEach(function (m) {
                const cb = ensureCheckbox(m);
                if (!cb) return;
                if (checked) {
                    cb.checked = true;
                } else if (!memberInOtherSelected(m.id, groupId) && !initialChecked.has(String(m.id))) {
                    cb.checked = false;
                }
            });

            if (window.AgreementSeq) window.AgreementSeq.refresh();
        };
    })();
</script>

<script>
    /**
     * Очерёдность согласования: строит панель из отмеченных согласующих,
     * позволяет менять порядок (уровни 1..N) и пишет id в скрытое поле agreement_sequence.
     */
    window.AgreementSeq = (function () {
        let mode = 'parallel';
        let order = [];

        function panel()  { return document.getElementById('agreementSequencePanel'); }
        function listEl() { return document.getElementById('sequenceList'); }
        function hidden() { return document.getElementById('agreementSequence'); }
        function emptyEl(){ return document.getElementById('sequenceEmpty'); }

        function checkedBoxes() {
            return Array.prototype.slice.call(
                document.querySelectorAll('#agreementUsersList input[name="to_agree[]"]:checked'));
        }
        function nameFor(cb) {
            const card = cb.closest('.select-user, .user-checkbox-card');
            const n = card ? card.querySelector('.user-name') : null;
            return n ? n.textContent.trim() : cb.value;
        }
        function syncOrder() {
            const ids = checkedBoxes().map(cb => String(cb.value));
            order = order.filter(id => ids.indexOf(id) !== -1);
            ids.forEach(id => { if (order.indexOf(id) === -1) order.push(id); });
        }
        function move(idx, delta) {
            const j = idx + delta;
            if (j < 0 || j >= order.length) return;
            const t = order[idx]; order[idx] = order[j]; order[j] = t;
            render();
        }
        function render() {
            syncOrder();
            const list = listEl();
            if (!list) return;
            const boxes = {};
            checkedBoxes().forEach(cb => boxes[String(cb.value)] = cb);
            list.innerHTML = '';
            if (emptyEl()) emptyEl().style.display = order.length ? 'none' : 'block';

            order.forEach(function (id, idx) {
                const cb = boxes[id]; if (!cb) return;
                const li = document.createElement('li');
                li.className = 'sequence-item';
                li.setAttribute('draggable', 'true');
                li.dataset.id = id;
                li.innerHTML =
                    '<span class="seq-grip"><i data-lucide="grip-vertical"></i></span>' +
                    '<span class="seq-level">' + (idx + 1) + '</span>' +
                    '<span class="seq-name"></span>' +
                    '<span class="seq-actions">' +
                    '<button type="button" class="seq-btn" data-dir="up" ' + (idx === 0 ? 'disabled' : '') + '>&#8593;</button>' +
                    '<button type="button" class="seq-btn" data-dir="down" ' + (idx === order.length - 1 ? 'disabled' : '') + '>&#8595;</button>' +
                    '</span>';
                li.querySelector('.seq-name').textContent = nameFor(cb);
                li.querySelector('[data-dir="up"]').addEventListener('click', function () { move(idx, -1); });
                li.querySelector('[data-dir="down"]').addEventListener('click', function () { move(idx, 1); });
                li.addEventListener('dragstart', function () { setTimeout(function () { li.classList.add('dragging'); }, 0); });
                li.addEventListener('dragend', function () {
                    li.classList.remove('dragging');
                    // Новый порядок берём из текущего расположения карточек в DOM.
                    order = Array.prototype.slice.call(list.querySelectorAll('.sequence-item')).map(function (el) { return el.dataset.id; });
                    render();
                });
                list.appendChild(li);
            });

            if (hidden()) hidden().value = (mode === 'sequential') ? order.join(',') : '';
            if (window.lucide) lucide.createIcons();
        }
        function setMode(m) {
            mode = m;
            if (panel()) panel().style.display = (m === 'sequential') ? 'block' : 'none';
            render();
        }
        function getDragAfterElement(container, y) {
            const els = Array.prototype.slice.call(container.querySelectorAll('.sequence-item:not(.dragging)'));
            let closest = { offset: Number.NEGATIVE_INFINITY, element: null };
            els.forEach(function (child) {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) closest = { offset: offset, element: child };
            });
            return closest.element;
        }
        function init(presetOrder, presetMode) {
            if (Array.isArray(presetOrder)) order = presetOrder.map(String);
            const listRoot = document.getElementById('agreementUsersList');
            if (listRoot) {
                listRoot.addEventListener('change', function (e) {
                    if (e.target && e.target.name === 'to_agree[]') render();
                });
            }
            // Перетаскивание карточек очереди (как в Trello) — событие вешаем один раз на контейнер.
            const list = listEl();
            if (list && !list.__dndBound) {
                list.__dndBound = true;
                list.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    const dragging = list.querySelector('.dragging');
                    if (!dragging) return;
                    const after = getDragAfterElement(list, e.clientY);
                    if (after == null) list.appendChild(dragging);
                    else list.insertBefore(dragging, after);
                });
            }
            setMode(presetMode || 'parallel');
        }
        return { setMode: setMode, refresh: render, render: render, init: init };
    })();

    // Шаблон подставляется через $().html() уже после загрузки страницы,
    // поэтому DOMContentLoaded не сработает — инициализируем сразу.
    window.AgreementSeq.init(window.__seqPreset || [], window.__seqModePreset || 'parallel');
</script>

<style>
    .approval-groups-box { margin: 8px 0 14px; padding: 12px 14px; background: #f7f4ff; border: 1px solid #e6ddff; border-radius: 12px; }
    .approval-groups-label { display: flex; align-items: center; gap: 7px; font-weight: 700; color: #5b34d6; font-size: .9rem; margin-bottom: 10px; }
    .approval-groups-label i { width: 17px; height: 17px; }
    .approval-groups-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .approval-group-chip { display: inline-flex; align-items: center; gap: 7px; margin: 0; padding: 7px 12px; background: #fff; border: 1.5px solid #d9ccff; border-radius: 999px; cursor: pointer; font-size: .86rem; color: #3a2f66; transition: all .15s; user-select: none; }
    .approval-group-chip i { width: 15px; height: 15px; color: #794DFD; }
    .approval-group-chip b { background: #efeaff; color: #6b40e6; border-radius: 999px; padding: 1px 8px; font-size: .75rem; }
    .approval-group-chip input { display: none; }
    .approval-group-chip:hover { border-color: #794DFD; }
    .approval-group-chip:has(input:checked) { background: #794DFD; border-color: #794DFD; color: #fff; }
    .approval-group-chip:has(input:checked) i { color: #fff; }
    .approval-group-chip:has(input:checked) b { background: rgba(255,255,255,.25); color: #fff; }

    /* Режим согласования */
    .agreement-mode { margin: 8px 0 14px; }
    .agreement-mode-label { display:flex; align-items:center; gap:7px; font-weight:700; color:#334155; font-size:.9rem; margin-bottom:8px; }
    .agreement-mode-label i { width:17px; height:17px; color:#794DFD; }
    .agreement-mode-options { display:flex; gap:10px; flex-wrap:wrap; }
    .agreement-mode-opt { display:flex; align-items:center; gap:10px; flex:1 1 220px; margin:0; padding:11px 14px; border:1.5px solid #e6e8f0; border-radius:12px; cursor:pointer; background:#fff; transition:all .15s; }
    .agreement-mode-opt input { display:none; }
    .agreement-mode-opt .amo-box { width:38px; height:38px; border-radius:10px; display:grid; place-items:center; background:#f1f2f7; color:#64748b; flex:0 0 auto; transition:all .15s; }
    .agreement-mode-opt .amo-box i { width:20px; height:20px; }
    .agreement-mode-opt .amo-text { display:flex; flex-direction:column; }
    .agreement-mode-opt .amo-text b { font-size:.92rem; color:#1f2340; }
    .agreement-mode-opt .amo-text small { font-size:.76rem; color:#94a3b8; }
    .agreement-mode-opt:hover { border-color:#c9b8ff; }
    .agreement-mode-opt:has(input:checked) { border-color:#794DFD; background:#f7f4ff; }
    .agreement-mode-opt:has(input:checked) .amo-box { background:#794DFD; color:#fff; }

    /* Панель очерёдности */
    .agreement-sequence { margin:6px 0 14px; padding:12px 14px; background:#f8fafc; border:1px solid #e6e8f0; border-radius:12px; }
    .agreement-sequence-label { display:flex; align-items:center; gap:7px; font-weight:700; color:#334155; font-size:.88rem; margin-bottom:10px; }
    .agreement-sequence-label i { width:16px; height:16px; color:#794DFD; }
    .sequence-list { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:8px; }
    .sequence-item { display:flex; align-items:center; gap:12px; padding:9px 12px; background:#fff; border:1.5px solid #eceef4; border-radius:10px; }
    .seq-level { width:26px; height:26px; border-radius:8px; background:#794DFD; color:#fff; font-weight:800; font-size:.85rem; display:grid; place-items:center; flex:0 0 auto; }
    .seq-name { flex:1 1 auto; font-weight:600; color:#1f2340; font-size:.9rem; }
    .seq-actions { display:flex; gap:6px; }
    .seq-btn { width:28px; height:28px; border-radius:8px; border:1.5px solid #e2e5ee; background:#fff; color:#475569; cursor:pointer; font-size:14px; line-height:1; transition:all .15s; }
    .seq-btn:hover:not(:disabled) { border-color:#794DFD; color:#794DFD; }
    .seq-btn:disabled { opacity:.4; cursor:default; }
    .sequence-empty { text-align:center; color:#94a3b8; font-size:.84rem; padding:6px 0; }
    .sequence-item { cursor:default; user-select:none; transition:box-shadow .15s, border-color .15s, opacity .15s; }
    .seq-grip { display:grid; place-items:center; width:22px; color:#b6bccb; cursor:grab; flex:0 0 auto; }
    .seq-grip:active { cursor:grabbing; }
    .seq-grip i { width:16px; height:16px; }
    .sequence-item.dragging { opacity:.6; box-shadow:0 10px 24px rgba(121,77,253,.22); border-color:#794DFD; transform:rotate(1deg); }
</style>
