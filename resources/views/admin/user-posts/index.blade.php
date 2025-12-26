@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-home bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.user_positions') }}</h5>
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
                                <a href="#">{{ __('menu.user_positions') }}</a>
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
                        <a href="{{ route('user-posts.create') }}" class="btn btn-primary">
                            <i class="ik ik-plus-circle"></i>{{ __('action.create') }}
                        </a>
                    </div>
                    <div class="card-body template-demo">
                        <div class="dt-responsive">
                            <form action="{{ route('user-posts.search') }}" method="post" role="search" class="form-horizontal mb-3">
                                @csrf
                                <div class="row">
                                    <div class="col-4">
                                        <input type="text" name="s_text" value="{{ $s_text??'' }}" class="form-control" placeholder="{{ __('action.search') }}...">
                                    </div>
                                    <button type="submit" class="btn btn-info mr-1 mb-2 mr-10"><i class="ik ik-search"></i> {{ __('action.search') }} ({{ $models->total() }})</button>
                                    <a href="{{ route('user-posts.index') }}" class="btn btn-light mb-2">
                                        <i class="ik ik-refresh-ccw"></i>{{ __('action.clear') }}
                                    </a>
                                </div>
                            </form>
                            <hr>
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.name') }}</th>
                                    <th>{{ __('field.department') }}</th>
                                    <th>{{ __('field.order_number') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.action') }}</th>
                                    <th>{{ __('field.created_at') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $item)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $item->lang($item->id) }}</td>
                                        <td style="max-width: 180px;">
                                            @if($item->dep_ids && $item->dep_ids != 'null')
                                                <i class="text-muted">{{ $item->selectedDeps($item->dep_ids) }}</i>
                                            @endif
                                        </td>
                                        <td>{{ $item->order_number }}</td>
                                        <td>
                                             <span class="badge badge-pill @if($item->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $item->isActive($item->status) }}
                                             </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('user-posts.edit', $item->id) }}" class="btn btn-info">
                                                <i class="ik ik-edit-1"></i> {{ __('action.edit') }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d.m.y, H:i') }}</td>
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
