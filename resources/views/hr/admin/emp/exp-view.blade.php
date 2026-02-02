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
                        <h3><i class="ik ik-list"></i> {{ __('menu.emp_exp') }}</h3>
                    </div>
                    <div class="card-body template-demo">
                        @if($user->status == 1)
                            <a href="{{ route('exp.show', $user->id) }}" class="btn btn-primary">
                                <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                            </a>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr class="text-center">
                                <th>{{ __('field.#') }}</th>
                                <th>{{ __('field.exp_name') }}</th>
                                <th>{{ __('field.post_name') }}</th>
                                <th>{{ __('field.exp_descr') }}</th>
                                <th>{{ __('field.from_date') }}</th>
                                <th>{{ __('field.to_date') }}</th>
                                <th>{{ __('field.is_org') }}</th>
                                <th>{{ __('field.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $key => $model)
                                <tr class="text-center">
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $model->exp_name }}</td>
                                    <td>{{ $model->post_name }}</td>
                                    <td>{{ $model->exp_descr }}</td>
                                    <td>{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                                    <td>
                                        @if($model->to_date)
                                            {{ date('d.m.Y', strtotime($model->to_date)) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($model->is_org === 'Y')
                                            <span class="badge badge-pill bg-success">
                                                {{ __('field.in_org') }}
                                            </span>
                                        @else
                                            <span class="badge badge-pill bg-light-blue">
                                                {{ __('field.other_org') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($model->is_org === 'N' and $user->status == 1)
                                            <a href="{{ route('exp.edit', $model->id) }}"
                                               class="btn btn-icon btn-outline-info">
                                                <i class="ik ik-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('field.not_found') }}</td>
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
