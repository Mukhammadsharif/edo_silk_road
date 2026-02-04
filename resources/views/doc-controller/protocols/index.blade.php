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
        .hidden-by-org {
            display: none;
        }
    </style>

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-signature"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.protocols') }}</h1>
                        <p>{{ __('field.protocols') }} / {{ __('menu.list') }}</p>
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
                    <a href="{{ route('doc-controller.protocols') }}" class="breadcrumb-item">
                        <span>{{ __('field.protocols') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.list') }}</span>
                </nav>
            </div>
        </div>

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

                <form action="{{ route('doc-controller.protocols') }}" method="POST" class="filters-form-modern">
                    @csrf
                    <div class="advanced-filters-modern">
                        @if($isSuperUser)
                            <!-- Organization Filter (SuperUser only) -->
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

                            <!-- Protocol Type (hidden initially for superUser) -->
                            <div class="filter-item hidden-by-org" id="protocol-type-wrapper">
                                <label>
                                    <i data-lucide="tag"></i>
                                    {{ __('field.protocol-types') }}
                                </label>
                                <select name="s_type" id="s_type_select" class="filter-select-modern select2" disabled>
                                    <option value="">{{ __('field.protocol-types') }}</option>
                                </select>
                            </div>
                        @else
                            <!-- Protocol Type (always visible for regular users) -->
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="tag"></i>
                                    {{ __('field.protocol-types') }}
                                </label>
                                <select name="s_type" class="filter-select-modern select2">
                                    <option value="">{{ __('field.protocol-types') }}</option>
                                    @foreach($protocolTypes as $key => $item)
                                        <option value="{{ $item->id }}" {{ $s_type == $item->id ? 'selected':'' }}>
                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Document Name -->
                        <div class="filter-item {{ $isSuperUser ? 'hidden-by-org' : '' }}">
                            <label>
                                <i data-lucide="file-text"></i>
                                {{ __('document.doc_name') }}
                            </label>
                            <input type="text"
                                   name="s_text"
                                   placeholder="{{ __('document.doc_name') }}"
                                   value="{{ old('s_text', $s_text) }}"
                                   class="filter-input-modern">
                        </div>

                        <!-- From Date -->
                        <div class="filter-item">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('input.from_day') }}
                            </label>
                            <div class="date-picker-container">
                                <div class="input-wrapper">
                                    <input type="text"
                                           name="date_from"
                                           class="date-input form-control-modern"
                                           placeholder="{{ __('input.from_day') }}"
                                           value="{{ old('date_from', $date_from) }}"
                                           autocomplete="off" />
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

                        <!-- Till Date -->
                        <div class="filter-item">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('input.till_day') }}
                            </label>
                            <div class="date-picker-container">
                                <div class="input-wrapper">
                                    <input type="text"
                                           name="date_till"
                                           class="date-input form-control-modern"
                                           placeholder="{{ __('input.till_day') }}"
                                           value="{{ old('date_till', $date_till) }}"
                                           autocomplete="off" />
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

                        @if($isSuperUser)
                            <!-- Status (hidden initially for superUser) -->
                            <div class="filter-item hidden-by-org">
                                <label>
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status_all') }}
                                </label>
                                <select name="s_status" id="s_status_select" class="filter-select-modern select2" disabled>
                                    <option value="">{{ __('field.status_all') }}</option>
                                </select>
                            </div>

                            <!-- Creator (hidden initially for superUser) -->
                            <div class="filter-item hidden-by-org">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('select.status_all_creators') }}
                                </label>
                                <select name="created_user" id="created_user_select" class="filter-select-modern select2" disabled>
                                    <option value="">{{ __('select.status_all_creators') }}</option>
                                </select>
                            </div>
                        @else
                            <!-- Status (always visible for regular users) -->
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status_all') }}
                                </label>
                                <select name="s_status" class="filter-select-modern select2">
                                    <option value="">{{ __('field.status_all') }}</option>
                                    @foreach($statusList as $key => $item)
                                        <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':'' }}>
                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Creator (always visible for regular users) -->
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('select.status_all_creators') }}
                                </label>
                                <select name="created_user" class="filter-select-modern select2">
                                    <option value="">{{ __('select.status_all_creators') }}</option>
                                    @forelse($cr_users as $key => $item)
                                        <option value="{{ $item->id }}" {{ ($created_user == $item->id ? "selected":"") }}>
                                            {{ $item->userPersonal->shortName() ?? '' }}
                                        </option>
                                    @empty
                                        <option>{{ __('field.not_found') }}</option>
                                    @endforelse
                                </select>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="filter-item actions" style="grid-column: span 2;">
                            <button type="submit" id="searchBtn" class="btn-modern btn-primary" {{ $isSuperUser ? 'disabled' : '' }}>
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('doc-controller.protocols') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i>
                                {{ __('action.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="list"></i>
                        <span>{{ __('field.protocols') }} {{ __('menu.list') }}</span>
                    </div>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table protocol-table-modern">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.#') }}
                            </th>
                            <th>
                                <i data-lucide="tag"></i>
                                {{ __('field.doc_type') }}
                            </th>
                            <th style="text-align: left;">
                                <i data-lucide="file-text"></i>
                                {{ __('field.doc_name') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.doc_number') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="calendar"></i>
                                {{ __('field.doc_date') }}
                            </th>
                            <th style="width: 140px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </th>
                            <th style="width: 130px;">
                                <i data-lucide="clock"></i>
                                {{ __('field.doc_term') }}
                            </th>
                            <th style="width: 160px;">
                                <i data-lucide="user-check"></i>
                                {{ __('field.doc_secretary') }}
                            </th>
                            <th style="width: 130px;">
                                <i data-lucide="calendar-plus"></i>
                                {{ __('field.created_date') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $item)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td>
                                    <span class="doc-type-badge-modern protocol">
                                        <i data-lucide="file-signature"></i>
                                        {{ $item->docType->lang($item->docType->id) }}
                                    </span>
                                </td>
                                <td style="text-align: left;">
                                    <a href="{{ route('doc-controller.protocol-view', [$item->id, $item->doc_hash]) }}"
                                       class="doc-name-link-modern">
                                        <i data-lucide="file-text"></i>
                                        {{ $item->doc_name ?? '-' }}
                                    </a>
                                </td>
                                <td class="protocol-number">{{ $item->doc_number }}</td>
                                <td class="doc-date-cell-modern">{{ date('d.m.Y', strtotime($item->doc_date)) }}</td>
                                <td>
                                    @php
                                        $statusClass = 'draft';
                                        if ($item->status == 4 || $item->status == 5) {
                                            $statusClass = 'pending';
                                        } else {
                                            $statusClass = 'completed';
                                        }
                                    @endphp
                                    <span class="doc-status-badge-modern {{ $statusClass }}">
                                        <i data-lucide="circle"></i>
                                        {{ $item->statusDoc($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php($doc_term = $item->doc_term ?? '')
                                    @if($doc_term)
                                        <span class="term-badge-modern urgent">
                                            <i data-lucide="clock"></i>
                                            {{ date('d.m.Y', strtotime($item->doc_term)) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php($secretaryShortName = $item->secretary->personal ?? 0)
                                    @if($secretaryShortName)
                                        <div class="secretary-cell-modern">
                                            <i data-lucide="user-check"></i>
                                            <span>{{ $secretaryShortName->shortName() }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span class="date">{{ date('d.m.Y', strtotime($item->created_at)) }}</span>
                                        <span class="time">{{ date('H:i', strtotime($item->created_at)) }}</span>
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
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });
    </script>

    @include('templates.filter-calendar.modern-calendar-css')
    @include('templates.filter-calendar.modern-calendar-script')

    @if($isSuperUser)
        <script>
            $(document).ready(function () {
                const $orgSelect = $('#org_id_select');
                const $protocolTypeSelect = $('#s_type_select');
                const $statusSelect = $('#s_status_select');
                const $userSelect = $('#created_user_select');
                const $searchBtn = $('#searchBtn');

                // Сохраняем старые значения из PHP
                const savedProtocolType = '{{ old("s_type", $s_type) }}';
                const savedStatus = '{{ old("s_status", $s_status) }}';
                const savedCreatedUser = '{{ old("created_user", $created_user) }}';

                // При смене организации
                $orgSelect.on('change', function () {
                    const orgId = $(this).val();

                    if (!orgId) {
                        // Скрываем остальные поля
                        $('.hidden-by-org').addClass('hidden-by-org');
                        $searchBtn.prop('disabled', true);
                        return;
                    }

                    // Показываем остальные фильтры
                    $('.hidden-by-org').removeClass('hidden-by-org');

                    $.ajax({
                        url: '{{ route("doc-controller.protocols.filter-data") }}',
                        method: 'GET',
                        data: { org_id: orgId },
                        dataType: 'json',
                        success: function (response) {
                            if (!response.success) {
                                alert(response.message || 'Ошибка загрузки данных');
                                return;
                            }

                            // Типы протоколов
                            let typeOptions = '<option value="">{{ __('field.protocol-types') }}</option>';
                            response.protocol_types.forEach(item => {
                                const selected = (savedProtocolType == item.id) ? 'selected' : '';
                                typeOptions += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                            });
                            $protocolTypeSelect.html(typeOptions).prop('disabled', false);

                            // Статусы
                            let statusOptions = '<option value="">{{ __('field.status_all') }}</option>';
                            response.statuses.forEach(item => {
                                const selected = (savedStatus == item.code) ? 'selected' : '';
                                statusOptions += `<option value="${item.code}" ${selected}>${item.name}</option>`;
                            });
                            $statusSelect.html(statusOptions).prop('disabled', false);

                            // Пользователи
                            let userOptions = '<option value="">{{ __('select.status_all_creators') }}</option>';
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
                @if(old('org_id', $org_id))
                $orgSelect.trigger('change');
                @endif
            });
        </script>
    @endif
@endsection
