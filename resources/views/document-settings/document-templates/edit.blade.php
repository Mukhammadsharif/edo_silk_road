@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.doc_templates') }}</h5>
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
                                <a href="{{ route('document.templates') }}">{{ __('menu.doc_templates') }}</a>
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
                        <h3><i class="ik ik-plus-circle"></i> {{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('document.templates.update', ['id' => $template->id]) }}" class="forms-sample">
                            @csrf
                            @method('POST')
                            <div class="form-group">
                                <select class="form-control select2" name="doc_id" style="width: 100%" required>
                                    <option value=""> {{ __('field.choose') }} </option>
                                    @foreach($documentTypes as $documentType)
                                        <option value="{{ $documentType->id }}" {{ $template->doc_type_id == $documentType->id ? 'selected' : '' }}>
                                            {{ $documentType->lang($documentType->lang_id) }}
                                            {{ $documentType->parent ?  '('.$documentType->parent->lang($documentType->parent->lang_id).')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ __('field.doc_text') }} <span class="text-red">*</span></label>
                                <textarea name="t_text" class="form-control" id="div_editor1" style="min-height: 450px !important;">
                                    {!! $template->t_text !!}
                                </textarea>
                            </div>
                            <div class="form-radio">
                                <label>{{ __('field.status') }}?</label>
                                <div class="radio radio-inline radio-success">
                                    <label>
                                        <input type="radio" name="status" value="active" {{ ($template->status == 1) ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('field.active') }}
                                    </label>
                                </div>
                                <div class="radio radio-inline radio-danger">
                                    <label>
                                        <input type="radio" name="status" value="passive" {{ ($template->status == 0) ? 'checked':'' }}>
                                        <i class="helper"></i>{{ __('field.delete') }}
                                    </label>
                                </div>
                            </div>
                            <a href="{{ route('document.templates') }}" class="btn btn-light mr-2">
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
        let editor1 = new RichTextEditor("#div_editor1", {
            minHeight: "600px"
        });
    </script>
@endsection
