@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-clipboard bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('app_temps') }}</h5>
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
                                <a href="#">{{ __('app_temps') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <a href="{{ route('app-temps.create') }}" class="btn btn-outline-primary"><i data-lucide="circle-plus"></i>{{ __('create') }}</a>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                            <thead>
                            <tr class="text-center">
                                <th>{{ __('#') }}</th>
                                <th hidden>{{ __('doc_code') }}</th>
                                <th>{{ __('doc_name') }}</th>
                                <th>{{ __('status') }}</th>
                                <th colspan="2">{{ __('action') }}</th>
                                <th>{{ __('created_at') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $key => $model)
                                <tr class="text-center">
                                    <td>{{ $key+1 }}</td>
                                    <td hidden>
                                        <span class="badge badge-pill bg-light-blue">
                                            {{ $model->t_code }}
                                        </span>
                                    </td>
                                    <td>{{ $model->t_name }}</td>
                                    <td>
                                        <span class="badge badge-pill {{ $model->statusName->bg_class??'' }}">
                                            {{ __($model->statusName->lang_code??'unk') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('app-temps.show', $model->id) }}"
                                           class="btn btn-outline-primary">
                                            <i class="ik ik-eye"></i> {{ __('view') }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('app-temps.edit', $model->id) }}"
                                           class="btn btn-outline-info">
                                            <i class="ik ik-edit"></i> {{ __('edit') }}
                                        </a>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('not_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
