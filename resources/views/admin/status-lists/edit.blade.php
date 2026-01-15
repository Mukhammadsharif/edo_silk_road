@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.status_list') }}</h5>
                            <span>{{ __('menu.status_list') }}</span>
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
                                <a href="{{ route('status-lists.index') }}">{{ __('menu.status_list') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('status-lists.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')

                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label>{{ __('field.title') }}</label>
                                    <input type="text" name="title" class="form-control" value="{{ $model->title }}">
                                </div>
                                <!--end col-->
                                <div class="col-sm-3">
                                    <label for="roleNameInput" class="form-label">{{ __('form.lang-id') }}</label>
                                    <select class="form-control select2" name="lang_id">
                                        <option value="">{{ __('page.not-selected') }}</option>
                                        @foreach($languages as $key => $value)
                                            <option value="{{ $value->id }}" @if($value->id == $model->lang_id) selected @endif>{{ $key+1 }}. {{ __($value->group.'.'.$value->key) }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label>{{ __('field.type') }}</label>
                                    <input type="text" name="type" class="form-control" value="{{ $model->type }}">
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.code') }}</label>
                                    <input type="number" name="code" class="form-control" value="{{ $model->code }}">
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.order_number') }}</label>
                                    <input type="number" name="order_number" class="form-control" value="{{ $model->order_number  }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.icon_id') }}</label>
                                    <input type="text" id="searchIconCreate" onkeyup="searchIconsCreate()" placeholder="Search..." class="form-control mb-10">
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            @foreach($iconClassList->where('type', 1) as $key => $item)
                                                <div class="radio radio-inline icon_div_list_create">
                                                    <label>
                                                        <input type="radio" name="icon_id" value="{{ $item->id }}" @if($model->icon_id == $item->id) checked @endif>
                                                        <i class="helper"></i>
                                                        <div class="icon-list-item">
                                                            <i class="{{ $item->name }}"></i><div class="name">{{ $item->name }}</div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <label>{{ __('field.class_id') }}</label>
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            @foreach($iconClassList->where('type', 2) as $key => $item)
                                                <div class="radio radio-inline">
                                                    <label>
                                                        <input type="radio" name="class_id" value="{{ $item->id }}" @if($model->class_id == $item->id) checked @endif>
                                                        <i class="helper"></i>
                                                        <div class="icon-list-item">
                                                            <span class="{{ $item->name }}">{{ $item->name }}</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-lg-12">
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="{{ url('admin/status-lists') }}" class="btn btn-light">
                                        <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i data-lucide="save"></i> {{ __('action.save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
