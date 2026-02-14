@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-templates-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-text"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.doc_templates') }}</h1>
                        <p>{{ __('menu.doc_templates') }} / {{ __('menu.list') }}</p>
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
                        <span>{{ __('menu.doc_templates') }}</span>
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
                        <i data-lucide="search"></i>
                        <span>{{ __('action.search') }} & {{ __('field.filters') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('document.templates') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item" style="grid-column: span 1;">
                            <a href="{{ route('document.templates.create') }}" class="template-create-btn">
                                <i data-lucide="circle-plus"></i>
                                {{ __('action.create') }}
                            </a>
                        </div>

                        @if($isSuperUser)
                            <div class="filter-item">
                                <label class="mb-2">
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
                                {{ __('input.doc_name') }}
                            </label>
                            <input type="text"
                                   name="doc_name"
                                   value="{{ $doc_name ?? '' }}"
                                   class="template-search-input"
                                   placeholder="{{ __('input.doc_name') }}">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('document.templates') }}" class="btn-modern btn-secondary">
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
                        <span>{{ __('menu.doc_templates') }} {{ __('menu.list') }}</span>
                    </div>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table template-table-modern">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.#') }}
                            </th>
                            <th>
                                <i data-lucide="folder"></i>
                                {{ __('field.doc_type') }}
                            </th>
                            <th style="text-align: left;">
                                <i data-lucide="file-text"></i>
                                {{ __('field.name') }}
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
                                {{ __('field.created_date') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($k = 1)
                        @forelse($models as $key => $model)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $k++ }}</td>
                                <td>
                                    <div class="doc-type-cell">
                                        @if($model->document->parent)
                                            <span class="parent-badge">
                                                <i data-lucide="folder"></i>
                                                {{ $model->document->parent->title }}
                                            </span>
                                        @else
                                            <span class="badge-modern badge-primary">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="doc-name-cell">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="file-text" style="color: #794DFD; width: 16px; height: 16px;"></i>
                                        {{ $model->document->lang($model->document->lang_id) }}
                                    </div>
                                </td>
                                <td class="td-status">
                                    @if($model->status == 1)
                                        <span class="status-badge-modern badge-success">
                                            <i data-lucide="check-circle"></i>
                                            {{ __('field.active') }}
                                        </span>
                                    @else
                                        <span class="status-badge-modern badge-danger">
                                            <i data-lucide="x-circle"></i>
                                            {{ __('field.delete') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="td-action">
                                    <a href="{{ route('document.templates.edit', $model->id) }}"
                                       class="template-action-btn edit">
                                        <i data-lucide="square-pen"></i>
                                        {{ __('action.edit') }}
                                    </a>
                                </td>
                                <td>
                                    <div class="created-date-cell">
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
@endsection
