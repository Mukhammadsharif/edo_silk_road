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
                        <i data-lucide="shield-check"></i>
                    </div>
                    <div class="header-text">
                        <h1>Litsenziyani faollashtirish</h1>
                        <p>Obuna / Litsenziya</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">Litsenziya</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">
            <div style="max-width: 820px; margin: 0 auto;">

                {{-- Текущая активная подписка --}}
                @if($currentSubscription)
                    <div class="table-card-modern" style="margin-bottom: 1.5rem;">
                        <div class="filters-header" style="border-bottom: 1px solid #e2e8f0;">
                            <div class="filters-title" style="color: #16a34a;">
                                <i data-lucide="check-circle"></i>
                                <span>Faol obuna mavjud</span>
                            </div>
                        </div>
                        <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                            <div>
                                <div style="display:flex;align-items:center;gap:6px;color:#64748b;font-size:13px;margin-bottom:4px;">
                                    <i data-lucide="users" style="width:15px;height:15px;"></i> Maks. xodimlar
                                </div>
                                <strong style="font-size:18px;color:#1e293b;">{{ $currentSubscription->tariff->max_users ?? '—' }}</strong>
                            </div>
                            <div>
                                <div style="display:flex;align-items:center;gap:6px;color:#64748b;font-size:13px;margin-bottom:4px;">
                                    <i data-lucide="file-text" style="width:15px;height:15px;"></i> Maks. hujjatlar
                                </div>
                                <strong style="font-size:18px;color:#1e293b;">{{ $currentSubscription->tariff->max_documents ?? '—' }}</strong>
                            </div>
                            <div>
                                <div style="display:flex;align-items:center;gap:6px;color:#64748b;font-size:13px;margin-bottom:4px;">
                                    <i data-lucide="calendar-check" style="width:15px;height:15px;"></i> Amal qiladi
                                </div>
                                <strong style="font-size:18px;color:#1e293b;">{{ \Carbon\Carbon::parse($currentSubscription->ends_at)->format('d.m.Y') }}</strong>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="warning-box" style="margin-bottom: 1.5rem;">
                        <p style="margin: 0; color: #92400e;">
                            <i data-lucide="alert-triangle" style="width:16px;height:16px;vertical-align:middle;"></i>
                            Hozircha faol obuna yo'q. Litsenziya faylini yuklab faollashtiring.
                        </p>
                    </div>
                @endif

                {{-- Форма активации --}}
                <div class="table-card-modern" style="margin-bottom: 1.5rem;">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="upload-cloud"></i>
                            <span>Litsenziya faylini yuklash</span>
                        </div>
                    </div>
                    <div style="padding: 2rem;">
                        <form action="{{ route('license.activate.submit') }}" method="POST" enctype="multipart/form-data" class="modern-form">
                            @csrf
                            <div class="tariff-form-group full-width">
                                <label>
                                    <i data-lucide="file-key"></i>
                                    Sertifikat fayli (.lic)
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="paperclip"></i>
                                    <input type="file" name="license_file" class="tariff-input-modern"
                                           accept=".lic,.txt" required>
                                </div>
                                <small class="text-muted">Menejer bergan litsenziya faylini tanlang.</small>
                                @error('license_file') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-actions" style="justify-content: flex-end;">
                                <button type="submit" class="btn-modern btn-primary">
                                    <i data-lucide="shield-check"></i>
                                    Litsenziyani faollashtirish
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- История лицензий --}}
                @if($licenses->count())
                    <div class="table-card-modern">
                        <div class="filters-header">
                            <div class="filters-title">
                                <i data-lucide="history"></i>
                                <span>Litsenziyalar tarixi</span>
                            </div>
                        </div>
                        <div class="table-wrapper-modern">
                            <table class="modern-table">
                                <thead>
                                <tr>
                                    <th style="text-align: left;"><i data-lucide="key"></i> Kalit</th>
                                    <th style="width: 110px;"><i data-lucide="users"></i> Xodimlar</th>
                                    <th style="width: 120px;"><i data-lucide="file-text"></i> Hujjatlar</th>
                                    <th style="width: 100px;"><i data-lucide="calendar"></i> Muddat</th>
                                    <th style="width: 150px;"><i data-lucide="activity"></i> Holati</th>
                                    <th style="width: 150px;"><i data-lucide="clock"></i> Faollashtirilgan</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($licenses as $lic)
                                    <tr class="table-row-modern">
                                        <td class="tariff-code" style="text-align: left;">{{ $lic->license_key }}</td>
                                        <td><span class="limit-badge"><i data-lucide="users"></i> {{ $lic->max_users }}</span></td>
                                        <td><span class="limit-badge"><i data-lucide="file-text"></i> {{ $lic->max_documents }}</span></td>
                                        <td class="price-cell">{{ $lic->duration_days }} <span class="currency">kun</span></td>
                                        <td>
                                            @if($lic->status == 2)
                                                <span class="tariff-status-badge active"><i data-lucide="check-circle"></i> Faol</span>
                                            @elseif($lic->status == 1)
                                                <span class="tariff-status-badge" style="background: rgba(59,130,246,0.12); color: #2563eb;"><i data-lucide="circle-dashed"></i> Yaratilgan</span>
                                            @else
                                                <span class="tariff-status-badge inactive"><i data-lucide="x-circle"></i> Bekor</span>
                                            @endif
                                        </td>
                                        <td class="price-cell">{{ $lic->activated_at ? $lic->activated_at->format('d.m.Y H:i') : '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script> if (window.lucide) lucide.createIcons(); </script>
@endsection
