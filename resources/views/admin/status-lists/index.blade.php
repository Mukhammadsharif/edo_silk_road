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
                                <a href="#">{{ __('menu.status_list') }}</a>
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
                    <div class="card-header d-block">
                        <a href="{{ route('status-lists.create') }}" class="btn btn-primary">
                            <i class="ik ik-plus-circle"></i> {{ __('action.create') }}
                        </a>
                    </div>

                    <div class="card-body template-demo">
                        <form action="{{ route('status-lists.index') }}" method="post" role="search" class="form-horizontal">
                            @csrf
                            <div class="row ml-1">
                                <div class="mb-2 mr-sm-2 w-25">
                                    <input type="text" name="s_text" value="{{ $s_text??'' }}" class="form-control" placeholder="{{ __('action.search') }}...">
                                </div>

                                <div class="mb-2 mr-sm-2 w-25">
                                    <select name="s_type" class="form-control select2" style="width: 100%">
                                        <option value="">{{ __('field.fields') }}</option>
                                        @foreach($types as $key => $item)
                                            <option value="{{ $item->type }}" {{ $s_type == $item->type ? 'selected':''}}>
                                                {{ $key+1 }}. {{ $item->type ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-info mr-sm-2 mb-2">
                                    <i class="ik ik-search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                </button>
                                <a href="{{ route('status-lists.index') }}" class="btn btn-light mb-2">
                                    <i class="ik ik-refresh-ccw"></i> {{ __('action.clear') }}
                                </a>
                            </div>
                        </form><hr>

                        <div class="dt-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr class="text-center">
                                        <th>{{ __('field.number') }}</th>
                                        <th>{{ __('field.title') }}</th>
                                        <th>{{ __('field.lang_id') }}</th>
                                        <th>{{ __('field.type') }}</th>
                                        <th>{{ __('field.code') }}</th>
                                        <th>{{ __('field.order_number') }}</th>
                                        <th>{{ __('field.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $model->title }}</td>
                                        <td>
                                            <span class="{{ $model->badgeClass->name??'-' }}">
                                               <i class="{{ $model->iconClass->name??'-' }}"></i> {{ $model->lang($model->id) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-pill badge-secondary mb-1">{{ $model->type }}</span>
                                        </td>
                                        <td>{{ $model->code }}</td>
                                        <td>{{ $model->order_number??'' }}</td>
                                        <td>
                                            <a href="{{ route('status-lists.edit', $model->id) }}" class="btn btn-sm btn-info">
                                                <i class="ik ik-edit-1"></i> {{ __('action.edit') }}
                                            </a>
                                        </td>
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

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

