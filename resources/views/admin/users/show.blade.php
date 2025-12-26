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
            <div class="col-lg-4 col-md-5">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            @if(file_exists(public_path().'/profile_img/'.'img_'.$user->id.'.png'))
                                <img src="/profile_img/img_{{ $user->id }}.png" class="rounded-circle" width="150" />
                            @else
                                <img src="{{URL::asset('/images/profile_image1.png')}}" class="rounded-circle" width="150" />
                            @endif
                            <h4 class="card-title mt-10">{{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}</h4>
                            <p class="card-subtitle">{{ $user->userPersonal->m_name??'' }}</p>
                            <div class="row text-center justify-content-md-center" hidden>
                                <div class="col-4">
                                    <a href="javascript:void(0)" class="link">
                                        <i class="ik ik-user"></i> <font class="font-medium">-</font>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="javascript:void(0)" class="link">
                                        <i class="ik ik-image"></i> <font class="font-medium">-</font>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="mb-0">
                    <div class="card-body">
                        <h6 class="text-muted d-block">{{ __('field.department') }}:</h6>
                        <h6>{{ $user->currentWork->department->name_uz??'' }}</h6>
                        <h6 class="text-muted d-block pt-10">{{ __('field.user_posts') }}:</h6>
                        <h6>{{ $user->currentWork->position->name_uz??'' }}</h6>
                        <h6 class="text-muted d-block pt-10">{{ __('field.user_position_from_date') }}</h6>
                        @php($user_position_from_date = $user->currentWork->from_date??now())
                        <h6>{!! $user->fromPositionDate($user_position_from_date) !!}</h6>

                        <h5 class="mt-30">{{ __('field.user_roles') }}</h5>
                        <hr>
                        @foreach($userRoles as $key => $item)
                            <span class="badge badge-pill badge-primary mb-1">
                                {{ $item->lang($item->role_id) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-7">
                <div class="card">
                    <ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-user-personal-tab" data-toggle="pill" href="#user-personal" role="tab" aria-controls="pills-user-personal" aria-selected="false">
                                {{ __('field.user_personal_info') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-user-working-tab" data-toggle="pill" href="#user-working" role="tab" aria-controls="pills-user-working" aria-selected="true">
                                {{ __('field.user_working_histories') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-user-login-password-tab" data-toggle="pill" href="#user-login-password" role="tab" aria-controls="pills-user-login-password" aria-selected="false">
                                {{ __('field.user_login_password') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-user-setting-tab" data-toggle="pill" href="#user-setting" role="tab" aria-controls="pills-user-setting" aria-selected="false">
                                {{ __('field.user_setting') }}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="user-personal" role="tabpanel" aria-labelledby="pills-user-personal-tab">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-6"> <strong>Username (Login)</strong>
                                        <br>
                                        <p class="text-muted">{{ $user->username }}</p>
                                    </div>
                                    <div class="col-md-3 col-6"> <strong>Email</strong>
                                        <br>
                                        <p class="text-muted">{{ $user->email }}</p>
                                    </div>
                                    <div class="col-md-3 col-6"> <strong>Mobile</strong>
                                        <br>
                                        <p class="text-muted">{{ $user->userPersonal->phone??'-' }}</p>
                                    </div>
                                    <div class="col-md-3 col-6"> <strong>Status</strong>
                                        <br>
                                        <p class="text-muted">
                                            <span class="badge badge-pill @if($user->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $user->isActive($user->status) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <hr>
                                <p class="mt-30">Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt.Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim.</p>
                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries </p>
                                <p>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>

                                <hr>
                                <a href="{{ route('users.edit', $user->user_hash) }}" class="btn btn-info">
                                    <i class="ik ik-edit-1"></i> {{ __('action.edit') }}
                                </a>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="user-working" role="tabpanel" aria-labelledby="pills-user-working-tab">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>{{ __('field.number') }}</th>
                                            <th>{{ __('field.dep_id') }}</th>
                                            <th>{{ __('field.post_id') }}</th>
                                            <th>{{ __('field.from_date') }}</th>
                                            <th>{{ __('field.to_date') }}</th>
                                            <th>{{ __('field.status') }}</th>
                                            <th>{{ __('field.date') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($userWorkingHistories as $key => $item)
                                            <tr class="text-center">
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $item->department->name_uz??'' }}</td>
                                                <td>{{ $item->position->name_uz??'' }}</td>
                                                <td>{{ date('d.m.Y', strtotime($item->from_date)) }}</td>
                                                <td>{{ date('d.m.Y', strtotime($item->to_date)) }}</td>
                                                <td>{{ $item->status }}</td>
                                                <td>{{ date('d.m.Y', strtotime($item->created_at)) }}</td>
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
                        <div class="tab-pane fade" id="user-login-password" role="tabpanel" aria-labelledby="pills-user-login-password-tab">
                            <div class="card-body">
                                <form method="POST" action="{{ url('admin/user/edit-user-login-password', [$user->id, $user->user_hash]) }}" class="form-horizontal">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group">
                                        <label for="example-name">{{ __('field.email') }}</label>
                                        <input type="email" value="{{ $user->email }}" class="form-control" name="email">
                                    </div>
                                    <div class="form-group">
                                        <label for="example-name">{{ __('field.username') }}</label>
                                        <input type="text" value="{{ $user->username }}" class="form-control" name="username">
                                    </div>
                                    <div class="form-group">
                                        <label for="example-password">{{ __('field.password') }}</label>
                                        <input type="password" class="form-control" name="password" id="password">
                                    </div>
                                    <div class="form-group">
                                        <label for="example-password">{{ __('field.confirm_password') }}</label>
                                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ik ik-save"></i> {{ __('action.save') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="user-setting" role="tabpanel" aria-labelledby="pills-user-setting-tab">
                            <div class="card-body">
                                <form method="POST" action="{{ url('admin/user/edit-user-status', [$user->id, $user->user_hash]) }}" class="form-horizontal">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('field.user_status') }}</label>
                                                <select class="form-control select2" name="user_status" style="width: 100%">
                                                    @foreach($statusList as $key => $item)
                                                        <option value="{{ $item->code }}" @if($item->code == $user->status) selected @endif>
                                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>{{ __('field.user_sort') }}</label>
                                                <input class="form-control" name="user_sort" style="width: 50%"
                                                       value="{{ $user->userPersonal->sort }}" type="number"/>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ik ik-save"></i> {{ __('action.save') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
