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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('status-lists.store') }}" class="forms-sample">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label>{{ __('field.name') }}</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                                </div>

                                <div class="col-sm-3">
                                    <label class="form-label">@lang('translation.role_name')</label>
                                    <select class="form-control select2" name="lang_id">
                                        <option value=""> {{ __('field.empty') }} </option>
                                        @foreach($languages as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ __($value->group.'.'.$value->key) }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label>{{ __('field.type') }}</label>
                                    <input type="text" name="type" class="form-control" value="{{ old('type') }}">
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.code') }}</label>
                                    <input type="number" name="code" class="form-control" value="{{ old('code') }}">
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.order_number') }}</label>
                                    <input type="number" name="order_number" class="form-control" value="1">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.icon_id') }}</label>
                                    <input type="text" id="searchIconCreate" onkeyup="searchIconsCreate()" placeholder="Search..." class="form-control mb-10">
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            @foreach($iconClassList->where('type', 2) as $key => $item)
                                                <div class="radio radio-inline icon_div_list_create">
                                                    <label>
                                                        <input type="radio" name="icon_id" value="{{ $item->id }}">
                                                        <i class="helper"></i>
                                                        <div class="icon-list-item">
                                                            <i data-lucide="{{ $item->name }}"></i><div class="name">{{ $item->name }}</div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <label>Class ID</label>
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            @foreach($iconClassList->where('type', 3) as $key => $item)
                                                <div class="radio radio-inline">
                                                    <label>
                                                        <input type="radio" name="class_id" value="{{ $item->id }}">
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
                            <div class="form-group row">
                                <div class="col-sm-12">
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
