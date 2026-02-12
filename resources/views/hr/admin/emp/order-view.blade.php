@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <div class="document-page-modern">
        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-text"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('menu.emp_order') }}</p>
                    </div>
                </div>
                <div class="header-right">
                    @include('hr.admin.emp.breadcrumb')
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div class="col-md-12">
            @include('templates.alert')
        </div>

        <div class="hr-page-layout">
            <!-- Left Menu -->
            @include('hr.admin.emp.left-menu')

            <!-- Content Card -->
            <div class="hr-content-card">
                <div class="hr-card-header">
                    <h3><i data-lucide="file-text"></i> {{ __('menu.emp_order') }}</h3>
                </div>
                <div class="hr-card-body">
                    @if($user->status == 1)
                        <a href="{{ route('hr-order.show', $user->id) }}" class="create-btn-modern">
                            <i data-lucide="plus-circle"></i>
                            {{ __('action.create') }}
                        </a>
                    @endif

                    <div class="table-card-modern">
                        <div class="table-wrapper-modern">
                            <table class="modern-table">
                                <thead>
                                <tr>
                                    <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>
                                    <th><i data-lucide="tag"></i> {{ __('field.ord_code') }}</th>
                                    <th><i data-lucide="file-text"></i> {{ __('field.ord_name') }}</th>
                                    <th><i data-lucide="hash"></i> {{ __('field.ord_num') }}</th>
                                    <th><i data-lucide="calendar"></i> {{ __('field.from_date') }}</th>
                                    <th><i data-lucide="calendar-x"></i> {{ __('field.to_date') }}</th>
                                    <th><i data-lucide="activity"></i> {{ __('field.status') }}</th>
                                    <th><i data-lucide="settings"></i> {{ __('field.action') }}</th>
                                    <th><i data-lucide="clock"></i> {{ __('field.created_at') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="table-row-modern">
                                        <td class="td-number">{{ $key+1 }}</td>
                                        <td>
                                                <span class="badge-modern badge-primary">
                                                    {{ $model->orderType($model->ord_code) }}
                                                </span>
                                        </td>
                                        <td>{{ $model->ord_name }}</td>
                                        <td>{{ $model->ord_num }}</td>
                                        <td class="td-date">{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                                        <td class="td-date">
                                            @if($model->to_date)
                                                {{ date('d.m.Y', strtotime($model->to_date)) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($model->isActive === 'A')
                                                <span class="status-badge-modern badge-success">
                                                        <i data-lucide="check-circle"></i>
                                                        {{ __('status.active') }}
                                                    </span>
                                            @else
                                                <span class="status-badge-modern badge-danger">
                                                        <i data-lucide="x-circle"></i>
                                                        {{ __('status.passive') }}
                                                    </span>
                                            @endif
                                        </td>
                                        <td class="td-action">
                                            @if($user->status == 1)
                                                <a href="{{ route('hr-order.edit', $model->id) }}" class="action-btn view-btn">
                                                    <i data-lucide="edit"></i>
                                                    <span>{{ __('action.edit') }}</span>
                                                </a>
                                            @endif
                                        </td>
                                        <td class="td-datetime">
                                            <div class="datetime-cell">
                                                <span class="date">{{ date('d.m.y', strtotime($model->created_at)) }}</span>
                                                <span class="time">{{ date('H:i', strtotime($model->created_at)) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="empty-state">
                                            <div class="empty-state-content">
                                                <i data-lucide="file-text"></i>
                                                <p>{{ __('field.not_found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
