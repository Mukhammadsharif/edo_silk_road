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
                        <form method="POST" action="{{ route('family.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label>{{ __('field.fam_code') }}</label>
                                        <select name="fam_code" class="form-control select2">
                                            @foreach($familyList as $key => $item)
                                                <option value="{{ $item->code }}" @if($item->code === $model->fam_code) selected @endif>{{ $key+1 }}. {{ $item->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-8">
                                        <label>{{ __('field.fam_name') }}</label>
                                        <input type="text" name="fam_name" value="{{ $model->fam_name }}" class="form-control">
                                    </div>
                                    <div class="col-sm-4">
                                        <br>
                                        <label>{{ __('field.br_date') }}</label>
                                        <input type="date" name="br_date" value="{{ date('Y-m-d', strtotime($model->br_date)) }}" class="form-control">
                                    </div>
                                    <div class="col-sm-8">
                                        <br>
                                        <label>{{ __('field.br_address') }}</label>
                                        <input type="text" name="br_address" value="{{ $model->br_address }}" class="form-control">
                                    </div>
                                    <div class="col-sm-6">
                                        <br>
                                        <label>{{ __('field.live_address') }}</label>
                                        <input type="text" name="live_address" value="{{ $model->live_address }}" class="form-control">
                                    </div>
                                    <div class="col-sm-6">
                                        <br>
                                        <label>{{ __('field.post_address') }}</label>
                                        <input type="text" name="post_address" value="{{ $model->post_address }}" class="form-control">
                                    </div>
                                    <div class="col-sm-6">
                                        <br>
                                        <label>{{ __('field.post_name') }}</label>
                                        <input type="text" name="post_name" value="{{ $model->post_name }}" class="form-control">
                                    </div>
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
