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
                        <h3><i class="ik ik-book-open"></i> {{ __('menu.emp_edu') }}</h3>
                    </div>
                    <div class="card-body template-demo">
                        @if($user->status == 1)
                            <a href="{{ route('education.show', $user->id) }}" class="btn btn-primary">
                                <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                            </a>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr class="text-center">
                                <th>{{ __('field.#') }}</th>
                                <th>{{ __('field.edu_name') }}</th>
                                <th>{{ __('field.fac_name') }}</th>
                                <th>{{ __('field.spec_name') }}</th>
                                <th>{{ __('field.dip_name') }}</th>
                                <th>{{ __('field.from_date') }}</th>
                                <th>{{ __('field.to_date') }}</th>
                                <th>{{ __('field.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $key => $model)
                                <tr class="text-center">
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $model->edu_name }}</td>
                                    <td>{{ $model->fac_name }}</td>
                                    <td>{{ $model->spec_name }}</td>
                                    <td>{{ $model->dip_name }}</td>
                                    <td>{{ date('Y', strtotime($model->from_date)) }}</td>
                                    <td>
                                        @if($model->to_date)
                                            {{ date('Y', strtotime($model->to_date)) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->status == 1)
                                            <a href="{{ route('education.edit', $model->id) }}"
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
