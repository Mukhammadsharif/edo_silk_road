@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-home bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('field.department_journals') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('field.department_journals') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('department-journals.create') }}" class="btn btn-primary">
                            <i data-lucide="circle-plus"></i> {{ __('action.create') }}
                        </a>
                    </div>
                    <div class="card-header d-block">
                        <form action="{{ route('department-journals.search') }}" method="post" role="search"
                              class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-lg-4">
                                    <input type="text" name="name" placeholder="{{ __('field.name') }}"
                                           value="{{ $name??'' }}" class="form-control">
                                </div>
                                    <button type="submit" class="btn btn-info mr-5 mr-10">
                                        <i data-lucide="search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                    </button>
                                    <a href="{{ route('department-journals.index') }}" class="btn btn-light">
                                        <i data-lucide="refresh-ccw"></i>{{ __('action.clear') }}
                                    </a>
                            </div>
                        </form><hr>

                        <div class="table-responsive">
                            <table
                                   class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.title') }}</th>
                                    <th>{{ __('field.department') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.action') }}</th>
                                    <th>{{ __('field.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($k = 1)
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>{{ $k++ }}</td>
                                        <td>{{ $model->lang($model->id) }}</td>
                                        <td>
                                            {{ $model->department->lang($model->department->id) }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-pill @if($model->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $model->isActive($model->status) }}
                                            </span>
                                        </td>
                                        <td style="width: 200px">
                                            <a href="{{ route('department-journals.edit', $model->id) }}"
                                               class="btn btn-sm btn-info">
                                                <i data-lucide="square-pen"></i> {{ __('action.edit') }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('field.not_found') }}</td>
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
