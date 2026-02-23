@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/working-documents-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding: 0.2rem 1rem !important;
            color: #1e293b !important;
            font-size: 14px !important;
        }
        .date-input {
            width: 100%;
            padding: 0.75rem 0.75rem !important;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }
    </style>

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="briefcase"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.working_documents') }}</h1>
                        <p>{{ __('title.working_documents') }} / {{ __('menu.list') }}</p>
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
                    <a href="#" class="breadcrumb-item">
                        <span>{{ __('title.working_documents') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.list') }}</span>
                </nav>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="alert-container-modern"></div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Filters Card -->
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="filter"></i>
                        <span>{{ __('field.filters') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('doc-controller.work-docs') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="advanced-filters-modern">
                        @if($isSuperUser)
                            <!-- Только организация для супер-юзера -->
                            <div class="filter-item" id="org-filter-wrapper">
                                <label>
                                    <i data-lucide="building"></i>
                                    {{ __('field.organizations') }}
                                </label>
                                <select name="org_id" id="org_id_select" class="filter-select-modern select2" required>
                                    <option value="">{{ __('field.choose_organization') }}</option>
                                    @foreach($organizations as $organization)
                                        <option value="{{ $organization->id }}"
                                            {{ old('org_id', $org_id) == $organization->id ? 'selected' : '' }}>
                                            {{ $organization->lang($organization->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Остальные фильтры изначально скрыты или пустые -->
                            <div class="filter-item hidden-by-org" id="doc-type-wrapper">
                                <label><i data-lucide="folder"></i> {{ __('document.document') }}</label>
                                <select name="doc_type" id="doc_type_select" class="filter-select-modern select2"
                                        disabled>
                                    <option value="all">{{ __('document.all_types') }}</option>
                                </select>
                            </div>

                            <div class="filter-item hidden-by-org">
                                <label><i data-lucide="file-text"></i> {{ __('input.doc_name') }}</label>
                                <input type="text" name="doc_name" value="{{ old('doc_name', $doc_name) }}"
                                       class="filter-input-modern" placeholder="{{ __('input.doc_name') }}">
                            </div>

                            <!-- Дата от и до – можно оставить видимыми сразу -->
                            <div class="filter-item">
                                <label><i data-lucide="calendar"></i> {{ __('input.from_day') }}</label>
                                <div class="date-picker-container">
                                    <div class="input-wrapper">
                                        <input
                                            type="text"
                                            name="date_from"
                                            class="date-input form-control-modern"
                                            placeholder="{{ __('input.from_day') }}"
                                            value="{{ old('date_from', $date_from) }}"
                                            autocomplete="off"
                                        />
                                    </div>

                                    <div class="calendar-popup">
                                        <div class="calendar-header">
                                            <button type="button" class="nav-btn prev">&lsaquo;</button>
                                            <div class="month-year"></div>
                                            <button type="button" class="nav-btn next">&rsaquo;</button>
                                        </div>
                                        <div class="days"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-item">
                                <label><i data-lucide="calendar"></i> {{ __('input.till_day') }}</label>
                                <div class="date-picker-container">
                                    <div class="input-wrapper">
                                        <input
                                            type="text"
                                            name="date_till"
                                            class="date-input form-control-modern"
                                            placeholder="{{ __('input.till_day') }}"
                                            value="{{ old('date_till', $date_till) }}"
                                            autocomplete="off"
                                        />
                                    </div>

                                    <div class="calendar-popup">
                                        <div class="calendar-header">
                                            <button type="button" class="nav-btn prev">&lsaquo;</button>
                                            <div class="month-year"></div>
                                            <button type="button" class="nav-btn next">&rsaquo;</button>
                                        </div>
                                        <div class="days"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-item hidden-by-org" id="status-wrapper">
                                <label><i data-lucide="activity"></i> {{ __('select.status_all') }}</label>
                                <select name="status" id="status_select" class="filter-select-modern select2" disabled>
                                    <option value="">{{ __('select.all_statuses') }}</option>
                                </select>
                            </div>

                            <div class="filter-item hidden-by-org" id="creator-wrapper">
                                <label><i data-lucide="user"></i> {{ __('select.status_all_creators') }}</label>
                                <select name="created_user" id="created_user_select"
                                        class="filter-select-modern select2" disabled>
                                    <option value="">{{ __('select.all_creators') }}</option>
                                </select>
                            </div>

                            <!-- Кнопки -->
                            <div class="filter-item actions" style="grid-column: span 2;">
                                <button type="submit" class="btn-modern btn-primary" id="searchBtn">
                                    <i data-lucide="search"></i> {{ __('action.search') }}
                                </button>
                                <a href="{{ route('doc-controller.work-docs') }}" class="btn-modern btn-secondary">
                                    <i data-lucide="refresh-ccw"></i> {{ __('action.clear') }}
                                </a>
                            </div>
                        @else
                            <!-- Document Type -->
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('document.document') }}
                                </label>
                                <select name="doc_type" class="filter-select-modern select2">
                                    <option value="all">{{ __('document.document') }}</option>
                                    @forelse($journal_types as $key => $journal_type)
                                        <option
                                            value="{{ $journal_type->id }}" {{ ($doc_type == $journal_type->id ? "selected":"") }}>
                                            {{ $key+1 }}. {{ $journal_type->lang($journal_type->lang_id) }}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>

                            <!-- Document Name -->
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="file-text"></i>
                                    {{ __('input.doc_name') }}
                                </label>
                                <input type="text" name="doc_name" value="{{ $doc_name }}"
                                       class="filter-input-modern"
                                       placeholder="{{ __('input.doc_name') }}">
                            </div>

                            <div class="filter-item">
                                <label>
                                    <i data-lucide="calendar"></i>
                                    {{ __('input.from_day') }}
                                </label>
                                <div class="date-picker-container">
                                    <div class="input-wrapper">
                                        <input
                                            type="text"
                                            name="date_from"
                                            class="date-input form-control-modern"
                                            placeholder="{{ __('input.from_day') }}"
                                            value="{{ $date_from??'' }}"
                                            autocomplete="off"
                                        />
                                    </div>

                                    <div class="calendar-popup">
                                        <div class="calendar-header">
                                            <button type="button" class="nav-btn prev">&lsaquo;</button>
                                            <div class="month-year"></div>
                                            <button type="button" class="nav-btn next">&rsaquo;</button>
                                        </div>
                                        <div class="days"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-item">
                                <label>
                                    <i data-lucide="calendar"></i>
                                    {{ __('input.till_day') }}
                                </label>
                                <div class="date-picker-container">
                                    <div class="input-wrapper">
                                        <input
                                            type="text"
                                            name="date_till"
                                            class="date-input form-control-modern"
                                            placeholder="{{ __('input.till_day') }}"
                                            value="{{ $date_till??'' }}"
                                            autocomplete="off"
                                        />
                                    </div>

                                    <div class="calendar-popup">
                                        <div class="calendar-header">
                                            <button type="button" class="nav-btn prev">&lsaquo;</button>
                                            <div class="month-year"></div>
                                            <button type="button" class="nav-btn next">&rsaquo;</button>
                                        </div>
                                        <div class="days"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="activity"></i>
                                    {{ __('select.status_all') }}
                                </label>
                                <select name="status" class="filter-select-modern select2">
                                    <option value="">{{ __('select.all_statuses') }}</option>
                                    @forelse($statusNames as $statusName)
                                        <option
                                            value="{{ $statusName->code }}" {{ ($status == $statusName->code ? "selected":"") }}>
                                            {{ $statusName->lang($statusName->id) }}
                                        </option>
                                    @empty
                                    @endforelse
                                    <option value="12" {{ ($status == 12 ? "selected":"") }}>{{ __('field.deadline_expired') }}</option>
                                    <option value="14" {{ ($status == 14 ? "selected":"") }}>{{ __('field.resolution_expired') }}</option>
                                </select>
                            </div>

                            <!-- Created By -->
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('select.status_all_creators') }}
                                </label>
                                <select name="created_user" class="filter-select-modern select2">
                                    <option value="">{{ __('select.all_creators') }}</option>
                                    @forelse($cr_users as $cr_user)
                                        @if($cr_user->userPersonal)
                                            <option
                                                value="{{ $cr_user->id }}" {{ ($created_user == $cr_user->id ? "selected":"") }}>
                                                {{ $cr_user->userPersonal->shortName() }}
                                            </option>
                                        @endif
                                    @empty
                                    @endforelse
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="filter-item actions" style="grid-column: span 2;">
                                <button type="submit" class="btn-modern btn-primary">
                                    <i data-lucide="search"></i>
                                    {{ __('action.search') }}
                                </button>
                                <a href="{{ route('doc-controller.work-docs') }}" class="btn-modern btn-secondary">
                                    <i data-lucide="refresh-ccw"></i>
                                    {{ __('action.clear') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Documents Table Card -->
            <div class="table-card-modern">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th>№</th>
                            <th>{{ __('field.registry_number') }}</th>
                            <th>{{ __('field.document_name') }}</th>
                            <th>{{ __('field.document_type') }}</th>
                            <th>{{ __('field.document_date') }}</th>
                            <th>{{ __('field.status') }}</th>
                            <th>{{ __('title.execution_time') }}</th>
                            <th>{{ __('field.res_term') }}</th>
                            <th>{{ __('field.created_by') }}</th>
                            <th>{{ __('field.created_at') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $model)
                            <tr onclick="window.location='{{ route('doc-controller.action-route', ['id' => $model->id, 'hash' => $model->doc->doc_hash ?? '']) }}'">
                                <td class="number-cell">{{ $models->firstItem() + $key }}</td>
                                <td>
                                    <span class="doc-number-badge-modern">
                                        {{ $model->j_number ?? '' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="doc-name-cell-modern">
                                        <i data-lucide="file-text"></i>
                                        <span>{{ $model->doc->doc_name ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="doc-type-badge-modern">
                                        {{ $model->journal->lang($model->journal->lang_id) ?? '' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span
                                            class="date">{{ $model->doc->doc_date ? \Carbon\Carbon::parse($model->doc->doc_date)->format('d.m.Y') : '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($model->statusWork)
                                        <span
                                            class="doc-status-badge-modern {{ strtolower($model->statusWork->lang_code ?? 'draft') }}">
                                            <i data-lucide="{{ $model->statusWork->iconClass->name?? 'circle' }}"></i>
                                            {{ $model->statusWork->lang($model->statusWork->id) ?? '' }}
                                        </span>
                                    @else
                                        <span class="doc-status-badge-modern draft">
                                            <i data-lucide="{{ $default_status->name?? 'circle' }}"></i>
                                            {{ __($default_status->lang_code ?? 'unk') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @include('templates.execution-time')
                                </td>
                                <td>
                                    @include('templates.res-term-in-doc-contr')
                                </td>
                                <td>
                                    <div class="creator-cell-modern">
                                        <i data-lucide="user"></i>
                                        <span>{{ $model->fromUser->personal->shortName() ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span
                                            class="date">{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.Y') }}</span>
                                        <span
                                            class="time">{{ \Carbon\Carbon::parse($model->created_at)->format('H:i') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="folder-x"></i>
                                        <p>{{ __('field.not_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($models->hasPages())
                    <div class="pagination-wrapper-modern">
                        {!! $models->links() !!}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });
    </script>

    @include('templates.filter-calendar.modern-calendar-css')
    @include('templates.filter-calendar.modern-calendar-script')

    <script src="{{ asset('js/table-sort.js') }}"></script>
    <script>
        $(document).ready(function () {

            const $orgSelect     = $('#org_id_select');
            const $docTypeSelect = $('#doc_type_select');
            const $statusSelect  = $('#status_select');
            const $userSelect    = $('#created_user_select');
            const $searchBtn     = $('#searchBtn');

            // Сохраняем старые значения из PHP
            const savedDocType = '{{ old("doc_type", $doc_type) }}';
            const savedStatus = '{{ old("status", $status) }}';
            const savedCreatedUser = '{{ old("created_user", $created_user) }}';

            // При смене организации
            $orgSelect.on('change', function () {
                const orgId = $(this).val();

                if (!orgId) {
                    // Скрываем / очищаем остальные поля
                    $('.hidden-by-org').addClass('hidden-by-org');
                    {{--$docTypeSelect.html('<option value="all">{{ __('document.all_types') }}</option>').prop('disabled', true);--}}
                    {{--$statusSelect.html('<option value="">{{ __('select.all_statuses') }}</option>').prop('disabled', true);--}}
                    {{--$userSelect.html('<option value="">{{ __('select.all_creators') }}</option>').prop('disabled', true);--}}
                    {{--$searchBtn.prop('disabled', true);--}}
                    return;
                }

                // Показываем остальные фильтры
                $('.hidden-by-org').removeClass('hidden-by-org');

                $.ajax({
                    url: '{{ route("doc-controller.work-docs.filter-data") }}',
                    method: 'GET',
                    data: { org_id: orgId },
                    dataType: 'json',
                    success: function (response) {
                        if (!response.success) {
                            alert(response.message || 'Ошибка загрузки данных');
                            return;
                        }

                        // Типы документов
                        let typeOptions = '<option value="all">{{ __('document.all_types') }}</option>';
                        response.journal_types.forEach(item => {
                            const selected = (savedDocType == item.id) ? 'selected' : '';
                            typeOptions += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        $docTypeSelect.html(typeOptions).prop('disabled', false);

                        // Статусы
                        let statusOptions = '<option value="">{{ __('select.all_statuses') }}</option>';
                        response.statuses.forEach(item => {
                            const selected = (savedStatus == item.code) ? 'selected' : '';
                            statusOptions += `<option value="${item.code}" ${selected}>${item.name}</option>`;
                        });
                        // Добавляем специальные статусы
                        const expiredSelected = (savedStatus == '12') ? 'selected' : '';
                        const resExpiredSelected = (savedStatus == '14') ? 'selected' : '';
                        statusOptions += `<option value="12" ${expiredSelected}>{{ __('field.deadline_expired') }}</option>`;
                        statusOptions += `<option value="14" ${resExpiredSelected}>{{ __('field.resolution_expired') }}</option>`;
                        $statusSelect.html(statusOptions).prop('disabled', false);

                        // Пользователи
                        let userOptions = '<option value="">{{ __('select.all_creators') }}</option>';
                        response.users.forEach(item => {
                            const selected = (savedCreatedUser == item.id) ? 'selected' : '';
                            userOptions += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        $userSelect.html(userOptions).prop('disabled', false);

                        // Активируем кнопку поиска
                        $searchBtn.prop('disabled', false);

                        // Переинициализируем select2
                        {{--$('.select2').select2('destroy').select2({--}}
                        {{--    placeholder: "{{ __('action.choose') }}",--}}
                        {{--    allowClear: true--}}
                        {{--});--}}

                    },
                    error: function () {
                        alert('Не удалось загрузить данные по организации');
                    }
                });
            });

            // Если при загрузке страницы уже выбрана организация — подгружаем данные
            @if($isSuperUser && old('org_id', $org_id))
            $orgSelect.trigger('change');
            @endif
        });
    </script>
@endsection
