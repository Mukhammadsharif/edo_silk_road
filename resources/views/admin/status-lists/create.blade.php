@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/status-lists/status-lists-modern.css') }}" rel="stylesheet">
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
                        <h1>{{ __('action.create') }} {{ __('menu.status_list') }}</h1>
                        <p>{{ __('menu.status_list') }} / {{ __('action.create') }}</p>
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
                    <a href="{{ route('status-lists.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.status_list') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Create Form Card -->
            <div class="table-card-modern" style="max-width: 1200px; margin: 0 auto;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="file-plus"></i>
                        <span>{{ __('field.new_status') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('status-lists.store') }}" class="modern-form">
                        @csrf

                        <div class="form-grid">
                            <!-- Name -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name') }}
                                    <span class="text-red">*</span>
                                </label>
                                <input type="text"
                                       name="title"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.name') }}"
                                       value="{{ old('title') }}"
                                       required>
                            </div>

                            <!-- Language -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="languages"></i>
                                    {{ __('translation.role_name') }}
                                </label>
                                <select class="form-control-modern select2" name="lang_id">
                                    <option value="">{{ __('field.empty') }}</option>
                                    @foreach($languages as $key => $value)
                                        <option value="{{ $value->id }}">
                                            {{ $key+1 }}. {{ __($value->group.'.'.$value->key) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Type -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="tag"></i>
                                    {{ __('field.type') }}
                                </label>
                                <input type="text"
                                       name="type"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.type') }}"
                                       value="{{ old('type') }}">
                            </div>

                            <!-- Code -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="binary"></i>
                                    {{ __('field.code') }}
                                </label>
                                <input type="number"
                                       name="code"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.code') }}"
                                       value="{{ old('code') }}">
                            </div>

                            <!-- Order Number -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="list-ordered"></i>
                                    {{ __('field.order_number') }}
                                </label>
                                <input type="number"
                                       name="order_number"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.order_number') }}"
                                       value="1">
                            </div>
                        </div>

                        <!-- Icon Selection -->
                        <div class="icon-selection-section">
                            <div class="section-header">
                                <i data-lucide="sparkles"></i>
                                <h3>{{ __('field.icon_id') }}</h3>
                            </div>

                            <input type="text"
                                   id="searchIconCreate"
                                   onkeyup="searchIconsCreate()"
                                   placeholder="Search icons..."
                                   class="form-control-modern"
                                   style="margin-bottom: 1.5rem;">

                            <div class="icon-grid-modern">
                                @foreach($iconClassList->where('type', 2) as $key => $item)
                                    <label class="icon-option-modern icon_div_list_create">
                                        <input type="radio" name="icon_id" value="{{ $item->id }}">
                                        <div class="icon-card">
                                            <i data-lucide="{{ $item->name }}"></i>
                                            <span>{{ $item->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Class Selection -->
                        <div class="icon-selection-section">
                            <div class="section-header">
                                <i data-lucide="palette"></i>
                                <h3>{{ __('field.class_id') }}</h3>
                            </div>

                            <div class="class-grid-modern">
                                @foreach($iconClassList->where('type', 3) as $key => $item)
                                    <label class="class-option-modern">
                                        <input type="radio" name="class_id" value="{{ $item->id }}">
                                        <div class="class-card">
                                            <span class="{{ $item->name }}">{{ $item->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('status-lists.index') }}" class="btn-modern btn-secondary">
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
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });

        function searchIconsCreate() {
            const input = document.getElementById('searchIconCreate');
            const filter = input.value.toUpperCase();
            const iconDivs = document.getElementsByClassName('icon_div_list_create');

            for (let i = 0; i < iconDivs.length; i++) {
                const iconName = iconDivs[i].querySelector('.icon-card span').textContent;
                if (iconName.toUpperCase().indexOf(filter) > -1) {
                    iconDivs[i].style.display = '';
                } else {
                    iconDivs[i].style.display = 'none';
                }
            }
        }
    </script>
@endsection
