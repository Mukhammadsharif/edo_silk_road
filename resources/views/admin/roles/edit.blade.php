@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/roles/roles-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="shield-check"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit') }} {{ __('menu.roles') }}</h1>
                        <p>{{ __('menu.roles') }} / {{ __('action.edit') }}</p>
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
                    <a href="{{ route('roles.index') }}" class="breadcrumb-item">
                        <span>Roles</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">Edit</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Edit Form Card -->
            <div class="table-card-modern" style="max-width: 800px; margin: 0 auto;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="shield-check"></i>
                        <span>{{ __('action.edit') }} {{ __('field.role') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('roles.update', $role->id) }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <!-- Code -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="code"></i>
                                    {{ __('field.code') }}
                                </label>
                                <input type="text"
                                       name="name"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.code') }}"
                                       value="{{ $role->name }}">
                            </div>

                            <!-- Language -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="languages"></i>
                                    {{ __('form.lang-id') }}
                                </label>
                                <select class="form-control-modern select2" name="lang_id">
                                    <option value="">{{ __('page.not-selected') }}</option>
                                    @foreach($languages as $key => $value)
                                        <option value="{{ $value->id }}" @if($value->id == $role->lang_id) selected @endif>
                                            {{ $key+1 }}. {{ __($value->group.'.'.$value->key) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </label>
                                <div class="status-toggle-wrapper">
                                    <label class="status-toggle">
                                        <input type="checkbox"
                                               name="status"
                                               value="1"
                                               class="js-switch"
                                               @if($role->status === 1) checked @endif>
                                        <span class="toggle-label">
                                            <i data-lucide="check-circle"></i>
                                            {{ __('field.active') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('roles.index') }}" class="btn-modern btn-secondary">
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
    </script>
@endsection
