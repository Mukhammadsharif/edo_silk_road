@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-logs-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <style>
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding: 0.3rem 1rem !important;
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
                        <i data-lucide="file-clock"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.doc_logs') }}</h1>
                        <p>{{ __('menu.doc_logs') }} / {{ __('field.list') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('doc-logs.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.doc_logs') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('field.list') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Info Alert -->
            <div class="info-alert-modern">
                <i data-lucide="info"></i>
                <p>{{ __('field.log_info_text') }}</p>
            </div>

            <!-- Export Section -->
            <div class="export-section-modern">
                <div class="export-header-modern">
                    <i data-lucide="download"></i>
                    <h3>{{ __('action.export') }} {{ __('menu.doc_logs') }}</h3>
                </div>

                <form action="{{ route('export-doc-logs') }}" method="post" class="export-form-modern">
                    @csrf
                    <div class="export-grid-modern">
                        <!-- From Date -->
                        <div class="date-input-group-modern">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('field.from_date') }}
                                <span class="text-red">*</span>
                            </label>
                            <input type="date"
                                   name="from_date"
                                   class="date-input-modern"
                                   value="{{ date('Y-m-d', strtotime($from_date ?? '2023-01-01')) }}"
                                   required />
                        </div>

                        <!-- To Date -->
                        <div class="date-input-group-modern">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('field.to_date') }}
                                <span class="text-red">*</span>
                            </label>
                            <input type="date"
                                   name="to_date"
                                   class="date-input-modern"
                                   value="{{ date('Y-m-d', strtotime($to_date ?? now())) }}"
                                   required />
                        </div>

                        <!-- Spacer -->
                        <div class="date-input-group-modern">
                            @if($isSuperUser)
                                <label>
                                    <i data-lucide="building"></i>
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
                            @endif
                        </div>

                        <!-- Export Button -->
                        <div>
                            <button type="submit" class="export-btn-modern">
                                <i data-lucide="download"></i>
                                {{ __('action.download') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="list"></i>
                        <span>{{ __('menu.doc_logs') }} {{ __('field.list') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table logs-table-modern">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.#') }}
                            </th>
                            <th style="width: 100px;">
                                <i data-lucide="file-text"></i>
                                {{ __('field.doc_id') }}
                            </th>
                            <th style="width: 130px;">
                                <i data-lucide="tag"></i>
                                {{ __('field.doc_type') }}
                            </th>
                            <th style="text-align: left;">
                                <i data-lucide="file-text"></i>
                                {{ __('field.doc_name') }}
                            </th>
                            <th style="width: 130px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.act_type') }}
                            </th>
                            <th style="width: 130px;">
                                <i data-lucide="monitor"></i>
                                {{ __('field.computer_id') }}
                            </th>
                            <th style="width: 180px;">
                                <i data-lucide="user"></i>
                                {{ __('field.fam_name') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="calendar"></i>
                                {{ __('field.date') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $model)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ ++$key }}</td>
                                <td class="doc-id-cell">{{ $model->doc_id }}</td>
                                <td>
                                    <span class="doc-type-badge">
                                        <i data-lucide="file"></i>
                                        {{ $model->log_type }}
                                    </span>
                                </td>
                                <td class="doc-name-cell">
                                    {!! $model->doc_name !!}
                                </td>
                                <td>
                                    @php
                                        $actType = $model->actType ? $model->actType->lang($model->actType->id) : '-';
                                        $badgeClass = 'viewed';
                                        $icon = 'eye';

                                        if (stripos($actType, 'create') !== false || stripos($actType, 'yaratildi') !== false) {
                                            $badgeClass = 'created';
                                            $icon = 'plus-circle';
                                        } elseif (stripos($actType, 'update') !== false || stripos($actType, 'tahrir') !== false) {
                                            $badgeClass = 'updated';
                                            $icon = 'edit';
                                        } elseif (stripos($actType, 'delete') !== false || stripos($actType, 'o\'chirildi') !== false) {
                                            $badgeClass = 'deleted';
                                            $icon = 'trash-2';
                                        }
                                    @endphp
                                    <span class="act-type-badge {{ $badgeClass }}">
                                        <i data-lucide="{{ $icon }}"></i>
                                        {{ $actType }}
                                    </span>
                                </td>
                                <td class="computer-id-cell">{{ $model->computer_id }}</td>
                                <td>
                                    <div class="user-cell">
                                        <i data-lucide="user"></i>
                                        <span>
                                            {{ $model->userWorkHistory?->personal?->l_name ?? '-' }}
                                            {{ $model->userWorkHistory?->personal?->f_name ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <span class="date">{{ date('d.m.Y', strtotime($model->created_at)) }}</span>
                                        <span class="time">{{ date('H:i', strtotime($model->created_at)) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">
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
