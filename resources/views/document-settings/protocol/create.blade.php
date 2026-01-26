@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.prt_doc_types') }}</h5>
                            <span>{{ __('menu.list') }}</span>
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
                                <a href="{{ route('protocol-types.index') }}">{{ __('menu.prt_doc_types') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('protocol-types.store') }}" class="forms-sample">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6" hidden>
                                            <div class="form-group">
                                                <label>{{ __('field.parent_id') }}</label>
                                                <select name="parent_id" class="form-control select2">
                                                    <option value="0">{{ __('action.choose') }}</option>
                                                    @foreach($docTypes as $key => $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('field.name_uz') }} <span class="text-red">*</span></label>
                                                <input type="text" name="name_uz" class="form-control" placeholder="{{ __('field.name_uz') }}" value="{{ old('name_uz') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('field.name_ru') }}</label>
                                                <input type="text" name="name_ru" class="form-control" placeholder="{{ __('field.name_ru') }}" value="{{ old('name_ru') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('field.name_en') }}</label>
                                                <input type="text" name="name_en" class="form-control" placeholder="{{ __('field.name_en') }}" value="{{ old('name_en') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 d-flex flex-row justify-content-end">
                                    <a href="{{ route('protocol-types.index') }}" class="btn btn-light mr-2">
                                        <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i data-lucide="save"></i> {{ __('action.save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

