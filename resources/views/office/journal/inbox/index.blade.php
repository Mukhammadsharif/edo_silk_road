@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-arrow-down-right bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.inbox') }}</h5>
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
                                <a href="#">{{ __('title.inbox') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <form action="{{ route('office.inbox-docs') }}" method="post" role="search" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <select name="doc_org" class="form-control form-txt-danger select2">
                                        <option value="">{{ __('field.from_org') }}</option>
                                        @forelse($doc_organizations as $key => $doc_organization)
                                            <option value="{{ $doc_organization->id }}" {{ ($doc_org == $doc_organization->id ? "selected":"") }}>
                                                {{ $key+1 }}.
                                                {{ $doc_organization->lang($doc_organization->id)??'' }}
                                            </option>
                                        @empty
                                            <option>{{ __('field.not_found') }}</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-1 margin-top-5" hidden>
                                    <input type="text" name="doc_number" placeholder="{{ __('field.doc_#') }}" value="{{ $doc_number??'' }}" class="form-control">
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <input type="text" name="doc_name" placeholder="{{ __('field.doc_name') }}" value="{{ $doc_name??'' }}" class="form-control">
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <input type="text" name="date_from" class="form-control my-bootstrap-datepicker" placeholder="{{ __('field.from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <input type="text" name="date_till" class="form-control my-bootstrap-datepicker" placeholder="{{ __('field.till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <select name="status" class="form-control">
                                        <option value="">{{ __('field.status_all') }}</option>
                                        @forelse($statusNames as $key => $val)
                                            <option value="{{ $val->code }}" {{ ($status == $val->code ? "selected":"") }}>
                                                {{ $val->lang($val->id) }}
                                            </option>
                                        @empty
                                            <option>{{ __('field.not_found') }}</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-2 margin-top-5">
                                    <button type="submit" class="btn btn-outline-info mr-5">
                                        <i class="ik ik-search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                    </button>
                                    <a href="{{ route('office.inbox-docs') }}" class="btn btn-light">
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
                                    <th>{{  __('field.#') }}</th>
                                    <th>{{ __('field.created_at') }}</th>
                                    <th>{{ __('field.from_org') }}</th>
                                    <th>{{ __('field.doc_name') }}</th>
                                    <th>{{ __('field.doc_#') }}</th>
                                    <th>{{ __('field.doc_date') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.doc_term') }}</th>
                                    <th>{{ __('field.creator') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>{{ $model->j_number }}</td>
                                        <td>{{ date('d.m.y, H:i', strtotime($model->created_at)) }}</td>
                                        <td style="max-width: 170px">{{ $model->docOrg ? $model->docOrg->organization->lang($model->docOrg->organization->id)??'' : '' }}</td>
                                        <td style="max-width: 180px">
                                            <a href="{{ route('office.view-inbox-doc', [$model->id, $model->doc->doc_hash??'-']) }}" class="text-primary">
                                                {{ $model->doc->doc_name??__('unk') }}
                                            </a>
                                        </td>
                                        <td>{{ $model->doc->doc_number??'' }}</td>
                                        <td>{{ date('d.m.Y', strtotime($model->doc->doc_date??now())) }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $model->statusWork->badgeClass??'' }}">
                                                {{ $model->statusWork->lang($model->statusWork->id)??'' }}
                                            </span>
                                        </td>
                                        <td>@include('templates.execution-time')</td>
                                        <td>{{ $model->fromUser->personal->shortName()??'' }}</td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="9">{{ __('field.not_found') }}</td>
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
