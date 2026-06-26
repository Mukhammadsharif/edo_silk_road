<div x-show="modalOpen" x-cloak class="task-modal-backdrop" @click.self="closeCard()" @keydown.escape.window="closeCard()">
    <div class="task-modal" @click.stop>

        <div class="task-modal-head">
            <span class="task-list-pill" x-text="card.list_title || 'Задача'"></span>
            <button type="button" class="task-close" @click="closeCard()">&times;</button>
        </div>

        <div class="task-modal-body">

            <div class="task-main">
                <div class="task-title-row">
                    <i class="fa fa-clipboard task-title-icon"></i>
                    <textarea class="task-title-input" x-model="card.title" @blur="saveField('title')"
                              rows="1" @input="autoGrow($event)"></textarea>
                </div>

                <div class="task-actions">
                    <button class="task-action-btn" @click="toggleLabelPicker()"><i class="fa fa-tag"></i> Метки</button>
                    <button class="task-action-btn" @click="focusDate()"><i class="fa fa-clock"></i> Даты</button>
                    <button class="task-action-btn" @click="addChecklist()"><i class="fa fa-list-check"></i> Чек-лист</button>
                    <button class="task-action-btn" @click="toggleMemberPicker()"><i class="fa fa-users"></i> Участники</button>
                </div>

                <div class="task-meta" x-show="card.members?.length || card.due_date || card.label_color">
                    <div class="task-meta-block" x-show="card.members?.length">
                        <span class="task-meta-label">Участники</span>
                        <div class="task-members">
                            <template x-for="m in card.members" :key="m.id">
                                <div class="task-member" :title="m.name" @click="removeMember(m)" x-text="initials(m.name)"></div>
                            </template>
                        </div>
                    </div>
                    <div class="task-meta-block" x-show="card.label_color">
                        <span class="task-meta-label">Метка</span>
                        <span class="card-label" :style="labelStyle(card.label_color)" x-text="labelText(card.label_color)"></span>
                    </div>
                    <div class="task-meta-block" x-show="card.due_date">
                        <span class="task-meta-label">Срок</span>
                        <span class="task-due-pill" :class="card.overdue && 'overdue'" x-text="card.due_label"></span>
                    </div>
                </div>

                <div class="task-picker" x-show="showLabelPicker" x-cloak>
                    <span class="task-meta-label mb-2 d-block">Выбрать метку</span>
                    <div class="task-label-grid">
                        <template x-for="(l, key) in LABELS" :key="key">
                            <button class="task-label-opt" :style="`background:${l.bg};color:${l.fg}`"
                                    @click="setLabel(key)" x-text="l.t"></button>
                        </template>
                        <button class="task-label-opt" style="background:#F1EFE8;color:#5F5E5A" @click="setLabel(null)">Без метки</button>
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
                           x-model="card.due_date" @change="saveField('due_date')">
                </div>

                <div class="task-section">
                    <div class="task-section-head">
                        <span><i class="fa fa-align-left me-2"></i>Описание</span>
                    </div>
                    <textarea class="task-desc" x-model="card.description" @blur="saveField('description')"
                              placeholder="Добавьте описание задачи…" rows="3" @input="autoGrow($event)"></textarea>
                </div>

                <template x-for="cl in card.checklists" :key="cl.id">
                    <div class="task-section">
                        <div class="task-section-head">
                            <span><i class="fa fa-list-check me-2"></i><span x-text="cl.title"></span></span>
                            <span class="task-cl-count" x-text="`${cl.items.filter(i=>i.is_done).length}/${cl.items.length}`"></span>
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

                <div class="task-delete-row">
                    <button class="btn btn-sm btn-outline-danger" @click="deleteCard()"><i class="fa fa-trash me-1"></i> Удалить карточку</button>
                </div>
            </div>

            <div class="task-side">
                <div class="task-section-head mb-3"><span><i class="fa fa-comments me-2"></i>Комментарии и события</span></div>
                <div class="task-comment-box">
                    <input type="text" class="form-control form-control-sm" placeholder="Напишите комментарий…"
                           x-model="newComment" @keydown.enter="addComment()">
                </div>
                <div class="task-feed">
                    <template x-for="a in feed" :key="a._key">
                        <div class="task-feed-item">
                            <div class="task-member sm" :style="a.color ? `background:${a.color}` : ''" x-text="initials(a.user)"></div>
                            <div class="task-feed-text">
                                <span class="task-feed-user" x-text="a.user"></span>
                                <span x-text="' ' + a.text"></span>
                                <div class="task-feed-time" x-text="a.time"></div>
                            </div>
                        </div>
                    </template>
                    <p class="text-muted small" x-show="!feed.length">Пока нет событий</p>
                </div>
            </div>

        </div>
    </div>
</div>
