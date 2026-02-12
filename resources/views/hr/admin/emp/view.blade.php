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
                        <i data-lucide="user"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('menu.emp_about') }}</p>
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
            <div class="hr-content-card" id="printPage">
                <div class="hr-card-header">
                    <h3><i data-lucide="user"></i> {{ __('menu.emp_about') }}</h3>
                </div>
                <div class="hr-card-body">
                    <!-- Profile Section -->
                    <div class="emp-profile-section">
                        <img src="{{ url('/images/profile_image1.png') }}" alt="profile image" class="emp-profile-photo" />
                        <div class="emp-profile-info">
                            <h2 class="emp-profile-name">{{ $personal->l_name??'-' }} {{ $personal->f_name??'-' }} {{ $personal->m_name??'-' }}</h2>
                            <div class="emp-status-badge
                                @if($user->status == 1) badge-success @else badge-danger @endif">
                                @if($user->status == 1)
                                    <i data-lucide="check-circle"></i>
                                @else
                                    <i data-lucide="x-circle"></i>
                                @endif
                                <span>{{ __('field.status') }}: {{ $user->isActive($user->status) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Info Section -->
                    <div class="emp-info-section">
                        <h4 class="emp-section-title">{{ __('menu.emp_info') }}</h4>
                        <div class="emp-info-grid">
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.fam_name') }}</div>
                                <div class="emp-info-value">{{ $personal->l_name??'-' }} {{ $personal->f_name??'-' }} {{ $personal->m_name??'-' }}</div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.p_pin') }}</div>
                                <div class="emp-info-value">{{ $personal->doc_pin??'-' }}</div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.p_num') }}</div>
                                <div class="emp-info-value">{{ $personal->doc_number??'-' }}</div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.br_date') }}</div>
                                <div class="emp-info-value">
                                    @if($personal->birthday)
                                        {{ date('d.m.Y', strtotime($personal->birthday)) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.doc_type') }}</div>
                                <div class="emp-info-value">{{ $personal->doc_type??'-' }}</div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.email') }}</div>
                                <div class="emp-info-value">{{ $user->email }}</div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.username') }}</div>
                                <div class="emp-info-value">{{ $user->username??'-' }}</div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.mobile_phone') }}</div>
                                <div class="emp-info-value">{{ $personal->phone??'-' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Position Info Section -->
                    <div class="emp-info-section">
                        <h4 class="emp-section-title">{{ __('field.user_posts') }}</h4>
                        <div class="emp-info-grid">
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.department') }}</div>
                                <div class="emp-info-value">
                                    @php($department = $user->currentWork->department??0)
                                    @if($department)
                                        {{ $department->lang($department->id) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.user_posts') }}</div>
                                <div class="emp-info-value">
                                    @php($position = $user->currentWork->position??0)
                                    @if($position)
                                        {{ $position->lang($position->id) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="emp-info-item">
                                <div class="emp-info-label">{{ __('field.from_date') }}</div>
                                <div class="emp-info-value">{{ date('d.m.Y', strtotime($userWork->from_date??now())) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table Section -->
                    <div class="emp-info-section">
                        <h4 class="emp-section-title">{{ __('title.emp_order') }}</h4>
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
                                        <th><i data-lucide="clock"></i> {{ __('field.created_at') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($hrEmpOrders as $key => $model)
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
                                            <td class="td-datetime">
                                                <div class="datetime-cell">
                                                    <span class="date">{{ date('d.m.y', strtotime($model->created_at)) }}</span>
                                                    <span class="time">{{ date('H:i', strtotime($model->created_at)) }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="empty-state">
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

                    <!-- Experience Table Section -->
                    <div class="emp-info-section">
                        <h4 class="emp-section-title">{{ __('title.emp_exp') }}</h4>
                        <div class="table-card-modern">
                            <div class="table-wrapper-modern">
                                <table class="modern-table">
                                    <thead>
                                    <tr>
                                        <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>
                                        <th><i data-lucide="building"></i> {{ __('field.exp_name') }}</th>
                                        <th><i data-lucide="user-check"></i> {{ __('field.post_name') }}</th>
                                        <th><i data-lucide="file-text"></i> {{ __('field.exp_descr') }}</th>
                                        <th><i data-lucide="calendar"></i> {{ __('field.from_date') }}</th>
                                        <th><i data-lucide="calendar-x"></i> {{ __('field.to_date') }}</th>
                                        <th><i data-lucide="map-pin"></i> {{ __('field.is_org') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($userExp as $key => $model)
                                        <tr class="table-row-modern">
                                            <td class="td-number">{{ $key+1 }}</td>
                                            <td>{{ $model->exp_name }}</td>
                                            <td>{{ $model->post_name }}</td>
                                            <td>{{ $model->exp_descr }}</td>
                                            <td class="td-date">{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                                            <td class="td-date">
                                                @if($model->to_date)
                                                    {{ date('d.m.Y', strtotime($model->to_date)) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($model->is_org === 'Y')
                                                    <span class="badge-modern badge-success">
                                                            {{ __('field.in_org') }}
                                                        </span>
                                                @else
                                                    <span class="badge-modern badge-info">
                                                            {{ __('field.other_org') }}
                                                        </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="empty-state">
                                                <div class="empty-state-content">
                                                    <i data-lucide="briefcase"></i>
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

            <!-- Print Button -->
            <button type="button" onclick="printPageNew()" class="print-btn-modern">
                <i data-lucide="printer"></i>
                {{ __('action.print') }}
            </button>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('/print/print.css') }}" type="text/css">
    <script src='{{ asset('/print/print.js') }}'></script>

    <script>
        $(document).ready(function() {
            let locale_code = "{{ config('app.locale') }}";

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        function printPageNew() {
            printJS({
                printable: 'printPage',
                type: 'html',
                css: [
                    "{{ asset('/assets/dist/css/main.css') }}",
                    "{{ asset('/assets/dist/css/theme.min.css') }}",
                    "{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}",
                    "{{ asset('/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}",
                    "{{ asset('/assets/node_modules/bootstrap/scss/mixins/_grid.scss') }}",
                ],
                scanStyles: false
            });
        }
    </script>
@endsection
