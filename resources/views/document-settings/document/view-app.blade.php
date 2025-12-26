@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('field.app_temps') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    @include('templates.alert')
                </div>
                <div class="col-md-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('field.app_temps') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block pb-3">
                        <form action="{{ route('app-document') }}" method="post" role="search"
                              class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-lg-4">
                                    <input type="text" name="doc_name" placeholder="{{ __('input.doc_name') }}"
                                           value="{{ $doc_name??'' }}" class="form-control">
                                </div>
                                    <button type="submit" class="btn btn-info mr-5">
                                        <i class="ik ik-search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                    </button>
                                    <a href="{{ route('app-document') }}" class="btn btn-light">
                                        <i class="ik ik-refresh-ccw"></i>{{ __('action.clear') }}
                                    </a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.name') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.action') }}</th>
                                    <th>{{ __('field.created_date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($k = 1)
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>{{ $k++ }}</td>
                                        <td>{{ $model->t_name }}</td>
                                        <td>
                                            <span
                                                class="badge badge-pill @if($model->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $model->isActive($model->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('app-document.edit', $model->id) }}"
                                               class="btn btn-sm btn-info">
                                                <i class="ik ik-edit-1"></i> {{ __('action.edit') }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="5">{{ __('field.not_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            {!! $models->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
