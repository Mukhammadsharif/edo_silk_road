@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/protocol-types-modern.css') }}" rel="stylesheet">

    <link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
    <style>
        /* Селект роли участника. Стили самодостаточные — не зависят от того,
           определён ли .role-select-modern в теме, и не влияют на другие экраны. */
        .prt-role-select-wrap {
            margin-left: auto;
            padding-left: .75rem;
            flex-shrink: 0;
        }

        .prt-role-select {
            min-width: 130px;
            max-width: 170px;
            padding: .35rem .5rem;
            font-size: .8125rem;
            line-height: 1.3;
            color: inherit;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, .15);
            border-radius: .375rem;
            cursor: pointer;
        }

        .prt-role-select:focus {
            outline: none;
            border-color: #4f7cff;
            box-shadow: 0 0 0 2px rgba(79, 124, 255, .18);
        }
    </style>

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
                        <h1>{{ __('action.edit') }} {{ __('menu.prt_doc_types') }}</h1>
                        <p>{{ __('menu.prt_doc_types') }} / {{ __('action.edit') }}</p>
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
                    <a href="{{ route('protocol-types.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.prt_doc_types') }}</span>
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
                            <span>{{ __('field.edit_protocol_type') }}</span>
                        </div>
                    </div>

                    {{--
                        id нужен списку участников в правой колонке: он лежит вне
                        этого <form>, поэтому его поля привязаны через form="protocolTypeForm".
                        Без этого user_id[] / user_role_id[] не уходили на сервер,
                        и update() снимал права доступа у всех пользователей.
                    --}}
                    <form method="POST" action="{{ route('protocol-types.update', $model->id) }}" class="modern-form"
                          id="protocolTypeForm"
                          style="padding: 2rem;">
                        @csrf
                        @method('PUT')

                        <div class="form-grid" style="grid-template-columns: 1fr;">
                            <!-- Parent ID (Hidden) -->
                            <div class="form-group-modern" style="display: none;">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.parent_id') }}
                                </label>
                                <select name="parent_id" class="form-control-modern select2">
                                    <option value="0">{{ __('action.choose') }}</option>
                                    @foreach($docTypes as $key => $item)
                                        <option
                                            value="{{ $item->id }}" {{ ($item->id == $model->parent_id ? "selected":"") }}>
                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Title -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.title') }}
                                    <span class="text-red">*</span>
                                </label>
                                <input type="text"
                                       class="form-control-modern"
                                       name="title"
                                       value="{{ $model->title }}"
                                       required>
                            </div>

                            <!-- Language -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="languages"></i>
                                    {{ __('field.lang_id') }}
                                </label>
                                <select class="form-control-modern select2" name="lang_id">
                                    <option value="">{{ __('field.not_selected') }}</option>
                                    @foreach($languages as $key => $value)
                                        <option
                                            value="{{ $value->id }}" {{ ($value->id == $model->lang_id ? "selected":"") }}>
                                            {{ $key+1 }}. {{ __($value->group.'.'.$value->key) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Order Number -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="list-ordered"></i>
                                    {{ __('field.order_number') }}
                                </label>
                                <input type="number"
                                       class="form-control-modern"
                                       name="order_number"
                                       value="{{ $model->order_number }}"
                                       min="0">
                            </div>

                            <!-- Document Code -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="code"></i>
                                    {{ __('field.doc_code') }}
                                </label>
                                <input type="text"
                                       class="form-control-modern font-mono"
                                       name="doc_code"
                                       value="{{ $model->doc_code }}">
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
                                               id="status_active"
                                               name="status"
                                               value="1"
                                            {{ ($model->status == 1) ? 'checked':'' }}>
                                        <label for="status_active">{{ __('status.active') }}</label>
                                    </div>
                                    <div class="radio-option-modern danger">
                                        <input type="radio"
                                               id="status_inactive"
                                               name="status"
                                               value="2"
                                            {{ ($model->status == 2) ? 'checked':'' }}>
                                        <label for="status_inactive">{{ __('status.delete') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('protocol-types.index') }}" class="btn-modern btn-secondary">
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

                <!-- Right Column - User Selection -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="users"></i>
                            <span>{{ __('field.select_members') }}</span>
                        </div>
                    </div>

                    <div class="user-selection-container">
                        <div class="user-search-box">
                            <input type="text"
                                   id="userSearch"
                                   onkeyup="searchUserFunction()"
                                   placeholder="{{ __('action.search') }}...">
                        </div>

                        <ul id="sortable" class="sortable-user-list">
                            @foreach($users as $key => $item)
                                <li class="user-item-modern select-user" data-id="{{ $item->id }}">
                                    <div class="user-item-left">
                                        <input class="user-checkbox-modern"
                                               type="checkbox"
                                               form="protocolTypeForm"
                                               id="user_{{ $item->id }}"
                                               name="user_id[]"
                                               value="{{ $item->id }}"
                                               @if(array_key_exists($item->id, $selectedUsers)) checked @endif>
                                        <div class="user-info-modern">
                                            <span class="user-name-modern text-primary">
                                                {{ $item->personal->l_name ?? '' }} {{ $item->personal->f_name ?? '' }}
                                            </span>
                                            <span class="user-role-modern">
                                                <i data-lucide="briefcase"></i>
                                                @php($department = $item->department ?? 0)
                                                @if($department)
                                                    {{ $department->lang($department->id) }}
                                                @endif
                                                                    -
                                                                    @php($position = $item->position ?? 0)
                                                @if($position)
                                                    {{ $position->lang($position->id) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    {{--
                                        Роль участника: 1 — секретарь, 2 — участник, 3 — оба.
                                        Ключ массива обязателен: update() читает как
                                        $request->user_role_id[$userId], где $userId —
                                        это user_work_histories.id. Последовательный
                                        user_role_id[] сюда не подойдёт.
                                    --}}
                                    <div class="prt-role-select-wrap">
                                        <select name="user_role_id[{{ $item->id }}]"
                                                form="protocolTypeForm"
                                                class="role-select-modern prt-role-select">
                                            @foreach($user_roles as $role)
                                                <option value="{{ $role->code }}"
                                                    {{ ($selectedUsers[$item->id] ?? 3) == $role->code ? 'selected' : '' }}>
                                                    {{ $role->lang($role->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="user-drag-handle">
                                        <i data-lucide="grip-vertical"></i>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sign Roles Card -->
            <form method="POST" action="{{ route('prt.doc.sign.roles') }}" class="sign-roles-card">
                @csrf
                <input name="protocol_type_id" value="{{ $model->id }}" hidden>

                <div class="sign-roles-header">
                    <i data-lucide="pen-line"></i>
                    <h3>{{ __('field.sign_roles') }}</h3>
                </div>

                <div class="sign-roles-body">
                    <ul id="signRolesSortable" class="sign-roles-list">
                        @foreach($signRoleTypes as $key => $signRoleType)
                            <li class="sign-role-item role-{{ $listGroupItemColors[$key % count($listGroupItemColors)] }}"
                                data-id="{{ $signRoleType->code }}">
                                @if($signRoleType->code == 1)
                                    <input class="sign-role-checkbox"
                                           type="checkbox"
                                           id="role_{{ $signRoleType->code }}"
                                           checked
                                           disabled>
                                    <input type="hidden" name="roles[]" value="{{ $signRoleType->code }}">
                                @else
                                    <input class="sign-role-checkbox"
                                           type="checkbox"
                                           id="role_{{ $signRoleType->code }}"
                                           value="{{ $signRoleType->code }}"
                                           name="roles[]"
                                        {{ in_array($signRoleType->code, $savedCodes) ? 'checked' : '' }}>
                                @endif
                                <label class="sign-role-label" for="role_{{ $signRoleType->code }}">
                                    {{ $signRoleType->lang($signRoleType->id) ?? '' }}
                                </label>
                            </li>
                        @endforeach
                    </ul>

                    <hr class="protocol-divider">

                    <!-- Sign Type Section -->
                    <div class="sign-type-section">
                        <h4 class="sign-type-title">
                            <i data-lucide="workflow"></i>
                            Imzolash turi
                        </h4>
                        <div class="sign-type-options">
                            <div class="sign-type-option">
                                <input type="radio"
                                       id="sign_sequential"
                                       name="sign_type"
                                       value="0"
                                    {{ $model->sign_type == 0 ? 'checked' : '' }}>
                                <label for="sign_sequential">
                                    <i data-lucide="arrow-right"></i>
                                    Ketma-ket
                                </label>
                            </div>
                            <div class="sign-type-option">
                                <input type="radio"
                                       id="sign_parallel"
                                       name="sign_type"
                                       value="1"
                                    {{ $model->sign_type == 1 ? 'checked' : '' }}>
                                <label for="sign_parallel">
                                    <i data-lucide="git-fork"></i>
                                    Parallel
                                </label>
                            </div>
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

    <script src="{{ asset('/assets/js/js-sortable.js') }}"></script>
    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });

        // User search function
        function searchUserFunction() {
            const input = document.getElementById('userSearch').value.toLowerCase();
            const nodes = document.querySelectorAll('#sortable .select-user');

            nodes.forEach(node => {
                const textContent = node.querySelector('.user-name-modern').textContent.toLowerCase();
                node.style.display = textContent.includes(input) ? 'flex' : 'none';
            });

            // Refresh sortable after filtering
            const sortableElement = document.getElementById('sortable');
            if (sortableElement && Sortable.get(sortableElement)) {
                Sortable.get(sortableElement).sort(Sortable.get(sortableElement).toArray());
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Sortable for user list
            const sortableElement = document.getElementById('sortable');
            if (sortableElement) {
                new Sortable(sortableElement, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    handle: '.user-drag-handle'
                });
                console.log('Sortable initialized for #sortable');
            }

            // Initialize Sortable for sign roles
            const signRolesSortable = document.getElementById('signRolesSortable');
            if (signRolesSortable) {
                new Sortable(signRolesSortable, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    filter: 'li:first-child',
                    onMove: function (evt) {
                        return evt.related !== evt.from.firstElementChild;
                    }
                });
                console.log('Sortable initialized for #signRolesSortable');
            }

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
