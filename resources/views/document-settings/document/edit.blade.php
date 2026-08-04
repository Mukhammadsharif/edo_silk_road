@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-edit"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit') }} {{ __('menu.doc_types') }}</h1>
                        <p>{{ __('menu.doc_types') }} / {{ __('action.edit') }}</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('document-type.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.doc_types') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Two Column Grid -->
            <div class="edit-grid-layout">

                <!-- Left Column - Main Edit Form -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="file-edit"></i>
                            <span>{{ __('field.edit_document_type') }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('document-type.update', $model->id) }}" class="modern-form" style="padding: 2rem;" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-grid" style="grid-template-columns: 1fr;">
                            <!-- Title -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name_uz') }}
                                    <span class="text-red">*</span>
                                </label>
                                <input type="text"
                                       name="title"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.title') }}"
                                       value="{{ $model->title }}"
                                       required>
                            </div>

                            <!-- Parent ID -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.parent_id') }}
                                </label>
                                <select id="parent_id" class="form-control-modern select2" name="parent_id">
                                    <option value="0">{{ __('field.empty') }}</option>
                                    @foreach($journalTypes as $key => $value)
                                        <option value="{{ $value->id }}" {{ $value->id === $model->parent_id ? 'selected' : '' }}>
                                            {{ $key+1 }}. {{ $value->lang($value->lang_id) }} ({{ $value->title }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Language -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="languages"></i>
                                    {{ __('field.doc_lang') }}
                                    <span class="text-red">*</span>
                                </label>
                                <select id="lag_id" class="form-control-modern select2" name="lang_id" required>
                                    <option value="">{{ __('field.empty') }}</option>
                                    @foreach($languages as $key => $value)
                                        <option value="{{ $value->id }}" {{ $value->id === $model->lang_id ? 'selected' : '' }}>
                                            {{ $key+1 }}. {{ __($value->group.'.'.$value->key) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Document Code -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="code"></i>
                                    {{ __('field.code') }}
                                    <span class="text-red">*</span>
                                </label>
                                <input id="doc_code"
                                       type="text"
                                       name="doc_code"
                                       class="form-control-modern font-mono"
                                       value="{{ $model->doc_code }}"
                                       required>
                            </div>

                            <!-- Status -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </label>
                                <div class="radio-group-modern">
                                    <div class="radio-option-modern success">
                                        <input type="radio"
                                               id="edit_status1"
                                               name="status"
                                               value="active"
                                            {{ $model->status ? 'checked' : '' }}>
                                        <label for="edit_status1">{{ __('field.active') }}</label>
                                    </div>
                                    <div class="radio-option-modern danger">
                                        <input type="radio"
                                               id="edit_status2"
                                               name="status"
                                               value="inactive"
                                            {{ !$model->status ? 'checked' : '' }}>
                                        <label for="edit_status2">{{ __('field.delete') }}</label>
                                    </div>
                                </div>
                            </div>

                            {{-- ═══ ШАБЛОН OnlyOffice (v63, опционально) ═══
                                 docx-шаблон типа (например, заявка на закуп): при
                                 создании документа OnlyOffice откроет ЕГО КОПИЮ
                                 вместо пустого листа. Хранится на FTP
                                 (journal_type_templates). --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="file-text"></i>
                                    Шаблон OnlyOffice (docx)
                                </label>
                                @if(!empty($typeTemplate))
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;
                                                padding:8px 12px; border:1px solid rgba(15,23,42,0.08);
                                                border-radius:10px; background:#f8fafc; font-size:12.5px;">
                                        <i data-lucide="file-check" style="width:15px;height:15px;color:#794DFD;"></i>
                                        <span style="flex:1;">{{ $typeTemplate->orig_name }}</span>
                                        <label style="display:flex; align-items:center; gap:5px; margin:0;
                                                      font-size:12px; color:#ef4444; cursor:pointer;">
                                            <input type="checkbox" name="delete_template" value="1"> удалить
                                        </label>
                                    </div>
                                @endif
                                <input type="file" name="type_template" accept=".docx"
                                       class="form-control-modern">
                                <small style="font-size:11.5px; color:#94a3b8;">
                                    Загрузка нового файла заменит текущий шаблон
                                </small>
                            </div>

                            {{-- ═══ МЕТКА «Внешаблонные документы (free-sign)» ═══
                                 Предзаполняется из free_sign_journal_types ($isFreeSignType,
                                 передаёт edit()). В update(): отмечена -> firstOrCreate,
                                 снята -> delete записи. Тип распознаётся модулем free-sign
                                 независимо от id и doc_code (v59). --}}
                            <div class="form-group-modern">
                                <label class="fs-type-toggle">
                                    <input type="checkbox" name="is_free_sign" value="1"
                                           {{ !empty($isFreeSignType) ? 'checked' : '' }}>
                                    <span class="fs-type-toggle__box"><i data-lucide="check"></i></span>
                                    <span class="fs-type-toggle__text">
                                        <strong>Внешаблонные документы (free-sign)</strong>
                                        <small>Загрузка готовых PDF с разметкой подписей</small>
                                    </span>
                                </label>
                            </div>

                            <style>
                                .fs-type-toggle { display:flex; align-items:flex-start; gap:12px; cursor:pointer;
                                    padding:14px 16px; border:1px solid rgba(15,23,42,0.06); border-radius:12px;
                                    background:#fff; transition:border-color .15s; margin:0; }
                                .fs-type-toggle:hover { border-color:rgba(121,77,253,0.35); }
                                .fs-type-toggle input { position:absolute; opacity:0; pointer-events:none; }
                                .fs-type-toggle__box { flex:0 0 auto; width:22px; height:22px; margin-top:1px;
                                    border:2px solid #cbd5e1; border-radius:6px; display:flex; align-items:center;
                                    justify-content:center; transition:all .15s; }
                                .fs-type-toggle__box i { width:14px; height:14px; color:#fff; opacity:0; }
                                .fs-type-toggle input:checked + .fs-type-toggle__box {
                                    background:#794DFD; border-color:#794DFD; }
                                .fs-type-toggle input:checked + .fs-type-toggle__box i { opacity:1; }
                                .fs-type-toggle__text { display:flex; flex-direction:column; gap:2px; }
                                .fs-type-toggle__text strong { font-size:14px; color:#1e293b; font-weight:600; }
                                .fs-type-toggle__text small { font-size:12.5px; color:#94a3b8; line-height:1.45; }
                            </style>

                            <!-- Order Number -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="list-ordered"></i>
                                    {{ __('field.order_number') }}
                                </label>
                                <input type="number"
                                       name="order_number"
                                       class="form-control-modern"
                                       value="{{ $model->order_number ?? 0 }}"
                                       min="0"
                                       required>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('document-type.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column - Execution Steps -->
                <div class="execution-steps-card">
                    <form method="POST" action="{{ route('document.step.store') }}">
                        @csrf
                        <input name="journal_type_id" value="{{ $model->id }}" hidden>

                        <div class="execution-steps-header">
                            <i data-lucide="workflow"></i>
                            <h3>{{ __('field.order') }}</h3>
                        </div>

                        <div class="execution-steps-body">
                            <ul class="steps-list-modern">
                                @foreach($execution_roles as $key => $execution_role)
                                    <li class="step-item-modern step-{{ strtolower($listGroupItemColors[$key] ?? 'primary') }}">
                                        @if($execution_role->doc_code && trim($execution_role->doc_code) === 'RESOL')
                                            <input class="step-checkbox-modern"
                                                   type="checkbox"
                                                   id="{{ $execution_role->id }}"
                                                   checked
                                                   disabled>
                                            <input type="hidden"
                                                   name="{{ $execution_role->doc_code }}"
                                                   value="{{ $execution_role->id }}">
                                        @else
                                            <input class="step-checkbox-modern"
                                                   type="checkbox"
                                                   id="{{ $execution_role->id }}"
                                                   value="{{ $execution_role->id }}"
                                                   name="{{ $execution_role->doc_code }}"
                                                {{ in_array($execution_role->id, $journal_exec_steps->pluck('exec_role_id')->toArray()) ? 'checked' : '' }}>
                                        @endif
                                        <label for="{{ $execution_role->id }}" class="step-label-modern">
                                            {{ $key + 1 }}. {{ $execution_role->title }}
                                            @if(!empty($listGroupDescriptions[$key]))
                                                - <i>{{ $listGroupDescriptions[$key] }}</i>
                                            @endif
                                        </label>
                                    </li>
                                @endforeach
                            </ul>

                            <hr class="divider-modern">

                            <!-- Control Section -->
                            <div class="control-section-modern">
                                <div class="control-option-modern">
                                    <input id="control"
                                           type="checkbox"
                                           name="control"
                                           @if($model->controlStatus && $model->controlStatus->status == 1) checked @endif>
                                    <label for="control">
                                        <strong>{{ __('control.control') }}</strong>
                                        <i>Rahbar rezolyutsiyasi uchun fishka (ijrochilarni biriktirish, muddat belgilash va ijro turini tanlash) kirituvchi xodim</i>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div style="padding: 1.5rem; border-top: 2px solid #f1f5f9; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn-modern btn-primary" style="width: auto;">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- Roles Grid -->
            @if($journal_exec_steps->count())
                <div class="roles-grid-layout">
                    @foreach($journal_exec_steps as $journal_exec_step)
                        <div class="roles-card-modern">
                            <form method="POST" action="{{ route('document.step.roles', ['step_id' => $journal_exec_step->id, 'j_type_id' => $model->id]) }}">
                                @csrf
                                @method('POST')

                                <div class="filters-header">
                                    <div class="filters-title">
                                        <i data-lucide="users"></i>
                                        <span>{{ $journal_exec_step->executionRole->lang($journal_exec_step->executionRole->lang_id) }}</span>
                                    </div>
                                </div>

                                <div class="roles-list-modern">
                                    @foreach($roles as $key => $item)
                                        <div class="role-item-modern">
                                            <input type="checkbox"
                                                   name="role_ids[]"
                                                   value="{{ $item->id }}"
                                                   id="role_{{ $journal_exec_step->id }}_{{ $item->id }}"
                                                   @if(in_array($item->id, $journal_exec_step->executionStepRolesIds->pluck('role_id')->toArray())) checked @endif>
                                            <label for="role_{{ $journal_exec_step->id }}_{{ $item->id }}">
                                                {{ $key+1 }}. {{ $item->lang($item->id) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- ═══ ФИКСИРОВАННЫЙ МАРШРУТ (v61, опционально) ═══
                                     Если для шага указаны должности/сотрудники — при
                                     создании документа этого типа участники подставятся
                                     АВТОМАТИЧЕСКИ (выбор скрыт), документ сразу пойдёт
                                     по цепочке. Пусто — обычное поведение по ролям. --}}
                                @php
                                    $stTargets = $fsStepTargets[$journal_exec_step->id] ?? collect();
                                    $stPostIds = $stTargets->where('target_type', 'post')->pluck('target_id')->map(fn($v)=>(int)$v)->all();
                                    $stUserIds = $stTargets->where('target_type', 'user')->pluck('target_id')->map(fn($v)=>(int)$v)->all();
                                @endphp
                                <div style="padding: 1rem 1.5rem; border-top: 2px solid #f1f5f9;">
                                    <div style="font-size:13px; font-weight:600; color:#1e293b; margin-bottom:4px;">
                                        <i data-lucide="route" style="width:14px;height:14px;"></i>
                                        Фиксированный маршрут (опционально)
                                    </div>
                                    <div style="font-size:12px; color:#94a3b8; margin-bottom:10px;">
                                        Укажите сотрудников — при создании документа
                                        участники шага подставятся автоматически, без выбора
                                    </div>
                                    {{-- v61.5: панель ДОЛЖНОСТЕЙ временно скрыта по решению
                                         Jasur — выбор только по сотрудникам. Бэкенд целей
                                         'post' не тронут (существующие данные и резолв
                                         работают); вернуть панель = восстановить блок из
                                         истории версий и grid на 1fr 1fr. --}}
                                    <div style="display:grid; grid-template-columns:1fr; gap:12px;">
                                        <div class="fst-picker">
                                            <div class="fst-picker__head">
                                                <span>Сотрудники</span>
                                                <span class="fst-picker__count" data-count>0</span>
                                            </div>
                                            <input type="text" class="fst-picker__search" placeholder="Поиск сотрудника...">
                                            <div class="fst-picker__list">
                                                @foreach($fsEmployees as $emp)
                                                    <label class="fst-picker__item">
                                                        <input type="checkbox" name="target_users[]"
                                                               value="{{ $emp->user_id }}"
                                                               @if(in_array((int) $emp->user_id, $stUserIds)) checked @endif>
                                                        <span class="fst-picker__box"><i data-lucide="check"></i></span>
                                                        <span class="fst-picker__label">
                                                            {{ $emp->personal?->fullName() ?? ('#'.$emp->user_id) }}
                                                            <small>{{ $emp->userPost?->lang($emp->userPost?->id) ?? '' }}</small>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div style="font-size:11.5px; color:#94a3b8; margin-top:6px;">
                                        Снять все отметки — шаг снова обычный (кандидаты по ролям)
                                    </div>
                                </div>

                                <div style="padding: 1.5rem; border-top: 2px solid #f1f5f9; display: flex; justify-content: flex-end;">
                                    <input type="hidden" name="status" value="{{ $journal_exec_step->exec_status ?? 0 }}">
                                    <button type="submit" class="btn-modern btn-primary" style="width: auto;">
                                        <i data-lucide="save"></i>
                                        {{ __('action.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <script src="{{ asset('/assets/js/js-sortable.js') }}"></script>
    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            let sortableElement = document.getElementById('sortable');
            if (sortableElement) {
                new Sortable(sortableElement, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                });
            }
        });
    </script>

    {{-- ═══ v61.2: панели выбора фиксированного маршрута ═══ --}}
    <style>
        .fst-picker { border:1px solid rgba(15,23,42,0.08); border-radius:12px; background:#fff; overflow:hidden; }
        .fst-picker__head { display:flex; justify-content:space-between; align-items:center;
            padding:8px 12px; font-size:12.5px; font-weight:600; color:#1e293b;
            background:#f8fafc; border-bottom:1px solid rgba(15,23,42,0.06); }
        .fst-picker__count { min-width:22px; height:20px; padding:0 6px; border-radius:10px;
            background:#e2e8f0; color:#64748b; font-size:11.5px; display:flex;
            align-items:center; justify-content:center; transition:all .15s; }
        .fst-picker__count.is-active { background:#794DFD; color:#fff; }
        .fst-picker__search { width:100%; border:none; border-bottom:1px solid rgba(15,23,42,0.06);
            padding:8px 12px; font-size:12.5px; outline:none; }
        .fst-picker__search:focus { background:rgba(121,77,253,0.03); }
        .fst-picker__list { max-height:180px; overflow-y:auto; padding:4px; }
        .fst-picker__item { display:flex; align-items:flex-start; gap:8px; padding:6px 8px;
            border-radius:8px; cursor:pointer; margin:0; transition:background .1s; }
        .fst-picker__item:hover { background:rgba(121,77,253,0.06); }
        .fst-picker__item.is-hidden { display:none; }
        .fst-picker__item input { position:absolute; opacity:0; pointer-events:none; }
        .fst-picker__box { flex:0 0 auto; width:17px; height:17px; margin-top:1px;
            border:2px solid #cbd5e1; border-radius:5px; display:flex; align-items:center;
            justify-content:center; transition:all .12s; }
        .fst-picker__box i { width:11px; height:11px; color:#fff; opacity:0; }
        .fst-picker__item input:checked + .fst-picker__box { background:#794DFD; border-color:#794DFD; }
        .fst-picker__item input:checked + .fst-picker__box i { opacity:1; }
        .fst-picker__item input:checked ~ .fst-picker__label { color:#794DFD; font-weight:600; }
        .fst-picker__label { font-size:12.5px; color:#334155; line-height:1.35;
            display:flex; flex-direction:column; }
        .fst-picker__label small { font-size:11px; color:#94a3b8; font-weight:400; }
    </style>
    <script>
        /* Панели «Фиксированный маршрут»: живой поиск + счётчик выбранного.
           Делегирование на document — панелей несколько (по одной паре на
           каждый шаг маршрута), обработчики общие. */
        (function () {
            function refreshCount(picker) {
                var n = picker.querySelectorAll('input[type="checkbox"]:checked').length;
                var badge = picker.querySelector('[data-count]');
                if (badge) {
                    badge.textContent = n;
                    badge.classList.toggle('is-active', n > 0);
                }
            }

            document.addEventListener('input', function (e) {
                if (!e.target.classList || !e.target.classList.contains('fst-picker__search')) return;
                var q = e.target.value.trim().toLowerCase();
                e.target.closest('.fst-picker').querySelectorAll('.fst-picker__item').forEach(function (item) {
                    item.classList.toggle('is-hidden', q !== '' && item.textContent.toLowerCase().indexOf(q) === -1);
                });
            });

            document.addEventListener('change', function (e) {
                var picker = e.target.closest && e.target.closest('.fst-picker');
                if (picker) refreshCount(picker);
            });

            document.querySelectorAll('.fst-picker').forEach(refreshCount);   // стартовые счётчики
            if (typeof lucide !== 'undefined') lucide.createIcons();
        })();
    </script>
@endsection
