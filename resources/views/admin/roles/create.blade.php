@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.roles') }}</h5>
                            <span>{{ __('menu.roles') }}</span>
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
                                <a href="{{ route('roles.index') }}">{{ __('route.roles') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('route.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i data-lucide="circle-plus"></i> {{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('roles.store') }}" class="forms-sample">
                            @csrf
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.name_uz') }} <span class="text-red">*</span></label>
                                        <input type="text" name="name_uz" class="form-control" placeholder="{{ __('field.name_uz') }}" value="{{ old('name_uz') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.name_ru') }}</label>
                                        <input type="text" name="name_ru" class="form-control" placeholder="{{ __('field.name_ru') }}" value="{{ old('name_ru') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('field.name_en') }}</label>
                                        <input type="text" name="name_en" class="form-control" placeholder="{{ __('field.name_en') }}" value="{{ old('name_en') }}">
                                    </div>
                                </div>
                            </div>

                            <a href="{{ url('admin/roles') }}" class="btn btn-light mr-5">
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
