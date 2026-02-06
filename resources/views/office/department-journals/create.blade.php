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
                        <i data-lucide="folder-open"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.department_journals_name') }}</h1>
                        <p>{{ __('field.department_journals_name') }} / {{ __('action.create') }}</p>
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
                        <span>{{ __('action.create') }} {{ __('field.department_journals_name') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('department-journals.store') }}" class="modern-form">
                        @csrf

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
                                       value="{{ old('name_uz') }}"
                                       required>
                                {!! $errors->first('name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
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
                                       value="{{ old('name_en') }}">
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
                                       value="{{ old('name_ru') }}">
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
                                       value="{{ old('name_kr') }}">
                            </div>

                            @if($isSuperUser && $organizations)
                                <div class="form-group-modern full-width">
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

                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="building"></i>
                                        {{ __('field.department') }}
                                        <span class="text-red">*</span>
                                    </label>
                                    <div class="multi-select-wrapper">
                                        <select name="department" class="form-control-modern select2" id="dep_ids" required>
                                            <option value="">{{ __('field.choose') }}</option>--}}
                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(!$isSuperUser)
                                <!-- Department Selection -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="building"></i>
                                        {{ __('field.department') }}
                                        <span class="text-red">*</span>
                                    </label>
                                    <div class="multi-select-wrapper">
                                        <select name="department" class="form-control-modern select2" required>
                                            @foreach($departments as $key => $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $key+1 }}. {{ $item->parentlang($item->parent_id) != '-' ? '('.$item->parentlang($item->parent_id).')'.'→' : ''}} {{ $item->lang($item->id) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {!! $errors->first('dep_ids', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                                    <small class="position-helper-text">{{ __('field.select_departments_hint') }}</small>
                                </div>
                            @endif

                            <!-- Department -->
{{--                            <div class="form-group-modern full-width">--}}
{{--                                <label>--}}
{{--                                    <i data-lucide="building"></i>--}}
{{--                                    {{ __('field.department') }}--}}
{{--                                    <span class="text-danger">*</span>--}}
{{--                                </label>--}}
{{--                                <select id="department"--}}
{{--                                        class="form-control-modern select2"--}}
{{--                                        style="width: 100%"--}}
{{--                                        name="department"--}}
{{--                                        required>--}}
{{--                                    <option value="">{{ __('field.choose') }}</option>--}}
{{--                                    @foreach($departments as $key => $department)--}}
{{--                                        <option value="{{ $department->id }}">{{ $key+1 }}. {{ $department->lang($department->id) }}</option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}

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
                                    <option value="incoming">1. {{ __('field.incoming') }}</option>
                                    <option value="outgoing">2. {{ __('field.outgoing') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('department-journals.index') }}" class="btn-modern btn-secondary">
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
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            const $departments = $('#dep_ids');
            const $orgId = $('#org_id');

            $orgId.on('change', function () {
                const selectedId = this.value ? this.value : 0;
                $departments.empty();

                $.ajax({
                    url: `/admin/user-posts/get-organization-departments/${selectedId}`,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        $departments.prop('disabled', false).empty();
                        $departments.append(`<option value="">{{ __('action.choose') }}</option>`);

                        if (response?.data?.length) {
                            // If sub-types exist
                            $.each(response?.data, function (key, value) {
                                let docNameLocal = value.name_uz;

                                if (value.lang_name) {
                                    const docName = value.lang_name.text;
                                    if (locale === 'ru') docNameLocal = docName.ru;
                                    else if (locale === 'en') docNameLocal = docName.en;
                                    else docNameLocal = docName.uz;
                                }

                                $departments.append(`<option data-code="${value.code}" value="${value.id}">${++key}. ${docNameLocal}</option>`);
                            });


                            // Reinitialize Select2 for journal_type2
                            setTimeout(function () {
                                $departments.select2({
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
