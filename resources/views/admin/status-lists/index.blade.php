@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/status-lists/status-lists-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="list-checks"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.status_list') }}</h1>
                        <p>{{ __('menu.status_list') }} / {{ __('menu.list') }}</p>
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
                        <span>{{ __('menu.status_list') }}</span>
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

                <form action="{{ route('status-lists.index') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </label>
                            <input type="text"
                                   name="s_text"
                                   value="{{ $s_text ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('action.search') }}...">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="filter"></i>
                                {{ __('field.type') }}
                            </label>
                            <select name="s_type" class="form-control-modern select2">
                                <option value="">{{ __('field.fields') }}</option>
                                @foreach($types as $key => $item)
                                    <option value="{{ $item->type }}" {{ $s_type == $item->type ? 'selected':'' }}>
                                        {{ $key+1 }}. {{ $item->type ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('status-lists.index') }}" class="btn-modern btn-secondary">
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
                        <i data-lucide="list"></i>
                        <span>{{ __('menu.status_list') }}</span>
                    </div>
                    <a href="{{ route('status-lists.create') }}" class="btn-modern btn-primary" style="width: auto;">
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
                                <i data-lucide="type"></i>
                                {{ __('field.title') }}
                            </th>
                            <th>
                                <i data-lucide="languages"></i>
                                {{ __('field.lang_id') }}
                            </th>
                            <th>
                                <i data-lucide="tag"></i>
                                {{ __('field.type') }}
                            </th>
                            <th style="width: 100px;">
                                <i data-lucide="binary"></i>
                                {{ __('field.code') }}
                            </th>
                            <th style="width: 100px;">
                                <i data-lucide="list-ordered"></i>
                                {{ __('field.order_number') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="settings"></i>
                                {{ __('field.action') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $model)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key + 1 }}</td>
                                <td class="td-doc-name">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        @if($model->iconClass)
                                            <i data-lucide="{{ $model->iconClass->name }}" style="color: #794DFD; width: 16px; height: 16px;"></i>
                                        @endif
                                        <strong>{{ $model->title }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge-modern {{ $model->badgeClass->name ?? 'badge-info' }}">
                                        @if($model->iconClass)
                                            <i data-lucide="{{ $model->iconClass->name }}"></i>
                                        @endif
                                        {{ $model->lang($model->id) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-modern badge-primary">{{ $model->type }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="code-badge">{{ $model->code }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="order-number-badge">{{ $model->order_number ?? '' }}</span>
                                </td>
                                <td class="td-action">
                                    <a href="{{ route('status-lists.edit', $model->id) }}" class="action-btn view-btn">
                                        <i data-lucide="square-pen"></i>
                                        {{ __('action.edit') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
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
@endsection
