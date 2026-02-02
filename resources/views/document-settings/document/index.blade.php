@extends('layouts.app')

@section('content')
    <link href="{{ asset('departments/tree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">

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
                        <h1>{{ __('title.doc_type_name') }}</h1>
                        <p>{{ __('title.doc_type') }} / {{ __('menu.list') }}</p>
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
                        <span>{{ __('title.doc_type') }}</span>
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

                <form action="{{ route('document-type.search') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item" style="grid-column: span 2;">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('input.doc_name') }}
                            </label>
                            <input type="text"
                                   name="doc_name"
                                   value="{{ $doc_name ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('input.doc_name') }}">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('document-type.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i>
                                {{ __('action.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 400px 1fr; gap: 1.5rem; align-items: start;">

                <!-- Document Types Tree Sidebar -->
                <div class="table-card-modern" style="position: sticky; top: 2rem;">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="git-branch"></i>
                            <span>{{ __('menu.doc_types') }} {{ __('field.tree') }}</span>
                        </div>
                    </div>
                    <div class="document-types-tree">
                        @php $lang_key = session()->get('locale') ?? 'uz'; @endphp
                        <ul>
                            @foreach($parentDocumentTypes as $key => $item)
                                <li>
                                    <div class="tree-item-content">
                                        <span class="order-number-badge">{{ $item->order_number }}</span>
                                        <span class="tree-item-text">{{ $item->lang($item->lang_id) }}</span>
                                        <span class="tree-item-code">{{ $item->doc_code }}</span>

                                        <div class="tree-item-actions">
                                            <a href="{{ route('document-type.edit', $item->id) }}" class="tree-edit-btn">
                                                <i data-lucide="square-pen"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @if(count($item->childs))
                                        @include('document-settings.document.manageChild', ['childs' => $item->childs])
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Document Types Table -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="list"></i>
                            <span>{{ __('menu.doc_types') }} {{ __('menu.list') }}</span>
                        </div>
                        <a href="{{ route('document-type.create') }}" class="btn-modern btn-primary" style="width: auto;">
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
                                    {{ __('field.#') }}
                                </th>
                                <th>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.parent_id') }}
                                </th>
                                <th style="text-align: left;">
                                    <i data-lucide="tag"></i>
                                    {{ __('field.name') }}
                                </th>
                                <th style="width: 100px;">
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
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $key => $model)
                                <tr class="table-row-modern">
                                    <td class="td-number">{{ $key + 1 }}</td>
                                    <td>
                                        @php($parent = $model->parent->title ?? null)
                                        @if($parent)
                                            <span class="badge-modern badge-info">{{ $parent }}</span>
                                        @else
                                            <span class="badge-modern badge-primary">{{ __('field.root') }}</span>
                                        @endif
                                    </td>
                                    <td class="td-doc-name">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <i data-lucide="file-text" style="color: #794DFD; width: 16px; height: 16px;"></i>
                                            <strong>{{ $model->lang($model->lang_id) }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="order-number-badge">{{ $model->order_number }}</span>
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
                                                {{ __('field.inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="td-action">
                                        <a href="{{ route('document-type.edit', $model->id) }}" class="action-btn view-btn">
                                            <i data-lucide="square-pen"></i>
                                            {{ __('action.edit') }}
                                        </a>
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
    </div>
@endsection
