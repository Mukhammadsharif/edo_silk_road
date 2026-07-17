@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="user-plus"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('menu.hr_emp') }} / {{ __('action.create') }}</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('hr.emp') }}" class="breadcrumb-item">
                        <span>{{ __('menu.hr_emp') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Create Form Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="user-plus"></i>
                        <span>{{ __('action.create') }} {{ __('title.emp') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('hr.emp.store') }}" class="modern-form">
                        @csrf

                        <!-- Department & Position Selection -->
                        <div class="form-section-header">
                            <i data-lucide="briefcase"></i>
                            <h3>{{ __('field.work_info') }}</h3>
                        </div>

                        <div class="form-grid">
                            <div class="form-group-modern department-selects">
                                <label>
                                    <i data-lucide="building"></i>
                                    {{ __('field.choose_department') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-control-modern select2 parent_dep" name="dep_id">
                                    <option value="">{{ __('action.choose') }}</option>
                                    @if(isset($departments))
                                        @foreach($departments as $key => $item)
                                            <option value="{{ $item->id }}">
                                                {{ $key+1 }}. {{ $item->lang($item->id) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                {!! $errors->first('dep_id', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                                <span id="loader" style="display: none; color: #794DFD; font-size: 13px; margin-top: 0.5rem;">
                                    <i data-lucide="loader" class="spinning"></i> Yuklanmoqda...
                                </span>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group-modern user_position_div" style="display: none;">
                                <label>
                                    <i data-lucide="briefcase"></i>
                                    {{ __('action.choose_position') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="post_id" id="user_position_select" class="form-control-modern select2" style="width: 100% !important;">
                                </select>
                            </div>
                        </div>

                        <!-- Last Name Section -->
                        <div class="form-section-header">
                            <i data-lucide="user"></i>
                            <h3>{{ __('field.l_name') }}</h3>
                        </div>

                        <div class="form-grid" style="grid-template-columns: repeat(4, 1fr);">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    UZ Latin
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="l_name_uz"
                                       class="form-control-modern {{ $errors->first('l_name_uz') ? 'border-danger' : '' }}"
                                       value="{{ old('l_name_uz') }}"
                                       oninput="transliterate(this, 'l_name')">
                                {!! $errors->first('l_name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    UZ Cyrillic
                                </label>
                                <input type="text"
                                       name="l_name_kr"
                                       class="form-control-modern"
                                       value="{{ old('l_name_kr') }}"
                                       readonly>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    RU
                                </label>
                                <input type="text"
                                       name="l_name_ru"
                                       class="form-control-modern"
                                       value="{{ old('l_name_ru') }}"
                                       readonly>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    EN
                                </label>
                                <input type="text"
                                       name="l_name_en"
                                       class="form-control-modern"
                                       value="{{ old('l_name_en') }}"
                                       readonly>
                            </div>
                        </div>

                        <!-- First Name Section -->
                        <div class="form-section-header">
                            <i data-lucide="user"></i>
                            <h3>{{ __('field.f_name') }}</h3>
                        </div>

                        <div class="form-grid" style="grid-template-columns: repeat(4, 1fr);">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    UZ Latin
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="f_name_uz"
                                       class="form-control-modern {{ $errors->first('f_name_uz') ? 'border-danger' : '' }}"
                                       value="{{ old('f_name_uz') }}"
                                       oninput="transliterate(this, 'f_name')">
                                {!! $errors->first('f_name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    UZ Cyrillic
                                </label>
                                <input type="text"
                                       name="f_name_kr"
                                       class="form-control-modern"
                                       value="{{ old('f_name_kr') }}"
                                       readonly>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    RU
                                </label>
                                <input type="text"
                                       name="f_name_ru"
                                       class="form-control-modern"
                                       value="{{ old('f_name_ru') }}"
                                       readonly>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    EN
                                </label>
                                <input type="text"
                                       name="f_name_en"
                                       class="form-control-modern"
                                       value="{{ old('f_name_en') }}"
                                       readonly>
                            </div>
                        </div>

                        <!-- Middle Name Section -->
                        <div class="form-section-header">
                            <i data-lucide="user"></i>
                            <h3>{{ __('field.m_name') }}</h3>
                        </div>

                        <div class="form-grid" style="grid-template-columns: repeat(4, 1fr);">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    UZ Latin
                                </label>
                                <input type="text"
                                       name="m_name_uz"
                                       class="form-control-modern {{ $errors->first('m_name_uz') ? 'border-danger' : '' }}"
                                       value="{{ old('m_name_uz') }}"
                                       oninput="transliterate(this, 'm_name')">
                                {!! $errors->first('m_name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    UZ Cyrillic
                                </label>
                                <input type="text"
                                       name="m_name_kr"
                                       class="form-control-modern"
                                       value="{{ old('m_name_kr') }}"
                                       readonly>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    RU
                                </label>
                                <input type="text"
                                       name="m_name_ru"
                                       class="form-control-modern"
                                       value="{{ old('m_name_ru') }}"
                                       readonly>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    EN
                                </label>
                                <input type="text"
                                       name="m_name_en"
                                       class="form-control-modern"
                                       value="{{ old('m_name_en') }}"
                                       readonly>
                            </div>
                        </div>

                        <!-- Contact Info Section -->
                        <div class="form-section-header">
                            <i data-lucide="contact"></i>
                            <h3>{{ __('field.contact_info') }}</h3>
                        </div>

                        <div class="form-grid">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="mail"></i>
                                    Email
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       name="email"
                                       id="check_email"
                                       class="form-control-modern {{ $errors->first('email') ? 'border-danger' : '' }}"
                                       value="{{ old('email') }}">
                                {!! $errors->first('email', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                                <span class="checking_email" style="font-size: 13px; margin-top: 0.5rem; display: block;"></span>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="phone"></i>
                                    {{ __('field.phone') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="phone"
                                       maxlength="9"
                                       class="form-control-modern {{ $errors->first('phone') ? 'border-danger' : '' }}"
                                       value="{{ old('phone') }}"
                                       placeholder="901234567">
                                {!! $errors->first('phone', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="map-pin"></i>
                                    {{ __('field.address') ?? 'Адрес проживания' }}
                                </label>
                                <input type="text"
                                       name="address"
                                       maxlength="500"
                                       class="form-control-modern"
                                       value="{{ old('address') }}">
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('hr.emp') }}" class="btn-modern btn-secondary">
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
    <script>
        var select_sub_dep = "{{ __('field.select_sub_dep') }}";

        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            // Initialize Lucide icons
            if (window.lucide) lucide.createIcons();
        });

        $('.parent_dep').on('change', function(){
            var dep_id = $(this).val();

            if (dep_id) {
                $('.user_position_div').show();
                $('.child_select_div').remove();
                getChildDeps(dep_id);
            }
        });

        $(document).on('change','.department-selects .child_select', function() {
            let id = $(this).val();
            getChildDeps(id)
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
                            console.log(i);
                            $('.level'+i).remove();
                        }
                    }

                    if (departments.length) {
                        let childSelectOption = '<div class="form-group-modern level'+level_number+' child_select_div">';
                        childSelectOption+= '<label><i data-lucide="building"></i> {{ __("field.sub_department") }}</label>';
                        childSelectOption+= '<select class="form-control-modern select2 child_select" name="dep_id">';
                        childSelectOption+='<option value="'+dep_id+'">'+select_sub_dep+'</option>';
                        $.each(response.departments, function(key, value){
                            childSelectOption+='<option value='+value.id+'>'+value.name+'</option>';
                        });
                        childSelectOption+='</select>';
                        childSelectOption+='</div>';

                        $('.department-selects').append(childSelectOption);

                        // Reinitialize select2 for new select
                        $('.child_select').select2({
                            placeholder: "{{ __('action.choose') }}",
                            allowClear: true
                        });

                        // Reinitialize Lucide icons
                        if (window.lucide) lucide.createIcons();
                    }

                    if (response.positions.length) {
                        let user_positions = '';
                        $.each(response.positions, function(key, value){
                            user_positions+='<option value='+value.id+'>'+value.name+'</option>';
                        });
                        $('#user_position_select').append(user_positions);

                        // Reinitialize select2

                        {{--$('.select2').select2('destroy').select2({--}}
                        {{--    placeholder: "{{ __('action.choose') }}",--}}
                        {{--    allowClear: true--}}
                        {{--});--}}

                        {{--$('#user_position_select').select2('destroy').select2({--}}
                        {{--    placeholder: "{{ __('action.choose') }}",--}}
                        {{--    allowClear: true--}}
                        {{--});--}}
                    }
                },
                complete: function() {
                    hideLoader();
                }
            });
        }

        hideLoader();

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
                            $('.checking_email').html(`<span class="text-warning"><i data-lucide="loader" class="spinning" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i> Email tekshirilmoqda...</span>`);
                            if (window.lucide) lucide.createIcons();
                        },
                        success:function(response) {
                            console.log(response)
                            if (response.message === true) {
                                $('.checking_email').html(`<span class="text-danger"><i data-lucide="x-circle" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i> Ushbu email tizimda mavjud...</span>`);
                            } else {
                                $('.checking_email').html(`<span class="text-success"><i data-lucide="check-circle" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i> Emaildan foydalanish mumkin</span>`);
                            }
                            if (window.lucide) lucide.createIcons();
                        }
                    });
                }
            }
        });

        function hideLoader() {
            $('#loader').hide();
        }

        function showLoader() {
            $('#loader').show();
        }

        function transliterate(element, field) {
            const value = element.value;

            const toTitleCase = (text) => {
                return text
                    .split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            };

            const toUzbekCyrillic = (text) => {
                const map = {
                    'a': 'а', 'b': 'б', 'd': 'д', 'e': 'е', 'f': 'ф', 'g': 'г', 'h': 'ҳ',
                    'i': 'и', 'j': 'ж', 'k': 'к', 'l': 'л', 'm': 'м', 'n': 'н', 'o': 'о',
                    'p': 'п', 'q': 'қ', 'r': 'р', 's': 'с', 't': 'т', 'u': 'у', 'v': 'в',
                    'x': 'х', 'y': 'й', 'z': 'з', 'o\'': 'ў', 'g\'': 'ғ', 'sh': 'ш', 'ch': 'ч',
                    'ng': 'нг'
                };
                let result = text.toLowerCase();
                result = result.replace(/o\'/g, 'ў').replace(/g\'/g, 'ғ').replace(/sh/g, 'ш').replace(/ch/g, 'ч').replace(/ng/g, 'нг');
                result = result.split('').map(char => map[char] || char).join('');
                return toTitleCase(result);
            };

            const toRussian = (text) => {
                const map = {
                    'a': 'а', 'b': 'б', 'd': 'д', 'e': 'е', 'f': 'ф', 'g': 'г', 'h': 'х',
                    'i': 'и', 'j': 'ж', 'k': 'к', 'l': 'л', 'm': 'м', 'n': 'н', 'o': 'о',
                    'p': 'п', 'q': 'к', 'r': 'р', 's': 'с', 't': 'т', 'u': 'у', 'v': 'в',
                    'x': 'х', 'y': 'й', 'z': 'з', 'o\'': 'у', 'g\'': 'ғ', 'sh': 'ш', 'ch': 'ч',
                    'ng': 'нг'
                };
                let result = text.toLowerCase();
                result = result.replace(/o\'/g, 'у').replace(/g\'/g, 'г').replace(/sh/g, 'ш').replace(/ch/g, 'ч').replace(/ng/g, 'нг');
                result = result.split('').map(char => map[char] || char).join('');
                return toTitleCase(result);
            };

            const toEnglish = (text) => {
                const map = {
                    'a': 'a', 'b': 'b', 'd': 'd', 'e': 'e', 'f': 'f', 'g': 'g', 'h': 'h',
                    'i': 'i', 'j': 'j', 'k': 'k', 'l': 'l', 'm': 'm', 'n': 'n', 'o': 'o',
                    'p': 'p', 'q': 'q', 'r': 'r', 's': 's', 't': 't', 'u': 'u', 'v': 'v',
                    'x': 'x', 'y': 'y', 'z': 'z', 'o\'': 'o', 'g\'': 'g', 'sh': 'sh', 'ch': 'ch',
                    'ng': 'ng'
                };
                let result = text.toLowerCase();
                result = result.replace(/o\'/g, 'o').replace(/g\'/g, 'g').replace(/sh/g, 'sh').replace(/ch/g, 'ch').replace(/ng/g, 'ng');
                result = result.split('').map(char => map[char] || char).join('');
                return toTitleCase(result);
            };

            document.querySelector(`input[name=${field}_kr]`).value = toUzbekCyrillic(value);
            document.querySelector(`input[name=${field}_ru]`).value = toRussian(value);
            document.querySelector(`input[name=${field}_en]`).value = toEnglish(value);
        }
    </script>

    <style>
        /* Form Section Headers */
        .form-section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-left: 4px solid #794DFD;
            border-radius: 10px;
            margin: 2rem 0 1.5rem 0;
        }

        .form-section-header i {
            width: 24px;
            height: 24px;
            color: #794DFD;
        }

        .form-section-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Spinning animation for loader */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .spinning {
            animation: spin 1s linear infinite;
        }

        /* Readonly inputs styling */
        input[readonly].form-control-modern {
            background: #f8fafc;
            cursor: not-allowed;
            color: #64748b;
        }

        /* Text color utilities */
        .text-warning {
            color: #f59e0b;
        }

        .text-success {
            color: #10b981;
        }
    </style>
@endsection
