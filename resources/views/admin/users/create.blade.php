@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/users/show.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/organizations/create.css') }}">

    @php $lang_key = session()->get('locale') ?? 'uz'; @endphp

    <div class="document-page-modern">

        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="user-plus"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.create') }} {{ __('menu.users') }}</h1>
                        <p>{{ __('menu.users') }} / {{ __('action.create') }}</p>
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
                    <a href="{{ route('users.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.users') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">
            <div class="tabs-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="user-plus"></i>
                        <span>{{ __('action.create') }} {{ __('menu.users') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('users.store') }}" class="modern-form" id="user-create-form">
                        @csrf

                        <div class="form-grid">

                            {{-- Организация: супер-админ выбирает любую, остальным подставлена своя --}}
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="building-2"></i>
                                    {{ __('menu.organization') }}
                                    <span class="text-danger">*</span>
                                </label>
                                @if($isSuperUser)
                                    <select name="org_id" id="org_id" class="form-control-modern select2" required>
                                        <option value="">{{ __('action.choose') }}</option>
                                        @foreach($organizations as $org)
                                            <option value="{{ $org->id }}" {{ old('org_id') == $org->id ? 'selected' : '' }}>
                                                {{ $org['name_'.$lang_key] ?? $org->name_uz }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="org_id" id="org_id" value="{{ $currentOrgId }}">
                                    <input type="text" class="form-control-modern"
                                           value="{{ $organizations->first()['name_'.$lang_key] ?? ($organizations->first()->name_uz ?? '') }}"
                                           disabled>
                                @endif
                                {!! $errors->first('org_id', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Отдел --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="briefcase"></i>
                                    {{ __('field.department') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="dep_id" id="dep_id" class="form-control-modern" required>
                                    <option value="">{{ __('action.choose') }}</option>
                                    @foreach($departments as $dep)
                                        <option value="{{ $dep->id }}" {{ old('dep_id') == $dep->id ? 'selected' : '' }}>
                                            {{ $dep['name_'.$lang_key] ?? $dep->name_uz }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('dep_id', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Должность --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="award"></i>
                                    {{ __('field.post_name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="post_id" id="post_id" class="form-control-modern" required>
                                    <option value="">{{ __('action.choose') }}</option>
                                </select>
                                {!! $errors->first('post_id', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Фамилия --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('field.l_name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="l_name" maxlength="50" required
                                       class="form-control-modern {{ $errors->first('l_name') ? 'border-danger' : '' }}"
                                       value="{{ old('l_name') }}">
                                {!! $errors->first('l_name', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Имя --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('field.f_name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="f_name" maxlength="50" required
                                       class="form-control-modern {{ $errors->first('f_name') ? 'border-danger' : '' }}"
                                       value="{{ old('f_name') }}">
                                {!! $errors->first('f_name', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Отчество --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="user"></i>
                                    {{ __('field.m_name') }}
                                </label>
                                <input type="text" name="m_name" maxlength="50"
                                       class="form-control-modern" value="{{ old('m_name') }}">
                            </div>

                            {{-- Телефон --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="phone"></i>
                                    {{ __('field.phone') }}
                                </label>
                                <input type="text" name="phone" maxlength="50"
                                       class="form-control-modern" value="{{ old('phone') }}">
                            </div>

                            {{-- Адрес --}}
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="map-pin"></i>
                                    {{ __('field.live_address') }}
                                </label>
                                <input type="text" name="address" maxlength="500"
                                       class="form-control-modern" value="{{ old('address') }}">
                            </div>

                            {{-- Email --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="mail"></i>
                                    {{ __('field.email') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" id="check_email" required
                                       class="form-control-modern {{ $errors->first('email') ? 'border-danger' : '' }}"
                                       placeholder="a.ivanov@edoline.uz" value="{{ old('email') }}">
                                <p id="email-exists-msg" class="text-danger"
                                   style="font-size:13px; margin-top:0.5rem; display:none;">
                                    {{ __('field.email') }} — band.
                                </p>
                                {!! $errors->first('email', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Логин --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="at-sign"></i>
                                    {{ __('field.type_login') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="username" maxlength="50" required
                                       class="form-control-modern {{ $errors->first('username') ? 'border-danger' : '' }}"
                                       value="{{ old('username') }}">
                                {!! $errors->first('username', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Пароль --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="lock"></i>
                                    {{ __('field.password') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" minlength="6" required autocomplete="new-password"
                                       class="form-control-modern {{ $errors->first('password') ? 'border-danger' : '' }}">
                                {!! $errors->first('password', '<p class="text-danger" style="font-size:13px; margin-top:0.5rem;">:message</p>') !!}
                            </div>

                            {{-- Подтверждение пароля --}}
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="lock"></i>
                                    {{ __('field.confirm_password') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password_confirmation" minlength="6" required
                                       autocomplete="new-password" class="form-control-modern">
                            </div>

                            {{-- Роли --}}
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="shield"></i>
                                    {{ __('field.roles') }}
                                </label>
                                <select name="role_ids[]" class="form-control-modern select2" multiple>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ collect(old('role_ids'))->contains($role->id) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="form-actions" style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                            <a href="{{ route('users.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            /* Организация → отделы. Для не-супер-админа организация одна и
               отделы уже отрендерены сервером, поэтому обработчик только для select. */
            $('#org_id').on('change', function () {
                let orgId = $(this).val();
                let dep = $('#dep_id');
                let post = $('#post_id');

                dep.html('<option value="">{{ __('action.choose') }}</option>');
                post.html('<option value="">{{ __('action.choose') }}</option>');

                if (!orgId) return;

                $.get("{{ url('admin/user-posts/get-organization-departments') }}/" + orgId, function (res) {
                    if (res.status !== 200) return;
                    let lang = "{{ $lang_key }}";
                    $.each(res.data, function (i, d) {
                        let name = d['name_' + lang] || d.name_uz;
                        dep.append('<option value="' + d.id + '">' + name + '</option>');
                    });
                });
            });

            /* Отдел → должности организации этого отдела. */
            $('#dep_id').on('change', function () {
                let depId = $(this).val();
                let post = $('#post_id');

                post.html('<option value="">{{ __('action.choose') }}</option>');
                if (!depId) return;

                $.get("{{ url('admin/get-positions') }}/" + depId, function (res) {
                    let list = res.positions || res.data || [];
                    $.each(list, function (i, p) {
                        post.append('<option value="' + p.id + '">' + (p.name || p.name_uz) + '</option>');
                    });
                });
            });

            /* Подсказка о занятом email — тот же эндпоинт, что использовался раньше. */
            $('#check_email').on('blur', function () {
                let val = $(this).val();
                if (!val) return;

                $.get("{{ url('admin/user-check-email') }}/" + encodeURIComponent(val), function (res) {
                    $('#email-exists-msg').toggle(!!res.message);
                });
            });

            // Если отделы отрендерены сервером (не супер-админ) — сразу подтянем должности.
            if ($('#dep_id').val()) {
                $('#dep_id').trigger('change');
            }
        });
    </script>
@endsection
