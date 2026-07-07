@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/users/edit.css') }}">

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

        <div class="content-wrapper-modern">
            <!-- Edit Personal Info Card - Hidden -->
            <div class="edit-card-modern" hidden>
                <div class="card-header-modern">
                    <div class="header-title">
                        <i data-lucide="user"></i>
                        <span>{{ __('field.edit_personal_info') }}</span>
                    </div>
                </div>
                <div class="card-body-modern">
                    <form method="POST" action="{{ url('admin/user/edit-personal-info', [$user->id, $user->user_hash]) }}" class="modern-form">
                        @csrf
                        @method('PUT')
                        <div class="form-grid three-columns">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('field.l_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="l_name" class="form-control-modern{{($errors->first('l_name') ? " is-invalid" : "")}}" value="{{ $personal->l_name }}" required>
                                {!! $errors->first('l_name', '<p class="error-message">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('field.f_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="f_name" class="form-control-modern{{($errors->first('f_name') ? " is-invalid" : "")}}" value="{{ $personal->f_name }}" required>
                                {!! $errors->first('f_name', '<p class="error-message">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('field.m_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="m_name" class="form-control-modern" value="{{ $personal->m_name }}" required>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="hash"></i>
                                    {{ __('field.doc_pin') }}
                                </label>
                                <input type="text" name="doc_pin" maxlength="14" class="form-control-modern" value="{{ $personal->doc_pin }}">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="credit-card"></i>
                                    {{ __('field.doc_number') }}
                                </label>
                                <input type="text" name="doc_number" maxlength="9" class="form-control-modern" value="{{ $personal->doc_number }}">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="file-text"></i>
                                    {{ __('field.doc_type') }}
                                </label>
                                @php($doc_type = $user->userPersonal->doc_type??0)
                                <select class="form-control-modern" name="doc_type">
                                    <option value="1" @if($doc_type == 1) selected @endif>Passport</option>
                                    <option value="2" @if($doc_type == 2) selected @endif>ID Card</option>
                                </select>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="calendar"></i>
                                    {{ __('field.birthday') }}
                                </label>
                                <input type="date" name="birthday" class="form-control-modern" value="{{ date('Y-m-d', strtotime($personal->birthday)) }}">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="users"></i>
                                    {{ __('field.gender') }}
                                </label>
                                @php($gender = $user->userPersonal->gender??0)
                                <select class="form-control-modern" name="gender">
                                    <option value="1" @if($gender == 1) selected @endif>Erkak</option>
                                    <option value="2" @if($gender == 2) selected @endif>Ayol</option>
                                </select>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="phone"></i>
                                    {{ __('field.phone') }}
                                </label>
                                <input type="text" name="phone" maxlength="13" class="form-control-modern" value="{{ $personal->phone }}">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="map-pin"></i>
                                    {{ __('field.address') ?? 'Адрес проживания' }}
                                </label>
                                <input type="text" name="address" maxlength="500" class="form-control-modern" value="{{ $personal->address ?? '' }}">
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

            <!-- User Position Card - Hidden -->
            <div class="edit-card-modern" hidden>
                <div class="card-header-modern">
                    <div class="header-title">
                        <i data-lucide="briefcase"></i>
                        <span>{{ __('field.user_position') }}</span>
                    </div>
                </div>
                <div class="card-body-modern">
                    <form method="POST" action="{{ url('admin/user/edit-user-dep-position', [$user->id, $user->user_hash]) }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <!-- Current Position Info -->
                        <div class="current-position-info">
                            <div class="info-block">
                                <div class="info-label">
                                    <i data-lucide="briefcase"></i>
                                    {{ __('field.user_current_department') }}
                                </div>
                                <div class="info-value department-path">
                                    @if($user->currentWork && $user->currentWork->department)
                                        @foreach($user->currentWork->department->allParents()->reverse() as $item)
                                            <span class="path-item">{{ $item->name_uz }}</span>
                                            <i data-lucide="chevron-right" class="path-separator"></i>
                                        @endforeach
                                        <span class="path-item current">{{ $user->currentWork->department->name_uz??'' }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="info-block">
                                <div class="info-label">
                                    <i data-lucide="user-check"></i>
                                    {{ __('field.user_current_position') }}
                                </div>
                                <div class="info-value">{{ $user->currentWork->position ? $user->currentWork->position->name_uz??'' : '' }}</div>
                            </div>

                            <div class="info-block">
                                <div class="info-label">
                                    <i data-lucide="calendar"></i>
                                    {{ __('field.user_position_from_date') }}
                                </div>
                                <div class="info-value">
                                    @php($user_position_from_date = $user->currentWork->from_date??now())
                                    {{ date('d.m.Y', strtotime($user_position_from_date)) }}
                                </div>
                            </div>
                        </div>

                        <div class="divider-modern"></div>

                        <!-- New Position Selection -->
                        <div class="form-grid">
                            <div class="form-group-modern full-width">
                                <div class="department-selects">
                                    <label>
                                        <i data-lucide="building-2"></i>
                                        {{ __('field.choose_department') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="form-group">
                                        <select class="form-control-modern parent_dep" name="dep_id">
                                            <option value="" selected="true">{{ __('field.choose') }}</option>
                                            @if(isset($departments))
                                                @foreach($departments as $key => $item)
                                                    <option value="{{ $item->id }}">{{ $item->name_uz }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        {!! $errors->first('dep_id', '<p class="error-message">:message</p>') !!}
                                    </div>
                                </div>
                                <div class="loader-wrapper" id="loader" style="display: none">
                                    <i data-lucide="loader" class="spinner"></i>
                                    <span>Загрузка...</span>
                                </div>
                            </div>

                            <div class="form-group-modern user_position_div" style="display: none">
                                <label>
                                    <i data-lucide="user-check"></i>
                                    {{ __('field.choose_position') }} <span class="text-danger">*</span>
                                </label>
                                <select name="post_id" id="user_position_select" class="form-control-modern">
                                </select>
                            </div>
                        </div>

                        <div class="form-group-modern from_date_div" style="display: none">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('field.user_position_from_date') }}
                            </label>
                            <input type="date" name="user_position_from_date" class="form-control-modern" value="{{ date('Y-m-d', strtotime(now())) }}">
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

            <!-- Two Column Layout for Roles and ECP -->
            <div class="two-column-layout">
                <!-- User Roles Card -->
                <div class="edit-card-modern">
                    <div class="card-header-modern">
                        <div class="header-title">
                            <i data-lucide="shield"></i>
                            <span>{{ __('field.user_roles') }}</span>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <form method="POST" action="{{ url('admin/user/edit-user-roles', [$user->id, $user->user_hash]) }}" class="modern-form">
                            @csrf
                            @method('PUT')

                            <div class="roles-list-modern">
                                @foreach($roles as $key => $item)
                                    <div class="role-item-modern">
                                        <div class="role-checkbox">
                                            <input type="checkbox"
                                                   name="role_ids[]"
                                                   value="{{ $item->id }}"
                                                   id="role_{{ $item->id }}"
                                                   class="modern-checkbox"
                                                   @if(in_array($item->id, $userRoles)) checked @endif />
                                            <label for="role_{{ $item->id }}" class="checkbox-label">
                                                <span class="checkbox-custom"></span>
                                                <span class="role-info">
                                                    <span class="role-number">{{ $key+1 }}.</span>
                                                    <span class="role-name">{{ $item->lang($item->id) }}</span>
                                                    <span class="role-code">({{ $item->name }})</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
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

                <!-- ECP Status Card -->
                <div class="edit-card-modern">
                    <div class="card-header-modern">
                        <div class="header-title">
                            <i data-lucide="key"></i>
                            <span>{{ __('field.ecp_status') }}</span>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <form method="POST" action="{{ url('admin/user/edit-ecp-status', [$user->id, $user->user_hash]) }}" class="modern-form">
                            @csrf
                            @method('PUT')

                            <div class="ecp-toggle-section">
                                <div class="toggle-item-modern">
                                    <input type="checkbox"
                                           name="status"
                                           id="ecp_status"
                                           class="modern-toggle"
                                           @if($user->userPersonal->doc_sign_type == 1) checked @endif />
                                    <label for="ecp_status" class="toggle-label">
                                        <span class="toggle-switch"></span>
                                        <span class="toggle-text">{{ __('field.status') }}</span>
                                    </label>
                                </div>

                                <div class="alert-modern alert-primary">
                                    <i data-lucide="info"></i>
                                    <p>Holat "aktiv" bo'lgan holatda, hujjatlarni faqat ERI kaliti orqali imzolaydi</p>
                                </div>

                                <div class="alert-modern alert-warning">
                                    <i data-lucide="alert-triangle"></i>
                                    <p>Holat "passiv" bo'lgan holatda hujjatlarni ERI kalitsiz imzolashi mumkin</p>
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

    <script>
        $(document).ready(function() {
            // Department selection logic
            $('.parent_dep').on("change", function() {
                let dep_id = $(this).val();

                if (dep_id) {
                    $('.user_position_div').show();
                    $('.from_date_div').show();
                    $('.child_select_div').remove();
                    getChildDeps(dep_id);
                }
            });

            $(document).on('change','.department-selects .child_select', function() {
                let id = $(this).val();
                getChildDeps(id);
            });

            function getChildDeps(dep_id) {
                $.ajax({
                    processing : 'true',
                    serverSide : 'true',
                    url: '/admin/get-dep-pos/'+dep_id,
                    type:"GET",
                    dataType:"json",
                    beforeSend: function () {
                        $('#user_position_select').empty();
                        showLoader();
                    },
                    success:function(response) {
                        let departments = response.departments;
                        let current_dep = response.current_dep;
                        let level_number = current_dep.level_number + 1;
                        let last_level = response.last_level;

                        if (level_number > 1) {
                            for (let i = level_number; i <= last_level; i++){
                                $('.level'+i).remove();
                            }
                        }

                        if (departments.length) {
                            let childSelectOption = '<div class="form-group level'+level_number+' child_select_div">';
                            childSelectOption+= '<select class="form-control-modern child_select" name="dep_id">';
                            childSelectOption+='<option value="'+dep_id+'">Quyi bo`limni tanlang...</option>';
                            $.each(response.departments, function(key, value){
                                childSelectOption+='<option value='+value.id+'>'+value.name_uz+'</option>';
                            });
                            childSelectOption+='</select>';
                            childSelectOption+='</div>';

                            $('.department-selects').append(childSelectOption);
                        }

                        if (response.positions.length) {
                            let user_positions = '';
                            $.each(response.positions, function(key, value){
                                user_positions+='<option value='+value.id+'>'+value.name_uz+'</option>';
                            });
                            $('#user_position_select').append(user_positions);
                        }
                    },
                    complete: function() {
                        hideLoader();
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                });
            }

            hideLoader();

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        function hideLoader() {
            $('#loader').hide();
        }

        function showLoader() {
            $('#loader').show();
        }

        // Email check (if needed)
        $("#check_email").on("keyup", function() {
            let str_email = $('#check_email').val().trim();

            if (str_email) {
                if (str_email.length >= 3) {
                    $.ajax({
                        processing : 'true',
                        serverSide : 'true',
                        url: '/admin/user-check-email/' + str_email,
                        type:"GET",
                        dataType:"json",
                        beforeSend: function() {
                            $('.checking_email').html(`<span class="text-warning">Email tekshirilmoqda......</span>`);
                        },
                        success:function(response) {
                            if (response.message === true) {
                                $('.checking_email').html(`<span class="text-danger">Ushbu email tizimda mavjud...</span>`);
                            } else {
                                $('.checking_email').html(`<span class="text-success">Emaildan foydalanish mumkin</span>`);
                            }
                        }
                    });
                }
            }
        });
    </script>
@endsection
