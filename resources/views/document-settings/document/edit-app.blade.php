@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('field.app_temps') }}</h5>
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
                                <a href="{{ route('app-document') }}">{{ __('field.app_temps') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-8 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit"></i> {{ __('action.edit') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('app-document.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.doc_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="t_name" value="{{ $model->t_name }}" class="form-control{{($errors->first('t_name') ? " form-control-warning" : "")}}">
                                </div>
                                <div class="col-sm-12" hidden>
                                    <label>{{ __('field.doc_code') }}</label>
                                    <input type="text" name="t_code" value="{{ $model->t_code }}" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ __('field.doc_text') }} <span class="text-red">*</span></label>
                                <textarea name="t_text" class="form-control" id="div_editor1">{!! $model->t_text !!}</textarea>
                            </div>
                            <div class="form-radio">
                                <label>{{ __('field.status') }}?</label>
                                <div class="radio radio-inline radio-success">
                                    <label>
                                        <input type="radio" name="status" value="active" {{ ($model->status == 1) ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('field.active') }}
                                    </label>
                                </div>
                                <div class="radio radio-inline radio-danger">
                                    <label>
                                        <input type="radio" name="status" value="passive" {{ ($model->status == 0) ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('field.delete') }}
                                    </label>
                                </div>
                            </div>
                            <a href="{{ route('app-document') }}" class="btn btn-light mr-2">
                                <i class="ik ik-x"></i>{{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i>{{ __('action.save') }}
                            </button>
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
