@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-md-3">
                <div class="card" style="">
                    <div class="card-body text-center">
                        <div class="profile-pic mb-20">
                            @if(file_exists(public_path().'/profile_img/'.'img_'.Auth::user()->id.'.png'))
                                <img src="{{ url('/profile_img/'.'img_'.Auth::user()->id.'.png') }}" width="150"
                                     height="150" class="rounded-circle" alt="user" style="object-fit: cover;">
                            @else
                                <img src="{{ url('/images/profile_image1.png') }}" width="150" height="150"
                                     class="rounded-circle" alt="user" style="object-fit: cover;">
                            @endif

                            @php($user_name = Auth::user()->userPersonal??0)
                            <h4 class="mt-20 mb-0">
                                @if($user_name)
                                    {{ $user_name->fullName() }}
                                @endif
                            </h4>
                            <h5 class="mt-20 mb-0">
                                {{ Auth::user()->organization->name_uz??'' }}
                            </h5>
                            <a href="#">
                                @php($user_department = Auth::user()->currentWork->department??0)
                                @if($user_department)
                                    {{ $user_department->lang($user_department->id) }}
                                @endif
                                @php($user_position = Auth::user()->currentWork->position??0)
                                @if($user_position)
                                    {{ $user_position->lang($user_position->id) }}
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="p-4 border-top mt-15">
                        <div class="row text-center">
                            <div class="col-6">
                                <a href="{{ url('/profile/my') }}"
                                   class="link d-flex align-items-center justify-content-center">
                                    <i class="ik ik-user f-20 mr-5"></i>{{ __('field.profile') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <!-- ticket, proj, clent start -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                @if(in_array('Director', $authRoleName))
                                    <p class="mb-30 bg-red lbl-card"><i class="ik ik-check"></i> {{ __('menu.for_resolution') }}</p>
                                    <div class="text-center">
                                        <h2 class="mb-0 d-inline-block text-red"><span>{{ $docs_count['doc_conf_menu_id'] }}</span></h2>
                                    </div>
                                @else
                                    <p class="mb-30 bg-red lbl-card"><i class="ik ik-file-minus"></i> {{ __('menu.for_execution') }}</p>
                                    <div class="text-center">
                                        <h2 class="mb-0 d-inline-block text-red"><span>{{ $docs_count['doc_exec_menu_id'] }}</span></h2>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                <p class="mb-30 bg-warning lbl-card"><i class="fas fa-feather"></i> {{ __('menu.for_sign') }}</p>
                                <div class="text-center">
                                    <h2 class="mb-0 d-inline-block text-warning"><span>{{ $docs_count['doc_sign_menu_id'] }}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                <p class="mb-30 bg-blue lbl-card"><i class="fa fa-handshake"></i> {{ __('menu.for_coillaborate') }}</p>
                                <div class="text-center">
                                    <h2 class="mb-0 d-inline-block text-blue"><span>{{ $docs_count['doc_approve_menu_id'] }}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                <p class="mb-30 bg-green lbl-card"><i class="fas fa-file-alt"></i> {{ __('menu.my_docs') }}</p>
                                <div class="text-center">
                                    <h2 class="mb-0 d-inline-block text-green"><span>{{ $docs_count['my_docs_menu_id'] }}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ticket, proj, clent end -->
                </div>

                <div class="row">
                    <div class="col-xl-4 col-md-12">
                        <div class="card analytic-card card-green">
                            <div class="card-body">
                                <div class="row align-items-center mb-30">
                                    <div class="col-auto">
                                        <i class="fas fa-users text-green f-18 analytic-icon"></i>
                                    </div>
                                    <div class="col text-right">
                                        <h3 class="mb-5 text-white">{{ $employees['totalEmpCount'] }}</h3>
                                        <h6 class="mb-0 text-white">{{ __('menu.all_users') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-12">
                        <div class="card analytic-card card-blue">
                            <div class="card-body">
                                <div class="row align-items-center mb-30">
                                    <div class="col-auto">
                                        <i class="fas fa-check text-blue f-18 analytic-icon"></i>
                                    </div>
                                    <div class="col text-right">
                                        <h3 class="mb-5 text-white">{{ $employees['activeEmpCount'] }}</h3>
                                        <h6 class="mb-0 text-white">{{ __('menu.active_users') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-12">
                        <div class="card analytic-card card-yellow">
                            <div class="card-body">
                                <div class="row align-items-center mb-30">
                                    <div class="col-auto">
                                        <i class="fas fa-trash-alt text-red f-18 analytic-icon"></i>
                                    </div>
                                    <div class="col text-right">
                                        <h3 class="mb-5 text-white">{{ $employees['deletedEmpCount'] }}</h3>
                                        <h6 class="mb-0 text-white">{{ __('menu.deleted_users') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header" hidden>
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-plus bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.fast_create_doc') }}</h5>
                            <span>{{ __('field.app_temps') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" hidden>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-timeline-tab" data-toggle="pill" href="#current-month" role="tab" aria-controls="pills-timeline" aria-selected="true">{{ __('document.app') }}</a>
                        </li>
                        <li class="nav-item" hidden>
                            <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#last-month" role="tab" aria-controls="pills-profile" aria-selected="false">{{ __('menu.outgoing_messages') }}</a>
                        </li>
                        <li class="nav-item" hidden>
                            <a class="nav-link" id="pills-setting-tab" data-toggle="pill" href="#previous-month" role="tab" aria-controls="pills-setting" aria-selected="false">{{ __('menu.working_documents') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="current-month" role="tabpanel" aria-labelledby="pills-timeline-tab">
                            <div class="card-body">
                                <div class="row">
                                    @if(count($app_temps) > 0)
                                        @foreach($app_temps as $app_temp)
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card sos-st-card box">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col">
                                                                <div class="icon">
                                                                    <h4 class="mb-0 text-blue">
                                                                        <i class="fas fa-pencil-alt text-primary"></i> {{ $app_temp->lang($app_temp->lang_id) }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <a href="#" class="btn btn-outline-primary">Yaratmoq</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="pills-profile-tab">
                            <div class="card-body">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="previous-month" role="tabpanel" aria-labelledby="pills-setting-tab">
                            <div class="card-body">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row hidden">
            <div class="col-md-4">
                <div class="card" style="">
                    <div class="card-body text-center">
                        <div class="profile-pic mb-20">
                            @if(file_exists(public_path().'/profile_img/'.'img_'.Auth::user()->id.'.png'))
                                <img src="{{ url('/profile_img/'.'img_'.Auth::user()->id.'.png') }}" width="150"
                                     height="150" class="rounded-circle" alt="user" style="object-fit: cover;">
                            @else
                                <img src="{{ url('/images/profile_image1.png') }}" width="150" height="150"
                                     class="rounded-circle" alt="user" style="object-fit: cover;">
                            @endif

                            @php($user_name = Auth::user()->userPersonal??0)
                            <h4 class="mt-20 mb-0">
                                @if($user_name)
                                    {{ $user_name->fullName() }}
                                @endif
                            </h4>
                            <a href="#">
                                @php($user_department = Auth::user()->currentWork->department??0)
                                @if($user_department)
                                    {{ $user_department->lang($user_department->id) }}
                                @endif
                                @php($user_position = Auth::user()->currentWork->position??0)
                                @if($user_position)
                                    {{ $user_position->lang($user_position->id) }}
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="p-4 border-top mt-15">
                        <div class="row text-center">
                            <div class="col-6">
                                <a href="{{ url('/profile/my') }}"
                                   class="link d-flex align-items-center justify-content-center">
                                    <i class="ik ik-user f-20 mr-5"></i>{{ __('field.profile') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
