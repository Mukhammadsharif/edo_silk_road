@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/tariffs/tariffs-modern.css') }}" rel="stylesheet">

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
                        <h1>{{ __('action.create') }} {{ __('menu.tariff') }}</h1>
                        <p>{{ __('menu.tariffs') }} / {{ __('action.create') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('tariffs.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.tariffs') }}</span>
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
                        <i data-lucide="package"></i>
                        <span>{{ __('action.create') }} {{ __('menu.tariff') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    @include('templates.alert')

                    <form action="{{ route('tariffs.store') }}" method="POST" class="modern-form">
                        @csrf

                        <div class="tariff-form-grid">
                            <!-- Code -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="code"></i>
                                    {{ __('field.code') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="hash"></i>
                                    <input type="text"
                                           name="code"
                                           class="tariff-input-modern"
                                           value="{{ old('code') }}"
                                           placeholder="Masalan: BASIC"
                                           required>
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="tag"></i>
                                    {{ __('field.name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="package"></i>
                                    <input type="text"
                                           name="name"
                                           class="tariff-input-modern"
                                           value="{{ old('name') }}"
                                           placeholder="Masalan: Boshlang'ich tarif"
                                           required>
                                </div>
                            </div>

                            <!-- Max Users -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="users"></i>
                                    {{ __('field.max_users') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="user-plus"></i>
                                    <input type="number"
                                           name="max_users"
                                           class="tariff-input-modern"
                                           value="{{ old('max_users') }}"
                                           min="1"
                                           placeholder="10"
                                           required>
                                </div>
                            </div>

                            <!-- Max Documents -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="file-text"></i>
                                    {{ __('field.max_documents') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="files"></i>
                                    <input type="number"
                                           name="max_documents"
                                           class="tariff-input-modern"
                                           value="{{ old('max_documents') }}"
                                           min="1"
                                           placeholder="1000"
                                           required>
                                </div>
                            </div>

                            <!-- Price Monthly -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="calendar"></i>
                                    {{ __('field.price_monthly') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-currency-wrapper">
                                    <input type="number"
                                           name="price_monthly"
                                           class="tariff-input-modern"
                                           value="{{ old('price_monthly') }}"
                                           min="0"
                                           placeholder="100000"
                                           required>
                                </div>
                            </div>

                            <!-- Price Yearly -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="calendar-range"></i>
                                    {{ __('field.price_yearly') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-currency-wrapper">
                                    <input type="number"
                                           name="price_yearly"
                                           class="tariff-input-modern"
                                           value="{{ old('price_yearly') }}"
                                           min="0"
                                           placeholder="1000000"
                                           required>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="tariff-form-group full-width">
                                <label>
                                    <i data-lucide="power"></i>
                                    {{ __('field.status') }}
                                </label>
                                <div class="status-radio-modern">
                                    <div class="status-radio-option success">
                                        <input type="radio"
                                               name="status"
                                               id="status_active"
                                               value="1">
                                        <label for="status_active">{{ __('status.active') }}</label>
                                    </div>
                                    <div class="status-radio-option danger">
                                        <input type="radio"
                                               name="status"
                                               id="status_inactive"
                                               value="0">
                                        <label for="status_inactive">{{ __('status.inactive') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('tariffs.index') }}" class="btn-modern btn-secondary">
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
@endsection
