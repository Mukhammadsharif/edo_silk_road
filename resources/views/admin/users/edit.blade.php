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
                            <span>{{ __('action.edit') }}</span>
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
                            <li class="breadcrumb-item">
                                <a href="{{ route('users.show', $user->user_hash) }}">
                                    {{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6" hidden>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-user"></i> {{ __('field.edit_personal_info') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('admin/user/edit-personal-info', [$user->id, $user->user_hash]) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>{{ __('field.l_name') }} <span class="text-red">*</span></label>
                                        <input type="text" name="l_name" class="form-control{{($errors->first('l_name') ? " form-control-warning" : "")}}" value="{{ $personal->l_name }}">
                                        {!! $errors->first('l_name', '<p class="text-danger">:message</p>') !!}
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                    <label>{{ __('field.f_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="f_name" class="form-control{{($errors->first('f_name') ? " form-control-warning" : "")}}" value="{{ $personal->f_name }}">
                                    {!! $errors->first('f_name', '<p class="text-danger">:message</p>') !!}
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                    <label>{{ __('field.m_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="m_name" class="form-control" value="{{ $personal->m_name }}">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                    <label>{{ __('field.doc_pin') }}</label>
                                    <input type="text" name="doc_pin" max="14" class="form-control" value="{{ $personal->doc_pin }}">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                    <label>{{ __('field.doc_number') }}</label>
                                    <input type="text" name="doc_number" max="9" class="form-control" value="{{ $personal->doc_number }}">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>{{ __('field.doc_type') }}</label>
                                        @php($doc_type = $user->userPersonal->doc_type??0)
                                        <select class="form-control" name="doc_type">
                                            <option value="1" @if($doc_type == 1) selected @endif>Passport</option>
                                            <option value="2" @if($doc_type == 2) selected @endif>ID Card</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>{{ __('field.birthday') }}</label>
                                        <input type="date" name="birthday" class="form-control" value="{{ date('Y-m-d', strtotime($personal->birthday)) }}">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>{{ __('field.gender') }}</label>
                                        @php($gender = $user->userPersonal->gender??0)
                                        <select class="form-control" name="gender">
                                            <option value="1" @if($gender == 1) selected @endif>Erkak</option>
                                            <option value="2" @if($gender == 2) selected @endif>Ayol</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>{{ __('field.phone') }}</label>
                                        <input type="text" name="phone" max="9" class="form-control" value="{{ $personal->phone }}">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> {{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6" hidden>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-plus-circle"></i> {{ __('field.user_position') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('admin/user/edit-user-dep-position', [$user->id, $user->user_hash]) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 col-12"> <label>{{ __('field.user_current_department') }}</label>
                                    <br>
                                    @foreach($user->currentWork->department->allParents()->reverse() as $item)
                                       <span class="h6 text-muted">{{ $item->name_uz.' -> ' }}</span>
                                    @endforeach
                                    <span class="h6">{{ $user->currentWork->department->name_uz??'' }}</span>
                                </div>
                                <div class="col-md-3 col-12"> <label>{{ __('field.user_current_position') }}</label>
                                    <br>
                                    <h5>{{ $user->currentWork->position->name_uz??'' }}</h5>
                                </div>
                                <div class="col-md-3 col-12"> <label>{{ __('field.user_position_from_date') }}</label>
                                    <br>
                                    @php($user_position_from_date = $user->currentWork->from_date??now())
                                    <h5>{{ date('d.m.Y', strtotime($user_position_from_date)) }}</h5>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <div class="col-sm-6">
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
                                <div class="col-sm-6">
                                    <div class="form-group user_position_div" style="display: none">
                                        <label>{{ __('field.choose_position') }} <span class="text-red">*</span></label>
                                        <select name="post_id" id="user_position_select" class="form-control form-control-primary">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row from_date_div" style="display: none">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>{{ __('field.user_position_from_date') }}</label>
                                        <input type="date" name="user_position_from_date" class="form-control" value="{{ date('Y-m-d', strtotime(now())) }}">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> {{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-lock"></i> {{ __('field.user_roles') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('admin/user/edit-user-roles', [$user->id, $user->user_hash]) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-sm-12 mb-30">
                                    @foreach($roles as $key => $item)
                                        <p>{{ $key+1 }}.
                                            <input type="checkbox" name="role_ids[]" value="{{ $item->id }}" class="js-switch" @if(in_array($item->id, $userRoles)) checked @endif />
                                            <label>{{ $item->lang($item->id) }} <small>({{ $item->name }})</small></label>
                                        </p>
                                    @endforeach
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> {{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-feather"></i> {{ __('field.ecp_status') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('admin/user/edit-ecp-status', [$user->id, $user->user_hash]) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">

                                <div class="col-sm-12 mb-30">
                                    <p>
                                        <input id="control" type="checkbox" name="status" class="js-switch" @if($user->userPersonal->doc_sign_type == 1) checked @endif
                                        />
                                        <label for="control">{{ __('field.status') }}</label>
                                    </p>
                                    <div class="alert alert-primary"><i class="ik ik-alert-circle"></i> Holat "aktiv" bo'lgan holatda, hujjatlarni faqat ERI kaliti orqali imzolaydi</div>
                                    <div class="alert alert-warning"><i class="ik ik-alert-circle"></i> Holat "passiv" bo'lgan holatda hujjatlarni ERI kalitsiz imzolashi mumkin</div>
                                </div>
                            </div>
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
                    $('.from_date_div').show();
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
