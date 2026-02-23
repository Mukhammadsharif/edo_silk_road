@php use Carbon\Carbon; @endphp
@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/users/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

@section('content')
    <div class="document-page-modern">
        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.users') }}</h1>
                        <p>{{ __('message.manage_users') ?? 'Управление пользователями системы' }}</p>
                    </div>
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('menu.users') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div class="col-md-12">
            @include('templates.alert')
        </div>

        <div class="content-wrapper-modern">
            <!-- Filters Card -->
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="filter"></i>
                        <span>{{ __('action.filters') ?? 'Фильтры' }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.users') ?? 'пользователей' }}</span>
                    </div>
                </div>

                <form action="{{ route('user.search') }}" method="POST" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        @if($isSuperUser)
                            <div class="filter-item">
                                <label>
                                    <i data-lucide="building-2"></i>
                                    {{ __('field.organization') }}
                                </label>
                                <select name="s_org" class="form-control-modern select2">
                                    <option value="">{{ __('action.all') ?? 'Все' }}</option>
                                    @foreach($organizations as $key => $organization)
                                        <option
                                            value="{{ $organization->id }}" {{ $s_org == $organization->id ? 'selected':'' }}>
                                            {{ ++$key }}. {{ $organization->lang($organization->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="filter-item">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </label>
                            <input type="text" name="s_text" placeholder="{{ __('action.search') }}"
                                   value="{{ $s_text??'' }}" class="form-control-modern">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </label>
                            <select name="s_status" class="form-control-modern select2">
                                <option value="">{{ __('field.status_all') }}</option>
                                @foreach($statusList as $key => $item)
                                    <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':'' }}>
                                        {{ $key+1 }}. {{ $item->lang($item->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="shield"></i>
                                {{ __('field.role') }}
                            </label>
                            <select name="s_role" class="form-control-modern select2">
                                <option value="">{{ __('field.role_all') }}</option>
                                @foreach($userRoles as $key => $item)
                                    <option value="{{ $item->id }}" {{ $s_role == $item->id ? 'selected':'' }}>
                                        {{ $key+1 }}. {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                <span>{{ __('action.search') }}</span>
                            </button>
                            <a href="{{ route('users.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="rotate-ccw"></i>
                                <span>{{ __('action.clear') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Users Table Card -->
            <div class="table-card-modern">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>
                            <th><i data-lucide="user"></i> {{ __('field.fam_name') }}</th>
                            <th><i data-lucide="mail"></i> {{ __('field.email') }}</th>
                            <th><i data-lucide="briefcase"></i> {{ __('field.department') }}</th>
                            <th><i data-lucide="activity"></i> {{ __('field.status') }}</th>
                            <th><i data-lucide="shield"></i> {{ __('field.roles') }}</th>
                            <th><i data-lucide="settings"></i> {{ __('field.action') }}</th>
                            <th><i data-lucide="clock"></i> {{ __('field.date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $item)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td class="td-user-name">
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <i data-lucide="user"></i>
                                        </div>
                                        <div class="user-details">
                                            <span
                                                class="user-name">{{ $item->userPersonal->l_name??'-' }} {{ $item->userPersonal->f_name??'' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-email">
                                    <div class="email-cell">
                                        <i data-lucide="mail"></i>
                                        <span>{{ $item->email }}</span>
                                    </div>
                                </td>
                                <td class="td-department">
                                    <div class="department-cell">
                                        <span
                                            class="department-name">{{ $item->currentWork->department->lang($item->currentWork->department->id)??'-' }}</span>
                                        <span
                                            class="position-name">{{ $item->currentWork->position->lang($item->currentWork->position->id)??'-' }}</span>
                                    </div>
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
                                <td class="td-roles">
                                    <div class="roles-cell">
                                        @if(count($item->userRoles))
                                            @foreach($item->userRoles as $key1 => $role)
                                                <span class="badge-modern badge-primary">
                                                        {{ $role->lang($role->role_id) }}
                                                    </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="td-action">
                                    <a href="{{ route('users.show', $item->user_hash) }}" class="action-btn view-btn">
                                        <i data-lucide="eye"></i>
                                        <span>{{ __('action.view') }}</span>
                                    </a>
                                </td>
                                <td class="td-datetime">
                                    <div class="datetime-cell">
                                        <span
                                            class="date">{{ Carbon::parse($item->created_at)->format('d.m.y') }}</span>
                                        <span class="time">{{ Carbon::parse($item->created_at)->format('H:i') }}</span>
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

            <!-- Pending Requests Card - FOR SUPER USER -->
            {{--            @if($isSuperUser && !empty($pendingRequests))--}}
            {{--                <div class="table-card-modern pending-requests-card">--}}
            {{--                    <div class="card-header-modern">--}}
            {{--                        <div class="header-title">--}}
            {{--                            <i data-lucide="user-plus"></i>--}}
            {{--                            <span>{{ __('field.pending_requests') ?? 'Ожидающие заявки' }}</span>--}}
            {{--                        </div>--}}
            {{--                        <div class="header-badge">--}}
            {{--                            <span class="count-badge">{{ count($pendingRequests) }}</span>--}}
            {{--                        </div>--}}
            {{--                    </div>--}}

            {{--                    <div class="table-wrapper-modern">--}}
            {{--                        <table class="modern-table">--}}
            {{--                            <thead>--}}
            {{--                            <tr>--}}
            {{--                                <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>--}}
            {{--                                <th><i data-lucide="user"></i> {{ __('field.fam_name') }}</th>--}}
            {{--                                <th><i data-lucide="mail"></i> {{ __('field.email') }}</th>--}}
            {{--                                <th><i data-lucide="building-2"></i> {{ __('field.organization') }}</th>--}}
            {{--                                <th><i data-lucide="clock"></i> {{ __('field.date') }}</th>--}}
            {{--                                <th><i data-lucide="settings"></i> {{ __('field.action') }}</th>--}}
            {{--                            </tr>--}}
            {{--                            </thead>--}}
            {{--                            <tbody>--}}
            {{--                            @forelse($pendingRequests as $key => $request)--}}
            {{--                                <tr class="table-row-modern request-row">--}}
            {{--                                    <td class="td-number">{{ $key+1 }}</td>--}}
            {{--                                    <td class="td-user-name">--}}
            {{--                                        <div class="user-info-cell">--}}
            {{--                                            <div class="user-avatar pending">--}}
            {{--                                                <i data-lucide="user"></i>--}}
            {{--                                            </div>--}}
            {{--                                            <div class="user-details">--}}
            {{--                                                <span class="user-name">{{ $request->userPersonal->l_name??'-' }} {{ $request->userPersonal->f_name??'' }}</span>--}}
            {{--                                            </div>--}}
            {{--                                        </div>--}}
            {{--                                    </td>--}}
            {{--                                    <td class="td-email">--}}
            {{--                                        <div class="email-cell">--}}
            {{--                                            <i data-lucide="mail"></i>--}}
            {{--                                            <span>{{ $request->email }}</span>--}}
            {{--                                        </div>--}}
            {{--                                    </td>--}}
            {{--                                    <td class="td-organization">--}}
            {{--                                        <div class="org-cell">--}}
            {{--                                            <i data-lucide="building-2"></i>--}}
            {{--                                            <span>{{ $request->userRequestOrg->organization->lang($request->userRequestOrg->organization->id)??'-' }}</span>--}}
            {{--                                        </div>--}}
            {{--                                    </td>--}}
            {{--                                    <td class="td-datetime">--}}
            {{--                                        <div class="datetime-cell">--}}
            {{--                                            <span class="date">{{ Carbon::parse($request->created_at)->format('d.m.y') }}</span>--}}
            {{--                                            <span class="time">{{ Carbon::parse($request->created_at)->format('H:i') }}</span>--}}
            {{--                                        </div>--}}
            {{--                                    </td>--}}
            {{--                                    <td class="td-action">--}}
            {{--                                        <div class="action-buttons-group">--}}
            {{--                                            <button class="action-btn accept-btn"--}}
            {{--                                                    data-name="{{ $request->userPersonal->l_name??'' }} {{ $request->userPersonal->f_name??'' }}"--}}
            {{--                                                    data-url="{{ route('admin.accept-user', $request->id) }}">--}}
            {{--                                                <i data-lucide="check"></i>--}}
            {{--                                                <span>{{ __('action.accept') }}</span>--}}
            {{--                                            </button>--}}
            {{--                                            <button class="action-btn reject-btn"--}}
            {{--                                                    data-name="{{ $request->userPersonal->l_name??'' }} {{ $request->userPersonal->f_name??'' }}"--}}
            {{--                                                    data-url="{{ route('admin.reject-user', $request->id) }}">--}}
            {{--                                                <i data-lucide="x"></i>--}}
            {{--                                                <span>{{ __('action.reject') }}</span>--}}
            {{--                                            </button>--}}
            {{--                                        </div>--}}
            {{--                                    </td>--}}
            {{--                                </tr>--}}
            {{--                            @empty--}}
            {{--                                <tr>--}}
            {{--                                    <td colspan="6" class="empty-state">--}}
            {{--                                        <div class="empty-state-content">--}}
            {{--                                            <i data-lucide="user-check"></i>--}}
            {{--                                            <p>{{ __('field.no_pending_requests') }}</p>--}}
            {{--                                        </div>--}}
            {{--                                    </td>--}}
            {{--                                </tr>--}}
            {{--                            @endforelse--}}
            {{--                            </tbody>--}}
            {{--                        </table>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            @endif--}}

            <!-- Pending Join Requests Card - FOR REGULAR ADMIN -->
            @unless($isSuperUser)
                @php
                    $user = \App\Models\User::find(auth()->id());
                    $organization = \App\Models\Admin\OrganizationUnits::find($user->org_unit_id);
                    $canAddUsers = $organization && $organization->canAddUser();
                    $limits = $organization ? $organization->getSubscriptionLimits() : null;
                @endphp

                <div class="table-card-modern pending-requests-card {{ !$canAddUsers ? 'limit-reached' : '' }}">
                    <div class="card-header-modern {{ !$canAddUsers ? 'header-warning' : '' }}">
                        <div class="header-title">
                            <i data-lucide="user-plus"></i>
                            <span>{{ __('menu.pending_join_requests') }}</span>
                        </div>
                        <div class="header-info">
                            @if($limits)
                                <div class="limits-info">
                                    <i data-lucide="users"></i>
                                    <span>{{ __('field.users_limit') }}: {{ $limits['users']['current'] }} / {{ $limits['users']['max'] }}</span>
                                    <span class="available-count">({{ __('field.available') }}: {{ $limits['users']['available'] }})</span>
                                    <span class="count-badge">{{ count($pendingRequests) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(!$canAddUsers)
                        <div class="limit-warning">
                            <i data-lucide="alert-circle"></i>
                            <span>{{ __('field.user_limit_reached_warning') }}</span>
                        </div>
                    @endif

                    <div class="table-wrapper-modern {{ !$canAddUsers ? 'opacity-50' : '' }}">
                        <table class="modern-table">
                            <thead>
                            <tr>
                                <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>
                                <th><i data-lucide="user"></i> {{ __('field.fam_name') }}</th>
                                <th><i data-lucide="mail"></i> {{ __('field.email') }}</th>
                                <th><i data-lucide="credit-card"></i> {{ __('field.tax_number') }}</th>
                                <th><i data-lucide="clock"></i> {{ __('field.date') }}</th>
                                <th><i data-lucide="settings"></i> {{ __('field.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($pendingRequests as $key => $request)
                                @php
                                    $requestUser = \App\Models\User::find($request->user_id);
                                    $personal = $requestUser ? $requestUser->userPersonal : null;
                                @endphp
                                <tr class="table-row-modern request-row">
                                    <td class="td-number">{{ $key+1 }}</td>
                                    <td class="td-user-name">
                                        <div class="user-info-cell">
                                            <div class="user-avatar pending">
                                                <i data-lucide="user"></i>
                                            </div>
                                            <div class="user-details">
                                                <span
                                                    class="user-name">{{ $personal->l_name ?? '-' }} {{ $personal->f_name ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="td-email">
                                        <div class="email-cell">
                                            <i data-lucide="mail"></i>
                                            <span>{{ $requestUser->email ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="td-tax-number">
                                        <div class="tax-cell">
                                            <i data-lucide="credit-card"></i>
                                            <span>{{ $request->tax_number }}</span>
                                        </div>
                                    </td>
                                    <td class="td-datetime">
                                        <div class="datetime-cell">
                                            <span
                                                class="date">{{ Carbon::parse($request->created_at)->format('d.m.y') }}</span>
                                            <span
                                                class="time">{{ Carbon::parse($request->created_at)->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="td-action">
                                        <div class="action-buttons-group">
                                            <button class="action-btn btn-success accept-btn-regular"
                                                    data-id="{{ $request->id }}"
                                                    data-name="{{ $personal->l_name ?? '' }} {{ $personal->f_name ?? '' }}"
                                                    data-url="{{ route('users.accept-request', $request->id) }}"
                                                    {{ !$canAddUsers ? 'disabled' : '' }}
                                                    @if(!$canAddUsers)
                                                        title="{{ __('field.user_limit_reached') }}"
                                                @endif>
                                                <i data-lucide="check"></i>
                                                <span>{{ __('action.accept') }}</span>
                                            </button>
                                            <button class="action-btn reject-btn-regular btn-danger"
                                                    data-id="{{ $request->id }}"
                                                    data-name="{{ $personal->l_name ?? '' }} {{ $personal->f_name ?? '' }}"
                                                    data-url="{{ route('users.reject-request', $request->id) }}">
                                                <i data-lucide="x"></i>
                                                <span>{{ __('action.reject') }}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <div class="empty-state-content">
                                            <i data-lucide="user-check"></i>
                                            <p>{{ __('field.no_pending_requests') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endunless
        </div>
    </div>

    <!-- Accept Modal - FOR SUPER USER -->
    <div class="modal fade" id="acceptModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modal-header-success">
                    <h5 class="modal-title">
                        <i data-lucide="check-circle"></i>
                        <span>{{ __('action.accept_user') ?? 'Принять пользователя' }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <form id="acceptForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="confirmation-text">
                            <p>{{ __('message.confirm_accept_user') ?? 'Вы уверены, что хотите принять пользователя' }}
                                <strong id="acceptUserName"></strong>?</p>
                        </div>

                        <div style="width: 100%" class="d-flex flex-row justify-content-between">
                            <div class="form-group-modern" style="width: 48%">
                                <label>
                                    <i data-lucide="briefcase"></i>
                                    {{ __('field.department') }} <span class="text-danger">*</span>
                                </label>
                                <select name="dep_id" id="departmentSelect" class="form-control-modern select2"
                                        style="width: 100%" required>
                                    <option value="">{{ __('action.select') }} {{ __('field.department') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">
                                            {{ $department->lang($department->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group-modern" style="width: 48%">
                                <label>
                                    <i data-lucide="user-check"></i>
                                    {{ __('field.position') }}
                                </label>
                                <select name="post_id" id="positionSelect" class="form-control-modern select2"
                                        style="width: 100%">
                                    <option value="">{{ __('action.select') }} {{ __('field.position') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex flex-row justify-content-between" style="width: 100%;">
                            <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                                <i data-lucide="x"></i>
                                <span>{{ __('action.cancel') ?? 'Отмена' }}</span>
                            </button>
                            <button type="submit" class="btn-modern btn-success ml-2">
                                <i data-lucide="check"></i>
                                <span>{{ __('action.accept') ?? 'Принять' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal - FOR SUPER USER -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title">
                        <i data-lucide="x-circle"></i>
                        <span>{{ __('action.reject_user') ?? 'Отклонить заявку' }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="confirmation-text">
                        <p>{{ __('message.confirm_reject_user') ?? 'Вы уверены, что хотите отклонить заявку пользователя' }}
                            <strong id="rejectUserName"></strong>?</p>
                        <p class="warning-text">{{ __('message.action_cannot_be_undone') ?? 'Это действие нельзя будет отменить.' }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="rejectForm" method="POST" style="width: 100%;">
                        @csrf
                        <div class="d-flex flex-row justify-content-between" style="width: 100%;">
                            <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                                <i data-lucide="x"></i>
                                <span>{{ __('action.cancel') ?? 'Отмена' }}</span>
                            </button>

                            <button type="submit" class="btn-modern btn-danger ml-2">
                                <i data-lucide="trash-2"></i>
                                <span>{{ __('action.reject') ?? 'Отклонить' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "{{ __('action.select') }}",
                allowClear: true
            });

            // Department change handler - FOR SUPER USER
            $('#departmentSelect').on('change', function () {
                let depId = $(this).val();

                if (depId) {
                    $.ajax({
                        url: '{{ route('admin.get-positions', ':id') }}'.replace(':id', depId),
                        method: 'GET',
                        success: function (data) {
                            let $positionSelect = $('#positionSelect');
                            $positionSelect.empty();
                            $positionSelect.append('<option value="">{{ __('action.select') }} {{ __('field.position') }}</option>');

                            $.each(data.positions, function (index, position) {
                                $positionSelect.append('<option value="' + position.id + '">' + position.name + '</option>');
                            });

                            $positionSelect.trigger('change');
                        }
                    });
                } else {
                    $('#positionSelect').empty().append('<option value="">{{ __('action.select') }} {{ __('field.position') }}</option>');
                }
            });

            // Accept button handler - FOR SUPER USER
            $('.accept-btn').on('click', function () {
                let name = $(this).data('name').trim() || 'Пользователь';
                let url = $(this).data('url');

                $('#acceptUserName').text(name);
                $('#acceptForm').attr('action', url);

                $('#departmentSelect').val(null).trigger('change');
                $('#positionSelect').empty().append('<option value="">{{ __('action.select') }} {{ __('field.position') }}</option>');

                $('#acceptModal').modal('show');
            });

            // Reject button handler - FOR SUPER USER
            $('.reject-btn').on('click', function () {
                let name = $(this).data('name').trim() || 'Пользователь';
                let url = $(this).data('url');

                $('#rejectUserName').text(name);
                $('#rejectForm').attr('action', url);
                $('#rejectModal').modal('show');
            });

            // Accept button handler - FOR REGULAR ADMIN
            $('.accept-btn-regular').on('click', function () {
                if ($(this).is(':disabled')) {
                    return;
                }

                let name = $(this).data('name').trim() || 'Пользователь';
                let url = $(this).data('url');

                $('#acceptUserName').text(name);
                $('#acceptForm').attr('action', url);

                $('#departmentSelect').val(null).trigger('change');
                $('#positionSelect').empty().append('<option value="">{{ __('action.select') }} {{ __('field.position') }}</option>');

                $('#acceptModal').modal('show');
            });

            // Reject button handler - FOR REGULAR ADMIN
            $('.reject-btn-regular').on('click', function () {
                let name = $(this).data('name').trim() || 'Пользователь';
                let url = $(this).data('url');

                $('#rejectUserName').text(name);
                $('#rejectForm').attr('action', url);
                $('#rejectModal').modal('show');
            });

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
