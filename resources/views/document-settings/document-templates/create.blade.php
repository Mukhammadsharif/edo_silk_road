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
                        <i data-lucide="file-plus"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.create') }} {{ __('menu.doc_templates') }}</h1>
                        <p>{{ __('menu.doc_templates') }} / {{ __('action.create') }}</p>
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
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Create Form Card -->
            <div class="template-form-section" style="max-width: 1000px; margin: 0 auto;">
                <div class="template-form-header">
                    <i data-lucide="file-plus"></i>
                    <h3>{{ __('action.create') }} {{ __('menu.doc_templates') }}</h3>
                </div>

                <div class="template-form-body">
                    <form method="POST" action="{{ route('document.templates.store') }}" class="modern-form">
                        @csrf
                        @method('POST')

                        <!-- Document Type Selection -->
                        @if(!$isSuperUser && $documentTypes)
                            <div class="template-form-group my-2">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.doc_type') }}
                                    <span class="template-required-mark">*</span>
                                </label>
                                <div class="doc-type-select-wrapper">
                                    <select class="form-control-modern select2" name="doc_id" style="width: 100%"
                                            required>
                                        <option value="">{{ __('field.choose') }}</option>
                                        @foreach($documentTypes as $documentType)
                                            <option value="{{ $documentType->id }}">
                                                {{ $documentType->lang($documentType->lang_id) }}
                                                {{ $documentType->parent ? '(' . $documentType->parent->lang($documentType->parent->lang_id) . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if($isSuperUser && $organizations)
                            <div class="form-group-modern full-width my-2">
                                <label>
                                    <i data-lucide="building"></i>
                                    {{ __('field.organizations') }}
                                </label>
                                <select id="org_id" class="form-control-modern select2" name="org_id">
                                    <option value="">{{ __('field.empty') }}</option>
                                    @foreach($organizations as $key => $value)
                                        <option value="{{ $value->id }}" data-code="{{ $value->code }}">
                                            {{ $key+1 }}. {{ $value->lang($value->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group-modern full-width my-2">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.document') }}
                                </label>
                                <select id="doc_id" class="form-control-modern select2" name="doc_id">
                                    <option value="">{{ __('field.empty') }}</option>
                                </select>
                            </div>
                        @endif

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
                                          style="min-height: 450px !important;"></textarea>
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
                                           checked>
                                    <label for="status_active">{{ __('field.active') }}</label>
                                </div>
                                <div class="status-radio-option danger">
                                    <input type="radio"
                                           name="status"
                                           id="status_passive"
                                           value="passive">
                                    <label for="status_passive">{{ __('field.delete') }}</label>
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
    <link rel="stylesheet" href="{{ asset('/richeditor//richtexteditor/rte_theme_default.css') }}"/>
    <script type="text/javascript" src="{{ asset('/richeditor//richtexteditor/rte.js') }}"></script>
    <script type="text/javascript" src='{{ asset('/richeditor//richtexteditor/plugins/all_plugins.js') }}'></script>
    <script src="{{ asset('/richeditor/res/patch.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/richeditor/doc-patch.js') }}" type="text/javascript"></script>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            // Initialize Rich Text Editor
            let editor1 = new RichTextEditor("#div_editor1", {
                minHeight: "600px"
            });

            const $journalType = $('#doc_id');
            const $orgId = $('#org_id');

            $orgId.on('change', function () {
                const selectedId = this.value ? this.value : 0;
                $journalType.empty();

                $.ajax({
                    url: `/document-settings/get-organization-journals-temps/${selectedId}`,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        $journalType.prop('disabled', false).empty();
                        $journalType.append(`<option value="">{{ __('action.choose') }}</option>`);

                        if (response?.data?.length) {
                            // If sub-types exist
                            $.each(response?.data, function (key, value) {
                                let docNameLocal = value.title;

                                if (value.lang_name) {
                                    const docName = value.lang_name.text;
                                    if (locale === 'ru') docNameLocal = docName.ru;
                                    else if (locale === 'en') docNameLocal = docName.en;
                                    else docNameLocal = docName.uz;
                                }

                                $journalType.append(`<option data-code="${value.doc_code}" value="${value.id}">${++key}. ${docNameLocal}</option>`);
                            });


                            // Reinitialize Select2 for journal_type2
                            setTimeout(function() {
                                $journalType.select2({
                                    width: '100%',
                                    placeholder: '{{ __('action.choose') }}',
                                    minimumResultsForSearch: 5
                                });
                            }, 100);

                        }
                    },
                    error: function (xhr) {
                        console.error('Xatolik:', xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection
