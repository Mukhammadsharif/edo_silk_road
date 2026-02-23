@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
@section('content')
    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.emp_dating_orders') }}</h1>
                        <p>{{ __('message.manage_hr_orders') ?? 'Управление кадровыми приказами' }}</p>
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
                        <span class="breadcrumb-item active">{{ __('menu.emp_dating_orders') }}</span>
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

                <form action="{{ url('protocol/emp/hr-orders') }}" method="POST" class="filters-form-modern">
                    @csrf
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

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                <span>{{ __('action.search') }}</span>
                            </button>
                            <a href="{{ url('protocol/emp/hr-orders') }}" class="btn-modern btn-secondary">
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
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $item)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td>
                                        <span class="badge-modern badge-info">
                                            {{ $item->document->docType->lang($item->document->docType->id) }}
                                        </span>
                                </td>
                                <td class="td-doc-name">
                                    <a href="{{ url('protocol/emp/hr-order/view', [$item->id, $item->document->doc_hash]) }}" class="doc-link">
                                        <i data-lucide="file-text"></i>
                                        <span>{{ $item->document->doc_name??'-' }}</span>
                                        <i data-lucide="arrow-right" class="arrow-icon"></i>
                                    </a>
                                </td>
                                <td>{{ $item->document->doc_number??'' }}</td>
                                <td class="td-date">{{ date('d.m.Y', strtotime($item->document->doc_date)) }}</td>
                                <td class="td-status">
                                        <span class="status-badge-modern @if($item->status == 4 || $item->status == 5) badge-warning @else badge-info @endif">
                                            <i data-lucide="circle"></i>
                                            {{ $item->statusDoc($item->status) }}
                                        </span>
                                </td>
                                <td class="td-term">
                                    @php($doc_term = $item->document->doc_term??'')
                                    @if($doc_term)
                                        {{ date('d.m.Y', strtotime($item->document->doc_term)) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="td-creator">
                                    <div class="creator-cell">
                                        @php($secretaryShortName = $item->document->secretary->personal??0)
                                        @if($secretaryShortName)
                                            <i data-lucide="user"></i>
                                            <span>{{ $secretaryShortName->shortName() }}</span>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="users"></i>
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

    <script>
        // Initialize lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
