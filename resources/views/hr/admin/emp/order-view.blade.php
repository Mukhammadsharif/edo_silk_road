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
                    @include('hr.admin.emp.breadcrumb')
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                @include('hr.admin.emp.left-menu')
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-block bg-aqua">
                        <h3><i class="ik ik-file-text"></i> {{ __('menu.emp_order') }}</h3>
                    </div>
                    <div class="card-body template-demo">
                        @if($user->status == 1)
                            <a href="{{ route('hr-order.show', $user->id) }}" class="btn btn-primary">
                                <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                            </a>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr class="text-center">
                                <th>{{ __('field.#') }}</th>
                                <th>{{ __('field.ord_code') }}</th>
                                <th>{{ __('field.ord_name') }}</th>
                                <th>{{ __('field.ord_num') }}</th>
                                <th>{{ __('field.from_date') }}</th>
                                <th>{{ __('field.to_date') }}</th>
                                <th>{{ __('field.status') }}</th>
                                <th>{{ __('field.action') }}</th>
                                <th>{{ __('field.created_at') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $key => $model)
                                <tr class="text-center">
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        <span class="badge badge-blue">
                                            {{ $model->orderType($model->ord_code) }}
                                        </span>
                                    </td>
                                    <td>{{ $model->ord_name }}</td>
                                    <td>{{ $model->ord_num }}</td>
                                    <td>{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                                    <td>
                                        @if($model->to_date)
                                            {{ date('d.m.Y', strtotime($model->to_date)) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-blue">
                                            @if($model->isActive === 'A')
                                                {{ __('status.active') }}
                                            @else
                                                {{ __('status.passive') }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->status == 1)
                                            <a href="{{ route('hr-order.edit', $model->id) }}"
                                               class="btn btn-icon btn-outline-info">
                                                <i class="ik ik-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ date('d.m.y, H:i', strtotime($model->created_at)) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('field.not_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
