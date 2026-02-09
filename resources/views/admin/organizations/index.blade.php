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
                        <h1>{{ __('menu.organizations') }}</h1>
                        <p>{{ __('menu.home') }} / {{ __('menu.organizations') }}</p>
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
                    <span class="breadcrumb-item active">{{ __('menu.organizations') }}</span>
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

                <form action="{{ route('organizations.index') }}" method="post" class="filters-form-modern">
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
                                   placeholder="{{ __('field.search_placeholder_tin_and_name') }}">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('organizations.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i>
                                {{ __('field.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; align-items: start;">

                {{--                <!-- Organizations Tree Sidebar -->--}}
                {{--                <div class="table-card-modern" style="position: sticky; top: 2rem;">--}}
                {{--                    <div class="filters-header">--}}
                {{--                        <div class="filters-title">--}}
                {{--                            <i data-lucide="folder-tree"></i>--}}
                {{--                            <span>{{ __('menu.organizations') }} {{ __('field.tree') }}</span>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                    <div style="padding: 1.5rem;">--}}
                {{--                        @php $lang_key = session()->get('locale') ?? 'uz'; @endphp--}}
                {{--                        <section class="list-tree">--}}
                {{--                            <ul>--}}
                {{--                                @foreach($parentOrganizations as $key => $item)--}}
                {{--                                    <li>--}}
                {{--                                        <strong>{{ $item['name_'.$lang_key] ?? $item['name_uz'] }}</strong>--}}
                {{--                                        @if($item->childs && count($item->childs))--}}
                {{--                                            @include('admin.organizations.manageChild', ['childs' => $item->childs])--}}
                {{--                                        @endif--}}
                {{--                                    </li>--}}
                {{--                                @endforeach--}}
                {{--                            </ul>--}}
                {{--                        </section>--}}
                {{--                    </div>--}}
                {{--                </div>--}}

                <!-- Organizations Table -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="list"></i>
                            <span>{{ __('menu.organizations') }} {{ __('menu.list') }}</span>
                        </div>
{{--                        <a href="{{ route('organizations.create') }}" class="btn-modern btn-primary"--}}
{{--                           style="width: auto;">--}}
{{--                            <i data-lucide="circle-plus"></i>--}}
{{--                            {{ __('action.create') }}--}}
{{--                        </a>--}}
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
                                <th style="text-align: left;">
                                    <i data-lucide="award"></i>
                                    {{ __('field.current_subscription') }}
                                </th>
                                <th>
                                    <i data-lucide="users"></i>
                                    {{ __('field.users') }}
                                </th>
                                <th style="width: 120px;">
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </th>
                                <th style="width: 140px;">
                                    <i data-lucide="settings"></i>
                                    {{ __('field.action') }}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($organizations as $key => $item)
                                <tr class="table-row-modern">
                                    <td class="td-number">{{ $organizations->firstItem() + $key }}</td>
                                    <td class="td-doc-name">
                                        <a href="{{ route('departments.index', ['id' => $item->id]) }}"
                                           class="doc-link">
                                            <i data-lucide="building-2"></i>
                                            <strong>{{ $item->lang($item->id) }}</strong>
                                            <i data-lucide="arrow-right" class="arrow-icon"></i>
                                        </a>
                                    </td>
                                    <td>
                                        @if($item->currentSubscription && $item->currentSubscription->tariff)
                                            <div class="tariff-badge">
                                                <i data-lucide="zap"></i>
                                                {{ $item->currentSubscription->tariff->name??'' }}

                                                {!! $item->currentSubscription->status_badge??'' !!}
                                            </div>
                                        @elseif($item->pendingSubscription && $item->pendingSubscription->tariff)
                                            <div class="tariff-badge">
                                                <i data-lucide="zap"></i>
                                                {{ $item->pendingSubscription->tariff->name??'' }}

                                                {!! $item->pendingSubscription->status_badge??'' !!}
                                            </div>
                                        @else
                                            {{ __('field.no_subscription') }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-modern badge-info">
                                            @if($item->users && $item->users->count())
                                                {{ $item->users->count() }}
                                            @else
                                                0
                                            @endif
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
                                                    {{ __('status.inactive') }}
                                                </span>
                                        @endif
                                    </td>
                                    <td class="td-action">
                                        <a href="{{ route('organizations.edit', $item->id) }}"
                                           class="action-btn view-btn">
                                            <i data-lucide="square-pen"></i>
                                            {{ __('action.edit') }}
                                        </a>
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
