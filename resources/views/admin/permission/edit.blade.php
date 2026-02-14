@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/permissions/permissions-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="key-square"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('edit') }}</h1>
                        <p>{{ __('roles') }} / {{ __('edit') }}</p>
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
                        <span>{{ __('roles') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Edit Form Card -->
            <div class="table-card-modern" style="max-width: 900px; margin: 0 auto;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="key-square"></i>
                        <span>{{ __('edit') }} {{ __('field.permission') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ route('permissions.update', $permission->id) }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <!-- Name -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="type"></i>
                                    {{ __('field.name') }}
                                    <span class="text-red">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       class="form-control-modern"
                                       placeholder="{{ __('field.name') }}"
                                       value="{{ $permission->name }}"
                                       required>
                            </div>

                            <!-- Language -->
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="languages"></i>
                                    {{ __('field.lang_id') }}
                                </label>
                                <select class="form-control-modern select2" name="lang_id">
                                    <option value="">{{ __('page.not-selected') }}</option>
                                    @foreach($languages as $key => $value)
                                        <option value="{{ $value->id }}" @if($value->id == $permission->lang_id) selected @endif>
                                            {{ $key+1 }}. {{ __($value->group.'.'.$value->key) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Parent Selection -->
                            <div class="form-group-modern full-width">
                                <label>
                                    <i data-lucide="folder-tree"></i>
                                    {{ __('field.parent_id') }}
                                </label>
                                <div class="parent-radio-group">
                                    <input type="radio"
                                           class="btn-check"
                                           name="parent_id"
                                           value="0"
                                           id="parent_id"
                                           @if($permission->parent_id === 0) checked @endif>
                                    <label class="parent-radio-btn" for="parent_id">
                                        <i data-lucide="circle-x"></i>
                                        {{ __('field.not_selected') }}
                                    </label>

                                    @foreach($parents as $key => $item)
                                        <input type="radio"
                                               class="btn-check"
                                               name="parent_id"
                                               value="{{ $item->id }}"
                                               id="parent_id_{{ $item->id }}"
                                               @if($permission->parent_id === $item->id) checked @endif>
                                        <label class="parent-radio-btn" for="parent_id_{{ $item->id }}">
                                            <i data-lucide="folder"></i>
                                            {{ $item->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('permissions.index') }}" class="btn-modern btn-secondary">
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
