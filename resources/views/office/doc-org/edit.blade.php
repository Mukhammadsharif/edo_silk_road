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
                    <div class="header-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i data-lucide="building-2"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.doc_org_name') }}</h1>
                        <p>{{ __('field.doc_org_name') }} / {{ __('action.edit') }}</p>
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
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Edit Form Card -->
            <div class="table-card-modern" style="max-width: 900px; margin: 0 auto;">
                <div class="filters-header" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-bottom: 2px solid #10b981;">
                    <div class="filters-title" style="color: #059669;">
                        <i data-lucide="edit"></i>
                        <span>{{ __('action.edit') }} {{ __('field.doc_org_name') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('doc-org.update', $model->id) }}" class="modern-form">
                        @csrf
                        @method('PUT')

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
                                       value="{{ $model->name_uz }}"
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
                                       value="{{ $model->name_ru }}">
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
                                       value="{{ $model->name_en }}">
                            </div>

                            <!-- Journal Type -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="book-open"></i>
                                    {{ __('field.journal_type') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="for_doc_type"
                                        class="form-control-modern select2"
                                        style="width: 100%"
                                        name="for_doc_type"
                                        required>
                                    <option value="">{{ __('field.empty') }}</option>
                                    @foreach($journals as $key => $journal)
                                        <option value="{{ $journal->id }}" {{ $model->for_doc_type == $journal->id ? 'selected' : '' }}>
                                            {{ $key+1 }}. {{ $journal->lang($journal->lang_id) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small>{{ __('field.select_journal_hint') }}</small>
                            </div>

                            <!-- Status -->
                            <div class="form-radio-modern full-width">
                                <label>
                                    <i data-lucide="power"></i>
                                    {{ __('field.status') }}
                                </label>
                                <div class="radio-group-modern">
                                    <div class="radio-option-modern success">
                                        <input type="radio" name="status" id="status1" value="1" {{ ($model->status == 1) ? 'checked':'' }}>
                                        <label for="status1">{{ __('status.active') }}</label>
                                    </div>
                                    <div class="radio-option-modern danger">
                                        <input type="radio" name="status" id="status2" value="2" {{ ($model->status == 2) ? 'checked':'' }}>
                                        <label for="status2">{{ __('status.delete') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('doc-org.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn-modern btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
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
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            // Hide alert after 5 seconds
            setTimeout(function() {
                $('.hideAlert').hide();
            }, 5000);

            // Initialize Lucide icons
            if (window.lucide) lucide.createIcons();
        });
    </script>

    <style>
        .radio-option-modern label:has(input:checked) {
            border-color: #794DFD !important;
            background: #f5f0ff !important;
        }
    </style>
@endsection
