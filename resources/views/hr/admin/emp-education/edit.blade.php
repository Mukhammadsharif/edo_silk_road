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
                        <h3><i class="ik ik-edit-2"></i> {{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('education.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.edu_name') }}</label>
                                    <input type="text" name="edu_name" value="{{ $model->edu_name }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <br>
                                    <label>{{ __('field.fac_name') }}</label>
                                    <input type="text" name="fac_name" value="{{ $model->fac_name }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <br>
                                    <label>{{ __('field.spec_name') }}</label>
                                    <input type="text" name="spec_name" value="{{ $model->spec_name }}" class="form-control">
                                </div>
                                <div class="col-sm-4">
                                    <br>
                                    <label>{{ __('field.dip_name') }}</label>
                                    <input type="text" name="dip_name" value="{{ $model->dip_name }}" class="form-control">
                                </div>
                                <div class="col-sm-4">
                                    <br>
                                    <label>{{ __('field.from_date') }}</label>
                                    <select name="from_date" class="form-control select2">
                                        <option value="{{ date('Y', strtotime($model->from_date)) }}">{{ date('Y', strtotime($model->from_date)) }}</option>
                                        @for ($i = 1900; $i <= date('Y'); $i++)
                                            <option value="{{ $i }}">{{$i}}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <br>
                                    <label>{{ __('field.to_date') }}</label>
                                    <select name="to_date" class="form-control select2">
                                        <option value="{{ date('Y', strtotime($model->to_date)) }}">{{ date('Y', strtotime($model->to_date)) }}</option>
                                        <option value="">{{ __('field.continuing') }}</option>
                                        @for ($i = date('Y'); $i >= 1900; $i--)
                                            <option value="{{ $i }}">{{$i}}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="form-radio">
                                <label>{{ __('field.status') }}?</label>
                                <div class="radio radio-inline radio-success">
                                    <label>
                                        <input type="radio" name="isActive" value="A" {{ ($model->isActive == 'A') ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('status.active') }}
                                    </label>
                                </div>
                                <div class="radio radio-inline radio-danger">
                                    <label>
                                        <input type="radio" name="isActive" value="P" {{ ($model->isActive == 'P') ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('status.passive') }}
                                    </label>
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
@endsection
