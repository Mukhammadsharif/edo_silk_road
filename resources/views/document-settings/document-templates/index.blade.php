@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.doc_templates') }}</h5>
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
                                <a href="#">{{ __('menu.doc_templates') }}</a>
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
                        <form action="{{ route('document.templates') }}" method="post" role="search"
                              class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-1 col-sm-1 col-lg-1">
                                    <a href="{{ route('document.templates.create') }}" class="btn btn-primary">
                                        <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                                    </a>
                                </div>

                                <div class="col-md-6 col-sm-6 col-lg-4">
                                    <input type="text" name="doc_name" placeholder="{{ __('input.doc_name') }}"
                                           value="{{ $doc_name??'' }}" class="form-control">
                                </div>

                                <div class="col-md-4 col-sm-4 col-lg-4">
                                    <button type="submit" class="btn btn-info mr-5">
                                        <i data-lucide="search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                    </button>
                                    <a href="{{ route('document.templates') }}" class="btn btn-light">
                                        <i data-lucide="refresh-ccw"></i>{{ __('action.clear') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.doc_type') }}</th>
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
                                        <td><span class="badge badge-primary">{{ $model->document->parent->title??'-' }}</span></td>
                                        <td>{{ $model->document->lang($model->document->lang_id) }}</td>
                                        <td>
                                            <span
                                                class="badge badge-pill @if($model->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $model->isActive($model->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('document.templates.edit', $model->id) }}"
                                               class="btn btn-sm btn-info">
                                                <i data-lucide="square-pen"></i> {{ __('action.edit') }}
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
