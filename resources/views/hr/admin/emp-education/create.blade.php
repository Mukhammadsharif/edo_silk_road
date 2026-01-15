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
                                <a href="#">{{ __('title.emp') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                @include('hr.admin.emp.left-menu')
            </div>
            <div class="col-sm-9">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-user"></i> {{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('education.store') }}" class="forms-sample">
                            @csrf
                            <input type="number" name="user_id" value="{{ $id }}" hidden>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.edu_name') }}</label>
                                    <input type="text" name="edu_name" value="{{ old('edu_name') }}" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label>{{ __('field.fac_name') }}</label>
                                    <input type="text" name="fac_name" value="{{ old('fac_name') }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label>{{ __('field.spec_name') }}</label>
                                    <input type="text" name="spec_name" value="{{ old('spec_name') }}" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label>{{ __('field.dip_name') }}</label>
                                    <input type="text" name="dip_name" value="{{ old('dip_name') }}" class="form-control">
                                </div>
                                <div class="col-sm-4">
                                    <label>{{ __('field.from_date') }}</label>
                                    <select name="from_date" class="form-control select2">
                                        @for ($i = 1900; $i <= date('Y'); $i++)
                                            <option value="{{ $i }}">{{$i}}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label>{{ __('field.to_date') }}</label>
                                    <select name="to_date" class="form-control select2">
                                        <option value="">{{ __('field.continuing') }}</option>
                                        @for ($i = date('Y'); $i >= 1900; $i--)
                                            <option value="{{ $i }}">{{$i}}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}" class="btn btn-light mr-2">
                                <i class="ik ik-x"></i>{{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i>{{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
