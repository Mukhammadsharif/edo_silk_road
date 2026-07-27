@extends('layouts.app')

{{-- Создание произвольного исходящего документа (Didox doctype 000):
     заполняем поля + прикладываем PDF → создаётся черновик в Didox. --}}

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/working-documents-modern.css') }}" rel="stylesheet">

    <style>
        .ext-form-grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:14px 18px; }
        .ext-form-grid .full { grid-column: 1 / -1; }
        .ext-field label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#334155; }
        .ext-field input, .ext-field select, .ext-field textarea {
            width:100%; padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; background:#fff;
        }
        .ext-field input[readonly] { background:#f8fafc; color:#64748b; }
        .ext-section-title { font-weight:700; font-size:15px; margin:6px 0 2px; display:flex; align-items:center; gap:8px; }
        @media (max-width: 800px) { .ext-form-grid { grid-template-columns: minmax(0,1fr); } }
    </style>

    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content-modern">
                <div class="header-title-modern">
                    <i data-lucide="file-plus-2"></i>
                    <h1>Новый исходящий документ (Didox)</h1>
                </div>
                <nav class="breadcrumb-modern" style="margin-top:1rem;">
                    <a href="{{ route('external-docs.outgoing') }}" class="breadcrumb-item">Исходящие</a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">Создание</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">
            @if(session('error'))
                <div class="filters-card-modern" style="padding:14px 18px; margin-bottom:14px; color:#b91c1c; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="circle-alert"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="filters-card-modern" style="padding:14px 18px; margin-bottom:14px; color:#b91c1c;">
                    <div style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:6px;">
                        <i data-lucide="circle-alert"></i> Проверьте поля формы
                    </div>
                    <ul style="margin:0; padding-left:20px; font-size:13px;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @unless($authorized)
                <div class="filters-card-modern" style="padding:14px 18px; margin-bottom:14px; color:#a16207; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="alert-triangle"></i>
                    Нет активной авторизации Didox —
                    <a href="{{ route('external-docs.index') }}" style="color:#7c3aed; font-weight:600;">войти →</a>
                </div>
            @endunless

            <form action="{{ route('external-docs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="filters-card-modern" style="padding:18px 20px;">
                    <div class="ext-section-title"><i data-lucide="file-text" style="width:18px;height:18px;"></i> Документ</div>
                    <div class="ext-form-grid" style="margin-top:10px;">
                        <div class="ext-field">
                            <label>Номер документа *</label>
                            <input type="text" name="document_no" value="{{ old('document_no') }}" required>
                        </div>
                        <div class="ext-field">
                            <label>Дата документа *</label>
                            <input type="date" name="document_date" value="{{ old('document_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="ext-field full">
                            <label>Наименование документа *</label>
                            <input type="text" name="document_name" value="{{ old('document_name') }}" required>
                        </div>
                        <div class="ext-field">
                            <label>Тип (Subtype) *</label>
                            <select name="subtype" required>
                                @foreach($subtypes as $code => $label)
                                    <option value="{{ $code }}" {{ (int) old('subtype', 6) === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ext-field">
                            <label>PDF-файл документа * (до 10 МБ)</label>
                            <input type="file" name="document_file" accept="application/pdf" required>
                        </div>
                        <div class="ext-field">
                            <label>Номер договора</label>
                            <input type="text" name="contract_no" value="{{ old('contract_no') }}">
                        </div>
                        <div class="ext-field">
                            <label>Дата договора</label>
                            <input type="date" name="contract_date" value="{{ old('contract_date') }}">
                        </div>
                    </div>
                </div>

                <div class="filters-card-modern" style="padding:18px 20px; margin-top:14px;">
                    <div class="ext-section-title"><i data-lucide="building-2" style="width:18px;height:18px;"></i> Поставщик (ваша организация)</div>
                    <div class="ext-form-grid" style="margin-top:10px;">
                        <div class="ext-field">
                            <label>Наименование</label>
                            <input type="text" value="{{ $sellerName }}" readonly>
                        </div>
                        <div class="ext-field">
                            <label>ИНН</label>
                            <input type="text" value="{{ $org->tax_number }}" readonly>
                        </div>
                        <div class="ext-field full">
                            <label>Адрес поставщика *</label>
                            <input type="text" name="seller_address" value="{{ old('seller_address') }}" required>
                        </div>
                    </div>
                </div>

                <div class="filters-card-modern" style="padding:18px 20px; margin-top:14px;">
                    <div class="ext-section-title"><i data-lucide="user-check" style="width:18px;height:18px;"></i> Контрагент (получатель)</div>
                    <div class="ext-form-grid" style="margin-top:10px;">
                        <div class="ext-field">
                            <label>ИНН / ПИНФЛ контрагента *</label>
                            <input type="text" name="buyer_tin" value="{{ old('buyer_tin') }}" required>
                        </div>
                        <div class="ext-field">
                            <label>Наименование контрагента *</label>
                            <input type="text" name="buyer_name" value="{{ old('buyer_name') }}" required>
                        </div>
                        <div class="ext-field full">
                            <label>Адрес контрагента *</label>
                            <input type="text" name="buyer_address" value="{{ old('buyer_address') }}" required>
                        </div>
                    </div>
                </div>

                <div style="margin-top:16px; display:flex; gap:10px;">
                    <button type="submit" class="btn-modern btn-primary" @unless($authorized) disabled @endunless>
                        <i data-lucide="send"></i> Создать в Didox
                    </button>
                    <a href="{{ route('external-docs.outgoing') }}" class="btn-modern">Отмена</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endsection
