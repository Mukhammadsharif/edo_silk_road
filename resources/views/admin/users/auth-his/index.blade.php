@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-activity bg-blue"></i>
                        <div class="d-inline">
                            <h5>Users His</h5>
                            <span>users His</span>
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
                                <a href="#">Users His</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">His</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table id="simpletable"
                                   class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th>{{ __('#') }}</th>
                                    <th>FIO</th>
                                    <th>POST</th>
                                    <th>IP</th>
                                    <th>VIEW</th>
                                    <th>{{ __('date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="gradeX text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            {{ $model->personal->l_name??'-' }} {{ $model->personal->f_name??'' }}
                                        </td>
                                        <td>
                                            {{ $model->user->currentWork->userPost->post_name??'-' }}
                                        </td>
                                        <td>
                                            {{ $model->ip_name }}
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-outline-info">
                                                <i class="fa fa-eye"></i> {{ __('view') }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y H:i') }}<br>
                                            <span class="text-red">
                                            {{ \Carbon\Carbon::parse($model->created_at)->diffForHumans() }}
                                            </span>
                                        </td>
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
