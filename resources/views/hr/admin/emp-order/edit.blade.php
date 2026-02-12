@extends('layouts.app')

<link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
<link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.emp_order') }}</h1>
                        <p>{{ __('menu.list') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <nav class="breadcrumb-modern">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </a>
                    <svg class="breadcrumb-separator" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <a href="#" class="breadcrumb-item">{{ __('menu.emp_order') }}</a>
                    <svg class="breadcrumb-separator" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <span class="breadcrumb-item active">{{ __('menu.list') }}</span>
                </nav>
            </div>
        </div>

        <!-- Alert Messages -->
        @include('templates.alert')

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem;">

                <!-- Left Menu -->
                <div>
                    @include('hr.admin.emp.left-menu')
                </div>

                <!-- Main Form Card -->
                <div class="filters-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            {{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}
                        </div>
                    </div>

                    <div class="filters-form-modern">
                        <form method="POST" action="{{ route('hr-order.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')

                            <!-- Order Type & Current Info (Disabled) -->
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
                                <div class="filter-item">
                                    <label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                        {{ __('field.ord_code') }} <span class="text-red">*</span>
                                    </label>
                                    <select name="ord_code" class="form-control-modern" disabled>
                                        @foreach($ordTypes as $key => $item)
                                            <option value="{{ $item->code }}" {{ ($model->ord_code == $item->code ? "selected":"") }}>
                                                {{ $item->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="filter-item">
                                    <label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="3" y1="9" x2="21" y2="9"></line>
                                        </svg>
                                        {{ __('field.department') }}
                                    </label>
                                    <select class="form-control-modern" disabled>
                                        <option>
                                            {{ $model->empExp->department->name_uz??'-' }}
                                        </option>
                                    </select>
                                </div>

                                <div class="filter-item">
                                    <label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        {{ __('field.post_name') }}
                                    </label>
                                    <select class="form-control-modern" disabled>
                                        <option>
                                            {{ $model->empExp->position->name_uz??'-' }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Department Selection -->
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
                                <div>
                                    <div class="department-selects">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 0.5rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            </svg>
                                            {{ __('field.choose_department') }} <span class="text-red">*</span>
                                        </label>
                                        <div class="form-group">
                                            <select class="form-control-modern parent_dep" name="dep_id">
                                                <option value="" selected="true">{{ __('action.choose') }}</option>
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

                                <div class="form-group user_position_div" style="display: none">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 0.5rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        {{ __('field.choose_position') }} <span class="text-red">*</span>
                                    </label>
                                    <select name="post_id" id="user_position_select" class="form-control-modern">
                                    </select>
                                </div>
                            </div>

                            <!-- Order Name & Number -->
                            <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                                <div class="filter-item">
                                    <label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="17" y1="10" x2="3" y2="10"></line>
                                            <line x1="21" y1="6" x2="3" y2="6"></line>
                                            <line x1="21" y1="14" x2="3" y2="14"></line>
                                            <line x1="17" y1="18" x2="3" y2="18"></line>
                                        </svg>
                                        {{ __('field.ord_name') }} <span class="text-red">*</span>
                                    </label>
                                    <input type="text" name="ord_name" value="{{ $model->ord_name }}" class="form-control-modern{{($errors->first('ord_name') ? " form-control-warning" : "")}}" placeholder="{{ __('field.ord_name') }}">
                                    {!! $errors->first('ord_name', '<p class="text-danger">:message</p>') !!}
                                </div>

                                <div class="filter-item">
                                    <label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        {{ __('field.ord_num') }} <span class="text-red">*</span>
                                    </label>
                                    <input type="text" name="ord_num" value="{{ $model->ord_num }}" class="form-control-modern{{($errors->first('ord_num') ? " form-control-warning" : "")}}" placeholder="№">
                                    {!! $errors->first('ord_num', '<p class="text-danger">:message</p>') !!}
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1.5rem;">
                                <div class="filter-item">
                                    <label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        {{ __('field.from_date') }} <span class="text-red">*</span>
                                    </label>
                                    <input type="date" name="from_date" value="{{ date('Y-m-d', strtotime($model->from_date)) }}" class="form-control-modern">
                                    {!! $errors->first('from_date', '<p class="text-danger">:message</p>') !!}
                                </div>

                                <div class="filter-item">
                                    <label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        {{ __('field.to_date') }}
                                    </label>
                                    <input type="date" name="to_date" value="{{ ($model->to_date ? date('Y-m-d', strtotime($model->to_date)):"") }}" class="form-control-modern">
                                </div>
                            </div>

                            <!-- Status Radio Buttons -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    {{ __('field.status') }}
                                </label>
                                <div style="display: flex; gap: 1.5rem;">
                                    <div class="radio radio-inline radio-success">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                            <input type="radio" name="isActive" value="A" {{ ($model->isActive == 'A') ? 'checked':'' }}>
                                            <i class="helper"></i>
                                            <span class="badge-modern badge-success">{{ __('status.active') }}</span>
                                        </label>
                                    </div>
                                    <div class="radio radio-inline radio-danger">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                            <input type="radio" name="isActive" value="P" {{ ($model->isActive == 'P') ? 'checked':'' }}>
                                            <i class="helper"></i>
                                            <span class="badge-modern badge-danger">{{ __('status.passive') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; border-top: 2px solid #f1f5f9;">
                                <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}" class="btn-modern btn-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                    {{ __('action.cancel') }}
                                </a>
                                <button type="submit" class="btn-modern btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    {{ __('action.save') }}
                                </button>
                            </div>
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
                            childSelectOption+= '<select class="form-control-modern child_select" name="dep_id">';
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
