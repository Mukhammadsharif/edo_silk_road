@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('css/templates/profile/modern-profile.css') }}">

<div class="modern-container">
    <!-- Modern Page Header -->
    <div class="page-header-modern">
        <div class="header-content">
            <div class="header-left">
                <div class="page-icon">
                    <i data-lucide="user"></i>
                </div>
                <div class="page-info">
                    <h1>{{ __('menu.my_profile') }}</h1>
                    <p>Shaxsiy ma'lumotlar va sozlamalar</p>
                </div>
            </div>
            <nav class="breadcrumb-modern">
                <a href="{{ route('home') }}">
                    <i data-lucide="home"></i>
                    <span>{{ __('menu.home') }}</span>
                </a>
                <i data-lucide="chevron-right" class="separator"></i>
                <a href="#">{{ __('menu.my_profile') }}</a>
                <i data-lucide="chevron-right" class="separator"></i>
                <span class="current">{{ __('menu.view') }}</span>
            </nav>
        </div>
    </div>

    @include('templates.alert')

    <!-- Profile Info & Image Card - Combined -->
    <div class="form-card">
        <div class="profile-combined-layout">
            <!-- Left Side - Profile Info -->
            <div class="profile-info-section">
                <div class="section-header">
                    <i data-lucide="user-circle"></i>
                    <h3>Asosiy ma'lumotlar</h3>
                </div>

                <div class="profile-info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i data-lucide="user"></i>
                            <span>{{ __('field.fam_name') }}</span>
                        </div>
                        @php($user_name = $user->userPersonal??0)
                        <div class="info-value">
                            @if($user_name)
                                {{ $user_name->fullName() }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i data-lucide="building-2"></i>
                            <span>{{ __('field.organization') }}</span>
                        </div>
                        @php($user_organization = $user->organization??'')
                        <div class="info-value">
                            @if($user_organization)
                                {{ $user_organization->name_uz }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i data-lucide="briefcase"></i>
                            <span>{{ __('field.department') }}</span>
                        </div>
                        @php($user_department = $user->currentWork->department??0)
                        <div class="info-value">
                            @if($user_department)
                                {{ $user_department->lang($user_department->id) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i data-lucide="award"></i>
                            <span>{{ __('field.user_posts') }}</span>
                        </div>
                        @php($user_position = $user->currentWork->position??0)
                        <div class="info-value">
                            @if($user_position)
                                {{ $user_position->lang($user_position->id) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Profile Image -->
            <div class="profile-image-section">
                <div class="section-header">
                    <i data-lucide="image"></i>
                    <h3>Profil rasmi</h3>
                </div>

                <div class="avatar-section">
                    <div class="avatar-upload-modern">
                        <div class="avatar-edit">
                            <input type='file' id="updateImage" accept=".png, .jpg, .jpeg" />
                            <span id="loader" class="upload-loader">
                                <i class="fa fa-spinner fa-3x fa-spin"></i>
                            </span>
                        </div>
                        <div class="avatar-preview">
                            @if(file_exists(public_path().'/profile_img/'.'img_'.$user->id.'.png'))
                                <div class="show-image" id="imagePreview"
                                    style="background-image: url({{ url('/profile_img/'.'img_'.$user->id.'.png') }});">
                                    <button id="btnDelImg" type="button" class="btn-delete-image">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </div>
                            @else
                                <div id="imagePreview"
                                    style="background-image: url({{ url('/images/profile_image1.png') }});">
                                </div>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('update-image') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="file-upload-section">
                            <div class="file-input-wrapper">
                                <div class="custom-file-hidden">
                                    <input type="file" name="profile_img" accept="image/png, image/gif, image/jpeg"
                                        class="custom-file-input" id="profileImageInput" onchange="this.form.submit()">
                                </div>
                                
                                <div class="file-display-area" onclick="document.getElementById('profileImageInput').click();">
                                    <div class="file-icon">
                                        <i data-lucide="upload"></i>
                                    </div>
                                    <div class="file-text">
                                        <p class="file-name">{{ __('action.upload_profile_picture') }}</p>
                                        <span class="file-hint">PNG, JPG, JPEG (max 5MB)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Password & Account Settings Card -->
    <div class="form-card">
        <div class="section-header">
            <i data-lucide="lock"></i>
            <h3>Akkaunt sozlamalari</h3>
        </div>

        <form method="POST" action="{{ route('my.pass-update') }}">
            @csrf
            <div class="form-row-modern">
                <div class="form-group-modern">
                    <label>
                        <i data-lucide="mail"></i>
                        {{ __('field.email') }}
                    </label>
                    <input type="email" class="form-control-modern" value="{{ $user->email }}" readonly>
                </div>

                <div class="form-group-modern">
                    <label>
                        <i data-lucide="user"></i>
                        {{ __('field.username') }}
                    </label>
                    <input type="text" class="form-control-modern" value="{{ $user->username }}" readonly>
                </div>
            </div>

            <div class="form-row-modern">
                <div class="form-group-modern">
                    <label>
                        <i data-lucide="lock"></i>
                        {{ __('field.password') }}
                    </label>
                    <input type="password" name="password"
                        class="form-control-modern {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Yangi parol kiriting">
                    {!! $errors->first('password', '<p class="error-text">:message</p>') !!}
                </div>

                <div class="form-group-modern">
                    <label>
                        <i data-lucide="lock"></i>
                        {{ __('field.confirm_password') }}
                    </label>
                    <input type="password" name="passwordConf"
                        class="form-control-modern {{ $errors->has('passwordConf') ? 'is-invalid' : '' }}"
                        placeholder="Parolni tasdiqlang">
                    {!! $errors->first('passwordConf', '<p class="error-text">:message</p>') !!}
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i data-lucide="save"></i>
                {{ __('action.save') }}
            </button>

            <div class="roles-section">
                <div class="section-header-small">
                    <i data-lucide="shield-check"></i>
                    <h4>{{ __('field.my_active_roles') }}</h4>
                </div>
                <div class="roles-badges">
                    @if(count($activeRoles))
                        @foreach($activeRoles as $key1 => $role)
                            <span class="role-badge">
                                <i data-lucide="check-circle"></i>
                                {{ $role->lang($role->role_id) }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-muted">Faol rollar topilmadi</p>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Employee Orders Card -->
    <div class="form-card">
        <div class="section-header">
            <i data-lucide="file-text"></i>
            <h3>{{ __('title.emp_order') }}</h3>
        </div>

        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>{{ __('field.#') }}</th>
                        <th>{{ __('field.ord_code') }}</th>
                        <th>{{ __('field.ord_name') }}</th>
                        <th>{{ __('field.ord_num') }}</th>
                        <th>{{ __('field.from_date') }}</th>
                        <th>{{ __('field.to_date') }}</th>
                        <th>{{ __('field.status') }}</th>
                        <th>{{ __('field.created_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hrEmpOrders as $key => $model)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>
                                <span class="status-badge status-blue">
                                    {{ $model->orderType($model->ord_code) }}
                                </span>
                            </td>
                            <td>{{ $model->ord_name }}</td>
                            <td>{{ $model->ord_num }}</td>
                            <td>{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                            <td>
                                @if($model->to_date)
                                    {{ date('d.m.Y', strtotime($model->to_date)) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $model->isActive === 'A' ? 'status-success' : 'status-gray' }}">
                                    @if($model->isActive === 'A')
                                        {{ __('status.active') }}
                                    @else
                                        {{ __('status.passive') }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ date('d.m.y, H:i', strtotime($model->created_at)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i data-lucide="inbox"></i>
                                <p>{{ __('field.not_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Employee Experience Card -->
    <div class="form-card">
        <div class="section-header">
            <i data-lucide="briefcase"></i>
            <h3>{{ __('title.emp_exp') }}</h3>
        </div>

        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>{{ __('field.#') }}</th>
                        <th>{{ __('field.exp_name') }}</th>
                        <th>{{ __('field.post_name') }}</th>
                        <th>{{ __('field.exp_descr') }}</th>
                        <th>{{ __('field.from_date') }}</th>
                        <th>{{ __('field.to_date') }}</th>
                        <th>{{ __('field.is_org') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userExp as $key => $model)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $model->exp_name }}</td>
                            <td>{{ $model->post_name }}</td>
                            <td>{{ $model->exp_descr }}</td>
                            <td>{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                            <td>
                                @if($model->to_date)
                                    {{ date('d.m.Y', strtotime($model->to_date)) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($model->is_org === 'Y')
                                    <span class="status-badge status-success">
                                        {{ __('field.in_org') }}
                                    </span>
                                @else
                                    <span class="status-badge status-blue">
                                        {{ __('field.other_org') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i data-lucide="inbox"></i>
                                <p>{{ __('field.not_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>
<script>
    $(document).ready(function () {
        lucide.createIcons();
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        hideLoader();

        let profile;
        const user_id = "{{ $user->id }}";

        $('#updateImage').change(function (e) {
            profile = e.target.files[0];
            let formData = new FormData();
            formData.append('profile', profile);
            formData.append('user_id', user_id);
            
            $.ajax({
                cache: false,
                contentType: false,
                processData: false,
                type: 'post',
                data: formData,
                url: "{{ route('update-image') }}",
                beforeSend: function() {
                    showLoader();
                },
                success: function (response) {
                    // Success handling
                },
                complete: function() {
                    hideLoader();
                    location.reload();
                }
            });
        });

        $('#btnDelImg').on('click', function (e) {
            if(user_id) {
                $.ajax({
                    cache: false,
                    url: '/profile/del-user-img/'+user_id,
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        showLoader();
                        $('#btnDelImg').hide();
                    },
                    success: function(data) {
                        // Success handling
                    },
                    complete: function() {
                        hideLoader();
                        location.reload();
                    }
                });
            }
        });

        function hideLoader() {
            $('#loader').hide();
        }

        function showLoader() {
            $('#loader').show();
        }
    });
</script>

@endsection

