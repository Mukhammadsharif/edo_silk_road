@extends('layouts.app')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.my_profile') }}</h5>
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
                                <a href="#">{{ __('menu.my_profile') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3>{{ __('field.my_account') }}</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6"> <strong>{{ __('field.fam_name') }}</strong>
                                <br>
                                @php($user_name = $user->userPersonal??0)
                                <p class="text-muted">
                                    @if($user_name)
                                        {{ $user_name->fullName() }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6"> <strong>{{ __('field.organization') }}</strong>
                                <br>
                                @php($user_organization = $user->organization??'')
                                <p class="text-muted">
                                    @if($user_organization)
                                        {{ $user_organization->name_uz }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6"> <strong>{{ __('field.department') }}</strong>
                                <br>
                                @php($user_department = $user->currentWork->department??0)
                                <p class="text-muted">
                                    @if($user_department)
                                        {{ $user_department->lang($user_department->id) }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-3 col-6"> <strong>{{ __('field.user_posts') }}</strong>
                                <br>
                                @php($user_position = $user->currentWork->position??0)
                                <p class="text-muted">
                                    @if($user_position)
                                        {{ $user_position->lang($user_position->id) }}
                                    @endif
                                </p>
                            </div>
                        </div><hr>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-left">
                                    <div class="avatar-upload">
                                        <div class="avatar-edit">
                                            <input type='file' id="updateImage" accept=".png, .jpg, .jpeg" />
                                            <span id="loader"><i class="fa fa-spinner fa-3x fa-spin"></i></span>
                                        </div>
                                        <div class="avatar-preview">
                                            @if(file_exists(public_path().'/profile_img/'.'img_'.$user->id.'.png'))
                                                <div class="show-image" id="imagePreview" style="background-image: url({{ url('/profile_img/'.'img_'.$user->id.'.png') }});">
                                                    <button id="btnDelImg" type="button" class="btn btn-icon btn-outline-danger btnDelImg">
                                                        <i class="ik ik-trash-2"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div id="imagePreview" style="background-image: url({{ url('/images/profile_image1.png') }});">
                                                </div>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('update-image') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group row mt-10">
                                                <div class="col-sm-6">
                                                    <div class="custom-file">
                                                        <input type="file" name="profile_img" accept="image/png, image/gif, image/jpeg" class="custom-file-input" onchange="this.form.submit()">
                                                        <label class="custom-file-label" for="customFile">{{ __('action.upload_profile_picture') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div><hr>

                        <div class="row">
                            <div class="col-md-12">
                                <form method="POST" action="{{ route('my.pass-update') }}">
                                    @csrf
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label>{{ __('field.email') }}</label>
                                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                                        </div>
                                        <div class="col-sm-6">
                                            <label>{{ __('field.username') }}</label>
                                            <input type="text" class="form-control" value="{{ $user->username }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="{{ __('field.password') }}">
                                            {!! $errors->first('password', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="password" name="passwordConf" class="form-control {{ $errors->has('passwordConf') ? 'is-invalid' : '' }}" placeholder="{{ __('field.confirm_password') }}">
                                            {!! $errors->first('passwordConf', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ik ik-save"></i> {{ __('action.save') }}
                                    </button>
                                    <hr>
                                    <h4>{{ __('field.my_active_roles') }}</h4>
                                    @if(count($activeRoles))
                                        @foreach($activeRoles as $key1 => $role)
                                            <span class="badge badge-pill badge-primary mb-1">
                                                {{ $role->lang($role->role_id) }}
                                            </span>
                                        @endforeach
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-block">
                        <h3>{{ __('title.emp_order') }}</h3>
                    </div>
                    <div class="card-body template-demo">
                        <div class="row">
                            <table class="table table-striped table-bordered table-responsive">
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
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        hideLoader();

        let profile;
        const user_id = "{{ $user->id }}";

        $('#file').change(function (e) {
            profile = e.target.files[0];
        })

        $('#updateImage').change(function (e) {
            //readURL(this);
            profile = e.target.files[0];
            let formData = new FormData();
            formData.append('profile', profile);
            formData.append('user_id', user_id);
            $.ajax({
                cache: false,
                contentType: false,
                processData: false,
                type: 'post',
                data: formData,
                url: "{{ route('update-image') }}",
                beforeSend: function() {
                    showLoader();
                },
                success: function (response) {
                    /*if (response.success) {
                        $('#successMessage').show();
                        $('#successMessage').text(response.message);
                    } else {
                        $('#failedMessage').show();
                        $('#failedMessage').text(response.message);
                    }*/
                },
                complete: function() {
                    hideLoader();
                    /*setTimeout(() => {
                       location.reload();
                    }, 2000);*/
                    location.reload();
                }
            });

        });

        $('#btnDelImg').on('click', function (e) {

            if(user_id) {
                $.ajax({
                    cache: false,
                    processing : 'true',
                    serverSide : 'true',
                    url: '/profile/del-user-img/'+user_id,
                    type:"GET",
                    dataType:"json",
                    contentType:false,
                    processData:false,
                    beforeSend: function() {
                        showLoader();
                        $('#btnDelImg').hide();
                    },
                    success:function(data) {

                    },
                    complete: function() {
                        hideLoader();
                       /* setTimeout(() => {
                            location.reload();
                        }, 2000);*/
                        location.reload();
                    }
                });
            }

        });



        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').css('background-image', 'url('+e.target.result +')');
                    $('#imagePreview').hide();
                    $('#imagePreview').fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        /*$("#updateImage").change(function() {
            readURL(this);
        });*/

        function hideLoader() {
            $('#loader').hide();
        }

        function showLoader() {
            $('#loader').show();
        }

    </script>

@endsection
