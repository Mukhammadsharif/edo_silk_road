@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-edit"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit') }} {{ __('field.app_temps') }}</h1>
                        <p>{{ __('field.app_temps') }} / {{ __('action.edit') }}</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('app-document') }}" class="breadcrumb-item">
                        <span>{{ __('field.app_temps') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Edit Form Card -->
            <div class="table-card-modern" style="max-width: 1000px; margin: 0 auto;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="file-edit"></i>
                        <span>{{ __('field.edit_template') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('app-document.update', $model->id) }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <div class="form-grid" style="grid-template-columns: 1fr;">
                            <!-- Template Name -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.doc_name') }}
                                    <span class="text-red">*</span>
                                </label>
                                <input type="text"
                                       name="t_name"
                                       value="{{ $model->t_name }}"
                                       class="form-control-modern {{ ($errors->first('t_name') ? 'border-danger' : '') }}"
                                       placeholder="{{ __('field.doc_name') }}"
                                       required>
                                {!! $errors->first('t_name', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                            </div>

                            <!-- Template Code (Hidden) -->
                            <div class="form-group-modern" style="display: none;">
                                <label>
                                    <i data-lucide="code"></i>
                                    {{ __('field.doc_code') }}
                                </label>
                                <input type="text"
                                       name="t_code"
                                       value="{{ $model->t_code }}"
                                       class="form-control-modern font-mono">
                            </div>

                            <!-- Template Text -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="file-text"></i>
                                    {{ __('field.doc_text') }}
                                    <span class="text-red">*</span>
                                </label>
                                <div class="rte-wrapper-modern">
                                    <textarea name="t_text" id="div_editor1" class="form-control-modern">{!! $model->t_text !!}</textarea>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </label>
                                <div class="radio-group-modern">
                                    <div class="radio-option-modern success">
                                        <input type="radio"
                                               id="status_active"
                                               name="status"
                                               value="active"
                                            {{ ($model->status == 1) ? 'checked' : '' }}>
                                        <label for="status_active">{{ __('field.active') }}</label>
                                    </div>
                                    <div class="radio-option-modern danger">
                                        <input type="radio"
                                               id="status_passive"
                                               name="status"
                                               value="passive"
                                            {{ ($model->status == 0) ? 'checked' : '' }}>
                                        <label for="status_passive">{{ __('field.delete') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('app-document') }}" class="btn-modern btn-secondary">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('/richeditor/richtexteditor/rte_theme_default.css') }}" />
    <script type="text/javascript" src="{{ asset('/richeditor/richtexteditor/rte.js') }}"></script>
    <script type="text/javascript" src='{{ asset('/richeditor/richtexteditor/plugins/all_plugins.js') }}'></script>
    <script src="{{ asset('/richeditor/res/patch.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/richeditor/doc-patch.js') }}" type="text/javascript"></script>
    <script>
        let editor1 = new RichTextEditor("#div_editor1");
    </script>
@endsection
