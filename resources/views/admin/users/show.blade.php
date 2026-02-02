@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/users/show.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

@section('content')
    <div class="document-page-modern">
        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="user-cog"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.users') }}</h1>
                        <p>{{ __('action.edit') }} - {{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}</p>
                    </div>
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <a href="{{ route('users.index') }}" class="breadcrumb-item">
                            <span>{{ __('menu.users') }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <a href="{{ route('users.show', $user->user_hash) }}" class="breadcrumb-item">
                            <span>{{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div class="col-md-12">
            @include('templates.alert')
        </div>

        <div class="content-wrapper-modern user-profile-layout">
            <!-- Left Sidebar - User Profile Card -->
            <div class="profile-sidebar">
                <div class="profile-card-modern">
                    <!-- Profile Photo Section -->
                    <div class="profile-photo-section">
                        <div class="profile-photo-wrapper">
                            @if(file_exists(public_path().'/profile_img/'.'img_'.$user->id.'.png'))
                                <img src="/profile_img/img_{{ $user->id }}.png" class="profile-photo" alt="Profile Photo" />
                            @else
                                <img src="{{URL::asset('/images/profile_image1.png')}}" class="profile-photo" alt="Default Profile" />
                            @endif
                            <div class="profile-status-badge">
                                <i data-lucide="check-circle"></i>
                            </div>
                        </div>
                        <div class="profile-name-section">
                            <h3 class="profile-name">{{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}</h3>
                            <p class="profile-middle-name">{{ $user->userPersonal->m_name??'' }}</p>
                        </div>
                    </div>

                    <!-- Profile Info Section -->
                    <div class="profile-info-section">
                        <div class="info-item">
                            <div class="info-label">
                                <i data-lucide="briefcase"></i>
                                <span>{{ __('field.department') }}</span>
                            </div>
                            <div class="info-value">{{ $user->currentWork->department->name_uz??'-' }}</div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i data-lucide="user-check"></i>
                                <span>{{ __('field.user_posts') }}</span>
                            </div>
                            <div class="info-value">{{ $user->currentWork->position->name_uz??'-' }}</div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i data-lucide="calendar"></i>
                                <span>{{ __('field.user_position_from_date') }}</span>
                            </div>
                            <div class="info-value">
                                @php($user_position_from_date = $user->currentWork->from_date??now())
                                {{ date('d.m.Y', strtotime($user->fromPositionDate($user_position_from_date))) }}
                            </div>
                        </div>
                    </div>

                    <!-- User Roles Section -->
                    <div class="profile-roles-section">
                        <div class="roles-header">
                            <i data-lucide="shield"></i>
                            <span>{{ __('field.user_roles') }}</span>
                        </div>
                        <div class="roles-list">
                            @foreach($userRoles as $key => $item)
                                <span class="badge-modern badge-primary">
                                    {{ $item->lang($item->role_id) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content - Tabs Section -->
            <div class="profile-content">
                <div class="tabs-card-modern">
                    <!-- Tab Navigation -->
                    <div class="tabs-nav-modern">
                        <button class="tab-link active" data-tab="user-personal">
                            <i data-lucide="user"></i>
                            <span>{{ __('field.user_personal_info') }}</span>
                        </button>
                        <button class="tab-link" data-tab="user-working">
                            <i data-lucide="briefcase"></i>
                            <span>{{ __('field.user_working_histories') }}</span>
                        </button>
                        <button class="tab-link" data-tab="user-login-password">
                            <i data-lucide="lock"></i>
                            <span>{{ __('field.user_login_password') }}</span>
                        </button>
                        <button class="tab-link" data-tab="user-setting">
                            <i data-lucide="settings"></i>
                            <span>{{ __('field.user_setting') }}</span>
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tabs-content-modern">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane active" id="user-personal">
                            <div class="tab-pane-header">
                                <h3>{{ __('field.user_personal_info') }}</h3>
                            </div>

                            <div class="personal-info-grid">
                                <div class="info-card">
                                    <div class="info-card-icon">
                                        <i data-lucide="user" class="text-white"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">Username (Login)</div>
                                        <div class="info-card-value">{{ $user->username }}</div>
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="info-card-icon">
                                        <i data-lucide="mail" class="text-white"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">Email</div>
                                        <div class="info-card-value">{{ $user->email }}</div>
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="info-card-icon">
                                        <i data-lucide="phone" class="text-white"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">Mobile</div>
                                        <div class="info-card-value">{{ $user->userPersonal->phone??'-' }}</div>
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="info-card-icon">
                                        <i data-lucide="activity" class="text-white"></i>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-card-label">Status</div>
                                        <div class="info-card-value">
                                            @if($user->status == 1)
                                                <span class="status-badge-modern badge-success">
                                                    <i data-lucide="check-circle"></i>
                                                    {{ $user->isActive($user->status) }}
                                                </span>
                                            @else
                                                <span class="status-badge-modern badge-danger">
                                                    <i data-lucide="x-circle"></i>
                                                    {{ $user->isActive($user->status) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="description-section">
                                <h4>{{ __('field.description') ?? 'Описание' }}</h4>
                            </div>

                            <div class="action-buttons">
                                <a href="{{ route('users.edit', $user->user_hash) }}" class="btn-modern btn-primary">
                                    <i data-lucide="edit"></i>
                                    <span>{{ __('action.edit') }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Working History Tab -->
                        <div class="tab-pane" id="user-working">
                            <div class="tab-pane-header">
                                <h3>{{ __('field.user_working_histories') }}</h3>
                            </div>

                            <div class="table-card-modern">
                                <div class="table-wrapper-modern">
                                    <table class="modern-table">
                                        <thead>
                                        <tr>
                                            <th><i data-lucide="hash"></i> {{ __('field.number') }}</th>
                                            <th><i data-lucide="briefcase"></i> {{ __('field.dep_id') }}</th>
                                            <th><i data-lucide="user-check"></i> {{ __('field.post_id') }}</th>
                                            <th><i data-lucide="calendar"></i> {{ __('field.from_date') }}</th>
                                            <th><i data-lucide="calendar-x"></i> {{ __('field.to_date') }}</th>
                                            <th><i data-lucide="activity"></i> {{ __('field.status') }}</th>
                                            <th><i data-lucide="clock"></i> {{ __('field.date') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($userWorkingHistories as $key => $item)
                                            <tr class="table-row-modern">
                                                <td class="td-number">{{ $key+1 }}</td>
                                                <td>{{ $item->department->name_uz??'-' }}</td>
                                                <td>{{ $item->position->name_uz??'-' }}</td>
                                                <td class="td-date">{{ date('d.m.Y', strtotime($item->from_date)) }}</td>
                                                <td class="td-date">{{ date('d.m.Y', strtotime($item->to_date)) }}</td>
                                                <td>
                                                    <span class="badge-modern badge-info">{{ $item->status }}</span>
                                                </td>
                                                <td class="td-date">{{ date('d.m.Y', strtotime($item->created_at)) }}</td>
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

                        <!-- Login & Password Tab -->
                        <div class="tab-pane" id="user-login-password">
                            <div class="tab-pane-header">
                                <h3>{{ __('field.user_login_password') }}</h3>
                            </div>

                            <form method="POST" action="{{ url('admin/user/edit-user-login-password', [$user->id, $user->user_hash]) }}" class="modern-form">
                                @csrf
                                @method('PUT')

                                <div class="form-grid">
                                    <div class="form-group-modern full-width">
                                        <label>
                                            <i data-lucide="mail"></i>
                                            {{ __('field.email') }}
                                        </label>
                                        <input type="email" value="{{ $user->email }}" class="form-control-modern" name="email" required>
                                    </div>

                                    <div class="form-group-modern full-width">
                                        <label>
                                            <i data-lucide="user"></i>
                                            {{ __('field.username') }}
                                        </label>
                                        <input type="text" value="{{ $user->username }}" class="form-control-modern" name="username" required>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="lock"></i>
                                            {{ __('field.password') }}
                                        </label>
                                        <div class="password-input-wrapper">
                                            <input type="password" class="form-control-modern" name="password" id="password" placeholder="{{ __('field.new_password') ?? 'Новый пароль' }}">
                                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                                <i data-lucide="eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="lock"></i>
                                            {{ __('field.confirm_password') }}
                                        </label>
                                        <div class="password-input-wrapper">
                                            <input type="password" class="form-control-modern" name="password_confirmation" id="password_confirmation" placeholder="{{ __('field.confirm_password') }}">
                                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                                <i data-lucide="eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-modern btn-primary">
                                        <i data-lucide="save"></i>
                                        <span>{{ __('action.save') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Settings Tab -->
                        <div class="tab-pane" id="user-setting">
                            <div class="tab-pane-header">
                                <h3>{{ __('field.user_setting') }}</h3>
                            </div>

                            <form method="POST" action="{{ url('admin/user/edit-user-status', [$user->id, $user->user_hash]) }}" class="modern-form">
                                @csrf
                                @method('PUT')

                                <div class="form-grid">
                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="activity"></i>
                                            {{ __('field.user_status') }}
                                        </label>
                                        <select class="form-control-modern select2" name="user_status" style="width: 100%">
                                            @foreach($statusList as $key => $item)
                                                <option value="{{ $item->code }}" @if($item->code == $user->status) selected @endif>
                                                    {{ $key+1 }}. {{ $item->lang($item->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="arrow-up-down"></i>
                                            {{ __('field.user_sort') }}
                                        </label>
                                        <input class="form-control-modern" name="user_sort" value="{{ $user->userPersonal->sort }}" type="number" min="0"/>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-modern btn-primary">
                                        <i data-lucide="save"></i>
                                        <span>{{ __('action.save') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab Switching Logic
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');

                    // Remove active class from all tabs and panes
                    tabLinks.forEach(l => l.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));

                    // Add active class to clicked tab and corresponding pane
                    this.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');

                    // Reinitialize Lucide icons for the new content
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            });

            // Initialize Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    placeholder: "{{ __('action.select') }}",
                    allowClear: true
                });
            }

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Password Toggle Function
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                button.innerHTML = '<i data-lucide="eye-off"></i>';
            } else {
                field.type = 'password';
                button.innerHTML = '<i data-lucide="eye"></i>';
            }

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    </script>
@endsection
