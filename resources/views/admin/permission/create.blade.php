@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.roles') }}</h5>
                            <span>{{ __('menu.roles') }}</span>
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
                                <a href="{{ route('roles.index') }}">{{ __('menu.roles') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('permissions.store') }}" class="forms-sample">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="nameInput" class="form-label">{{ __('field.name') }}</label>
                                        <input type="text" class="form-control" name="name">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="roleNameInput" class="form-label">{{ __('field.lang_id') }}</label>
                                        <select class="form-control select2" name="lang_id">
                                            <option value="">{{ __('field.not_selected') }}</option>
                                            @foreach($languages as $key => $value)
                                                <option value="{{ $value->id }}">{{ $key+1 }}. {{ __($value->group.'.'.$value->key) }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <label class="form-label">{{ __('field.parent_id') }}</label>
                                    <div class="hstack gap-2 flex-wrap">
                                        <input type="radio" class="btn-check" name="parent_id" value="0" id="parent_id" checked>
                                        <label class="btn btn-outline-primary shadow-none" for="parent_id">{{ __('field.not_selected') }}</label>
                                        @foreach($parents as $key => $item)
                                            <input type="radio" class="btn-check" name="parent_id" value="{{ $item->id }}" id="parent_id_{{ $item->id }}">
                                            <label class="btn btn-outline-primary shadow-none" for="parent_id_{{ $item->id }}">{{ $item->name }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <a href="{{ url('admin/permissions') }}" class="btn btn-light">
                                            <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i data-lucide="save"></i> {{ __('action.save') }}
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
