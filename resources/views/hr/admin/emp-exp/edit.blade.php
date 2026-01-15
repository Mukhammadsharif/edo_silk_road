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
                        <form method="POST" action="{{ route('exp.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="checkbox-fade fade-in-primary">
                                        <label>
                                            <input type="checkbox" name="is_org" {{ ($model->is_org == 'Y' ? "checked":"") }}>
                                            <span class="cr">
                                                <i class="cr-icon ik ik-check txt-primary"></i>
                                            </span>
                                            <span>{{ __('field.is_org') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label>{{ __('field.exp_name') }}</label>
                                    <input type="text" name="exp_name" value="{{ $model->exp_name }}" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label>{{ __('field.post_name') }}</label>
                                    <input type="text" name="post_name" value="{{ $model->post_name }}" class="form-control">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-3">
                                    <label>{{ __('field.from_date') }}</label>
                                    <input type="text" name="from_date" value="{{ date('d.m.Y', strtotime($model->from_date)) }}" class="form-control my-bootstrap-datepicker" autocomplete="off">
                                </div>
                                <div class="col-3">
                                    <label>{{ __('field.to_date') }}</label>
                                    @php($to_date = '')
                                    @if($model->to_date)
                                        @php($to_date = date('d.m.Y', strtotime($model->to_date)))
                                    @endif
                                    <input type="text" name="to_date" value="{{ $to_date }}" class="form-control my-bootstrap-datepicker" autocomplete="off">
                                </div>
                                <div class="col-sm-6">
                                    <label>{{ __('field.exp_descr') }}</label>
                                    <textarea name="exp_descr" class="form-control">{!! $model->exp_descr !!}</textarea>
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
                                <i data-lucide="save"></i>{{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
