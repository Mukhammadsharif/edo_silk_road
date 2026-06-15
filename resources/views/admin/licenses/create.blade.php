@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/tariffs/tariffs-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <div class="document-page-modern">

        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="circle-plus"></i>
                    </div>
                    <div class="header-text">
                        <h1>Yangi litsenziya</h1>
                        <p>Litsenziya boshqaruvi / Yaratish</p>
                    </div>
                </div>

                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('admin.licenses.index') }}" class="breadcrumb-item">
                        <span>Litsenziyalar</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">Yaratish</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">

            <div class="table-card-modern" style="max-width: 900px; margin: 0 auto;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="key-round"></i>
                        <span>Yangi litsenziya yaratish</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    @include('templates.alert')

                    <form action="{{ route('admin.licenses.store') }}" method="POST" class="modern-form">
                        @csrf

                        <div class="tariff-form-grid">
                            <!-- Organization -->
                            <div class="tariff-form-group full-width">
                                <label>
                                    <i data-lucide="building-2"></i>
                                    Tashkilot
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="building"></i>
                                    <select name="org_unit_id" class="tariff-input-modern" required>
                                        <option value="">— Tashkilotni tanlang —</option>
                                        @foreach($organizations as $org)
                                            <option value="{{ $org->id }}" {{ old('org_unit_id') == $org->id ? 'selected' : '' }}>
                                                {{ $org->name_uz }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('org_unit_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Max Users -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="users"></i>
                                    Maksimal faol xodimlar
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="user-plus"></i>
                                    <input type="number" name="max_users" class="tariff-input-modern"
                                           value="{{ old('max_users', 10) }}" min="1" placeholder="10" required>
                                </div>
                                @error('max_users') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Max Documents -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="file-text"></i>
                                    Maksimal hujjatlar
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="files"></i>
                                    <input type="number" name="max_documents" class="tariff-input-modern"
                                           value="{{ old('max_documents', 1000) }}" min="1" placeholder="1000" required>
                                </div>
                                @error('max_documents') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Duration -->
                            <div class="tariff-form-group full-width">
                                <label>
                                    <i data-lucide="calendar-clock"></i>
                                    Amal qilish muddati (kun)
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="calendar"></i>
                                    <input type="number" name="duration_days" class="tariff-input-modern"
                                           value="{{ old('duration_days', 365) }}" min="1" placeholder="365" required>
                                </div>
                                <small class="text-muted">Masalan: 365 = 1 yil, 30 = 1 oy</small>
                                @error('duration_days') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="warning-box" style="margin-top: 1rem;">
                            <p style="margin: 0; color: #1e40af;">
                                <i data-lucide="info" style="width:16px;height:16px;vertical-align:middle;"></i>
                                Yaratgandan so'ng <strong>.lic</strong> fayli avtomatik yuklab olinadi. Bu faylni mijozga (tashkilot direktori yoki IT admin) yuboring.
                            </p>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('admin.licenses.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="download"></i>
                                Yaratish va yuklab olish
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script> if (window.lucide) lucide.createIcons(); </script>
@endsection
