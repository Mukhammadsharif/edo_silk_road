@php use Carbon\Carbon; @endphp
@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/users/index.css') }}">
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
                        <h1>{{ __('menu.hr_emp') }}</h1>
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
                        <span class="breadcrumb-item active">{{ __('menu.hr_emp') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            @include('templates.alert')
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
                        <span class="count-label">{{ __('field.users') ?? 'пользователей' }}</span>
                    </div>
                </div>

                <form action="{{ route('hr.emp') }}" method="POST" role="search" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item">
                            <a href="{{ route('hr.emp.create') }}" class="btn-modern btn-primary">
                                <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                            </a>
                        </div>

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
                            <select name="s_status" class="select2 form-control-modern">
                                <option value="">{{ __('field.status_all') }}</option>
                                @foreach($statusList as $key => $item)
                                    <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':''}}>
                                        {{ $key+1 }}.{{ $item->lang($item->id) }}
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
                                    <option value="{{ $item->id }}" {{ $s_role == $item->id ? 'selected':''}}>
                                        {{ $key+1 }}. {{ $item->lang($item->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }} ({{ $models->total() }})
                            </button>
                            <a href="{{ route('hr.emp') }}" class="btn-modern btn-secondary">
                                <i data-lucide="rotate-ccw"></i> {{ __('action.clear') }}
                            </a>
                        </div>
                    </div>
                </form>

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
                                        <a href="{{ route('hr.emp.view', [$item->id, $item->user_hash]) }}"
                                           class="action-btn view-btn">
                                            <i data-lucide="eye"></i> {{ __('action.view') }}
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
                        {!! $models->links() !!}
                    </div>
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
        })
    </script>
@endsection
