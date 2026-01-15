@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.doc_logs') }}</h5>
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
                                <a href="{{ route('doc-logs.index') }}">{{ __('menu.doc_logs') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('field.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <div class="alert alert-success" role="alert">
                           <i class="ik ik-file-text"></i> {{ __('field.log_info_text') }}
                        </div>
                        <form action="{{ route('export-doc-logs') }}" method="post" role="search" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>{{ __('field.from_date') }} <span class="text-red">*</span></label>
                                        <input type="date" name="from_date" class="form-control act_date" value="{{ date('Y-m-d', strtotime($from_date??'2023-01-01')) }}" />
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group">
                                        <label>{{ __('field.to_date') }} <span class="text-red">*</span></label>
                                        <input type="date" name="to_date" class="form-control act_date" value="{{ date('Y-m-d', strtotime($to_date??now())) }}" />
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group mt-30">
                                        <button type="submit" class="btn btn-outline-success">
                                            <i data-lucide="download"></i> {{ __('action.download') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body template-demo">
                        <div class="dt-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.doc_id') }}</th>
                                    <th>{{ __('field.doc_type') }}</th>
                                    <th>{{ __('field.doc_name') }}</th>
                                    <th>{{ __('field.act_type') }}</th>
                                    <th>{{ __('field.computer_id') }}</th>
                                    <th>{{ __('field.fam_name') }}</th>
                                    <th>{{ __('field.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $model->doc_id }}</td>
                                        <td>{{ $model->log_type }}</td>
                                        <td>{!! $model->doc_name !!}</td>
                                        <td>{{ $model->actType->title??'-' }}</td>
                                        <td>{{ $model->computer_id }}</td>
                                        <td>
                                            {{ $model->userWorkHistory->personal->l_name??'-' }}
                                            {{ $model->userWorkHistory->personal->f_name??'-' }}
                                        </td>
                                        <td>{{ date('d.m.y H:i', strtotime($model->created_at)) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('not_found') }}</td>
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
