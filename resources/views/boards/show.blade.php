@extends('layouts.app')

@section('content')
    @if(auth()->user()->hasKanbanAccess())

        <div x-data="kanban({{ Js::from($board->lists->map(fn($l) => [
        'id' => $l->id,
        'title' => $l->title,

        'cards' => $l->cards->map(fn ($c) => [
            'id' => $c->id,
            'title' => $c->title,
            'description' => $c->description,
            'due_date' => optional($c->due_date)->format('Y-m-d'),
            'due_label' => optional($c->due_date)->translatedFormat('d M'),
            'overdue' => $c->isOverdue(),
            'labels' => $c->labels->map(fn ($lb) => [
                'id' => $lb->id, 'name' => $lb->name, 'color' => $lb->color,
            ]),
            'priority' => $c->priority,
            'cover_color' => $c->cover_color,
            'assignee' => $c->assignee?->name,
            'comments_count' => $c->comments_count ?? 0,
            'checklist_total' => $c->checklists->sum(fn ($cl) => $cl->items->count()),
            'checklist_done'  => $c->checklists->sum(fn ($cl) => $cl->items->where('is_done', true)->count()),
        ]),
    ])) }}, {{ $board->id }}, {{ auth()->user()->hasRole(['Super_Admin','it_admin']) ? 'true' : 'false' }})"
             x-init="init(); boardVisibility = '{{ $board->visibility }}'; boardData = {
        title: @js($board->title),
        color: @js($board->color),
        description: @js($board->description),
        manager_id: {{ $board->manager_id ?? 'null' }},
        visibility: @js($board->visibility)
     };
     boardLabels = {{ Js::from($board->labels->map(fn($l) => ['id'=>$l->id,'name'=>$l->name,'color'=>$l->color])) }};
     canManageChecklists = ({{ auth()->user()->hasRole(['Super_Admin','IT_Admin']) ? 'true' : 'false' }} || {{ $board->manager_id === auth()->id() ? 'true' : 'false' }});
     " class="container-fluid py-3">

            @php
                $color = $board->color ?: '#7C3AED';
            @endphp

            <div class="board-head" style="background:linear-gradient(120deg,{{ $color }},{{ $color }}cc)">
                <div class="board-head-top">
                    <div class="board-head-left">
                        <a href="{{ route('boards.index') }}" class="board-head-back"><i class="fa fa-arrow-left"></i></a>
                        <div class="board-head-icon"><i class="fa fa-table-columns"></i></div>
                        <div>
                            <div class="board-head-title">{{ $board->title }}</div>
                            <div class="board-head-sub">{{ $board->description ?: 'Рабочая доска' }}</div>
                        </div>
                    </div>
                    <div class="board-head-actions">
                        <button type="button" class="board-head-btn" @click="openSettings()" x-show="isAdmin">
                            <i class="fa fa-gear"></i> <span>Настройки</span>
                        </button>
                    </div>
                </div>

                <div class="board-head-meta">
                    <span class="board-head-stat"><i class="fa fa-list"></i> {{ $board->lists_total }} списков</span>
                    <span class="board-head-stat"><i class="fa fa-square-check"></i> {{ $board->cards_total }} задач</span>
                    <span class="board-head-stat"><i
                            class="fa fa-circle-check"></i> {{ $board->cards_done }} выполнено</span>
                </div>

                <div class="board-head-bottom">
                    @if($board->manager)
                        @php
                            $mgrIni = \Illuminate\Support\Str::of($board->manager->name ?? '')
                                ->explode(' ')->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))->take(2)->implode('');
                        @endphp
                        <div class="board-head-manager">
                            <div class="board-head-manager-ava" style="color:{{ $color }}">{{ $mgrIni }}</div>
                            <div class="board-head-manager-info">
                                <span class="board-head-manager-label">Руководитель</span>
                                <span class="board-head-manager-name">{{ $board->manager->name }}</span>
                            </div>
                        </div>
                    @else
                        <span></span>
                    @endif

                    <span class="board-head-visibility">
      <i class="fa" :class="boardVisibility === 'public' ? 'fa-lock-open' : 'fa-lock'"></i>
      <span x-text="boardVisibility === 'public' ? 'Открытый' : 'Закрытый'"></span>
    </span>
                </div>
            </div>

            <div class="board-loading" x-show="!ready">
                <template x-for="i in 4" :key="i">
                    <div class="sk-column">
                        <div class="sk-col-head">
                            <span class="skeleton sk-dot"></span>
                            <span class="skeleton sk-line w-50" style="height:14px;flex:1"></span>
                            <span class="skeleton sk-badge"></span>
                        </div>
                        <div class="sk-card">
                            <span class="skeleton sk-line w-90"></span>
                            <span class="skeleton sk-pill"></span>
                            <span class="skeleton sk-line w-70"></span>
                        </div>
                        <div class="sk-card">
                            <span class="skeleton sk-line w-70"></span>
                            <span class="skeleton sk-pill"></span>
                            <span class="skeleton sk-line w-50"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="board-canvas" id="lists-container" x-show="ready" x-cloak>
                <template x-for="(list, idx) in lists" :key="list.id">
                    <div class="list-column" :data-list-id="list.id" :style="columnStyle(idx)">

                        {{-- Шапка списка --}}
                        <div class="list-head">
                            <span class="list-dot"></span>
                            <span class="list-title" x-show="editingList !== list.id"
                                  @click="isAdmin && startEditList(list)"
                                  :class="isAdmin && 'editable'"
                                  x-text="list.title"></span>

                            <input type="text" class="list-title-input" x-show="editingList === list.id" x-cloak
                                   x-model="editListTitle"
                                   :ref="'listInput' + list.id"
                                   @keydown.enter="saveListTitle(list)"
                                   @keydown.escape="cancelEditList()"
                                   @blur="saveListTitle(list)">

                            <span class="list-count" x-text="list.cards.length"></span>
                            <div class="list-head-actions list-menu-wrap">
                                <button class="list-menu-btn" @click="toggleListMenu(list.id)">
                                    <i class="fa fa-ellipsis"></i>
                                </button>

                                <div class="list-menu" x-show="openListMenu === list.id" x-cloak
                                     @click.outside="openListMenu = null">
                                    <button class="list-menu-item" @click="startAddCard(list); openListMenu = null">
                                        <i class="fa fa-plus"></i> Добавить карточку
                                    </button>
                                    <button class="list-menu-item danger" @click="deleteList(list)" x-show="isAdmin">
                                        <i class="fa fa-trash"></i> Удалить список
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Карточки --}}
                        <div class="cards-container" :data-list-id="list.id">
                            <template x-for="card in list.cards" :key="card.id">
                                <div class="kanban-card" :class="card._pending && 'card-pending'"
                                     :data-card-id="card.id" @click="openCard(card)">

                                    <template x-if="card.cover_color">
                                        <div class="card-cover" :style="`background:${card.cover_color}`"></div>
                                    </template>

                                    <div class="card-body">
                                        <div class="card-top">
                                            <i class="fa fa-grip-vertical card-grip"></i>
                                            <span class="card-title" x-text="card.title"></span>
                                        </div>

                                        <template x-if="card.labels && card.labels.length">
                                            <div class="card-labels">
                                                <template x-for="lb in card.labels" :key="lb.id">
                                                <span class="card-label-dot" :style="`background:${lb.color}`"
                                                      :title="lb.name"></span>
                                                </template>
                                            </div>
                                        </template>

                                        <div class="card-foot">
                                            <template x-if="card.due_label">
                                            <span class="card-stat due" :class="card.overdue && 'overdue'">
                                                <i class="fa fa-clock"></i> <span x-text="card.due_label"></span>
                                            </span>
                                            </template>

                                            {{-- Счётчик чек-листа --}}
                                            <template x-if="card.checklist_total > 0">
                                            <span class="card-stat checklist"
                                                  :class="card.checklist_done === card.checklist_total && 'done'">
                                                <i class="fa fa-square-check"></i>
                                                <span x-text="`${card.checklist_done}/${card.checklist_total}`"></span>
                                            </span>
                                            </template>
                                            <div class="card-stats-right">
                                            <span class="card-stat"><i class="fa fa-paperclip"></i> <span
                                                    x-text="card.checklists_count || 0"></span></span>
                                                <span class="card-stat"><i class="fa fa-comment"></i> <span
                                                        x-text="card.comments_count || 0"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Кнопка-триггер (видна, когда форма закрыта) --}}
                        <div class="add-card-row" x-show="addingTo !== list.id">
                            <button class="btn-add-card" @click="startAddCard(list)">
                                <i class="fa fa-plus"></i> Добавить карточку
                            </button>
                        </div>

                        {{-- Inline-форма (вместо кнопки, когда открыта) --}}
                        <div class="add-card-form" x-show="addingTo === list.id" x-cloak @click.outside="cancelAddCard()">
      <textarea class="add-card-input" rows="2"
                placeholder="Введите название или вставьте ссылку"
                x-model="newCardTitle"
                x-ref="newCardInput"
                @keydown.enter.prevent="submitNewCard(list)"
                @keydown.escape="cancelAddCard()"></textarea>
                            <div class="add-card-actions">
                                <button class="add-card-submit" :disabled="!newCardTitle.trim()"
                                        @click="submitNewCard(list)">
                                    Добавить карточку
                                </button>
                                <button class="add-card-cancel" @click="cancelAddCard()">&times;</button>
                            </div>
                        </div>

                    </div>
                </template>

                {{-- Кнопка-триггер добавления списка --}}
                <button class="btn-add-list" x-show="!addingList && isAdmin" @click="startAddList()">
                    <i class="fa fa-plus"></i> Добавить список
                </button>

                {{-- Inline-форма добавления списка --}}
                <div class="add-list-form" x-show="addingList" x-cloak @click.outside="cancelAddList()">
                    <input type="text" class="add-list-input"
                           placeholder="Введите название списка"
                           x-model="newListTitle"
                           x-ref="newListInput"
                           @keydown.enter.prevent="submitNewList()"
                           @keydown.escape="cancelAddList()">
                    <div class="add-list-actions">
                        <button class="add-list-submit" :disabled="!newListTitle.trim()" @click="submitNewList()">
                            Добавить список
                        </button>
                        <button class="add-list-cancel" @click="cancelAddList()">&times;</button>
                    </div>
                </div>
            </div>
            <div x-show="settingsOpen" x-cloak class="create-modal-backdrop"
                 @click.self="settingsOpen = false" @keydown.escape.window="settingsOpen = false">
                <div class="create-modal" @click.stop style="max-width:460px">
                    <div class="create-modal-head">
                        <h6><i class="fa fa-gear me-2"></i> Настройки доски</h6>
                        <button type="button" class="close" @click="settingsOpen = false">&times;</button>
                    </div>
                    <div class="create-modal-body" style="max-height:99vh; overflow-y:auto">

                        <div class="create-field">
                            <label>Название</label>
                            <input type="text" class="form-control" x-model="boardData.title">
                        </div>

                        <div class="create-field">
                            <label>Цвет</label>
                            <div class="create-color-grid">
                                <template x-for="c in colors" :key="c">
                                    <div class="create-color-opt" :class="boardData.color === c && 'active'"
                                         :style="`background:${c}`" @click="boardData.color = c"></div>
                                </template>
                            </div>
                        </div>
                        <div class="create-field">
                            <label>Метки доски</label>

                            {{-- Список существующих меток --}}
                            <div class="board-labels-list">
                                <template x-for="lb in boardLabels" :key="lb.id">
                                    <div class="board-label-row">
                                        <input type="color" class="label-color-input" x-model="lb.color"
                                               @change="updateBoardLabel(lb)">
                                        <input type="text" class="label-name-input" x-model="lb.name"
                                               @blur="updateBoardLabel(lb)">
                                        <button class="label-del" @click="deleteBoardLabel(lb)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                                <div class="board-labels-empty" x-show="!boardLabels.length">Меток пока нет</div>
                            </div>

                            {{-- Форма добавления --}}
                            <div class="label-add-row">
                                <input type="color" class="label-color-input" x-model="newLabelColor">
                                <input type="text" class="label-name-input" placeholder="Название метки"
                                       x-model="newLabelName" @keydown.enter.prevent="submitNewLabel()">
                                <button class="label-add-btn" :disabled="!newLabelName.trim()" @click="submitNewLabel()">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="create-field">
                            <label>Видимость</label>
                            <div class="visibility-toggle">
                                <button class="visibility-opt" :class="boardData.visibility === 'private' && 'active'"
                                        @click="boardData.visibility = 'private'">
                                    <i class="fa fa-lock"></i> Закрытый
                                </button>
                                <button class="visibility-opt" :class="boardData.visibility === 'public' && 'active'"
                                        @click="boardData.visibility = 'public'">
                                    <i class="fa fa-lock-open"></i> Открытый
                                </button>
                            </div>
                        </div>

                        <div class="create-field" style="position:relative;">
                            <label>Руководитель</label>
                            <div class="manager-trigger" @click="managerOpen = !managerOpen">
                                <template x-if="selectedManager()">
                                    <span class="ava" x-text="initials(selectedManager().name)"></span>
                                </template>
                                <template x-if="!selectedManager()">
                                    <span class="ava empty"><i class="fa fa-user"></i></span>
                                </template>
                                <span x-show="selectedManager()" x-text="selectedManager()?.name"></span>
                                <span x-show="!selectedManager()" class="placeholder">Не назначен</span>
                                <i class="fa fa-chevron-down chev"></i>
                            </div>
                            <div class="member-popover" x-show="managerOpen" x-cloak
                                 @click.outside="managerOpen = false"
                                 style="top:calc(100% + 6px); left:0; right:0; width:auto; max-height:240px;">
                                <div class="member-popover-head">Руководитель
                                    <button class="member-popover-close" @click="managerOpen = false">&times;</button>
                                </div>
                                <div class="member-popover-body">
                                    <input type="text" class="member-search" placeholder="Поиск сотрудников"
                                           x-model="managerSearch">
                                    <div class="member-list-label">Сотрудники</div>
                                    <div class="member-list">
                                        <button class="member-item" @click="pickManager(null)">
                                            <span class="ava empty"><i class="fa fa-user-slash"></i></span>
                                            <span>Не назначен</span>
                                            <i class="fa fa-check check" x-show="!boardData.manager_id"></i>
                                        </button>
                                        <template x-for="u in filteredManagerUsers()" :key="u.id">
                                            <button class="member-item" @click="pickManager(u)">
                                                <span class="ava" x-text="initials(u.name)"></span>
                                                <span x-text="u.name"></span>
                                                <i class="fa fa-check check" x-show="boardData.manager_id == u.id"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="create-field" style="position:relative;">
                            <label>Участники проекта</label>

                            {{-- Текущие участники --}}
                            <div class="board-members-list" x-show="boardMembers.length">
                                <template x-for="m in boardMembers" :key="m.id">
                                    <div class="board-member-chip">
                                        <span class="ava" x-text="initials(m.name)"></span>
                                        <span x-text="m.name"></span>
                                        <button class="chip-remove" @click="removeBoardMember(m)">&times;</button>
                                    </div>
                                </template>
                            </div>

                            {{-- Кнопка добавления --}}
                            <div class="manager-trigger" @click="boardMemberOpen = !boardMemberOpen" style="margin-top:8px">
                                <span class="ava empty"><i class="fa fa-user-plus"></i></span>
                                <span class="placeholder">Добавить участника</span>
                                <i class="fa fa-chevron-down chev"></i>
                            </div>

                            {{-- Поповер выбора из всех сотрудников --}}
                            <div class="member-popover" x-show="boardMemberOpen" x-cloak
                                 @click.outside="boardMemberOpen = false"
                                 style="top:calc(100% + 6px); left:0; right:0; width:auto; max-height:260px;">
                                <div class="member-popover-head">Добавить участника
                                    <button class="member-popover-close" @click="boardMemberOpen = false">&times;</button>
                                </div>
                                <div class="member-popover-body">
                                    <input type="text" class="member-search" placeholder="Поиск сотрудников"
                                           x-model="boardMemberSearch">
                                    <div class="member-list-label">Сотрудники организации</div>
                                    <div class="member-list">
                                        <template x-for="u in filteredOrgUsers()" :key="u.id">
                                            <button class="member-item" @click="toggleBoardMember(u)">
                                                <span class="ava" x-text="initials(u.name)"></span>
                                                <span x-text="u.name"></span>
                                                <i class="fa fa-check check" x-show="isBoardMember(u)"></i>
                                            </button>
                                        </template>
                                        <div class="member-empty" x-show="filteredOrgUsers().length === 0">Ничего не
                                            найдено
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="create-field">
                            <label>Описание</label>
                            <textarea class="form-control" rows="3" x-model="boardData.description"></textarea>
                        </div>

                        <div class="create-modal-foot">
                            <button type="button" class="btn btn-link text-muted text-decoration-none"
                                    @click="settingsOpen = false">Отмена
                            </button>
                            <button type="button" class="btn text-white fw-medium" style="background:#7C3AED"
                                    :disabled="savingSettings" @click="saveSettings()">
                                <span x-show="!savingSettings"><i class="fa fa-check me-1"></i> Сохранить</span>
                                <span x-show="savingSettings" x-cloak><span
                                        class="brd-spinner me-1"></span> Сохраняем…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @include('boards.partials.card-modal')
        </div>

    @else

        <div class="container-fluid py-3">
            <div class="kanban-no-access">
                <div class="kanban-no-access-icon">
                    <i class="fa fa-lock"></i>
                </div>
                <h5 class="mb-2">Нет доступа к доскам</h5>
                <p class="text-muted mb-0">
                    Модуль Kanban не подключён к вашей организации.<br>
                    Обратитесь к администратору для активации доступа.
                </p>
            </div>
        </div>

    @endif
@endsection

@push('scripts')
    @if(auth()->user()->hasKanbanAccess())
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('task', {
                    card: {},
                    feed: [],
                    open: false,
                    loading: false,   // загрузка деталей карточки
                    saving: false,    // автосохранение поля
                });

                Alpine.data('kanban', (initialLists, boardId, isAdmin) => ({
                    // ============ СОСТОЯНИЕ ============
                    boardId,
                    isAdmin,
                    lists: initialLists,
                    ready: false,
                    isDark: document.body.classList.contains('theme-dark'),
                    canManageChecklists: false,

                    // доска / настройки
                    settingsOpen: false,
                    savingSettings: false,
                    boardVisibility: 'private',
                    boardData: {title: '', color: '#7C3AED', description: '', manager_id: null, visibility: 'private'},
                    colors: ['#7C3AED', '#F0997B', '#ED93B1', '#97C459', '#85B7EB', '#FAC775', '#1D9E75', '#E0A040'],

                    // руководитель / участники (в настройках)
                    managerOpen: false,
                    managerSearch: '',
                    boardMembers: [],
                    orgUsers: [],
                    boardMemberOpen: false,
                    boardMemberSearch: '',

                    // метки доски
                    boardLabels: [],
                    newLabelName: '',
                    newLabelColor: '#7C3AED',

                    // списки
                    addingList: false,
                    newListTitle: '',
                    editingList: null,
                    editListTitle: '',
                    _savingListTitle: false,
                    openListMenu: null,

                    // карточки
                    addingTo: null,
                    newCardTitle: '',

                    // модалка карточки
                    allUsers: [],
                    newComment: '',
                    memberSearch: '',
                    showLabelPicker: false,
                    showMemberPicker: false,
                    showDate: false,
                    showPrioPicker: false,
                    showCoverPicker: false,
                    coverColors: ['#A78BFA', '#F0997B', '#ED93B1', '#97C459', '#85B7EB', '#FAC775'],

                    // ============ ИНИЦИАЛИЗАЦИЯ ============
                    init() {
                        this.$nextTick(() => {
                            this.initSortables();
                            setTimeout(() => {
                                this.ready = true;
                            }, 250);
                        });

                        const obs = new MutationObserver(() => {
                            this.isDark = document.body.classList.contains('theme-dark');
                        });
                        obs.observe(document.body, {attributes: true, attributeFilter: ['class']});
                    },
                    initSortables() {
                        document.querySelectorAll('.cards-container').forEach(el => {
                            if (el._sortable) return;
                            el._sortable = Sortable.create(el, {
                                group: 'cards',
                                animation: 160,
                                ghostClass: 'sortable-ghost',
                                chosenClass: 'sortable-chosen',
                                dragClass: 'sortable-drag',
                                delay: 150,                    // удержание перед drag
                                delayOnTouchOnly: true,        // задержка только на тач-экранах
                                touchStartThreshold: 5,
                                onEnd: (e) => this.onCardMove(e),
                            });
                        });
                        Sortable.create(document.getElementById('lists-container'), {
                            animation: 160,
                            handle: '.list-head',
                            draggable: '.list-column',
                            ghostClass: 'sortable-ghost',
                            delay: 150,
                            delayOnTouchOnly: true,
                            onEnd: () => this.onListMove(),
                        });
                    },
                    // ============ HELPERS ============
                    initials(name) {
                        return (name || '').split(/[\s_]+/).map(p => p[0]).slice(0, 2).join('').toUpperCase();
                    },
                    prioLabel(p) {
                        return {low: 'Low', medium: 'Medium', high: 'High'}[p] || '';
                    },
                    autoGrow(e) {
                        e.target.style.height = 'auto';
                        e.target.style.height = e.target.scrollHeight + 'px';
                    },
                    timeAgo(iso) {
                        if (!iso) return '';
                        const diff = (Date.now() - new Date(iso)) / 1000;
                        if (diff < 60) return 'только что';
                        if (diff < 3600) return Math.round(diff / 60) + ' мин назад';
                        if (diff < 86400) return Math.round(diff / 3600) + ' ч назад';
                        return Math.round(diff / 86400) + ' дн назад';
                    },
                    columnStyle(idx) {
                        const light = [
                            {bg: '#EFEAFB', dot: '#7C3AED', badgeBg: '#DCD3F5', badgeFg: '#534AB7'},
                            {bg: '#FBEFE3', dot: '#E0A040', badgeBg: '#F2DDC0', badgeFg: '#854F0B'},
                            {bg: '#FBEAF0', dot: '#D4537E', badgeBg: '#F4D0DE', badgeFg: '#993556'},
                            {bg: '#EAF3DE', dot: '#639922', badgeBg: '#D4E8B8', badgeFg: '#3B6D11'},
                        ];
                        const dark = [
                            {bg: '#241F36', dot: '#A78BFA', badgeBg: '#3F3760', badgeFg: '#C4B5F0'},
                            {bg: '#2E2536', dot: '#E0A040', badgeBg: '#4A3520', badgeFg: '#E8C07A'},
                            {bg: '#2E2230', dot: '#D4537E', badgeBg: '#4A2838', badgeFg: '#E89BB5'},
                            {bg: '#252E26', dot: '#7BB13C', badgeBg: '#33451F', badgeFg: '#A8D070'},
                        ];
                        const c = (this.isDark ? dark : light)[idx % 4];
                        return `--col-bg:${c.bg};--col-dot:${c.dot};--col-badge-bg:${c.badgeBg};--col-badge-fg:${c.badgeFg}`;
                    },

                    // ============ СЕТЬ ============
                    async req(url, method, body) {
                        const r = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: body ? JSON.stringify(body) : null,
                        });
                        if (!r.ok) {
                            const text = await r.text();
                            alert('Ошибка ' + r.status + ': ' + text.slice(0, 300));
                            throw new Error(r.status);
                        }
                        return r.status === 204 ? null : r.json();
                    },
                    async saveReq(url, method, body) {
                        const store = Alpine.store('task');
                        store.saving = true;
                        try {
                            return await this.req(url, method, body);
                        } finally {
                            setTimeout(() => {
                                store.saving = false;
                            }, 300);
                        }
                    },
                    normalize(c) {
                        const due = c.due_date ? c.due_date.substring(0, 10) : null;
                        return {
                            id: c.id,
                            title: c.title,
                            description: c.description,
                            due_date: due,
                            due_label: due ? new Date(due).toLocaleDateString('ru-RU', {
                                day: 'numeric',
                                month: 'short'
                            }) : null,
                            overdue: due ? new Date(due) < new Date(new Date().toDateString()) : false,
                            labels: c.labels || [],
                            priority: c.priority,
                            cover_color: c.cover_color,
                            assignee: c.assignee?.name ?? c.assignee ?? null,
                            checklist_total: c.checklist_total ?? 0,
                            checklist_done: c.checklist_done ?? 0,
                        };
                    },

                    // ============ SORTABLE / DnD ============
                    initSortables() {
                        document.querySelectorAll('.cards-container').forEach(el => {
                            if (el._sortable) return;
                            el._sortable = Sortable.create(el, {
                                group: 'cards',
                                animation: 160,
                                ghostClass: 'sortable-ghost',
                                chosenClass: 'sortable-chosen',
                                dragClass: 'sortable-drag',
                                onEnd: (e) => this.onCardMove(e),
                            });
                        });
                        Sortable.create(document.getElementById('lists-container'), {
                            animation: 160,
                            handle: '.list-head',
                            draggable: '.list-column',
                            ghostClass: 'sortable-ghost',
                            onEnd: () => this.onListMove(),
                        });
                    },
                    refreshSortables() {
                        this.$nextTick(() => this.initSortables());
                    },
                    onCardMove(e) {
                        const cardId = +e.item.dataset.cardId;
                        const fromId = +e.from.dataset.listId;
                        const toId = +e.to.dataset.listId;

                        const from = this.lists.find(l => l.id === fromId);
                        const to = this.lists.find(l => l.id === toId);
                        const idx = from.cards.findIndex(c => c.id === cardId);
                        const [moved] = from.cards.splice(idx, 1);
                        to.cards.splice(e.newIndex, 0, moved);

                        const orderedIds = to.cards.map(c => c.id);
                        this.req(`/cards/${cardId}/move`, 'PATCH', {board_list_id: toId, ordered_ids: orderedIds})
                            .catch(() => location.reload());
                    },
                    onListMove() {
                        const orderedIds = [...document.querySelectorAll('.list-column')].map(c => +c.dataset.listId);
                        this.lists.sort((a, b) => orderedIds.indexOf(a.id) - orderedIds.indexOf(b.id));
                        this.req(`/lists/${orderedIds[0]}/move`, 'PATCH', {ordered_ids: orderedIds})
                            .catch(() => location.reload());
                    },

                    // ============ СПИСКИ ============
                    startAddList() {
                        this.addingList = true;
                        this.newListTitle = '';
                        this.$nextTick(() => this.$refs.newListInput?.focus());
                    },
                    cancelAddList() {
                        this.addingList = false;
                        this.newListTitle = '';
                    },
                    async submitNewList() {
                        const title = this.newListTitle.trim();
                        if (!title) return;
                        try {
                            const list = await this.req(`/boards/${this.boardId}/lists`, 'POST', {title});
                            this.lists.push({id: list.id, title: list.title, cards: []});
                            this.newListTitle = '';
                            this.refreshSortables();
                            this.$nextTick(() => this.$refs.newListInput?.focus());
                        } catch (e) {
                        }
                    },
                    toggleListMenu(id) {
                        this.openListMenu = this.openListMenu === id ? null : id;
                    },
                    async deleteList(list) {
                        const count = list.cards.length;
                        const msg = count > 0
                            ? `Удалить список «${list.title}» вместе с ${count} карточк(ами)?`
                            : `Удалить список «${list.title}»?`;
                        if (!confirm(msg)) return;
                        this.openListMenu = null;
                        try {
                            await this.req(`/lists/${list.id}`, 'DELETE');
                            this.lists = this.lists.filter(l => l.id !== list.id);
                        } catch (e) {
                        }
                    },
                    startEditList(list) {
                        this.editingList = list.id;
                        this.editListTitle = list.title;
                        this.$nextTick(() => {
                            const el = this.$refs['listInput' + list.id];
                            if (el) {
                                el.focus();
                                el.select();
                            }
                        });
                    },
                    cancelEditList() {
                        this.editingList = null;
                        this.editListTitle = '';
                    },
                    async saveListTitle(list) {
                        if (this._savingListTitle) return;
                        const title = this.editListTitle.trim();
                        if (!title || title === list.title) {
                            this.cancelEditList();
                            return;
                        }
                        this._savingListTitle = true;
                        try {
                            await this.req(`/lists/${list.id}`, 'PATCH', {title});
                            list.title = title;
                        } catch (e) {
                        } finally {
                            this._savingListTitle = false;
                            this.cancelEditList();
                        }
                    },

                    // ============ КАРТОЧКИ (добавление в колонке) ============
                    startAddCard(list) {
                        this.addingTo = list.id;
                        this.newCardTitle = '';
                        this.$nextTick(() => this.$refs.newCardInput?.focus());
                    },
                    cancelAddCard() {
                        this.addingTo = null;
                        this.newCardTitle = '';
                    },
                    async submitNewCard(list) {
                        const title = this.newCardTitle.trim();
                        if (!title) return;
                        const temp = {
                            id: 'tmp-' + Date.now(),
                            title,
                            _pending: true,
                            checklist_total: 0,
                            checklist_done: 0
                        };
                        list.cards.push(temp);
                        this.newCardTitle = '';
                        this.$nextTick(() => this.$refs.newCardInput?.focus());
                        try {
                            const card = await this.req(`/lists/${list.id}/cards`, 'POST', {title});
                            const i = list.cards.findIndex(c => c.id === temp.id);
                            if (i !== -1) list.cards[i] = this.normalize(card);
                        } catch (e) {
                            list.cards = list.cards.filter(c => c.id !== temp.id);
                        }
                    },

                    // ============ НАСТРОЙКИ ДОСКИ ============
                    async openSettings() {
                        this.settingsOpen = true;
                        await this.loadOrgUsers();
                        await this.loadBoardMembers();
                    },
                    async saveSettings() {
                        this.savingSettings = true;
                        try {
                            await this.req(`/boards/${this.boardId}`, 'PATCH', {
                                title: this.boardData.title,
                                color: this.boardData.color,
                                description: this.boardData.description,
                                manager_id: this.boardData.manager_id || null,
                                visibility: this.boardData.visibility,
                            });
                            location.reload();
                        } catch (e) {
                        } finally {
                            this.savingSettings = false;
                        }
                    },

                    // руководитель
                    selectedManager() {
                        return this.orgUsers.find(u => u.id == this.boardData.manager_id) || null;
                    },
                    filteredManagerUsers() {
                        const q = this.managerSearch.trim().toLowerCase();
                        return q ? this.orgUsers.filter(u => (u.name || '').toLowerCase().includes(q)) : this.orgUsers;
                    },
                    pickManager(u) {
                        this.boardData.manager_id = u ? u.id : null;
                        this.managerOpen = false;
                        this.managerSearch = '';
                    },

                    // участники доски
                    async loadOrgUsers() {
                        if (this.orgUsers.length) return;
                        this.orgUsers = await this.req('/board-users-all', 'GET');
                    },
                    async loadBoardMembers() {
                        this.boardMembers = await this.req(`/boards/${this.boardId}/members`, 'GET');
                    },
                    filteredOrgUsers() {
                        const q = this.boardMemberSearch.trim().toLowerCase();
                        return q ? this.orgUsers.filter(u => (u.name || '').toLowerCase().includes(q)) : this.orgUsers;
                    },
                    isBoardMember(u) {
                        return this.boardMembers.some(m => m.id === u.id);
                    },
                    async toggleBoardMember(u) {
                        if (this.isBoardMember(u)) {
                            this.boardMembers = await this.req(`/boards/${this.boardId}/members/${u.id}`, 'DELETE');
                        } else {
                            this.boardMembers = await this.req(`/boards/${this.boardId}/members`, 'POST', {user_id: u.id});
                        }
                        this.allUsers = [];
                    },
                    async removeBoardMember(m) {
                        this.boardMembers = await this.req(`/boards/${this.boardId}/members/${m.id}`, 'DELETE');
                        this.allUsers = [];
                    },

                    // метки доски
                    async submitNewLabel() {
                        if (!this.newLabelName.trim()) return;
                        const lb = await this.req(`/boards/${this.boardId}/labels`, 'POST', {
                            name: this.newLabelName,
                            color: this.newLabelColor,
                        });
                        this.boardLabels.push(lb);
                        this.newLabelName = '';
                        this.newLabelColor = '#7C3AED';
                    },
                    async updateBoardLabel(lb) {
                        await this.req(`/board-labels/${lb.id}`, 'PATCH', {name: lb.name, color: lb.color});
                    },
                    async deleteBoardLabel(lb) {
                        if (!confirm(`Удалить метку «${lb.name}»?`)) return;
                        await this.req(`/board-labels/${lb.id}`, 'DELETE');
                        this.boardLabels = this.boardLabels.filter(l => l.id !== lb.id);
                        this.lists.forEach(l => l.cards.forEach(c => {
                            if (c.labels) c.labels = c.labels.filter(x => x.id !== lb.id);
                        }));
                    },

                    // ============ МОДАЛКА КАРТОЧКИ ============
                    async openCard(card) {
                        const cardId = card.id;
                        const store = Alpine.store('task');
                        store.open = true;
                        store.loading = true;
                        this.showLabelPicker = this.showMemberPicker = this.showDate = false;
                        this.showPrioPicker = this.showCoverPicker = false;
                        this.newComment = '';

                        const listTitle = this.lists.find(l => l.cards.some(c => c.id === cardId))?.title;
                        store.card = {
                            id: cardId,
                            title: card.title,
                            description: card.description,
                            list_title: listTitle,
                            members: [],
                            checklists: [],
                            labels: []
                        };

                        try {
                            const full = await this.req(`/cards/${cardId}/full`, 'GET');
                            store.card = {
                                id: cardId,
                                title: full.title,
                                description: full.description,
                                priority: full.priority,
                                cover_color: full.cover_color,
                                due_date: full.due_date ? full.due_date.substring(0, 10) : null,
                                due_label: full.due_date ? new Date(full.due_date.substring(0, 10)).toLocaleDateString('ru-RU', {
                                    day: 'numeric',
                                    month: 'short'
                                }) : null,
                                overdue: full.due_date ? new Date(full.due_date) < new Date(new Date().toDateString()) : false,
                                list_title: listTitle,
                                labels: full.labels || [],
                                members: full.members || [],
                                checklists: (full.checklists || []).map(cl => ({
                                    ...cl,
                                    items: cl.items || [],
                                    _newItem: ''
                                })),
                            };
                            this.buildFeed(full);
                        } catch (e) {
                            console.warn('full failed:', e);
                        } finally {
                            store.loading = false;
                        }
                    },
                    closeCard() {
                        Alpine.store('task').open = false;
                    },
                    buildFeed(full) {
                        const store = Alpine.store('task');
                        const name = (u) => u?.name || u?.username || '—';
                        const acts = (full.activities || []).map(a => ({
                            _key: 'a' + a.id, raw: a.created_at,
                            user: name(a.user), text: a.description, time: this.timeAgo(a.created_at),
                        }));
                        const comments = (full.comments || []).map(c => ({
                            _key: 'c' + c.id, raw: c.created_at,
                            user: name(c.user), text: c.body, time: this.timeAgo(c.created_at),
                        }));
                        store.feed = [...comments, ...acts].sort((a, b) => new Date(b.raw) - new Date(a.raw));
                    },
                    syncCardInList() {
                        const store = Alpine.store('task');
                        const n = this.normalize(store.card);
                        this.lists.forEach(l => {
                            const i = l.cards.findIndex(c => c.id === store.card.id);
                            if (i !== -1) l.cards[i] = {...l.cards[i], ...n};
                        });
                    },
                    async saveField(field) {
                        const store = Alpine.store('task');
                        const payload = {};
                        if (field === 'title') payload.title = store.card.title;
                        if (field === 'description') payload.description = store.card.description;
                        if (field === 'due_date') payload.due_date = store.card.due_date || null;

                        const updated = await this.saveReq(`/cards/${store.card.id}`, 'PATCH', payload);
                        const n = this.normalize(updated);
                        store.card.due_date = n.due_date;
                        store.card.due_label = n.due_label;
                        store.card.overdue = n.overdue;
                        this.syncCardInList();
                    },
                    async deleteCard() {
                        const store = Alpine.store('task');
                        if (!confirm('Удалить карточку?')) return;
                        await this.req(`/cards/${store.card.id}`, 'DELETE');
                        this.lists.forEach(l => {
                            l.cards = l.cards.filter(c => c.id !== store.card.id);
                        });
                        store.open = false;
                    },

                    // метки карточки
                    toggleLabelPicker() {
                        this.showLabelPicker = !this.showLabelPicker;
                        this.showMemberPicker = this.showPrioPicker = this.showCoverPicker = false;
                    },
                    cardHasLabel(lb) {
                        const store = Alpine.store('task');
                        return (store.card.labels || []).some(l => l.id === lb.id);
                    },
                    async toggleLabel(lb) {
                        const store = Alpine.store('task');
                        if (this.cardHasLabel(lb)) {
                            store.card.labels = await this.req(`/cards/${store.card.id}/labels/${lb.id}`, 'DELETE');
                        } else {
                            store.card.labels = await this.req(`/cards/${store.card.id}/labels/${lb.id}`, 'POST', {});
                        }
                        this.syncCardInList();
                    },

                    // приоритет / обложка
                    togglePrioPicker() {
                        this.showPrioPicker = !this.showPrioPicker;
                        this.showCoverPicker = false;
                    },
                    toggleCoverPicker() {
                        this.showCoverPicker = !this.showCoverPicker;
                        this.showPrioPicker = false;
                    },
                    async setPriority(p) {
                        const store = Alpine.store('task');
                        store.card.priority = p;
                        this.showPrioPicker = false;
                        await this.req(`/cards/${store.card.id}`, 'PATCH', {priority: p});
                        this.syncCardInList();
                    },
                    async setCover(color) {
                        const store = Alpine.store('task');
                        store.card.cover_color = color;
                        this.showCoverPicker = false;
                        await this.req(`/cards/${store.card.id}`, 'PATCH', {cover_color: color});
                        this.syncCardInList();
                    },
                    focusDate() {
                        this.showDate = !this.showDate;
                    },

                    // участники карточки
                    toggleMemberPicker() {
                        this.showMemberPicker = !this.showMemberPicker;
                        this.showLabelPicker = this.showPrioPicker = this.showCoverPicker = false;
                        if (this.showMemberPicker) {
                            this.loadUsers();
                            this.memberSearch = '';
                            this.$nextTick(() => this.$refs.memberSearchInput?.focus());
                        }
                    },
                    async loadUsers() {
                        if (this.allUsers.length) return;
                        this.allUsers = await this.req(`/board-users?board_id=${this.boardId}`, 'GET');
                    },
                    filteredUsers() {
                        const q = this.memberSearch.trim().toLowerCase();
                        return q ? this.allUsers.filter(u => (u.name || '').toLowerCase().includes(q)) : this.allUsers;
                    },
                    isMember(u) {
                        const store = Alpine.store('task');
                        return (store.card.members || []).some(m => m.id === u.id);
                    },
                    async toggleMember(u) {
                        const store = Alpine.store('task');
                        if (this.isMember(u)) {
                            store.card.members = await this.req(`/cards/${store.card.id}/members/${u.id}`, 'DELETE');
                        } else {
                            store.card.members = await this.req(`/cards/${store.card.id}/members`, 'POST', {user_id: u.id});
                        }
                    },
                    async removeMember(m) {
                        const store = Alpine.store('task');
                        if (!confirm(`Убрать ${m.name}?`)) return;
                        store.card.members = await this.req(`/cards/${store.card.id}/members/${m.id}`, 'DELETE');
                    },

                    // чек-листы
                    async addChecklist() {
                        const store = Alpine.store('task');
                        const cl = await this.req(`/cards/${store.card.id}/checklists`, 'POST', {});
                        store.card.checklists.push({...cl, items: cl.items || [], _newItem: ''});
                    },
                    async deleteChecklist(cl) {
                        if (!confirm(`Удалить чек-лист «${cl.title}» со всеми пунктами?`)) return;
                        const store = Alpine.store('task');
                        try {
                            await this.req(`/checklists/${cl.id}`, 'DELETE');
                            store.card.checklists = store.card.checklists.filter(c => c.id !== cl.id);
                            this.recalcChecklist();
                        } catch (e) {
                        }
                    },
                    async addItem(cl) {
                        if (!cl._newItem || !cl._newItem.trim()) return;
                        const item = await this.req(`/checklists/${cl.id}/items`, 'POST', {title: cl._newItem});
                        cl.items.push(item);
                        cl._newItem = '';
                        this.recalcChecklist();
                    },
                    async toggleItem(cl, item) {
                        const updated = await this.req(`/checklist-items/${item.id}`, 'PATCH', {});
                        item.is_done = updated.is_done;
                        this.recalcChecklist();
                    },
                    async deleteItem(cl, item) {
                        await this.req(`/checklist-items/${item.id}`, 'DELETE');
                        cl.items = cl.items.filter(i => i.id !== item.id);
                        this.recalcChecklist();
                    },
                    recalcChecklist() {
                        const store = Alpine.store('task');
                        const cls = store.card.checklists || [];
                        store.card.checklist_total = cls.reduce((s, cl) => s + cl.items.length, 0);
                        store.card.checklist_done = cls.reduce((s, cl) => s + cl.items.filter(i => i.is_done).length, 0);
                        this.lists.forEach(l => {
                            const i = l.cards.findIndex(c => c.id === store.card.id);
                            if (i !== -1) {
                                l.cards[i].checklist_total = store.card.checklist_total;
                                l.cards[i].checklist_done = store.card.checklist_done;
                            }
                        });
                    },

                    // комментарии
                    async addComment() {
                        const store = Alpine.store('task');
                        if (!this.newComment.trim()) return;
                        const c = await this.req(`/cards/${store.card.id}/comments`, 'POST', {body: this.newComment});
                        store.feed.unshift({
                            _key: 'c' + c.id,
                            raw: c.created_at,
                            user: c.user?.name || 'Вы',
                            text: c.body,
                            time: 'только что'
                        });
                        this.newComment = '';
                    },
                }));
            });
        </script>
    @endif
@endpush
