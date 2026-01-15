@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-clipboard bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('app_temps') }}</h5>
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
                                <a href="{{ route('app-temps.index') }}">{{ __('app_temps') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        @include('alert.alert')
        <div class="row">
            <div class="col-sm-8 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit"></i> {{ __('edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('app-temps.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <div class="col-sm-8">
                                    <label>{{ __('doc_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="t_name" value="{{ $model->t_name }}" class="form-control{{($errors->first('t_name') ? " form-control-warning" : "")}}">
                                </div>
                                <div class="col-sm-4" hidden>
                                    <label>{{ __('doc_code') }}</label>
                                    <input type="text" name="t_code" value="{{ $model->t_code }}" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ __('doc_text') }} <span class="text-red">*</span></label>
                                <textarea name="t_text" class="form-control" id="div_editor1">{!! $model->t_text !!}</textarea>
                            </div>
                            <div class="form-radio">
                                <label>{{ __('status') }}?</label>
                                <div class="radio radio-inline radio-success">
                                    <label>
                                        <input type="radio" name="isActive" value="A" {{ ($model->isActive == 'A') ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('active') }}
                                    </label>
                                </div>
                                <div class="radio radio-inline radio-danger">
                                    <label>
                                        <input type="radio" name="isActive" value="P" {{ ($model->isActive == 'P') ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('delete') }}
                                    </label>
                                </div>
                            </div>
                            <a href="{{ route('app-temps.index') }}" class="btn btn-outline-secondary mr-2">{{ __('cancel') }}</a>
                            <button type="submit" class="btn btn-outline-primary"><i data-lucide="save"></i>{{ __('save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('/richeditor//richtexteditor/rte_theme_default.css') }}" />
    <script type="text/javascript" src="{{ asset('/richeditor//richtexteditor/rte.js') }}"></script>
    <script type="text/javascript" src='{{ asset('/richeditor//richtexteditor/plugins/all_plugins.js') }}'></script>
    <script src="{{ asset('/richeditor/res/patch.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/richeditor/doc-patch.js') }}" type="text/javascript"></script>
    <script>
        let editor1 = new RichTextEditor("#div_editor1");
    </script>
@endsection
