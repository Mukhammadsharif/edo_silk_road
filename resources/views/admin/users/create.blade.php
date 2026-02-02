@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.users') }}</h5>
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
                                <a href="{{ route('users.index') }}">{{ __('menu.users') }}</a>
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
                        <h3><i data-lucide="circle-plus"></i> {{ __('field.create_user') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <div class="department-selects">
                                        <label>{{ __('field.choose_department') }} <span class="text-red">*</span></label>
                                        <div class="form-group">
                                            <select class="form-control parent_dep" name="dep_id">
                                                <option value="" selected="true">{{ __('field.choose') }}</option>
                                                @if(isset($departments))
                                                    @foreach($departments as $key => $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->name_uz }}
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
                                        <label>{{ __('field.choose_position') }} <span class="text-red">*</span></label>
                                        <select name="post_id" id="user_position_select" class="form-control form-control-primary">
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label>{{ __('field.l_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="l_name" class="form-control{{($errors->first('l_name') ? " form-control-warning" : "")}}" value="{{ old('l_name') }}">
                                    {!! $errors->first('l_name', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.f_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="f_name" class="form-control{{($errors->first('f_name') ? " form-control-warning" : "")}}" value="{{ old('f_name') }}">
                                    {!! $errors->first('f_name', '<p class="text-danger">:message</p>') !!}
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

    <script>
        $(document).ready(function() {

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
                            childSelectOption+='<option value="'+dep_id+'">Quyi bo`limni tanlang...</option>';
                            $.each(response.departments, function(key, value){
                                childSelectOption+='<option value='+value.id+'>'+value.name_uz+'</option>';
                            });
                            childSelectOption+='</select>';
                            childSelectOption+='</div>';

                            $('.department-selects').append(childSelectOption);
                        }

                        if (response.positions.length) {
                            let user_positions
                            $.each(response.positions, function(key, value){
                                user_positions+='<option value='+value.id+'>'+value.name_uz+'</option>';
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

        $("#check_email").on("keyup", function()
        {
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
    </script>
@endsection
