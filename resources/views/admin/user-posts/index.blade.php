@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/user-positions/user-positions-modern.css') }}" rel="stylesheet">

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
                        <h1>{{ __('menu.user_positions') }}</h1>
                        <p>{{ __('menu.user_positions') }} / {{ __('menu.list') }}</p>
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
                        <span>{{ __('menu.user_positions') }}</span>
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
                        <span>{{ __('action.search') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('user-posts.search') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="position-search-section">
                        <div class="search-input-wrapper">
                            <i data-lucide="search"></i>
                            <input type="text"
                                   name="s_text"
                                   value="{{ $s_text ?? '' }}"
                                   class="position-search-input"
                                   placeholder="{{ __('action.search') }}...">
                        </div>
                        <button type="submit" class="btn-modern btn-primary">
                            <i data-lucide="search"></i>
                            {{ __('action.search') }}
                        </button>
                        <a href="{{ route('user-posts.index') }}" class="btn-modern btn-secondary">
                            <i data-lucide="refresh-ccw"></i>
                            {{ __('action.clear') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Main Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="list"></i>
                        <span>{{ __('menu.user_positions') }}</span>
                    </div>
                    @if(!$isSuperUser)
                        <a href="{{ route('user-posts.create') }}" class="btn-modern btn-primary" style="width: auto;">
                            <i data-lucide="circle-plus"></i>
                            {{ __('action.create') }}
                        </a>
                    @endif
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table positions-table-modern">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.#') }}
                            </th>
                            <th style="text-align: left;">
                                <i data-lucide="briefcase"></i>
                                {{ __('field.name') }}
                            </th>
                            <th style="width: 250px;">
                                <i data-lucide="building"></i>
                                {{ __('field.department') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="list-ordered"></i>
                                {{ __('field.order_number') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="settings"></i>
                                {{ __('field.action') }}
                            </th>
                            <th style="width: 130px;">
                                <i data-lucide="calendar-plus"></i>
                                {{ __('field.created_at') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $item)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td class="position-name-cell">
                                    <i data-lucide="briefcase"></i>
                                    {{ $item->lang($item->id) }}
                                </td>
                                <td class="department-cell">
                                    @if($item->dep_ids && $item->dep_ids != 'null')
                                        <div class="department-list">
                                            @php
                                                $depIds = json_decode($item->dep_ids, true);
                                                if (!is_array($depIds)) {
                                                    $depIds = [];
                                                }
                                            @endphp
                                            @foreach($depIds as $depId)
                                                @php
                                                    $dep = \App\Models\Admin\Department::find($depId);
                                                @endphp
                                                @if($dep)
                                                    <span class="department-item">
                                                        <i data-lucide="building-2"></i>
                                                        {{ $dep->name_uz }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge-modern badge-primary">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="order-badge-modern">{{ $item->order_number }}</span>
                                </td>
                                <td class="td-status">
                                    @if($item->status == 1)
                                        <span class="status-badge-modern badge-success">
                                            <i data-lucide="check-circle"></i>
                                            {{ $item->isActive($item->status) }}
                                        </span>
                                    @else
                                        <span class="status-badge-modern badge-danger">
                                            <i data-lucide="x-circle"></i>
                                            {{ $item->isActive($item->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="td-action">
                                    <a href="{{ route('user-posts.edit', $item->id) }}" class="action-btn view-btn">
                                        <i data-lucide="square-pen"></i>
                                        {{ __('action.edit') }}
                                    </a>
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span class="date">{{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') }}</span>
                                        <span class="time">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</span>
                                    </div>
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
@endsection
