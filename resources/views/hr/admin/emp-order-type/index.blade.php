@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('hr_order_type') }}</h5>
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
                                <a href="#">{{ __('hr_order_type') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        @include('alert.alert')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <a href="{{ route('hr-order-type.create') }}" class="btn btn-outline-primary">
                            <i class="ik ik-plus-circle"></i>{{ __('create') }}
                        </a>
                    </div>
                    <div class="card-body template-demo">
                        <div class="dt-responsive">
                            <table id="simpletable"
                                   class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('#') }}</th>
                                    <th>{{ __('name') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('action') }}</th>
                                    <th>{{ __('date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            @if(session()->get('locale') === 'ru')
                                                {{ $model->ord_name_ru }}
                                            @else
                                                {{ $model->ord_name }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-pill {{ $model->statusName->bg_class??'' }}">
                                                <i class="{{ $model->statusName->icon_code??'' }}"></i>
                                                {{ __($model->statusName->lang_code??'unk') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('hr-order-type.edit', $model->id) }}" class="btn btn-outline-info">
                                                <i class="ik ik-edit"></i> {{ __('edit') }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('not_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
{{--                            {!! $models->links() !!}--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
