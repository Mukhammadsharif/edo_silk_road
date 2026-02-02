@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/protocol-types-modern.css') }}" rel="stylesheet">
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
                        <h1>{{ __('action.create') }} {{ __('menu.prt_doc_types') }}</h1>
                        <p>{{ __('menu.prt_doc_types') }} / {{ __('action.create') }}</p>
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
                    <a href="{{ route('protocol-types.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.prt_doc_types') }}</span>
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
                        <span>{{ __('field.new_protocol_type') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('protocol-types.store') }}" class="modern-form">
                        @csrf

                        <div class="form-grid">
                            <!-- Parent ID (Hidden) -->
                            <div class="form-group-modern full-width" style="display: none;">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.parent_id') }}
                                </label>
                                <select name="parent_id" class="form-control-modern select2">
                                    <option value="0">{{ __('action.choose') }}</option>
                                    @foreach($docTypes as $key => $item)
                                        <option value="{{ $item->id }}">
                                            {{ $key+1 }}. {{ $item->lang($item->id) }}
                                        </option>
                                    @endforeach
                                </select>
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
                                       class="form-control-modern"
                                       placeholder="{{ __('field.name_uz') }}"
                                       value="{{ old('name_uz') }}"
                                       required>
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
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('protocol-types.index') }}" class="btn-modern btn-secondary">
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
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });
    </script>
@endsection
