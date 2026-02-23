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
    </style>

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="globe"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.public_protocols') }}</h1>
                        <p>{{ __('field.public_protocols') }} / {{ __('menu.list') }}</p>
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
                    <a href="{{ route('doc-controller.public-protocols') }}" class="breadcrumb-item">
                        <span>{{ __('field.public_protocols') }}</span>
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

                <form action="{{ route('doc-controller.public-protocols') }}" method="POST" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <!-- Protocol Type -->
                        <div class="filter-item">
                            <label>
                                <i data-lucide="tag"></i>
                                {{ __('field.protocol-types') }}
                            </label>
                            <select name="s_type" class="form-control-modern select2">
                                <option value="">{{ __('field.protocol-types') }}</option>
                                @foreach($protocolTypes as $key => $item)
                                    <option value="{{ $item->id }}" {{ $s_type == $item->id ? 'selected':'' }}>
                                        {{ $key+1 }}. {{ $item->lang($item->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Document Name -->
                        <div class="filter-item">
                            <label>
                                <i data-lucide="file-text"></i>
                                {{ __('document.doc_name') }}
                            </label>
                            <input type="text"
                                   name="s_text"
                                   placeholder="{{ __('document.doc_name') }}"
                                   value="{{ old('s_text', $s_text) }}"
                                   class="form-control-modern">
                        </div>

                        <!-- Status -->
                        <div class="filter-item">
                            <label>
                                <i data-lucide="activity"></i>
                                {{ __('field.status_all') }}
                            </label>
                            <select name="s_status" class="form-control-modern select2">
                                <option value="">{{ __('field.status_all') }}</option>
                                @foreach($statusList as $key => $item)
                                    <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':'' }}>
                                        {{ $key+1 }}. {{ $item->lang($item->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('doc-controller.public-protocols') }}" class="btn-modern btn-secondary">
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
                        <i data-lucide="globe"></i>
                        <span>{{ __('field.public_protocols') }} {{ __('menu.list') }}</span>
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
                                    <span class="doc-type-badge-modern public">
                                        <i data-lucide="globe"></i>
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
@endsection
