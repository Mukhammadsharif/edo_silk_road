@extends('layouts.app')

@section('content')
    <div x-data="kanban({{ Js::from($board->lists->map(fn($l) => [
        'id' => $l->id,
        'title' => $l->title,
        'cards' => $l->cards->map(fn($c) => [
            'id' => $c->id,
            'title' => $c->title,
            'description' => $c->description,
            'due_date' => optional($c->due_date)->format('Y-m-d'),
            'due_label' => optional($c->due_date)->translatedFormat('d MMM'),
            'overdue' => $c->isOverdue(),
            'label_color' => $c->label_color,
            'assignee' => $c->assignee?->name,
        ]),
    ])) }}, {{ $board->id }})"
         x-init="init()" class="container-fluid py-3">

        <div class="board-header d-flex align-items-center justify-content-between px-4 py-3 mb-0">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('boards.index') }}" class="board-back-btn me-1">
                    <i class="fa fa-arrow-left fs-5"></i>
                </a>
                <div class="board-icon-box"><i class="fa fa-table-columns text-white fs-4"></i></div>
                <h5 class="text-white mb-0 fw-bold ms-1">{{ $board->title }}</h5>
            </div>
        </div>

        <div class="board-canvas" id="lists-container">
            <template x-for="list in lists" :key="list.id">
                <div class="list-column" :data-list-id="list.id">
                    <div class="list-head">
                        <span class="list-title" x-text="list.title"></span>
                        <span class="list-count" x-text="list.cards.length"></span>
                    </div>

                    <div class="cards-container" :data-list-id="list.id" x-ref="cards">
                        <template x-for="card in list.cards" :key="card.id">
                            <div class="kanban-card" :data-card-id="card.id" @click="openCard(card)">
                                <template x-if="card.label_color">
                <span class="card-label"
                      :style="labelStyle(card.label_color)"
                      x-text="labelText(card.label_color)"></span>
                                </template>
                                <div class="card-title" x-text="card.title"></div>
                                <div class="card-foot">
                                    <template x-if="card.due_label">
                  <span class="card-due" :class="card.overdue && 'overdue'">
                    <i class="fa fa-clock"></i> <span x-text="card.due_label"></span>
                  </span>
                                    </template>
                                    <template x-if="!card.due_label"><span></span></template>
                                    <template x-if="card.assignee">
                                        <div class="card-avatar" :title="card.assignee" x-text="initials(card.assignee)"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button class="btn-add-card" @click="addCard(list)">
                        <i class="fa fa-plus"></i> Добавить карточку
                    </button>
                </div>
            </template>

            <button class="btn-add-list" @click="addList()">
                <i class="fa fa-plus"></i> Добавить список
            </button>
        </div>

        @include('boards.partials.card-modal')
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        const LABELS = {
            urgent: { bg: '#FCEBEB', fg: '#A32D2D', t: 'Срочно' },
            review: { bg: '#FAEEDA', fg: '#854F0B', t: 'На проверке' },
            done:   { bg: '#E1F5EE', fg: '#0F6E56', t: 'Готово' },
            sign:   { bg: '#EEEDFE', fg: '#3C3489', t: 'Подпись E-IMZO' },
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('kanban', (initialLists, boardId) => ({
                LABELS,
                boardId,
                lists: initialLists,

                // --- состояние модалки ---
                modalOpen: false,
                card: {},
                allUsers: [],
                feed: [],
                newComment: '',
                showLabelPicker: false,
                showMemberPicker: false,
                showDate: false,

                init() {
                    this.$nextTick(() => this.initSortables());
                },

                // --- helpers рендера ---
                labelStyle(key) { const l = LABELS[key]; return l ? `background:${l.bg};color:${l.fg}` : ''; },
                labelText(key)  { return LABELS[key]?.t ?? ''; },
                initials(name)  { return (name || '').split(' ').map(p => p[0]).slice(0,2).join(''); },
                autoGrow(e)     { e.target.style.height = 'auto'; e.target.style.height = e.target.scrollHeight + 'px'; },

                timeAgo(iso) {
                    if (!iso) return '';
                    const diff = (Date.now() - new Date(iso)) / 1000;
                    if (diff < 60)    return 'только что';
                    if (diff < 3600)  return Math.round(diff / 60) + ' мин назад';
                    if (diff < 86400) return Math.round(diff / 3600) + ' ч назад';
                    return Math.round(diff / 86400) + ' дн назад';
                },

                // --- сетевой helper ---
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

                // приводим карточку с сервера к форме для рендера
                normalize(c) {
                    const due = c.due_date ? c.due_date.substring(0, 10) : null;
                    return {
                        id: c.id,
                        title: c.title,
                        description: c.description,
                        due_date: due,
                        due_label: due ? new Date(due).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' }) : null,
                        overdue: due ? new Date(due) < new Date(new Date().toDateString()) : false,
                        label_color: c.label_color,
                        assignee: c.assignee?.name ?? c.assignee ?? null,
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
                    const toId   = +e.to.dataset.listId;

                    const from = this.lists.find(l => l.id === fromId);
                    const to   = this.lists.find(l => l.id === toId);
                    const idx  = from.cards.findIndex(c => c.id === cardId);
                    const [moved] = from.cards.splice(idx, 1);
                    to.cards.splice(e.newIndex, 0, moved);

                    const orderedIds = to.cards.map(c => c.id);
                    this.req(`/cards/${cardId}/move`, 'PATCH', { board_list_id: toId, ordered_ids: orderedIds })
                        .catch(() => location.reload());
                },

                onListMove() {
                    const orderedIds = [...document.querySelectorAll('.list-column')].map(c => +c.dataset.listId);
                    this.lists.sort((a, b) => orderedIds.indexOf(a.id) - orderedIds.indexOf(b.id));
                    this.req(`/lists/${orderedIds[0]}/move`, 'PATCH', { ordered_ids: orderedIds })
                        .catch(() => location.reload());
                },

                // ============ CRUD карточек / списков ============
                async addCard(list) {
                    const title = prompt('Название карточки');
                    if (!title) return;
                    const card = await this.req(`/lists/${list.id}/cards`, 'POST', { title });
                    list.cards.push(this.normalize(card));
                },

                async addList() {
                    const title = prompt('Название списка');
                    if (!title) return;
                    const list = await this.req(`/boards/${this.boardId}/lists`, 'POST', { title });
                    this.lists.push({ id: list.id, title: list.title, cards: [] });
                    this.refreshSortables();
                },

                // ============ МОДАЛКА ЗАДАЧИ ============
                toggleLabelPicker()  { this.showLabelPicker = !this.showLabelPicker; this.showMemberPicker = false; },
                toggleMemberPicker() { this.showMemberPicker = !this.showMemberPicker; this.showLabelPicker = false; if (this.showMemberPicker) this.loadUsers(); },
                focusDate()          { this.showDate = !this.showDate; },

                async loadUsers() {
                    if (this.allUsers.length) return;
                    this.allUsers = await this.req('/board-users', 'GET');
                },

                async openCard(card) {
                    console.log('openCard VERSION 3, cardId =', card.id);
                    const cardId = card.id;
                    this.modalOpen = true;
                    this.showLabelPicker = this.showMemberPicker = this.showDate = false;
                    this.newComment = '';

                    const listTitle = this.lists.find(l => l.cards.some(c => c.id === cardId))?.title;

                    this.card = { id: cardId, title: card.title, list_title: listTitle, members: [], checklists: [], description: card.description };
                    console.log('после присвоения this.card.id =', this.card.id);

                    try {
                        const full = await this.req(`/cards/${cardId}/full`, 'GET');
                        console.log('full загружен, full.id =', full.id);
                        this.card.title = full.title;
                        this.card.description = full.description;
                        this.card.label_color = full.label_color;
                        this.card.due_date = full.due_date ? full.due_date.substring(0,10) : null;
                        this.card.due_label = this.card.due_date ? new Date(this.card.due_date).toLocaleDateString('ru-RU',{day:'numeric',month:'short'}) : null;
                        this.card.members = full.members || [];
                        this.card.checklists = (full.checklists || []).map(cl => ({ ...cl, _newItem: '' }));
                        this.buildFeed(full);
                        console.log('всё собрано, this.card.id =', this.card.id);
                    } catch (e) {
                        console.warn('full failed:', e);
                    }
                },

                closeCard() { this.modalOpen = false; },

                buildFeed(full) {
                    const acts = (full.activities || []).map(a => ({
                        _key: 'a' + a.id, raw: a.created_at,
                        user: a.user?.name || '—', text: a.description, time: this.timeAgo(a.created_at),
                    }));
                    const comments = (full.comments || []).map(c => ({
                        _key: 'c' + c.id, raw: c.created_at,
                        user: c.user?.name || '—', text: c.body, time: this.timeAgo(c.created_at),
                    }));
                    this.feed = [...comments, ...acts].sort((a, b) => new Date(b.raw) - new Date(a.raw));
                },

                // синхронизация карточки в колонке после правок
                syncCardInList() {
                    const n = this.normalize(this.card);
                    this.lists.forEach(l => {
                        const i = l.cards.findIndex(c => c.id === this.card.id);
                        if (i !== -1) l.cards[i] = { ...l.cards[i], ...n };
                    });
                },

                // автосохранение полей
                async saveField(field) {
                    const payload = {};
                    if (field === 'title')       payload.title = this.card.title;
                    if (field === 'description') payload.description = this.card.description;
                    if (field === 'due_date')    payload.due_date = this.card.due_date || null;

                    const updated = await this.req(`/cards/${this.card.id}`, 'PATCH', payload);
                    const n = this.normalize(updated);
                    this.card.due_date = n.due_date;
                    this.card.due_label = n.due_label;
                    this.card.overdue = n.overdue;
                    this.syncCardInList();
                },

                async setLabel(key) {
                    this.card.label_color = key;
                    this.showLabelPicker = false;
                    await this.req(`/cards/${this.card.id}`, 'PATCH', { label_color: key });
                    this.syncCardInList();
                },

                // --- участники ---
                async addMember(u) {
                    this.card.members = await this.req(`/cards/${this.card.id}/members`, 'POST', { user_id: u.id });
                    this.showMemberPicker = false;
                },
                async removeMember(m) {
                    if (!confirm(`Убрать ${m.name}?`)) return;
                    this.card.members = await this.req(`/cards/${this.card.id}/members/${m.id}`, 'DELETE');
                },

                // --- чек-листы ---
                async addChecklist() {
                    const cl = await this.req(`/cards/${this.card.id}/checklists`, 'POST', {});
                    this.card.checklists.push({ ...cl, items: cl.items || [], _newItem: '' });
                },
                async addItem(cl) {
                    if (!cl._newItem || !cl._newItem.trim()) return;
                    const item = await this.req(`/checklists/${cl.id}/items`, 'POST', { title: cl._newItem });
                    cl.items.push(item);
                    cl._newItem = '';
                },
                async toggleItem(cl, item) {
                    const updated = await this.req(`/checklist-items/${item.id}`, 'PATCH', {});
                    item.is_done = updated.is_done;
                },
                async deleteItem(cl, item) {
                    await this.req(`/checklist-items/${item.id}`, 'DELETE');
                    cl.items = cl.items.filter(i => i.id !== item.id);
                },

                // --- комментарии ---
                async addComment() {
                    console.log('addComment, это тот же компонент? lists =', this.lists?.length, 'card =', JSON.parse(JSON.stringify(this.card)));
                    if (!this.newComment.trim()) return;
                    const c = await this.req(`/cards/${this.card.id}/comments`, 'POST', { body: this.newComment });
                    this.feed.unshift({ _key: 'c' + c.id, raw: c.created_at, user: c.user?.name || 'Вы', text: c.body, time: 'только что' });
                    this.newComment = '';
                },

                // --- удаление карточки ---
                async deleteCard() {
                    if (!confirm('Удалить карточку?')) return;
                    await this.req(`/cards/${this.card.id}`, 'DELETE');
                    this.lists.forEach(l => { l.cards = l.cards.filter(c => c.id !== this.card.id); });
                    this.modalOpen = false;
                },
            }));
        });
    </script>
@endpush
