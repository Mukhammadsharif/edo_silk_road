@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-briefcase bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('kuap_protocols') }}</h5>
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
                                <a href="{{ route('protocol.create.doc') }}">{{ __('action.create') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

{{--        @include('alert.alert')--}}

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <form action="{{ route('protocol.index') }}" method="post" role="search" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-lg-1 margin-top-5">
                                    <a href="{{ route('protocol.create.doc') }}" class="btn btn-outline-primary">
                                        <i class="ik ik-plus-circle"></i> {{ __('action.create') }}
                                    </a>
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-1 margin-top-5">
{{--                                    <input type="text" name="doc_number" placeholder="{{ __('doc_#') }}" value="{{ $doc_number??'' }}" class="form-control">--}}
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-1 margin-top-5">
{{--                                    <input type="text" name="doc_name" placeholder="{{ __('doc_name') }}" value="{{ $doc_name??'' }}" class="form-control">--}}
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-1 margin-top-5">
{{--                                    <input type="text" name="date_from" class="form-control my-bootstrap-datepicker" placeholder="{{ __('from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">--}}
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-1 margin-top-5">
{{--                                    <input type="text" name="date_till" class="form-control my-bootstrap-datepicker" placeholder="{{ __('till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">--}}
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <select name="s" class="form-control">
                                        <option value="">{{ __('status_all') }}</option>
{{--                                        @forelse($status as $key => $item)--}}
{{--                                            <option value="{{ $item->status_id }}" {{ ($s == $item->status_id ? "selected":"") }}>--}}
{{--                                                {{ __($item->lang_code??'unk') }}--}}
{{--                                            </option>--}}
{{--                                        @empty--}}
{{--                                            <option>{{ __('not_found') }}</option>--}}
{{--                                        @endforelse--}}
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <button type="submit" class="btn btn-outline-info mr-5"><i class="ik ik-search"></i>
{{--                                        {{ __('search') }} ({{ $models->total() }})--}}
                                    </button>
                                    <a href="{{ route('protocol.index') }}" class="btn btn-light">
                                        <i class="ik ik-refresh-ccw"></i> {{ __('action.clear') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('#') }}</th>
                                    <th>{{ __('table.doc') }}</th>
                                    <th>{{ __('table.doc_date') }}</th>
                                    <th>{{ __('table.doc_name') }}</th>
                                    <th>{{ __('table.signing_status') }}</th>
                                    <th>{{ __('table.signing_term') }}</th>
                                    <th>{{ __('table.executor') }}</th>
                                    <th>{{ __('table.created_at') }}</th>
                                </tr>
                                </thead>
                                <tbody>
{{--                                @forelse($models as $key => $model)--}}
{{--                                    <tr class="text-center">--}}
{{--                                        <td>{{ $key+1 }}</td>--}}
{{--                                        <td>{{ $model->doc_number??'' }}</td>--}}
{{--                                        <td>{{ date('d.m.Y', strtotime($model->doc_date)) }}</td>--}}
{{--                                        <td class="text-justify">--}}
{{--                                            <a href="{{ route('app.sec.view-doc', [$model->id, $model->doc_hash]) }}" class="text-primary">--}}
{{--                                                {{ $model->doc_name??'' }}--}}
{{--                                            </a>--}}
{{--                                        </td>--}}
{{--                                        <td>--}}
{{--                                            <span class="badge badge-pill {{ $model->kuapSecStatus->bg_class??'' }}">--}}
{{--                                                {{ __($model->kuapSecStatus->lang_code??'unk') }}--}}
{{--                                            </span>--}}
{{--                                        </td>--}}
{{--                                        <td>--}}
{{--                                            @php $date = \Carbon\Carbon::parse($model->term_date)->startOfDay();--}}
{{--                                            $now = \Carbon\Carbon::parse($model->created_at)->startOfDay();--}}
{{--                                            if ($model->status === (int)2) {--}}
{{--                                                $now = \Carbon\Carbon::now()->startOfDay();--}}
{{--                                            }--}}
{{--                                            $diff = $date->diffInDays($now); @endphp--}}
{{--                                            {{ \Carbon\Carbon::parse($model->term_date)->format('d.m.Y') }}<br>--}}
{{--                                            @if($model->term_date < date('Y-m-d', strtotime(now())))--}}
{{--                                                <span class="badge badge-pill badge-yellow">--}}
{{--                                                    <i class="ik ik-alert-circle"></i> {{ __('overdue') }}<br>--}}
{{--                                                        -{{ $diff }} {{ __('day') }}--}}
{{--                                                </span>--}}
{{--                                            @else--}}
{{--                                                <span class="badge badge-pill badge-light-blue {{ ($diff === 0 ? "text-danger":"") }}">--}}
{{--                                                    <i data-lucide="clock"></i>--}}
{{--                                                    @switch(session()->get('locale'))--}}
{{--                                                        @case('ru')--}}
{{--                                                        {{ __('remainder') }} {{ $diff }} {{ __('day') }}--}}
{{--                                                        @break--}}
{{--                                                        @case('en')--}}
{{--                                                        {{ __('remainder') }} {{ $diff }} {{ __('day') }}--}}
{{--                                                        @break--}}
{{--                                                        @default--}}
{{--                                                        {{ $diff }} {{ __('day') }} {{ __('remainder') }}--}}
{{--                                                    @endswitch--}}
{{--                                                </span>--}}
{{--                                            @endif--}}
{{--                                        </td>--}}
{{--                                        <td>{{ $model->createdId->personal->shortName()??'*' }}</td>--}}
{{--                                        <td>{{ date('d.m.y, H:i', strtotime($model->created_at)) }}</td>--}}
{{--                                    </tr>--}}
{{--                                @empty--}}
{{--                                    <tr class="text-center">--}}
{{--                                        <td colspan="8">{{ __('not_found') }}</td>--}}
{{--                                    </tr>--}}
{{--                                @endforelse--}}
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
