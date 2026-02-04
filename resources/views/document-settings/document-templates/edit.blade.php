@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-templates-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="square-pen"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit') }} {{ __('menu.doc_templates') }}</h1>
                        <p>{{ __('menu.doc_templates') }} / {{ __('action.edit') }}</p>
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
                    <a href="{{ route('document.templates') }}" class="breadcrumb-item">
                        <span>{{ __('menu.doc_templates') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Edit Form Card -->
            <div class="template-form-section" style="max-width: 1000px; margin: 0 auto;">
                <div class="template-form-header">
                    <i data-lucide="square-pen"></i>
                    <h3>{{ __('action.edit') }} {{ __('menu.doc_templates') }}</h3>
                </div>

                <div class="template-form-body">
                    <form method="POST" action="{{ route('document.templates.update', ['id' => $template->id]) }}" class="modern-form">
                        @csrf
                        @method('POST')

                        <!-- Document Type Selection -->
                        <div class="template-form-group">
                            <label>
                                <i data-lucide="folder"></i>
                                {{ __('field.doc_type') }}
                                <span class="template-required-mark">*</span>
                            </label>
                            <div class="doc-type-select-wrapper">
                                <select class="form-control-modern select2" name="doc_id" style="width: 100%" required>
                                    <option value="">{{ __('field.choose') }}</option>
                                    @foreach($documentTypes as $documentType)
                                        <option value="{{ $documentType->id }}"
                                            {{ $template->doc_type_id == $documentType->id ? 'selected' : '' }}>
                                            {{ $documentType->lang($documentType->lang_id) }}
                                            {{ $documentType->parent ? '(' . $documentType->parent->lang($documentType->parent->lang_id) . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="template-helper-text">{{ __('field.select_parent_hint') }}</small>
                        </div>

                        <!-- Document Text Editor -->
                        <div class="template-form-group">
                            <label>
                                <i data-lucide="file-text"></i>
                                {{ __('field.doc_text') }}
                                <span class="template-required-mark">*</span>
                            </label>
                            <div class="rte-container-modern">
                                <textarea name="t_text"
                                          class="form-control"
                                          id="div_editor1"
                                          style="min-height: 450px !important;">{!! $template->t_text !!}</textarea>
                            </div>
                            <small class="template-helper-text">{{ __('field.doc_text_hint') }}</small>
                        </div>

                        <!-- Status Selection -->
                        <div class="template-form-group">
                            <label>
                                <i data-lucide="power"></i>
                                {{ __('field.status') }}?
                            </label>
                            <div class="status-radio-modern">
                                <div class="status-radio-option success">
                                    <input type="radio"
                                           name="status"
                                           id="status_active"
                                           value="active"
                                        {{ ($template->status == 1) ? 'checked' : '' }}>
                                    <label for="status_active">{{ __('field.active') }}</label>
                                </div>
                                <div class="status-radio-option danger">
                                    <input type="radio"
                                           name="status"
                                           id="status_passive"
                                           value="passive"
                                        {{ ($template->status == 0) ? 'checked' : '' }}>
                                    <label for="status_passive">{{ __('field.delete') }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Template Info Box -->
                        <div style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
                                    padding: 1rem 1.25rem;
                                    border-radius: 10px;
                                    border-left: 4px solid #794DFD;
                                    margin: 1.5rem 0;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i data-lucide="info" style="width: 20px; height: 20px; color: #4338ca;"></i>
                                <div>
                                    <strong style="color: #4338ca; font-size: 14px;">{{ __('field.template_info') }}</strong>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 13px; color: #6366f1;">
                                        {{ __('field.created_date') }}: {{ \Carbon\Carbon::parse($template->created_at)->format('d.m.Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="template-form-actions">
                            <a href="{{ route('document.templates') }}" class="template-btn-cancel">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="template-btn-save">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Rich Text Editor Scripts -->
    <link rel="stylesheet" href="{{ asset('/richeditor//richtexteditor/rte_theme_default.css') }}" />
    <script type="text/javascript" src="{{ asset('/richeditor//richtexteditor/rte.js') }}"></script>
    <script type="text/javascript" src='{{ asset('/richeditor//richtexteditor/plugins/all_plugins.js') }}'></script>
    <script src="{{ asset('/richeditor/res/patch.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/richeditor/doc-patch.js') }}" type="text/javascript"></script>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            // Initialize Rich Text Editor
            let editor1 = new RichTextEditor("#div_editor1", {
                minHeight: "600px"
            });
        });
    </script>
@endsection
