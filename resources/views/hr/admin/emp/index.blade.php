@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.hr_emp') }}</h5>
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
                                <a href="#">{{ __('menu.hr_emp') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <div class="row">
                            <div class="col-sm-12">
                                <form action="{{ route('hr.emp') }}" method="POST" role="search" class="form-inline w-100">
                                    @csrf
                                    <a href="{{ route('hr.emp.create') }}" class="btn btn-primary mb-2 mr-sm-2">
                                        <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                                    </a>
                                    <input type="text" name="s_text" placeholder="{{ __('action.search') }}" value="{{ $s_text??'' }}" class="form-control w-25 mb-2 mr-sm-2">

                                    <div class="mb-2 mr-sm-2 w-15">
                                        <select name="s_status" class="form-control w-100">
                                            <option value="">{{ __('field.status_all') }}</option>
                                            @foreach($statusList as $key => $item)
                                                <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':''}}>
                                                    {{ $key+1 }}.{{ $item->lang($item->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2 mr-sm-2">
                                        <select name="s_role" class="form-control select2">
                                            <option value="">{{ __('field.role_all') }}</option>
                                            @foreach($userRoles as $key => $item)
                                                <option value="{{ $item->id }}" {{ $s_role == $item->id ? 'selected':''}}>
                                                    {{ $key+1 }}. {{ $item->lang($item->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-info mb-2 mr-10">
                                        <i data-lucide="search"></i>
                                        {{ __('action.search') }} ({{ $models->total() }})
                                    </button>
                                    <a href="{{ route('hr.emp') }}" class="btn btn-light mb-2">
                                        <i class="ik ik-refresh-cw"></i> {{ __('action.clear') }}
                                    </a>
                                </form>
                            </div>
                        </div>

                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.fam_name') }}</th>
                                    <th>{{ __('field.email') }}</th>
                                    <th>{{ __('field.department') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.roles') }}</th>
                                    <th>{{ __('field.action') }}</th>
                                    <th>{{ __('field.created_at') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $item)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            {{ $item->userPersonal->l_name??'-' }} {{ $item->userPersonal->f_name??'' }}
                                        </td>
                                        <td class="text-primary">{{ $item->email }}</td>
                                        <td>
                                            {{ $item->currentWork->department->lang($item->currentWork->department->id)??'-' }}
                                            <p class="text-muted">
                                                {{ $item->currentWork->position->lang($item->currentWork->position->id)??'-' }}
                                            </p>
                                        </td>
                                        <td>
                                            <span class="badge badge-pill @if($item->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $item->isActive($item->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(count($item->userRoles))
                                                @foreach($item->userRoles as $key1 => $role)
                                                    <span class="badge badge-primary mb-1">
                                                        {{ $role->lang($role->role_id) }}
                                                    </span><br>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('hr.emp.view', [$item->id, $item->user_hash]) }}"
                                               class="btn btn-secondary">
                                                <i class="fa fa-eye"></i> {{ __('action.view') }}
                                            </a>
                                        </td>
                                        <td>{{ date('d.m.y, H:i', strtotime($item->created_at)) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('field.not_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            {!! $models->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
