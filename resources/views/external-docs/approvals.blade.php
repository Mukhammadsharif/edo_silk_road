@extends('layouts.app')

{{-- Документы Didox, назначенные текущему пользователю на внутреннее согласование --}}

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/working-documents-modern.css') }}" rel="stylesheet">

    <style>
        .didox-status { display:inline-flex; align-items:center; gap:6px; padding:4px 12px;
            border-radius:20px; font-size:12px; font-weight:600; white-space:nowrap; }
        .didox-status i, .didox-status svg { width:14px; height:14px; }
        .didox-status.st-approved  { background:#dcfce7; color:#15803d; }
        .didox-status.st-review    { background:#fef9c3; color:#a16207; }
        .didox-status.st-rejected  { background:#fee2e2; color:#b91c1c; }
        .modern-table tbody tr { cursor:pointer; }
    </style>

    <div class="document-page-modern">

        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="badge-check"></i>
                    </div>
                    <div class="header-text">
                        <h1>Мне на согласование</h1>
                        <p>{{ $org->name_ru ?? $org->name_uz }} · внешний документооборот (Didox)</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('external-docs.index') }}" class="breadcrumb-item">
                        <span>Внешний документооборот</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">Мне на согласование</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">

            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="filter"></i>
                        <span>{{ __('field.filters') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $approvals->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('external-docs.approvals') }}" method="get" class="filters-form-modern">
                    <div class="advanced-filters-modern">
                        <div class="filter-item">
                            <label><i data-lucide="activity"></i> {{ __('field.status') }}</label>
                            <select name="status" class="filter-select-modern" onchange="this.form.submit()">
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Ожидают моего решения</option>
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Все (включая рассмотренные)</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-card-modern">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th>№</th>
                            <th>{{ __('field.document_type') }}</th>
                            <th>{{ __('field.document_name') }}</th>
                            <th>{{ __('field.document_date') }}</th>
                            <th>Контрагент (ИНН)</th>
                            <th>Моё решение</th>
                            <th>Назначено</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($approvals as $key => $a)
                            @php $d = $a->document; @endphp
                            @if($d)
                                <tr onclick="window.location='{{ route('external-docs.show', $d->id) }}'">
                                    <td class="number-cell">{{ $approvals->firstItem() + $key }}</td>
                                    <td><span class="doc-type-badge-modern">{{ $typeLabels[$d->doc_type] ?? $d->doc_type }}</span></td>
                                    <td>
                                        <div class="doc-name-cell-modern">
                                            <i data-lucide="file-text"></i>
                                            <span>№ {{ $d->doc_number ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="datetime-cell">
                                            <span class="date">{{ optional($d->doc_date)->format('d.m.Y') ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $d->partner_tin ?? $d->seller_tin ?? '—' }}</td>
                                    <td>
                                        @if($a->status === 'approved')
                                            <span class="didox-status st-approved"><i data-lucide="check"></i> Согласовал</span>
                                        @elseif($a->status === 'rejected')
                                            <span class="didox-status st-rejected" title="{{ $a->comment }}"><i data-lucide="x"></i> Отклонил</span>
                                        @else
                                            <span class="didox-status st-review"><i data-lucide="clock"></i> Ожидает решения</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="datetime-cell">
                                            <span class="date">{{ $a->created_at->format('d.m.Y') }}</span>
                                            <span class="time">{{ $a->created_at->format('H:i') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="badge-check"></i>
                                        <p>{{ $status === 'pending' ? 'Нет документов, ожидающих вашего согласования.' : 'Вам ещё не назначали документы на согласование.' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($approvals->hasPages())
                    <div class="pagination-wrapper-modern">
                        {!! $approvals->links() !!}
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
