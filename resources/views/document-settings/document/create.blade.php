@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.doc_types') }}</h5>

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
                                <a href="{{ route('document-type.index') }}">{{ __('menu.doc_types') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-8 offset-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i data-lucide="circle-plus"></i> {{ __('action.create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('document-type.store') }}" class="forms-sample">
                            @csrf

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="parent_id">{{ __('field.parent_id') }}</label>
                                        <select id="parent_id" class="form-control select2" name="parent_id" style="width: 100%">
                                            <option value=""> {{ __('field.empty') }} </option>
                                            @foreach($journalTypes as $key => $value)
                                                <option value="{{ $value->id }}" data-code="{{ $value->doc_code }}">
                                                    {{ $key+1 }}. {{ $value->lang($value->lang_id) }} ({{ $value->title }})
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
                            </div>

                            <div class="form-group row">
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

                            <a href="{{ route('document-type.index') }}" class="btn btn-light mr-5">
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
    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#parent_id').on('change', function () {
               if ($(this).find(':selected').data('code') === 'APP') {
                   $('#doc_code').val('APP_')
               }
            })

        });
    </script>

@endsection

