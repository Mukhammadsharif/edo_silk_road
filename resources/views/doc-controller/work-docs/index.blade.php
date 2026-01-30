@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.working_documents') }}</h5>
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
                                <a href="#">{{ __('title.working_documents') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <form action="{{ route('doc-controller.work-docs') }}" method="post" role="search" class="form-inline w-100">
                            @csrf
                            <div class="mb-2 mr-sm-2">
                                <select name="doc_type" class="form-control select2">
                                    <option value="all">{{ __('document.document') }}</option>
                                    @forelse($journal_types as $key => $journal_type)
                                        <option value="{{ $journal_type->id }}" {{ ($doc_type == $journal_type->id ? "selected":"") }}>
                                            {{ $key+1 }}.
                                            {{ $journal_type->lang($journal_type->lang_id) }}
                                        </option>
                                    @empty
                                        <option>{{ __('field.not_found') }}</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="doc_name" placeholder="{{ __('input.doc_name') }}" value="{{ $doc_name??'' }}" class="form-control">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_from" class="form-control my-bootstrap-datepicker" placeholder="{{ __('input.from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_till" class="form-control my-bootstrap-datepicker" placeholder="{{ __('input.till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <select name="status" class="form-control">
                                    <option value="">{{ __('select.status_all') }}</option>
                                    @forelse($statusNames as $key => $val)
                                        <option value="{{ $val->code }}" {{ ($status == $val->code ? "selected":"") }}>
                                            {{ $val->lang($val->id) }}
                                        </option>
                                    @empty
                                        <option>{{ __('not_found') }}</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="mb-2 mr-sm-2">
                                <select name="created_user" class="form-control select2">
                                    <option value="">{{ __('select.status_all_creators') }}</option>
                                    @forelse($cr_users as $key => $item)
                                        <option value="{{ $item->id }}" {{ ($created_user == $item->id ? "selected":"") }}>
                                            {{ $item->userPersonal->shortName()??'' }}
                                        </option>
                                    @empty
                                        <option>{{ __('field.not_found') }}</option>
                                    @endforelse
                                </select>
                            </div>

                            <button type="submit" class="btn btn-info mr-1 mb-2 mr-10"><i class="fa fa-search"></i> {{ __('action.search') }} ({{ $models->total() }})</button>
                            <a href="{{ route('doc-controller.work-docs') }}" class="btn btn-light mb-2">
                                <i data-lucide="refresh-ccw"></i> {{ __('action.clear') }}
                            </a>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('document.document') }}</th>
                                    <th>{{ __('document.doc_name') }}</th>
                                    <th>{{ __('document.doc_number') }}</th>
                                    <th>{{ __('document.doc_date') }}</th>
                                    <th>{{ __('document.status') }}</th>
                                    <th>{{ __('document.signing_term') }}</th>
                                    <th>{{ __('document.perf_term') }}</th>
                                    <th>{{ __('document.creator') }}</th>
                                    <th>{{ __('document.created_at') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>
                                            <span class="badge badge-green">
                                                {{ $model->journal->lang($model->journal->lang_id)??'' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('doc-controller.action-route', [$model->id, $model->doc->doc_hash??'*']) }}" class="text-primary">
                                                {{ $model->doc->doc_name??'*' }}
                                            </a>
                                        </td>
                                        <td>{{ $model->doc->doc_number??'' }}</td>
                                        <td>{{ date('d.m.Y', strtotime($model->doc->doc_date??now())) }}</td>
                                        <td>
                                            @if($model->statusWork)
                                                <span class="badge badge-pill {{ $model->statusWork->badgeClass??'' }}">
                                                    {{ $model->statusWork->lang($model->statusWork->id)??'' }}
                                                </span>
                                            @else
                                                <span class="badge badge-pill {{ $default_status->bg_class??'' }}">
                                                    {{ __($default_status->lang_code??'unk') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('templates.execution-time')
                                        </td>
                                        <td>
                                            @include('templates.res-term-in-doc-contr')
                                        </td>
                                        <td>{{ $model->fromUser->personal->shortName()??'' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y, H:i') }}</td>
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
