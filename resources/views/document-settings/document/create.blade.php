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
                        <i data-lucide="file-plus"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.create') }} {{ __('menu.doc_types') }}</h1>
                        <p>{{ __('menu.doc_types') }} / {{ __('action.create') }}</p>
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
                    <a href="{{ route('document-type.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.doc_types') }}</span>
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
                        <span>{{ __('field.new_document_type') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('document-type.store') }}" class="modern-form">
                        @csrf

                        <div class="form-grid">
                            <!-- Parent Document Type -->
                            @if(!$isSuperUser && $journalTypes)
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="folder"></i>
                                        {{ __('field.parent_id') }}
                                    </label>
                                    <select id="parent_id" class="form-control-modern select2" name="parent_id">
                                        <option value="">{{ __('field.empty') }}</option>
                                        @foreach($journalTypes as $key => $value)
                                            <option value="{{ $value->id }}" data-code="{{ $value->doc_code }}">
                                                {{ $key+1 }}. {{ $value->lang($value->lang_id) }} ({{ $value->title }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small>{{ __('field.select_parent_hint') }}</small>
                                </div>
                            @endif

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
                                    <small>{{ __('field.select_parent_hint') }}</small>
                                </div>

                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="folder"></i>
                                        {{ __('field.parent_id') }}
                                    </label>
                                    <select id="parent_id" class="form-control-modern select2" name="parent_id">
                                        <option value="">{{ __('field.empty') }}</option>
                                    </select>
                                    <small>{{ __('field.select_parent_hint') }}</small>
                                </div>
                            @endif

                            <!-- Name UZ -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name_uz') }}
                                    <span class="text-red">*</span>
                                </label>
                                <input type="text"
                                       name="name_uz"
                                       class="form-control-modern {{ ($errors->first('name_uz') ? 'border-danger' : '') }}"
                                       placeholder="{{ __('field.name_uz') }}"
                                       value="{{ old('name_uz') }}"
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
                                       class="form-control-modern"
                                       placeholder="{{ __('field.name_ru') }}"
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
                                       class="form-control-modern"
                                       placeholder="{{ __('field.name_en') }}"
                                       value="{{ old('name_en') }}">
                            </div>

                            {{-- ═══ МЕТКА «Внешаблонные документы (free-sign)» ═══
                                 При отмеченной метке в store() создаётся запись в
                                 free_sign_journal_types — тип распознаётся модулем
                                 free-sign независимо от id и doc_code (v59). --}}
                            <div class="form-group-modern full-width">
                                <label class="fs-type-toggle">
                                    <input type="checkbox" name="is_free_sign" value="1"
                                           {{ old('is_free_sign') ? 'checked' : '' }}>
                                    <span class="fs-type-toggle__box"><i data-lucide="check"></i></span>
                                    <span class="fs-type-toggle__text">
                                        <strong>Внешаблонные документы (free-sign)</strong>
                                        <small>Тип для загрузки готовых PDF с разметкой мест
                                               подписей в редакторе, E-IMZO/логин-подписью
                                               и QR-проверкой</small>
                                    </span>
                                </label>
                            </div>

                            <style>
                                .fs-type-toggle { display:flex; align-items:flex-start; gap:12px; cursor:pointer;
                                    padding:14px 16px; border:1px solid rgba(15,23,42,0.06); border-radius:12px;
                                    background:#fff; transition:border-color .15s; margin:0; }
                                .fs-type-toggle:hover { border-color:rgba(121,77,253,0.35); }
                                .fs-type-toggle input { position:absolute; opacity:0; pointer-events:none; }
                                .fs-type-toggle__box { flex:0 0 auto; width:22px; height:22px; margin-top:1px;
                                    border:2px solid #cbd5e1; border-radius:6px; display:flex; align-items:center;
                                    justify-content:center; transition:all .15s; }
                                .fs-type-toggle__box i { width:14px; height:14px; color:#fff; opacity:0; }
                                .fs-type-toggle input:checked + .fs-type-toggle__box {
                                    background:#794DFD; border-color:#794DFD; }
                                .fs-type-toggle input:checked + .fs-type-toggle__box i { opacity:1; }
                                .fs-type-toggle__text { display:flex; flex-direction:column; gap:2px; }
                                .fs-type-toggle__text strong { font-size:14px; color:#1e293b; font-weight:600; }
                                .fs-type-toggle__text small { font-size:12.5px; color:#94a3b8; line-height:1.45; }
                            </style>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('document-type.index') }}" class="btn-modern btn-secondary">
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
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            $('#parent_id').on('change', function () {
                if ($(this).find(':selected').data('code') === 'APP') {
                    $('#doc_code').val('APP_')
                }
            });

            const $journalType = $('#parent_id');
            const $orgId = $('#org_id');

            $orgId.on('change', function () {
                const selectedId = this.value ? this.value : 0;
                $journalType.empty();

                $.ajax({
                    url: `/document-settings/get-organization-journals/${selectedId}`,
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
