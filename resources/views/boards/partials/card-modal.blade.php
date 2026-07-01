<div x-show="$store.task.open" x-cloak class="task-modal-backdrop"
     @click.self="$store.task.open = false" @keydown.escape.window="$store.task.open = false">
    <div class="task-modal" @click.stop>

        <div class="task-cover" :style="$store.task.card.cover_color ? `background:${$store.task.card.cover_color}` : ''">
            <span class="task-cover-pill" x-text="$store.task.card.list_title || 'Задача'"></span>
            <div class="task-cover-actions">
            <span class="task-saving" x-show="$store.task.saving" x-cloak style="color:rgba(255,255,255,.95)">
                <span class="brd-spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Сохранение…
            </span>
                <i class="fa fa-palette" @click="toggleCoverPicker()"></i>
                <button type="button" class="task-close" @click="$store.task.open = false">&times;</button>
            </div>
        </div>

        <div class="task-modal-body" x-show="$store.task.loading">
            <div class="task-main">
                <div class="sk-row">
                    <span class="skeleton sk-avatar"></span>
                    <span class="skeleton sk-line w-70" style="margin:0"></span>
                </div>
                <div class="sk-row" style="gap:7px">
                    <span class="skeleton sk-pill" style="width:80px;margin:0"></span>
                    <span class="skeleton sk-pill" style="width:70px;margin:0"></span>
                    <span class="skeleton sk-pill" style="width:75px;margin:0"></span>
                </div>
                <span class="skeleton sk-line w-40"></span>
                <span class="skeleton sk-block"></span>
                <span class="skeleton sk-line w-50"></span>
                <span class="skeleton sk-line w-90"></span>
                <span class="skeleton sk-line w-70"></span>
            </div>
            <div class="task-side">
                <span class="skeleton sk-line w-50"></span>
                <span class="skeleton sk-block" style="height:40px"></span>
                <div class="sk-row"><span class="skeleton sk-avatar"></span><span class="skeleton sk-line w-70" style="margin:0"></span></div>
                <div class="sk-row"><span class="skeleton sk-avatar"></span><span class="skeleton sk-line w-50" style="margin:0"></span></div>
            </div>
        </div>

        <div class="task-modal-body" x-show="!$store.task.loading" x-cloak>
            <div class="task-main">
                <div class="task-title-row">
                    <i class="fa fa-clipboard task-title-icon"></i>
                    <textarea class="task-title-input" x-model="$store.task.card.title" @blur="saveField('title')"
                              rows="1" @input="autoGrow($event)"></textarea>
                </div>

                <div class="task-actions">
                    <button class="task-action-btn" @click="togglePrioPicker()"><i class="fa fa-flag"></i> Приоритет</button>
                    <button class="task-action-btn" @click="toggleLabelPicker()"><i class="fa fa-tag"></i> Метки</button>

                    <button class="task-action-btn" @click="focusDate()"><i class="fa fa-clock"></i> Даты</button>
                    <button class="task-action-btn" @click="addChecklist()"><i class="fa fa-list-check"></i> Чек-лист</button>
                    <div style="position:relative; display:inline-block;">
                        <button class="task-action-btn" @click="toggleMemberPicker()">
                            <i class="fa fa-user-plus"></i> Участники
                        </button>

                        {{-- Поповер участников --}}
                        <div class="member-popover" x-show="showMemberPicker" x-cloak
                             @click.outside="showMemberPicker = false" style="top:calc(100% + 6px); left:0;">
                            <div class="member-popover-head">
                                Участники
                                <button class="member-popover-close" @click="showMemberPicker = false">&times;</button>
                            </div>
                            <div class="member-popover-body">
                                <input type="text" class="member-search" placeholder="Поиск участников"
                                       x-model="memberSearch" x-ref="memberSearchInput">
                                <div class="member-list-label">Участники доски</div>

                                <div class="member-list">
                                    <template x-for="u in filteredUsers()" :key="u.id">
                                        <button class="member-item" @click="toggleMember(u)">
                                            <span class="ava" :class="!u.name && 'empty'" x-text="initials(u.name)"></span>
                                            <span x-text="u.name"></span>
                                            <i class="fa fa-check check" x-show="isMember(u)"></i>
                                        </button>
                                    </template>

                                    <div class="member-empty" x-show="filteredUsers().length === 0">
                                        Ничего не найдено
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="task-picker" x-show="showPrioPicker" x-cloak>
                    <span class="task-meta-label mb-2 d-block">Приоритет</span>
                    <div class="task-prio-picker">
                        <button class="task-prio-opt low"    @click="setPriority('low')">Low</button>
                        <button class="task-prio-opt medium" @click="setPriority('medium')">Medium</button>
                        <button class="task-prio-opt high"   @click="setPriority('high')">High</button>
                        <button class="task-prio-opt none"   @click="setPriority(null)">No priority</button>
                    </div>
                </div>

                <div class="task-picker" x-show="showCoverPicker" x-cloak>
                    <span class="task-meta-label mb-2 d-block">Цвет обложки</span>
                    <div class="task-cover-grid">
                        <template x-for="c in coverColors" :key="c">
                            <div class="task-cover-opt" :class="$store.task.card.cover_color === c && 'active'"
                                 :style="`background:${c}`" @click="setCover(c)"></div>
                        </template>
                        <div class="task-cover-opt" style="background:#E4E0F0" @click="setCover(null)" title="Убрать"></div>
                    </div>
                </div>
                <div class="task-picker" x-show="showLabelPicker" x-cloak>
                    <span class="task-meta-label mb-2 d-block">Метки доски</span>
                    <div class="label-list">
                        <template x-for="lb in boardLabels" :key="lb.id">
                            <button class="label-row" @click="toggleLabel(lb)">
                                <span class="label-swatch" :style="`background:${lb.color}`"></span>
                                <span x-text="lb.name"></span>
                                <i class="fa fa-check ms-auto" x-show="cardHasLabel(lb)"></i>
                            </button>
                        </template>
                        <div class="member-empty" x-show="!boardLabels.length">
                            Меток нет. Добавьте их в настройках доски.
                        </div>
                    </div>
                </div>
                <div class="task-meta" x-show="$store.task.card.members?.length || $store.task.card.due_date || $store.task.card.label_color">
                    <div class="task-meta-block" x-show="$store.task.card.members?.length">
                        <span class="task-meta-label">Участники</span>
                        <div class="task-members">
                            <template x-for="m in $store.task.card.members" :key="m.id">
                                <div class="task-member" :title="m.name" @click="removeMember(m)" x-text="initials(m.name)"></div>
                            </template>
                        </div>
                    </div>
                    <div class="task-meta-block" x-show="$store.task.card.labels && $store.task.card.labels.length">
                        <span class="task-meta-label">Метки</span>
                        <div class="task-labels">
                            <template x-for="lb in $store.task.card.labels" :key="lb.id">
                                <span class="task-label-chip" :style="`background:${lb.color}20;color:${lb.color}`" x-text="lb.name"></span>
                            </template>
                        </div>
                    </div>
                    <div class="task-meta-block" x-show="$store.task.card.priority">
                        <span class="task-meta-label">Приоритет</span>
                        <span class="task-prio" :class="$store.task.card.priority" x-text="prioLabel($store.task.card.priority)"></span>
                    </div>
                    <div class="task-meta-block" x-show="$store.task.card.label_color">
                        <span class="task-meta-label">Метка</span>
                        <span class="card-label" :style="labelStyle($store.task.card.label_color)" x-text="labelText($store.task.card.label_color)"></span>
                    </div>
                    <div class="task-meta-block" x-show="$store.task.card.due_date">
                        <span class="task-meta-label">Срок</span>
                        <span class="task-due-pill" :class="$store.task.card.overdue && 'overdue'" x-text="$store.task.card.due_label"></span>
                    </div>
                </div>

                <div class="task-picker" x-show="showLabelPicker" x-cloak>
                    <span class="task-meta-label mb-2 d-block">Выбрать метку</span>
                    <div class="task-label-grid">
                        <template x-for="(l, key) in LABELS" :key="key">
                            <button class="task-label-opt" :style="`background:${l.bg};color:${l.fg}`"
                                    @click="setLabel(key)" x-text="l.t"></button>
                        </template>
                        <button class="task-label-opt" style="background:#F1EFE8;color:#5F5E5A" @click="setLabel(null)" x-show="isAdmin">Без метки</button>
                    </div>
                </div>

                <div class="task-picker" x-show="showMemberPicker" x-cloak>
                    <span class="task-meta-label mb-2 d-block">Добавить участника</span>
                    <template x-for="u in allUsers" :key="u.id">
                        <button class="task-user-opt" @click="addMember(u)">
                            <span class="task-member sm" x-text="initials(u.name)"></span>
                            <span x-text="u.name"></span>
                        </button>
                    </template>
                </div>

                <div class="task-section" x-show="showDate" x-cloak>
                    <input type="date" class="form-control form-control-sm" style="max-width:200px"
                           x-model="$store.task.card.due_date" @change="saveField('due_date')">
                </div>

                <div class="task-section">
                    <div class="task-section-head">
                        <span><i class="fa fa-align-left"></i>Описание</span>
                    </div>
                    <textarea class="task-desc" x-model="$store.task.card.description" @blur="saveField('description')"
                              placeholder="Добавьте описание задачи…" rows="3" @input="autoGrow($event)"></textarea>
                </div>

                <template x-for="cl in $store.task.card.checklists" :key="cl.id">
                    <div class="task-section">
                        <div class="task-section-head">
                            <span><i class="fa fa-list-check me-2"></i><span x-text="cl.title"></span></span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="task-cl-count" x-text="`${cl.items.filter(i=>i.is_done).length}/${cl.items.length}`"></span>
                                <button class="checklist-del" x-show="canManageChecklists" @click="deleteChecklist(cl)" title="Удалить чек-лист">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="task-progress">
                            <div class="task-progress-bar"
                                 :style="`width:${cl.items.length ? Math.round(cl.items.filter(i=>i.is_done).length/cl.items.length*100) : 0}%`"></div>
                        </div>
                        <template x-for="item in cl.items" :key="item.id">
                            <div class="task-cl-item" :class="item.is_done && 'done'">
                                <i class="fa" :class="item.is_done ? 'fa-square-check' : 'fa-square'" @click="toggleItem(cl, item)"></i>
                                <span x-text="item.title"></span>
                                <button class="task-cl-del" @click="deleteItem(cl, item)"><i class="fa fa-trash"></i></button>
                            </div>
                        </template>
                        <div class="task-cl-add">
                            <input type="text" class="form-control form-control-sm" placeholder="Новый пункт…"
                                   x-model="cl._newItem" @keydown.enter="addItem(cl)">
                            <button class="btn btn-sm task-add-mini" @click="addItem(cl)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </template>

                <div class="task-delete-row" x-show="isAdmin">
                    <button class="btn btn-sm btn-outline-danger" @click="deleteCard()">
                        <i class="fa fa-trash me-1"></i> Удалить карточку
                    </button>
                </div>
            </div>

            <div class="task-side">
                <div class="task-section-head mb-3"><span><i class="fa fa-comments me-2"></i>Комментарии и события</span></div>
                <div class="task-comment-box">
                    <input type="text" class="task-comment-input" placeholder="Напишите комментарий…"
                           x-model="newComment"
                           @keydown.enter="addComment()">
                    <button class="task-comment-send" :disabled="!newComment.trim()" @click="addComment()">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </div>
                <div class="task-feed">
                    <template x-for="a in $store.task.feed" :key="a._key">
                        <div class="task-feed-item">
                            <div class="task-member sm" :style="a.color ? `background:${a.color}` : ''" x-text="initials(a.user)"></div>
                            <div class="task-feed-text">
                                <span class="task-feed-user" x-text="a.user"></span>
                                <span x-text="' ' + a.text"></span>
                                <div class="task-feed-time" x-text="a.time"></div>
                            </div>
                        </div>
                    </template>
                    <p class="text-muted small" x-show="!$store.task.feed.length">Пока нет событий</p>
                </div>
            </div>

        </div>
    </div>
</div>
