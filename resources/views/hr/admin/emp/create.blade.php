@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.emp') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-3">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('hr.emp') }}">{{ __('menu.hr_emp') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-plus-circle"></i> {{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('hr.emp.store') }}">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <div class="department-selects">
                                        <label>{{ __('field.choose_department') }} <span class="text-red">*</span></label>
                                        <div class="form-group">
                                            <select class="form-control parent_dep" name="dep_id">
                                                <option value="" selected="true">{{ __('action.choose') }}</option>
                                                @if(isset($departments))
                                                    @foreach($departments as $key => $item)
                                                        <option value="{{ $item->id }}">
                                                           {{ $key+1 }}. {{ $item->lang($item->id) }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            {!! $errors->first('dep_id', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                    </div>
                                    <span id="loader" style="display: none"><i class="fa fa-spinner fa-3x fa-spin"></i></span>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group user_position_div" style="display: none">
                                        <label>{{ __('action.choose_position') }} <span class="text-red">*</span></label>
                                        <select name="post_id" id="user_position_select" class="form-control form-control-primary">
                                        </select>
                                    </div>
                                </div>
                            </div>

{{--                            <div class="form-group row">--}}
{{--                                <div class="col-sm-2">--}}
{{--                                    <label>{{ __('field.l_name') }} <span class="text-red">*</span></label>--}}
{{--                                    <input type="text" name="l_name" class="form-control{{($errors->first('l_name') ? " form-control-warning" : "")}}" value="{{ old('l_name') }}">--}}
{{--                                    {!! $errors->first('l_name', '<p class="text-danger">:message</p>') !!}--}}
{{--                                </div>--}}
{{--                                <div class="col-sm-2">--}}
{{--                                    <label>{{ __('field.f_name') }} <span class="text-red">*</span></label>--}}
{{--                                    <input type="text" name="f_name" class="form-control{{($errors->first('f_name') ? " form-control-warning" : "")}}" value="{{ old('f_name') }}">--}}
{{--                                    {!! $errors->first('f_name', '<p class="text-danger">:message</p>') !!}--}}
{{--                                </div>--}}
{{--                                <div class="col-sm-2">--}}
{{--                                    <label>{{ __('field.m_name') }}</label>--}}
{{--                                    <input type="text" name="m_name" class="form-control{{($errors->first('m_name') ? " form-control-warning" : "")}}" value="{{ old('m_name') }}">--}}
{{--                                    {!! $errors->first('m_name', '<p class="text-danger">:message</p>') !!}--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="form-group row">
                                <div class="col-sm-2">
                                    <label>{{ __('field.l_name') }} (UZ Latin) <span class="text-red">*</span></label>
                                    <input type="text" name="l_name_uz" class="form-control{{($errors->first('l_name_uz') ? ' form-control-warning' : '')}}" value="{{ old('l_name_uz') }}" oninput="transliterate(this, 'l_name')">
                                    {!! $errors->first('l_name_uz', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.l_name') }} (UZ Cyrillic)</label>
                                    <input type="text" name="l_name_kr" class="form-control" value="{{ old('l_name_kr') }}">
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.l_name') }} (RU)</label>
                                    <input type="text" name="l_name_ru" class="form-control" value="{{ old('l_name_ru') }}">
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.l_name') }} (EN)</label>
                                    <input type="text" name="l_name_en" class="form-control" value="{{ old('l_name_en') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-2">
                                    <label>{{ __('field.f_name') }} (UZ Latin) <span class="text-red">*</span></label>
                                    <input type="text" name="f_name_uz" class="form-control{{($errors->first('f_name_uz') ? ' form-control-warning' : '')}}" value="{{ old('f_name_uz') }}" oninput="transliterate(this, 'f_name')">
                                {!! $errors->first('f_name_uz', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.f_name') }} (UZ Cyrillic)</label>
                                    <input type="text" name="f_name_kr" class="form-control" value="{{ old('f_name_kr') }}">
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.f_name') }} (RU)</label>
                                    <input type="text" name="f_name_ru" class="form-control" value="{{ old('f_name_ru') }}">
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.f_name') }} (EN)</label>
                                    <input type="text" name="f_name_en" class="form-control" value="{{ old('f_name_en') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-2">
                                    <label>{{ __('field.m_name') }} (UZ Latin)</label>
                                    <input type="text" name="m_name_uz" class="form-control{{($errors->first('m_name_uz') ? ' form-control-warning' : '')}}" value="{{ old('m_name_uz') }}" oninput="transliterate(this, 'm_name')">
                                    {!! $errors->first('m_name_uz', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.m_name') }} (UZ Cyrillic)</label>
                                    <input type="text" name="m_name_kr" class="form-control" value="{{ old('m_name_kr') }}">
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.m_name') }} (RU)</label>
                                    <input type="text" name="m_name_ru" class="form-control" value="{{ old('m_name_ru') }}">
                                </div>
                                <div class="col-sm-2">
                                    <label>{{ __('field.m_name') }} (EN)</label>
                                    <input type="text" name="m_name_en" class="form-control" value="{{ old('m_name_en') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label>{{ __('field.email') }} <span class="text-red">*</span></label>
                                    <input type="email" name="email" id="check_email" class="form-control{{($errors->first('email') ? " form-control-warning" : "")}}" value="{{ old('email') }}" placeholder="a.ivanov@eai.uz">
                                    {!! $errors->first('email', '<p class="text-danger">:message</p>') !!}
                                    <i class="text-primary checking_email"></i>
                                </div>
                            </div>
                            <a href="{{ url('admin/users') }}" class="btn btn-light mr-5">
                                <i class="ik ik-x"></i> {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> {{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="select_sub_dep" hidden>{{ __('field.select_sub_dep') }}</div>

    <script>
        $(document).ready(function() {

            let select_sub_dep = $(".select_sub_dep").text();

            $('.parent_dep').on("change", function() {

                let dep_id = $(this).val();

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
                        //console.log(departments.length)
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
                            let childSelectOption = '<div class="form-group level'+level_number+' child_select_div">';
                            childSelectOption+= '<select class="form-control form-control-primary child_select" name="dep_id">';
                            childSelectOption+='<option value="'+dep_id+'">'+select_sub_dep+'</option>';
                            $.each(response.departments, function(key, value){
                                childSelectOption+='<option value='+value.id+'>'+value.name+'</option>';
                            });
                            childSelectOption+='</select>';
                            childSelectOption+='</div>';

                            $('.department-selects').append(childSelectOption);
                        }

                        if (response.positions.length) {
                            let user_positions
                            $.each(response.positions, function(key, value){
                                user_positions+='<option value='+value.id+'>'+value.name+'</option>';
                            });
                            $('#user_position_select').append(user_positions);
                        }
                    },
                    complete: function() {
                        hideLoader();
                    }
                });
            }

            hideLoader();

        });

        function getChildDepartments(dep_id) {

            var department_list = '';

            $.ajax({
                processing : 'true',
                serverSide : 'true',
                url: '/admin/get-dep-pos/'+dep_id,
                type:"GET",
                dataType:"json",
                beforeSend: function() {
                    showLoader();
                },
                success:function(response) {
                    console.log(response)
                    //return response;
                    department_list = response;

                },
                complete: function() {
                    hideLoader();
                }
            });

            return department_list;
        }

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
                            console.log(response)
                            if (response.message === true) {
                                $('.checking_email').html(`<span class="text-danger">Ushbu email tizimda mavjid...</span>`);
                            } else {
                                $('.checking_email').html(`<span class="text-success">Emaildan foydalanish mumkin<span>`);
                            }
                        },
                        complete: function() {
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

            // Helper function to capitalize the first letter of each word
            const toTitleCase = (text) => {
                return text
                    .split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            };

            // Uzbek Latin to Uzbek Cyrillic transliteration
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

            // Uzbek Latin to Russian transliteration
            const toRussian = (text) => {
                const map = {
                    'a': 'а', 'b': 'б', 'd': 'д', 'e': 'е', 'f': 'ф', 'g': 'г', 'h': 'х',
                    'i': 'и', 'j': 'ж', 'k': 'к', 'l': 'л', 'm': 'м', 'n': 'н', 'o': 'о',
                    'p': 'п', 'q': 'к', 'r': 'р', 's': 'с', 't': 'т', 'u': 'у', 'v': 'в',
                    'x': 'х', 'y': 'й', 'z': 'з', 'o\'': 'у', 'g\'': 'г', 'sh': 'ш', 'ch': 'ч',
                    'ng': 'нг'
                };
                let result = text.toLowerCase();
                result = result.replace(/o\'/g, 'у').replace(/g\'/g, 'г').replace(/sh/g, 'ш').replace(/ch/g, 'ч').replace(/ng/g, 'нг');
                result = result.split('').map(char => map[char] || char).join('');
                return toTitleCase(result);
            };

            // Uzbek Latin to English transliteration
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

            // Update corresponding inputs
            document.querySelector(`input[name=${field}_kr]`).value = toUzbekCyrillic(value);
            document.querySelector(`input[name=${field}_ru]`).value = toRussian(value);
            document.querySelector(`input[name=${field}_en]`).value = toEnglish(value);
        }
    </script>
@endsection
