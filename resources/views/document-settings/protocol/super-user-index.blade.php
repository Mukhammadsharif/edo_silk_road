@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/departments/tree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/organizations/index.css') }}" rel="stylesheet">

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
                        <h1>{{ __('menu.organization_docs') }}</h1>
                        <p>{{ __('menu.home') }} / {{ __('menu.organization_docs') }}</p>
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
                    <span class="breadcrumb-item active">{{ __('menu.organization_docs') }}</span>
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
                        <span class="count-badge">{{ $organizations->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('protocol-types.index') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item" style="grid-column: span 2;">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </label>
                            <input type="text"
                                   name="s_text"
                                   value="{{ $s_text ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('field.search_placeholder') }}">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('protocol-types.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i>
                                {{ __('field.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; align-items: start;">

                <!-- Organizations Table -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="list"></i>
                            <span>{{ __('menu.organizations') }} {{ __('menu.list') }}</span>
                        </div>

                        <a href="{{ route('protocol-types.create') }}" class="btn-modern btn-primary" style="width: auto;">
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
                                    {{ __('field.number') }}
                                </th>
                                <th style="text-align: left;">
                                    <i data-lucide="building"></i>
                                    {{ __('field.name') }}
                                </th>
                                <th>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.docs_number') }}
                                </th>
                                <th style="width: 120px;">
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($organizations as $key => $item)
                                <tr class="table-row-modern">
                                    <td class="td-number">{{ $organizations->firstItem() + $key }}</td>
                                    <td class="td-doc-name">
                                        <a href="{{ route('protocol-types.super-user-docs', ['orgId' => $item->id]) }}" class="doc-link">
                                            <i data-lucide="building-2"></i>
                                            <strong>{{ $item->lang($item->id) }}</strong>
                                            <i data-lucide="arrow-right" class="arrow-icon"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-modern badge-info">
                                            {{ $item->protocolTypes->count() }}
                                        </span>
                                    </td>
                                    <td class="td-status">
                                        @if($item->status == 1)
                                            <span class="status-badge-modern badge-success">
                                                    <i data-lucide="check-circle"></i>
                                                    {{ __('field.active') }}
                                                </span>
                                        @else
                                            <span class="status-badge-modern badge-danger">
                                                    <i data-lucide="x-circle"></i>
                                                    {{ __('field.inactive') }}
                                                </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">
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

                    @if($organizations->hasPages())
                        <div class="pagination-wrapper-modern">
                            {!! $organizations->links() !!}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
