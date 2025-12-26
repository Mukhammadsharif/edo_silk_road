@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-feather bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.sign_documents') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-3">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('title.sign_documents') }}</a>
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
                    <div class="card-header d-block">
                        <form action="{{ route('documents.for-sign.index') }}" method="post" role="search" class="form-inline w-100">
                            @csrf
                            <div class="mb-2 mr-sm-2">
                                <select name="doc_type" class="form-control">
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
                                <input type="text" name="doc_name" placeholder="{{ __('document.doc_name') }}" value="{{ $doc_name??'' }}" class="form-control">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_from" class="form-control my-bootstrap-datepicker" placeholder="{{ __('document.from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_till" class="form-control my-bootstrap-datepicker" placeholder="{{ __('document.till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <select name="status" class="form-control">
                                    <option value="all">{{ __('select.status_all') }}</option>
                                    @forelse($statusNames as $key => $val)
                                        <option value="{{ $val->code }}" {{ ($status == $val->code ? "selected":"") }}>
                                            {{ $val->lang($val->id) }}
                                        </option>
                                    @empty
                                        <option>{{ __('field.not_found') }}</option>
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
                                <a href="{{ route('documents.for-sign.index') }}" class="btn btn-light mb-2">
                                    <i class="ik ik-refresh-ccw"></i> {{ __('action.clear') }}
                                </a>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table id="simpletable1"
                                   class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.document') }}</th>
                                    <th>{{ __('field.doc_name') }}</th>
                                    <th>{{ __('field.doc_#') }}</th>
                                    <th>{{ __('field.doc_date') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.signing_term') }}</th>
                                    <th>{{ __('field.creator') }}</th>
                                    <th>{{ __('field.created_at') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            <span class="badge badge-green mb-1">
                                                {{ $model->journal->lang($model->journal->lang_id)??'' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('document.sign.view', [$model->id, $model->doc->doc_hash??'-']) }}" class="text-primary">
                                                {{ $model->doc->doc_name??'*' }}
                                            </a>
                                        </td>
                                        <td>{{ $model->doc->doc_number??'' }}</td>
                                        <td>{{ date('d.m.Y', strtotime($model->doc->doc_date??now())) }}</td>
                                        <td>
                                            <span class="badge badge-pill {{ $model->step->statusSign->badgeClass??'' }}">
                                                {{ $model->step->statusSign->lang($model->step->statusSign->id)??'unk' }}
                                            </span>
                                        </td>
                                        <td>
                                            @include('templates.execution-time')
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
