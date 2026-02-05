@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/user-positions/user-positions-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="circle-plus"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.create') }} {{ __('menu.user_positions') }}</h1>
                        <p>{{ __('menu.user_positions') }} / {{ __('action.create') }}</p>
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
                    <a href="{{ url('admin/user-posts') }}" class="breadcrumb-item">
                        <span>{{ __('menu.user_positions') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Create Form Card -->
            <div class="position-form-section" style="max-width: 800px; margin: 0 auto;">
                <div class="position-form-header">
                    <i data-lucide="briefcase"></i>
                    <h3>{{ __('action.create') }} {{ __('menu.user_positions') }}</h3>
                </div>

                <div class="position-form-body">
                    <form method="POST" action="{{ route('user-posts.store') }}" class="modern-form">
                        @csrf

                        @if($isSuperUser && $organizations)
                            <div class="position-form-group">
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

                            <div class="position-form-group">
                                <label>
                                    <i data-lucide="building"></i>
                                    {{ __('field.department') }}
                                    <span class="text-red">*</span>
                                </label>
                                <div class="multi-select-wrapper">
                                    <select name="dep_ids[]" class="form-control-modern select2" multiple="multiple" id="dep_ids" required>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if(!$isSuperUser)
                            <!-- Department Selection -->
                            <div class="position-form-group">
                                <label>
                                    <i data-lucide="building"></i>
                                    {{ __('field.department') }}
                                    <span class="text-red">*</span>
                                </label>
                                <div class="multi-select-wrapper">
                                    <select name="dep_ids[]" class="form-control-modern select2" multiple="multiple" required>
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

                        <!-- Language Inputs Section -->
                        <div class="language-inputs-section">
                            <div class="language-section-title">
                                <i data-lucide="languages"></i>
                                <span>{{ __('field.multilingual_names') }}</span>
                            </div>

                            <div class="language-grid">
                                <!-- Name UZ -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-uz"></div>
                                        {{ __('field.name_uz') }}
                                        <span class="text-red">*</span>
                                    </label>
                                    <input type="text"
                                           name="name_uz"
                                           class="position-input-modern"
                                           value="{{ old('name_uz') }}"
                                           placeholder="Masalan: Bosh direktor"
                                           required>
                                    {!! $errors->first('name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.25rem;">:message</p>') !!}
                                </div>

                                <!-- Name KR -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-kr"></div>
                                        {{ __('field.name_kr') }}
                                    </label>
                                    <input type="text"
                                           name="name_kr"
                                           class="position-input-modern"
                                           value="{{ old('name_kr') }}"
                                           placeholder="Бош директор">
                                </div>

                                <!-- Name RU -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-ru"></div>
                                        {{ __('field.name_ru') }}
                                    </label>
                                    <input type="text"
                                           name="name_ru"
                                           class="position-input-modern"
                                           value="{{ old('name_ru') }}"
                                           placeholder="Генеральный директор">
                                </div>

                                <!-- Name EN -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-en"></div>
                                        {{ __('field.name_en') }}
                                    </label>
                                    <input type="text"
                                           name="name_en"
                                           class="position-input-modern"
                                           value="{{ old('name_en') }}"
                                           placeholder="General Director">
                                </div>
                            </div>
                        </div>

                        <!-- Order Number -->
                        <div class="position-form-group">
                            <label>
                                <i data-lucide="list-ordered"></i>
                                {{ __('field.order_number') }}
                                <span class="text-red">*</span>
                            </label>
                            <input type="number"
                                   name="order_number"
                                   class="position-input-modern"
                                   value="1"
                                   min="1"
                                   required>
                            <small class="position-helper-text">{{ __('field.order_number_hint') }}</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('user-posts.index') }}" class="btn-modern btn-secondary">
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
