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
                        <i data-lucide="folder-open"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.department_journals_name') }}</h1>
                        <p>{{ __('field.department_journals_name') }} / {{ __('action.edit') }}</p>
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
                    <a href="{{ route('department-journals.index') }}" class="breadcrumb-item">
                        <span>{{ __('field.department_journals_name') }}</span>
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
                        <span>{{ __('action.edit') }} {{ __('field.department_journals_name') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('department-journals.update', $model->id) }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <!-- Name UZ -->
                            <div class="form-group-modern">
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
                            <div class="form-group-modern">
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

                            <!-- Name KR -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name_kr') }}
                                </label>
                                <input type="text"
                                       name="name_kr"
                                       id="name_kr"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.title') }}"
                                       value="{{ $model->name_kr }}">
                            </div>

                            <!-- Name EN -->
                            <div class="form-group-modern">
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

                            <!-- Department -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="building"></i>
                                    {{ __('field.department') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="department"
                                        class="form-control-modern select2"
                                        style="width: 100%"
                                        name="department"
                                        required>
                                    <option value="">{{ __('field.empty') }}</option>
                                    @foreach($departments as $key => $department)
                                        <option value="{{ $department->id }}" {{ $model->dep_id == $department->id ? 'selected' : '' }}>
                                            {{ $key+1 }}. {{ $department->lang($department->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Type -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="tag"></i>
                                    {{ __('field.type') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="type"
                                        class="form-control-modern select2"
                                        style="width: 100%"
                                        name="type"
                                        required>
                                    <option value="incoming" {{ $model->type == 'incoming' ? 'selected' : '' }}>1. {{ __('field.incoming') }}</option>
                                    <option value="outgoing" {{ $model->type == 'outgoing' ? 'selected' : '' }}>2. {{ __('field.outgoing') }}</option>
                                </select>
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
                                        <input type="radio" name="status" id="status2" value="2" {{ ($model->status == 0) ? 'checked':'' }}>
                                        <label for="status2">{{ __('status.passive') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('department-journals.index') }}" class="btn-modern btn-secondary">
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
