@extends('layouts.app')

@section('content')
    @if(auth()->user()->hasKanbanAccess())

        <div class="container-fluid py-3" x-data="boardsIndex({{ auth()->user()->canManageBoards() ? 'true' : 'false' }})">

            <div class="board-header d-flex align-items-center justify-content-between px-4 py-4 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="board-icon-box"><i class="fa fa-table-columns text-white fs-4"></i></div>
                    <div>
                        <h5 class="text-white mb-0 fw-bold">Проекты</h5>
                        <small class="text-white-50">Управление задачами и документами по проектам</small>
                    </div>
                </div>
                <button type="button" class="btn btn-light btn-sm fw-medium" @click="open = true" x-show="isAdmin">
                    <i class="fa fa-plus me-1"></i> Новая доска
                </button>
            </div>

            <div class="boards-grid">
                @forelse($boards as $board)
                    @php
                        $color = $board->color ?: '#7C3AED';
                        $ownerInitials = \Illuminate\Support\Str::of($board->owner?->name ?? $board->owner?->name ?? '')
                            ->explode(' ')->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))->take(2)->implode('');
                    @endphp
                    <a href="{{ route('boards.show', $board) }}" class="board-tile">
                        <div class="board-tile-bar" style="background:linear-gradient(90deg,{{ $color }},{{ $color }}99)"></div>
                        <div class="board-tile-body">
                            <div class="board-tile-head">
                                <div class="board-tile-icon" style="background:{{ $color }}1A">
                                    <i class="fa fa-table-columns" style="color:{{ $color }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="board-tile-title">{{ $board->title }}</div>
                                    @if($board->organization && auth()->user()->hasRole(['Super_Admin', 'IT_Admin']))
                                        <div class="board-tile-org">
                                            <i class="fa fa-building"></i> {{ $board->organization->lang($board->org_id) }}
                                        </div>
                                    @endif
                                    <div class="board-tile-sub">Обновлено {{ $board->updated_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="board-tile-foot">
                                <div class="board-tile-stats">
                                    <span class="board-tile-stat"><i class="fa fa-list" style="color:{{ $color }}"></i>{{ $board->lists_count }} списков</span>
                                    <span class="board-tile-stat"><i class="fa fa-square-check" style="color:{{ $color }}"></i>{{ $board->cards_total ?? 0 }} задач</span>
                                </div>
                                @if($ownerInitials)
                                    <div class="board-tile-avatars">
                                        <div class="av" style="background:{{ $color }}" title="{{ $board->owner?->name }}">{{ $ownerInitials }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="board-empty w-100">
                        <i class="fa fa-table-columns"></i>
                        <p class="mb-2">Пока нет ни одной доски</p>
                    </div>
                @endforelse

                @if(empty($boards))
                    <button type="button" class="board-tile-new" @click="open = true" x-show="isAdmin">
                        <i class="fa fa-plus"></i>
                        <span>Новая доска</span>
                    </button>
                @endif
            </div>

            {{-- Модалка создания доски --}}
            <div x-show="open" x-cloak class="create-modal-backdrop"
                 @click.self="open = false" @keydown.escape.window="open = false">
                <div class="create-modal" @click.stop>
                    <div class="create-modal-head">
                        <h6><i class="fa fa-plus me-2"></i>Новая доска</h6>
                        <button type="button" class="close" @click="open = false">&times;</button>
                    </div>
                    <div class="create-modal-body">
                        <div class="create-field">
                            <label>Название</label>
                            <input type="text" class="form-control" x-model="title"
                                   placeholder="Например, Документооборот Q2" @keydown.enter="submit()" x-ref="titleInput">
                        </div>

                        <div class="create-field">
                            <label>Цвет</label>
                            <div class="create-color-grid">
                                <template x-for="c in colors" :key="c">
                                    <div class="create-color-opt" :class="color === c && 'active'"
                                         :style="`background:${c}`" @click="color = c"></div>
                                </template>
                            </div>
                        </div>

                        <div class="create-field" style="position:relative;">
                            <label>Руководитель</label>

                            {{-- Кнопка-триггер --}}
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

                            {{-- Поповер выбора --}}
                            <div class="member-popover" x-show="managerOpen" x-cloak
                                 @click.outside="managerOpen = false"
                                 style="top:calc(100% + 6px); left:0; right:0; width:auto;">
                                <div class="member-popover-head">
                                    Руководитель
                                    <button class="member-popover-close" @click="managerOpen = false">&times;</button>
                                </div>
                                <div class="member-popover-body">
                                    <input type="text" class="member-search" placeholder="Поиск сотрудников"
                                           x-model="managerSearch" x-ref="managerSearchInput">
                                    <div class="member-list-label">Сотрудники</div>
                                    <div class="member-list">
                                        {{-- Опция "не назначен" --}}
                                        <button class="member-item" @click="pickManager(null)">
                                            <span class="ava empty"><i class="fa fa-user-slash"></i></span>
                                            <span>Не назначен</span>
                                            <i class="fa fa-check check" x-show="!managerId"></i>
                                        </button>

                                        <template x-for="u in filteredManagerUsers()" :key="u.id">
                                            <button class="member-item" @click="pickManager(u)">
                                                <span class="ava" x-text="initials(u.name)"></span>
                                                <span x-text="u.name"></span>
                                                <i class="fa fa-check check" x-show="managerId == u.id"></i>
                                            </button>
                                        </template>

                                        <div class="member-empty" x-show="filteredManagerUsers().length === 0">
                                            Ничего не найдено
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="create-field">
                            <label>Описание (необязательно)</label>
                            <textarea class="form-control" rows="2" x-model="description"
                                      placeholder="Коротко о доске"></textarea>
                        </div>

                        <div class="create-modal-foot">
                            <button type="button" class="btn btn-link text-muted text-decoration-none" @click="open = false">Отмена</button>
                            <button type="button" class="btn text-white fw-medium" style="background:#7C3AED"
                                    :disabled="!title.trim() || saving" @click="submit()">
                                <span x-show="!saving"><i class="fa fa-check me-1"></i> Создать</span>
                                <span x-show="saving" x-cloak><span class="brd-spinner me-1"></span> Создаём…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('boardsIndex', (isAdmin) => ({
                isAdmin,
                open: false,
                saving: false,
                title: '',
                description: '',
                color: '#7C3AED',
                managerId: '',
                managerOpen: false,
                managerSearch: '',
                users: [],
                colors: ['#7C3AED', '#F0997B', '#ED93B1', '#97C459', '#85B7EB', '#FAC775', '#1D9E75', '#E0A040'],

                init() {
                    this.$watch('open', (v) => {
                        if (v) {
                            this.$nextTick(() => this.$refs.titleInput?.focus());
                            this.loadUsers();
                        }
                    });
                    this.$watch('managerOpen', (v) => {
                        if (v) this.$nextTick(() => this.$refs.managerSearchInput?.focus());
                    });
                },

                async loadUsers() {
                    if (this.users.length) return;
                    try {
                        const r = await fetch('/board-users-all', { headers: { 'Accept': 'application/json' } });
                        this.users = await r.json();
                    } catch (e) {}
                },
                initials(name) {
                    if (!name) return '?';
                    const parts = name.trim().split(/[\s_]+/);
                    return parts.map(p => p[0]).slice(0, 2).join('').toUpperCase();
                },

                selectedManager() {
                    return this.users.find(u => u.id == this.managerId) || null;
                },

                filteredManagerUsers() {
                    const q = this.managerSearch.trim().toLowerCase();
                    if (!q) return this.users;
                    return this.users.filter(u => (u.name || '').toLowerCase().includes(q));
                },

                pickManager(u) {
                    this.managerId = u ? u.id : '';
                    this.managerOpen = false;        // одиночный выбор — закрываем
                    this.managerSearch = '';
                },

                async submit() {
                    if (!this.title.trim() || this.saving) return;
                    this.saving = true;
                    try {
                        const r = await fetch('{{ route('boards.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                title: this.title,
                                color: this.color,
                                description: this.description,
                                manager_id: this.managerId || null,
                            }),
                        });
                        if (!r.ok) {
                            const t = await r.text();
                            alert('Ошибка ' + r.status + ': ' + t.slice(0, 300));
                            this.saving = false;
                            return;
                        }
                        const board = await r.json();
                        window.location.href = '/boards/' + board.id;
                    } catch (e) {
                        alert('Сеть: ' + e);
                        this.saving = false;
                    }
                },
            }));
        });
    </script>
@endpush
