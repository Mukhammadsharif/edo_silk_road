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
                    @include('hr.admin.emp.breadcrumb')
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                @include('hr.admin.emp.left-menu')
            </div>
            <div class="col-md-9">
                <div class="card" id="printPage">
                    <div class="card-header bg-aqua"><h3><i class="ik ik-user"></i> {{ __('menu.emp_about') }}</h3></div>
                    <div class="card-body template-demo">
                        <div class="text-left">
                            <img src="{{ url('/images/profile_image1.png') }}" alt="profile image" class="rounded-circle" width="150" />
                            <h4 class="card-title mt-10">{{ __('field.status') }}:
                                <span class="badge badge-pill @if($user->status == 1) badge-green @else badge-danger @endif text-white">
                                    {{ $user->isActive($user->status) }}
                                </span>
                            </h4>
                        </div>
                        <hr>

                        <h4>{{ __('menu.emp_info') }}</h4>
                        <div class="row">
                            <div class="col-md-3 col-6"> <strong>{{ __('field.fam_name') }}</strong>
                                <br>
                                <p class="text-muted">{{ $personal->l_name??'-' }} {{ $personal->f_name??'-' }} {{ $personal->m_name??'-' }}</p>
                            </div>
                            <div class="col-md-2"> <strong>{{ __('field.p_pin') }}</strong>
                                <br>
                                <p class="text-muted">{{ $personal->doc_pin??'-' }}</p>
                            </div>
                            <div class="col-md-2"> <strong>{{ __('field.p_num') }}</strong>
                                <br>
                                <p class="text-muted">{{ $personal->doc_number??'-' }}</p>
                            </div>
                            <div class="col-md-2"> <strong>{{ __('field.br_date') }}</strong>
                                <br>
                                <p class="text-muted">
                                    @if($personal->birthday)
                                    {{ date('d.m.Y', strtotime($personal->birthday??now())) }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-2"> <strong>{{ __('field.doc_type') }}</strong>
                                <br>
                                <p class="text-muted">{{ $personal->doc_type??'-' }}</p>
                            </div>
                            <div class="col-md-2"> <strong>{{ __('field.email') }}</strong>
                                <br>
                                <p class="text-muted">{{ $user->email }}</p>
                            </div>
                            <div class="col-md-2"> <strong>{{ __('field.username') }}</strong>
                                <br>
                                <p class="text-muted">{{ $user->username??'-' }}</p>
                            </div>
                            <div class="col-md-2"> <strong>{{ __('field.mobile_phone') }}</strong>
                                <br>
                                <p class="text-muted">{{ $personal->phone??'-' }}</p>
                            </div>
                        </div>
                        <hr>

                        <h4>{{ __('field.user_posts') }}</h4>
                        <div class="row">
                            <div class="col-md-3 col-6"> <strong>{{ __('field.department') }}</strong>
                                <br>
                                <p class="text-muted">
                                    @php($department = $user->currentWork->department??0)
                                    @if($department)
                                        {{ $department->lang($department->id) }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6"> <strong>{{ __('field.user_posts') }}</strong>
                                <br>
                                <p class="text-muted">
                                    @php($position = $user->currentWork->position??0)
                                    @if($position)
                                        {{ $position->lang($position->id) }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6"> <strong>{{ __('field.from_date') }}</strong>
                                <br>
                                <p class="text-muted">{{ date('d.m.Y', strtotime($userWork->from_date??now())) }}</p>
                            </div>
                        </div>
                        <hr>

                        <h4>{{ __('title.emp_order') }}</h4>
                        <div class="row">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
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
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                        <span class="badge badge-blue">
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
                                                -
                                            @endif
                                        </td>
                                        <td>
                                        <span class="badge badge-pill badge-blue">
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
                                        <td colspan="8" class="text-center">{{ __('field.not_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <hr>

                        <h4>{{ __('title.emp_exp') }}</h4>
                        <div class="row">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
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
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $model->exp_name }}</td>
                                        <td>{{ $model->post_name }}</td>
                                        <td>{{ $model->exp_descr }}</td>
                                        <td>{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                                        <td>
                                            @if($model->to_date)
                                                {{ date('d.m.Y', strtotime($model->to_date)) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($model->is_org === 'Y')
                                                <span class="badge badge-pill bg-success">
                                                {{ __('field.in_org') }}
                                            </span>
                                            @else
                                                <span class="badge badge-pill bg-light-blue">
                                                {{ __('field.other_org') }}
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('field.not_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <button type="button" onclick="printPageNew()" class="btn btn-outline-secondary btn-rounded mr-5">
                    <i data-lucide="printer"></i> {{ __('action.print') }}
                </button>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('/print/print.css') }}" type="text/css">
    <script src='{{ asset('/print/print.js') }}'></script>

    <script>

        $(document).ready(function() {

            let locale_code = "{{ config('app.locale') }}";

        });

        function printPageNew() {
            printJS({
                printable: 'printPage',
                type: 'html',
                css: [
                    "{{ asset('/assets/dist/css/main.css') }}",
                    "{{ asset('/assets/dist/css/theme.min.css') }}",
                    "{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}",
                    "{{ asset('/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}",
                    "{{ asset('/assets/node_modules/bootstrap/scss/mixins/_grid.scss') }}",
                ],
                //style: ['@page { size:A4; margin-left: 0 !important;} body {margin-left: 0 !important;}'],
                scanStyles: false
            })
        }
    </script>

@endsection
