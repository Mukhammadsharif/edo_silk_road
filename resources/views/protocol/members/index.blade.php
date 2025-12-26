@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ $protocolType->lang($protocolType->id) }}</h5>
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
                                <a href="#">{{ $protocolType->lang($protocolType->id) }}</a>
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
                        <form action="{{ route('prt-secretary', $protocolType->doc_hash) }}" method="POST" role="search" class="form-inline w-100">
                            @csrf
                            <input type="text" name="s_text" placeholder="{{ __('document.doc_name') }}" value="{{ $s_text??'' }}" class="form-control w-25 mb-2 mr-sm-2">

                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_from" class="form-control my-bootstrap-datepicker" placeholder="{{ __('input.from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_till" class="form-control my-bootstrap-datepicker" placeholder="{{ __('input.till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">
                            </div>

                            <div class="mb-2 mr-sm-2">
                                <select name="s_status" class="form-control">
                                    <option value="all">{{ __('field.status_all') }}</option>
                                    @foreach($statusList as $key => $item)
                                        <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':''}}>
                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                        </option>
                                    @endforeach
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

                            <button type="submit" class="btn btn-info mb-2 mr-10">
                                <i class="ik ik-search"></i>
                                {{ __('action.search') }} ({{ $models->total() }})
                            </button>
                            <a href="{{ route('prt-secretary', $protocolType->doc_hash) }}" class="btn btn-light mb-2">
                                <i class="ik ik-refresh-cw"></i> {{ __('action.clear') }}
                            </a>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.doc_type') }}</th>
                                    <th>{{ __('field.doc_name') }}</th>
                                    <th>{{ __('field.doc_number') }}</th>
                                    <th>{{ __('field.doc_date') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.doc_term') }}</th>
                                    <th>{{ __('field.doc_secretary') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $item)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $item->document->docType->lang($item->document->docType->id) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('prt-secretary-view', [$item->document->id, $item->document->doc_hash]) }}" class="text-primary">
                                                {{ $item->document->doc_name??'-' }}
                                            </a>
                                        </td>
                                        <td>{{ $item->document->doc_number??'' }}</td>
                                        <td>{{ date('d.m.Y', strtotime($item->document->doc_date)) }}</td>
                                        <td>
                                            <span class="badge badge-pill @if($item->status == 4 || $item->status == 5) badge-warning @else badge-blue @endif">
                                                {{ $item->statusDoc($item->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php($doc_term = $item->document->doc_term??'')
                                            @if($doc_term)
                                                {{ date('d.m.Y', strtotime($item->document->doc_term)) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php($secretaryShortName = $item->document->secretary->personal??0)
                                            @if($secretaryShortName)
                                                {{ $secretaryShortName->shortName() }}
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
                            {!! $models->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
