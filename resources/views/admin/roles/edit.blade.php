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
                                <a href="{{ route('roles.index') }}">Roles</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit"></i> {{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('roles.update', $role->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="nameInput" class="form-label">{{ __('field.code') }}</label>
                                        <input type="text" class="form-control" name="name" value="{{ $role->name }}" placeholder="@lang('translation.name')">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="roleNameInput" class="form-label">{{ __('form.lang-id') }}</label>
                                        <select class="form-control select2" name="lang_id">
                                            <option value="">{{ __('page.not-selected') }}</option>
                                            @foreach($languages as $key => $value)
                                                <option value="{{ $value->id }}" @if($value->id == $role->lang_id) selected @endif>{{ $key+1 }}. {{ __($value->group.'.'.$value->key) }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="roleNameInput" class="form-label">{{ __('field.status') }}</label>
                                        <input type="checkbox" name="status" value="1" class="js-switch"  name="status" @if($role->status === 1) checked="" @endif />
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <a href="{{ url('admin/roles') }}" class="btn btn-light">
                                            <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ik ik-save"></i> {{ __('action.save') }}
                                        </button>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
