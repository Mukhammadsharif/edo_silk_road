@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.emp_order') }}</h5>
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
                                <a href="#">{{ __('menu.emp_order') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                @include('hr.admin.emp.left-menu')
            </div>
            <div class="col-sm-9">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit-2"></i> {{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('hr-order.store') }}" class="forms-sample">
                            @csrf
                            <input name="user_id" value="{{ $id }}" hidden>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label>{{ __('field.ord_code') }} <span class="text-red">*</span></label>
                                    <select name="ord_code" class="form-control select_order_type">
                                        <option value="">{{ __('field.select_ord_type') }}</option>
                                        @foreach($ordTypes as $key => $item)
                                            <option value="{{ $item->code }}">
                                               {{ $key+1 }}. {{ $item->lang($item->id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label>{{ __('field.department') }}</label>
                                    <select class="form-control" disabled>
                                        <option>
                                            @php($department = $user->currentWork->department??0)
                                            @if($department)
                                                {{ $department->lang($department->id) }}
                                            @endif
                                        </option>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label>{{ __('field.post_name') }}</label>
                                    <select class="form-control" disabled>
                                        <option>
                                            @php($position = $user->currentWork->position??0)
                                            @if($position)
                                                {{ $position->lang($position->id) }}
                                            @endif
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <div class="department-selects" style="display: none">
                                        <label>{{ __('field.choose_department') }} <span class="text-red">*</span></label>
                                        <div class="form-group">
                                            <select class="form-control parent_dep" name="dep_id">
                                                <option value="" selected="true">{{ __('field.choose') }}</option>
                                                @if(isset($departments))
                                                    @foreach($departments as $key => $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->lang($item->id) }}
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
                                <div class="col-sm-9">
                                    <label>{{ __('field.ord_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="ord_name" value="{{ old('ord_name') }}" class="form-control{{($errors->first('ord_name') ? " form-control-warning" : "")}}">
                                    {!! $errors->first('ord_name', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.ord_num') }} <span class="text-red">*</span></label>
                                    <input type="text" name="ord_num" value="{{ old('ord_num') }}" class="form-control{{($errors->first('ord_num') ? " form-control-warning" : "")}}">
                                    {!! $errors->first('ord_num', '<p class="text-danger">:message</p>') !!}
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label>{{ __('field.from_date') }} <span class="text-red">*</span></label>
                                    <input type="date" name="from_date" class="form-control">
                                    {!! $errors->first('from_date', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="col-sm-4">
                                    <label>{{ __('field.to_date') }}</label>
                                    <input type="date" name="to_date" class="form-control">
                                </div>
                            </div>

                            <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}" class="btn btn-light mr-2">
                                <i class="ik ik-x"></i>{{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ik ik-save"></i>{{ __('action.save') }}
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

            /*
            Order Type
            1 - Ishlaydi
            2 - Mehnat tatili
            3 - Boshqa lavozimga utish
            4 - Ishdan buwatiw
            5 - Oquv tatili
            6 - Kasallik tatili
            7 - GPX
            8 - Ishga qabul qilish
            9 - Boshqalar
            * */

            $('.select_order_type').on("change", function() {

                let order_type = Number($(this).val());

                if (order_type === 3 || order_type === 8) {
                    $('.department-selects').show();
                } else {
                    $('.department-selects').hide();
                }

            });

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
    </script>
@endsection
