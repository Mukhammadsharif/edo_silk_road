@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-clipboard bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('app_temps') }}</h5>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
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
                                <a href="{{ route('app-temps.index') }}">{{ __('app_temps') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-7 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-eye"></i> {{ __('view') }}</h3>
                    </div>
                    <div class="card-body">
                        {!! $model->t_text !!}
                        <hr>
                        <a href="{{ route('app-temps.index') }}" class="btn btn-outline-secondary mr-2"><i class="ik ik-rotate-ccw"></i> {{ __('list') }}</a>
                        <a href="{{ route('app-temps.create') }}" class="btn btn-outline-primary mr-2"><i class="ik ik-plus-circle"></i> {{ __('create') }}</a>
                        <a href="{{ route('app-temps.edit', $model->id) }}" class="btn btn-outline-info"><i class="ik ik-edit"></i> {{ __('edit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
