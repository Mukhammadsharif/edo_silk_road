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
                        <i data-lucide="building-2"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.doc_orgs') }}</h1>
                        <p>{{ __('menu.doc_orgs') }} / {{ __('action.view') }}</p>
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
                        <span>{{ __('menu.doc_orgs') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.view') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Filters Card -->
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="search"></i>
                        <span>{{ __('action.search') }} & {{ __('field.filters') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('doc-org.search') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        @if($isSuperUser)
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.organizations') }}
                                </label>
                                <select name="org_id" class="form-control-modern select2">
                                    <option value="">{{ __('field.organizations') }}</option>
                                    @forelse($organizations as $key => $organization)
                                        <option value="{{ $organization->id }}" {{ ($org_id == $organization->id ? "selected":"") }}>
                                            {{ ++$key }}.
                                            {{ $organization->lang($organization->id) }}
                                        </option>
                                    @empty
                                        <option>{{ __('field.not_found') }}</option>
                                    @endforelse
                                </select>
                            </div>
                        @endif

                        <div class="filter-item" style="grid-column: span 2;">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('input.doc_org_name') }}
                            </label>
                            <input type="text"
                                   name="org_name"
                                   value="{{ $org_name ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('input.doc_org_name') }}">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('doc-org.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i>
                                {{ __('action.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="table"></i>
                        <span>{{ __('menu.doc_orgs') }} {{ __('menu.list') }}</span>
                    </div>
                    <a href="{{ route('doc-org.create') }}" class="btn-modern btn-primary" style="width: auto;">
                        <i data-lucide="circle-plus"></i>
                        {{ __('action.create') }}
                    </a>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                #
                            </th>
                            <th style="text-align: left;">
                                <i data-lucide="type"></i>
                                {{ __('field.title') }}
                            </th>
                            @if($isSuperUser)
                                <th style="text-align: left;">
                                    <i data-lucide="building"></i>
                                    {{ __('field.organization') }}
                                </th>
                            @endif
                            <th>
                                <i data-lucide="book-open"></i>
                                {{ __('field.doc_type') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="settings"></i>
                                {{ __('field.action') }}
                            </th>
                            <th style="width: 150px;">
                                <i data-lucide="calendar"></i>
                                {{ __('field.date') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($k = 1)
                        @forelse($models as $key => $model)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $k++ }}</td>
                                <td class="td-doc-name">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="building-2" style="width: 16px; height: 16px; color: #794DFD;"></i>
                                        <strong>{{ $model->lang($model->id) }}</strong>
                                    </div>
                                </td>
                                <td class="td-doc-name">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="building" style="width: 16px; height: 16px; color: #794DFD;"></i>
                                        <strong>{{ $model->docType->organization->name_uz??'' }}</strong>
                                    </div>
                                </td>
                                <td>
                                    @foreach($journals as $i => $journal)
                                        @if($model->for_doc_type == $journal->id)
                                            <span class="badge-modern badge-info">{{ $journal->lang($journal->lang_id) }}</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="td-status">
                                    @if($model->status == 1)
                                        <span class="status-badge-modern badge-success">
                                            <i data-lucide="check-circle"></i>
                                            {{ $model->isActive($model->status) }}
                                        </span>
                                    @else
                                        <span class="status-badge-modern badge-danger">
                                            <i data-lucide="x-circle"></i>
                                            {{ $model->isActive($model->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('doc-org.edit', $model->id) }}" class="action-btn view-btn">
                                        <i data-lucide="square-pen"></i>
                                        {{ __('action.edit') }}
                                    </a>
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span class="date">{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.Y') }}</span>
                                        <span class="time">{{ \Carbon\Carbon::parse($model->created_at)->format('H:i') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">
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

    <style>
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: transparent;
            text-decoration: none;
        }

        .view-btn {
            color: #10b981;
            background: #f0fdf4;
        }

        .view-btn:hover {
            background: #dcfce7;
            transform: translateY(-2px);
            color: #10b981;
            text-decoration: none;
        }

        .view-btn i {
            width: 16px;
            height: 16px;
        }

        .select2-container--default .select2-selection--single {
            border: 2px solid #e2e8f0 !important;
            border-radius: 10px !important;
            height: auto !important;
            padding: 0.5rem 1rem !important;
        }
    </style>

    <script>
        // Initialize Lucide icons
        if (window.lucide) lucide.createIcons();
    </script>
@endsection
