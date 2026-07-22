@php
    $loc = session()->get('locale', app()->getLocale());
    $tf = [
        'uz' => ['name'=>'Guruh nomi','org'=>'Tashkilot','members'=>'Ishtirokchilar','search'=>'Qidirish','selectAll'=>'Barchasini tanlash','selected'=>'tanlandi','save'=>'Saqlash','back'=>'Orqaga','noUsers'=>'Bu tashkilotda foydalanuvchilar topilmadi','hint'=>'Guruhga qo\'shilgan barcha xodimlar bir marta bosishda hujjatdagi kelishuvchilar ro\'yxatiga qo\'shiladi.'],
        'ru' => ['name'=>'Название группы','org'=>'Организация','members'=>'Участники','search'=>'Поиск','selectAll'=>'Выбрать всех','selected'=>'выбрано','save'=>'Сохранить','back'=>'Назад','noUsers'=>'В этой организации нет пользователей','hint'=>'Все участники группы добавляются в список согласующих документа одним кликом.'],
        'en' => ['name'=>'Group name','org'=>'Organization','members'=>'Members','search'=>'Search','selectAll'=>'Select all','selected'=>'selected','save'=>'Save','back'=>'Back','noUsers'=>'No users in this organization','hint'=>'All group members are added to the document approvers in one click.'],
    ];
    $F = $tf[$loc] ?? $tf['uz'];
    $selectedUserIds = array_map('intval', $selectedUserIds ?? []);
@endphp

<div class="table-card-modern">
    <div class="content-card-body" style="padding: 1.5rem;">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-{{ $canPickOrg && !$lockOrg ? 6 : 12 }}">
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i data-lucide="tag"></i> {{ $F['name'] }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control-modern"
                               value="{{ old('name', $group->name ?? '') }}" required
                               placeholder="{{ $F['name'] }}">
                        {!! $errors->first('name', '<p class="text-danger mt-1 mb-0">:message</p>') !!}
                    </div>
                </div>

                @if($canPickOrg && !$lockOrg)
                    <div class="col-md-6">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i data-lucide="building"></i> {{ $F['org'] }} <span class="text-danger">*</span>
                            </label>
                            <select name="org" class="form-control-modern select2"
                                    onchange="window.location.href='{{ route('approval-groups.create') }}?org='+this.value">
                                <option value="">—</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ (int)$orgId === (int)$org->id ? 'selected':'' }}>
                                        {{ \App\Models\Admin\OrganizationUnits::lang($org->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @elseif($lockOrg)
                    <input type="hidden" name="org" value="{{ $orgId }}">
                @endif
            </div>

            {{-- User picker --}}
            <div class="ag-picker">
                <div class="ag-picker-head">
                    <div class="ag-picker-title">
                        <i data-lucide="user-check"></i>
                        <span>{{ $F['members'] }} <span class="text-danger">*</span></span>
                    </div>
                    <div class="ag-picker-tools">
                        <label class="ag-selectall">
                            <input type="checkbox" id="agSelectAll"> <span>{{ $F['selectAll'] }}</span>
                        </label>
                        <span class="ag-counter"><b id="agCount">0</b> <span>{{ $F['selected'] }}</span></span>
                    </div>
                </div>

                <div class="ag-search">
                    <i data-lucide="search"></i>
                    <input type="text" id="agUserSearch" class="form-control-modern" placeholder="{{ $F['search'] }}...">
                </div>

                <div class="ag-user-list" id="agUserList">
                    @forelse($orgUsers as $u)
                        @php $uid = (int) $u->user_id; $isSel = in_array($uid, $selectedUserIds, true); @endphp
                        <label class="ag-user-item {{ $isSel ? 'is-checked' : '' }}">
                            <input type="checkbox" name="users[]" value="{{ $uid }}" class="ag-user-cb" {{ $isSel ? 'checked' : '' }}>
                            <span class="ag-avatar"><i data-lucide="user"></i></span>
                            <span class="ag-user-meta">
                                <span class="ag-user-name">{{ optional($u->personal)->fullName() ?? '-' }}</span>
                                <span class="ag-user-pos">{{ optional($u->userPost)->lang(optional($u->userPost)->id) ?? '' }}</span>
                            </span>
                            <span class="ag-check"><i data-lucide="check"></i></span>
                        </label>
                    @empty
                        <div class="ag-empty">{{ $F['noUsers'] }}</div>
                    @endforelse
                </div>
                {!! $errors->first('users', '<p class="text-danger mt-2 mb-0">:message</p>') !!}
                <p class="ag-hint"><i data-lucide="info"></i> {{ $F['hint'] }}</p>
            </div>

            <div class="ag-actions">
                <button type="submit" class="btn-modern btn-primary">
                    <i data-lucide="save"></i> {{ $F['save'] }}
                </button>
                <a href="{{ route('approval-groups.index', ['org' => $orgId]) }}" class="btn-modern btn-secondary">
                    <i data-lucide="arrow-left"></i> {{ $F['back'] }}
                </a>
            </div>

            {{-- Изначально выбранные участники (режим редактирования) для гарантированной отметки --}}
            <script>window.__agSelected = @json($selectedUserIds);</script>
        </form>
    </div>
</div>

<style>
    .form-group-modern { margin-bottom: 1.1rem; }
    .form-label-modern { display:flex; align-items:center; gap:7px; font-weight:600; font-size:.9rem; color:#3a3f55; margin-bottom:.5rem; }
    .form-label-modern i { width:16px; height:16px; color:#794DFD; }

    .ag-picker { margin-top: .6rem; border: 1px solid #e9ebf2; border-radius: 14px; padding: 1.1rem 1.2rem; background:#fbfbfe; }
    .ag-picker-head { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom: .3rem; }
    .ag-picker-title { display:flex; align-items:center; gap:8px; font-weight:700; color:#2a2e45; }
    .ag-picker-title i { width:18px; height:18px; color:#794DFD; }
    .ag-picker-tools { display:flex; align-items:center; gap:18px; }
    .ag-selectall { display:flex; align-items:center; gap:7px; margin:0; cursor:pointer; font-size:.88rem; color:#4b5563; user-select:none; }
    .ag-selectall input { width:16px; height:16px; accent-color:#794DFD; cursor:pointer; }
    .ag-counter { font-size:.85rem; color:#6b7280; white-space:nowrap; }
    .ag-counter b { color:#794DFD; font-size:.95rem; }

    .ag-search { position:relative; margin:14px 0; }
    .ag-search > i { position:absolute; left:14px; top:50%; transform:translateY(-50%); width:17px; height:17px; color:#9ca3af; z-index:2; pointer-events:none; }
    .ag-search .form-control-modern { padding-left:40px !important; }

    .ag-user-list { max-height: 430px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(255px,1fr)); gap:10px; padding: 2px; }
    .ag-user-item { display:flex; align-items:center; gap:11px; padding:10px 12px; border:1.5px solid #eceef4; border-radius:12px; cursor:pointer; margin:0; background:#fff; transition:all .15s; position:relative; }
    .ag-user-item:hover { border-color:#cbb8ff; box-shadow:0 2px 10px rgba(121,77,253,.08); }
    .ag-user-item input.ag-user-cb { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
    .ag-avatar { width:38px; height:38px; border-radius:10px; background:#efeaff; color:#794DFD; display:grid; place-items:center; flex:0 0 auto; }
    .ag-avatar i { width:20px; height:20px; }
    .ag-user-meta { display:flex; flex-direction:column; overflow:hidden; min-width:0; }
    .ag-user-name { font-weight:600; color:#1f2340; font-size:.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .ag-user-pos { font-size:.77rem; color:#8a90a6; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .ag-check { margin-left:auto; width:24px; height:24px; border-radius:7px; border:2px solid #d7dae5; display:grid; place-items:center; flex:0 0 auto; transition:all .15s; background:#fff; }
    /* lucide заменяет <i data-lucide="check"> на <svg>, поэтому скрываем именно svg */
    .ag-check svg { width:14px; height:14px; color:#fff; opacity:0; transition:opacity .15s; }
    .ag-user-item.is-checked { border-color:#794DFD; background:#f7f4ff; }
    .ag-user-item.is-checked .ag-check { background:#794DFD; border-color:#794DFD; }
    .ag-user-item.is-checked .ag-check svg { opacity:1; }

    .ag-empty { padding:1.5rem; text-align:center; color:#9ca3af; grid-column:1/-1; }
    .ag-hint { margin:14px 0 0; font-size:.82rem; color:#8a90a6; display:flex; align-items:center; gap:6px; }
    .ag-hint i { width:15px; height:15px; flex:0 0 auto; }

    .ag-actions { display:flex; gap:10px; margin-top:1.4rem; }
    .ag-actions .btn-modern { width:auto; }
</style>

<script>
    (function () {
        function initPicker() {
            const list = document.getElementById('agUserList');
            if (!list) return;
            const cbs = Array.prototype.slice.call(list.querySelectorAll('.ag-user-cb'));
            const countEl = document.getElementById('agCount');
            const selectAll = document.getElementById('agSelectAll');
            const search = document.getElementById('agUserSearch');

            // Гарантированно отмечаем изначально выбранных участников (режим редактирования).
            const preset = (window.__agSelected || []).map(String);
            if (preset.length) {
                cbs.forEach(function (c) {
                    if (preset.indexOf(String(c.value)) !== -1) c.checked = true;
                });
            }

            function syncItem(c) {
                const item = c.closest('.ag-user-item');
                if (item) item.classList.toggle('is-checked', c.checked);
            }

            function refresh() {
                cbs.forEach(syncItem);
                const n = cbs.filter(c => c.checked).length;
                if (countEl) countEl.textContent = n;
                if (selectAll) selectAll.checked = (n > 0 && n === cbs.length);
            }

            cbs.forEach(c => c.addEventListener('change', function () { syncItem(c); refresh(); }));

            if (selectAll) selectAll.addEventListener('change', function () {
                cbs.forEach(function (c) {
                    const item = c.closest('.ag-user-item');
                    if (item && item.style.display !== 'none') { c.checked = selectAll.checked; syncItem(c); }
                });
                refresh();
            });

            if (search) search.addEventListener('keyup', function () {
                const f = search.value.toLowerCase();
                cbs.forEach(function (c) {
                    const item = c.closest('.ag-user-item');
                    if (!item) return;
                    item.style.display = item.innerText.toLowerCase().includes(f) ? '' : 'none';
                });
            });

            refresh();
            if (window.lucide) lucide.createIcons();
            if (window.jQuery && $.fn.select2) $('.select2').select2({ width: '100%' });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPicker);
        } else { initPicker(); }
    })();
</script>
