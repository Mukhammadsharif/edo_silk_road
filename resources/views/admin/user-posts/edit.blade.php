@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-home bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.user_positions') }}</h5>
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
                                <a href="#">{{ __('menu.user_positions') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('user-posts.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.department') }} <span class="text-red">*</span></label><br>
                                    <select name="dep_ids[]" class="form-control select2" multiple="multiple">
                                        @foreach($selected_deps as $key => $item)
                                            <option value="{{ $item->id }}" selected>
                                                {{ $item->name_uz??'' }}
                                            </option>
                                        @endforeach
                                        @foreach($un_selected_deps as $key => $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name_uz??'' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('dep_ids', '<p class="text-danger">:message</p>') !!}
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ __('field.name_uz') }} <span class="text-red">*</span></label>
                                <input type="text" name="name_uz" class="form-control" value="{{ $model->name_uz }}">
                                {!! $errors->first('name_uz', '<p class="text-danger">:message</p>') !!}
                            </div>
                            <div class="form-group">
                                <label>{{ __('field.name_kr') }}</label>
                                <input type="text" name="name_kr" class="form-control" value="{{ $model->name_kr }}">
                            </div>
                            <div class="form-group">
                                <label>{{ __('field.name_ru') }}</label>
                                <input type="text" name="name_ru" class="form-control" value="{{ $model->name_ru }}">
                            </div>
                            <div class="form-group">
                                <label>{{ __('field.name_en') }}</label>
                                <input type="text" name="name_en" class="form-control" value="{{ $model->name_en }}">
                            </div>
                            <div class="form-group">
                                <label>{{ __('field.order_number') }}</label>
                                <input type="number" name="order_number" class="form-control" value="{{ $model->order_number }}">
                            </div>
                            <div class="form-radio">
                                <label>{{ __('field.status') }}</label>
                                <div class="radio radio-inline radio-success">
                                    <label>
                                        <input type="radio" name="status" value="1" {{ ($model->status == 1) ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('status.active') }}
                                    </label>
                                </div>
                                <div class="radio radio-inline radio-danger">
                                    <label>
                                        <input type="radio" name="status" value="2" {{ ($model->status == 2) ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('status.delete') }}
                                    </label>
                                </div>
                            </div>
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('user-posts.index') }}" class="btn btn-light">
                                    <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ik ik-save"></i> {{ __('action.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
