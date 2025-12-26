@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-arrow-up-right bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.outgoing') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('title.outgoing') }}</a>
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
                    <div class="card-header d-block ml-3">
                        <form action="{{ route('office.outgoing-docs') }}" method="post" role="search" class="form-inline w-100">
                            @csrf
                            <div class="row">
                                <div class="mb-2 mr-sm-2">
                                    <select name="doc_org" class="form-control form-txt-danger select2">
                                        <option value="">{{ __('field.to_org') }}</option>
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
                                <div class="mb-2 mr-sm-2">
                                    <input type="text" name="doc_number" placeholder="{{ __('field.doc_#') }}" value="{{ $doc_number??'' }}" class="form-control">
                                </div>
                                <div class="mb-2 mr-sm-2">
                                    <input type="text" name="doc_name" placeholder="{{ __('field.doc_name') }}" value="{{ $doc_name??'' }}" class="form-control">
                                </div>
                                <div class="cmb-2 mr-sm-2">
                                    <input type="text" name="date_from" class="form-control my-bootstrap-datepicker" placeholder="{{ __('field.from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">
                                </div>
                                <div class="mb-2 mr-sm-2">
                                    <input type="text" name="date_till" class="form-control my-bootstrap-datepicker" placeholder="{{ __('field.till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">
                                </div>


                                <div class="mb-2 mr-sm-2">
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

                                <button type="submit" class="btn btn-info mr-1 mb-2 mr-sm-2"><i class="ik ik-search"></i> {{ __('action.search') }} ({{ $models->total() }})</button>
                                <a href="{{ route('office.outgoing-docs') }}" class="btn btn-light mb-2">
                                    <i class="ik ik-refresh-ccw"></i> {{ __('action.clear') }}
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.created_at') }}</th>
                                    <th>{{ __('field.to_org') }}</th>
                                    <th>{{ __('field.doc_name') }}</th>
                                    <th>{{ __('field.doc_date') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.signing_term') }}</th>
                                    <th>{{ __('field.creator') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>
                                            {{ $model->createdUser->department->dep_code_doc??'' }}{{ $model->j_number }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y, H:i') }}</td>
                                        <td style="max-width: 170px">{{ $model->docOrg ? $model->docOrg->organization->lang($model->docOrg->organization->id)??'' : '' }}</td>
                                        <td>
                                            <a href="{{ route('office.view-sent-doc', [$model->id, $model->doc->doc_hash??'-']) }}" class="text-primary">
                                                {{ $model->doc->doc_name??__('unk') }}
                                            </a>
                                        </td>
                                        <td>{{ date('d.m.Y', strtotime($model->doc->doc_date??now())) }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $model->statusOut->badgeClass??'' }}">
                                                {{ $model->statusOut->lang($model->statusOut->id)??'unk' }}
                                            </span>
                                        </td>
                                        <td>@include('templates.execution-time')</td>
                                        <td>{{ $model->fromUser->personal->shortName()??'' }}</td>
                                    </tr>
                                @empty
                                    <tr class="text-center">
                                        <td colspan="8">{{ __('field.not_found') }}</td>
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
