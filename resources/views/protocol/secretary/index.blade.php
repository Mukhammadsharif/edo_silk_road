@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/protocol/secretary-index.css') }}">

@section('content')
    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-badge"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ $protocolType->lang($protocolType->id) }}</h1>
                        <p>{{ __('message.manage_protocol_documents') ?? 'Управление протокольными документами' }}</p>
                    </div>
                </div>
                <div class="header-center">
                    @include('templates.alert')
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ $protocolType->lang($protocolType->id) }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <div class="content-wrapper-modern">
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="filter"></i>
                        <span>{{ __('action.filters') ?? 'Фильтры' }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.documents') ?? 'документов' }}</span>
                    </div>
                </div>

                <form action="{{ route('prt-secretary', $protocolType->doc_hash) }}" method="POST" class="filters-form-modern">
                    @csrf
                    <div class="create-button-wrapper">
                        <a href="{{ route('prt-secretary-create-only', $protocolType->doc_hash) }}" class="btn-modern btn-create">
                            <i data-lucide="plus-circle"></i>
                            <span>{{ __('action.create') }}</span>
                        </a>
                    </div>
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label>
                                <i data-lucide="file-text"></i>
                                {{ __('document.doc_name') }}
                            </label>
                            <input type="text" name="s_text" placeholder="{{ __('document.doc_name') }}" value="{{ $s_text??'' }}" class="form-control-modern">
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

                        <div class="filter-item">
                            <label>
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </label>
                            <select name="s_status" class="form-control-modern select2">
                                <option value="all">{{ __('field.status_all') }}</option>
                                @foreach($statusList as $key => $item)
                                    <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':''}}>
                                        {{ $key+1 }}. {{ $item->lang($item->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="user"></i>
                                {{ __('field.creator') }}
                            </label>
                            <select name="created_user" class="form-control-modern select2">
                                <option value="">{{ __('select.status_all_creators') }}</option>
                                @forelse($cr_users as $key => $item)
                                    <option value="{{ $item->id }}" {{ ($created_user == $item->id ? "selected":"") }}>
                                        {{ $item->userPersonal->shortName()??'' }}
                                    </option>
                                @empty
                                    <option>{{ __('field.not_found') }}</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                <span>{{ __('action.search') }}</span>
                            </button>
                            <a href="{{ route('prt-secretary', $protocolType->doc_hash) }}" class="btn-modern btn-secondary">
                                <i data-lucide="rotate-ccw"></i>
                                <span>{{ __('action.clear') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-card-modern">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>
                            <th><i data-lucide="folder"></i> {{ __('field.doc_type') }}</th>
                            <th><i data-lucide="file-text"></i> {{ __('field.doc_name') }}</th>
                            <th><i data-lucide="tag"></i> {{ __('field.doc_number') }}</th>
                            <th><i data-lucide="calendar"></i> {{ __('field.doc_date') }}</th>
                            <th><i data-lucide="activity"></i> {{ __('field.status') }}</th>
                            <th><i data-lucide="timer"></i> {{ __('field.doc_term') }}</th>
                            <th><i data-lucide="user"></i> {{ __('field.doc_secretary') }}</th>
                            <th><i data-lucide="calendar-clock"></i> {{ __('field.created_date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $item)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td>
                                        <span class="badge-modern badge-info">
                                            {{ $item->docType->lang($item->docType->id) }}
                                        </span>
                                </td>
                                <td class="td-doc-name">
                                    <a href="{{ route('prt-secretary-view', [$item->id, $item->doc_hash]) }}" class="doc-link">
                                        <i data-lucide="file-text"></i>
                                        <span>{{ $item->doc_name??'-' }}</span>
                                        <i data-lucide="arrow-right" class="arrow-icon"></i>
                                    </a>
                                </td>
                                <td>{{ $item->doc_number }}</td>
                                <td class="td-date">{{ date('d.m.Y', strtotime($item->doc_date)) }}</td>
                                <td class="td-status">
                                        <span class="status-badge-modern {{ $item->statusName->badgeClass->name??'' }}">
                                            <i data-lucide="circle"></i>
                                            {{ $item->statusDoc($item->status) }}
                                        </span>
                                </td>
                                <td class="td-term">
                                    @php($doc_term = $item->doc_term??'')
                                    @if($doc_term)
                                        <span class="badge-modern badge-danger">
                                                {{ date('d.m.Y', strtotime($item->doc_term)) }}
                                            </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="td-creator">
                                    <div class="creator-cell">
                                        @php($secretaryShortName = $item->secretary->personal??0)
                                        @if($secretaryShortName)
                                            <i data-lucide="user"></i>
                                            <span>{{ $secretaryShortName->shortName() }}</span>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="td-datetime">
                                    <div class="datetime-cell">
                                        <span class="date">{{ date('d.m.y', strtotime($item->created_at)) }}</span>
                                        <span class="time">{{ date('H:i', strtotime($item->created_at)) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="file-badge"></i>
                                        <p>{{ __('field.not_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper-modern">
                    {!! $models->links() !!}
                </div>
            </div>
        </div>
    </div>

    @include('templates.filter-calendar.modern-calendar-css')
    @include('templates.filter-calendar.modern-calendar-script')

    <script>
        // Initialize lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
