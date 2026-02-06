@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="building-2"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.doc_org_name') }}</h1>
                        <p>{{ __('field.doc_org_name') }} / {{ __('action.create') }}</p>
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
                    <a href="{{ route('doc-org.index') }}" class="breadcrumb-item">
                        <span>{{ __('field.doc_org_name') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Create Form Card -->
            <div class="table-card-modern" style="max-width: 900px; margin: 0 auto;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="file-plus"></i>
                        <span>{{ __('action.create') }} {{ __('field.doc_org_name') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('doc-org.store') }}" class="modern-form">
                        @csrf

                        @if(!$isSuperUser && $journals)
                            <div class="template-form-group my-2">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.doc_type') }}
                                    <span class="template-required-mark">*</span>
                                </label>
                                <div class="doc-type-select-wrapper">
                                    <select class="form-control-modern select2" name="for_doc_type" style="width: 100%"
                                            required>
                                        <option value="">{{ __('field.choose') }}</option>
                                        @foreach($journals as $journal)
                                            <option value="{{ $journal->id }}">
                                                {{ $journal->lang($journal->lang_id) }}
                                                {{ $journal->parent ? '(' . $journal->parent->lang($journal->parent->lang_id) . ')' : '' }}
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
                                <select id="doc_id" class="form-control-modern select2" name="for_doc_type">
                                    <option value="">{{ __('field.empty') }}</option>
                                </select>
                            </div>
                        @endif

                        <div class="form-grid">
                            <!-- Name UZ -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name_uz') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name_uz"
                                       id="name_uz"
                                       class="form-control-modern {{ ($errors->first('name_uz') ? 'border-danger' : '') }}"
                                       placeholder="{{ __('field.title') }}"
                                       value="{{ old('name_uz') }}"
                                       required>
                                {!! $errors->first('name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                            </div>

                            <!-- Name RU -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name_ru') }}
                                </label>
                                <input type="text"
                                       name="name_ru"
                                       id="name_ru"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.title') }}"
                                       value="{{ old('name_ru') }}">
                            </div>

                            <!-- Name EN -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name_en') }}
                                </label>
                                <input type="text"
                                       name="name_en"
                                       id="name_en"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.title') }}"
                                       value="{{ old('name_en') }}">
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('doc-org.index') }}" class="btn-modern btn-secondary">
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

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
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
