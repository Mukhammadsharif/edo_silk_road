@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('hr_order_type') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('hr-order-type.index') }}">{{ __('hr_order_type') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        @include('alert.alert')
        <div class="row">
            <div class="col-sm-6 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit"></i> {{ __('edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('hr-order-type.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group" hidden>
                                <label>{{ __('ord_code') }}</label>
                                <input type="text" name="ord_code" class="form-control" value="{{ $model->ord_code }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>{{ __('name') }} <span class="text-red">*</span></label>
                                <input type="text" name="ord_name" class="form-control" value="{{ $model->ord_name }}">
                                {!! $errors->first('ord_name', '<p class="text-danger">:message</p>') !!}
                            </div>
                            <div class="form-group">
                                <label>{{ __('name_ru') }} <span class="text-red">*</span></label>
                                <input type="text" name="ord_name_ru" class="form-control" value="{{ $model->ord_name_ru }}">
                                {!! $errors->first('ord_name_ru', '<p class="text-danger">:message</p>') !!}
                            </div>
                            <div class="form-group">
                                <label>{{ __('name_en') }}</label>
                                <input type="text" name="ord_name_en" class="form-control" value="{{ $model->ord_name_en }}">
                            </div>
                            <div class="form-radio">
                                <label>{{ __('status') }}</label>
                                <div class="radio radio-inline radio-success">
                                    <label>
                                        <input type="radio" name="isActive" value="A" {{ ($model->isActive == 'A') ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('active') }}
                                    </label>
                                </div>
                                <div class="radio radio-inline radio-danger">
                                    <label>
                                        <input type="radio" name="isActive" value="P" {{ ($model->isActive == 'P') ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('delete') }}
                                    </label>
                                </div>
                            </div>
                            <a href="{{ route('hr-order-type.index') }}" class="btn btn-outline-secondary">{{ __('cancel') }}</a>
                            <button type="submit" class="btn btn-outline-primary"><i data-lucide="save"></i>{{ __('save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
