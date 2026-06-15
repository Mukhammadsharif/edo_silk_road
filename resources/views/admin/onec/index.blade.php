@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/tariffs/tariffs-modern.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
    <style>
        .onec-token-box{display:flex;gap:.75rem;align-items:center;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;font-family:monospace;font-size:14px}
        .onec-token-box code{flex:1 1 auto;min-width:0;overflow-x:auto;white-space:nowrap;word-break:normal;display:block;padding:2px 0}
        .onec-token-box .btn-modern{flex:0 0 auto;white-space:nowrap}
        .onec-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin:1.25rem 0 1rem}
        .onec-tab{padding:.5rem 1rem;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-weight:600;color:#475569}
        .onec-tab.active{background:#794DFD;color:#fff;border-color:#794DFD}
        .onec-pane{display:none}
        .onec-pane.active{display:block}
        .onec-expire{color:#dc2626;font-size:13px;margin-top:.5rem}
        .onec-copy-ok{color:#16a34a;font-size:13px;margin-left:.5rem;display:none}
        .onec-tab-problem{border-color:#fca5a5}
        .onec-tab-problem.active{background:#dc2626;border-color:#dc2626}
        .onec-badge{padding:2px 8px;border-radius:6px;font-size:12px;font-weight:600}
        .onec-badge-pending{background:#fef3c7;color:#92400e}
        .onec-badge-error{background:#fee2e2;color:#991b1b}
        .onec-retry-banner{background:#dcfce7;border:1px solid #86efac;color:#166534;border-radius:8px;padding:10px 14px;margin-bottom:1rem;font-size:14px}
        .onec-retry-banner span{margin-right:.5rem}
    </style>

    <div class="document-page-modern">

        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="plug-zap"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('Интеграция с 1С') }}</h1>
                        <p>{{ __('Сгенерируйте токен и передайте его в 1С для отправки данных в систему') }}</p>
                    </div>
                </div>

                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('admin.onec.index') }}" class="breadcrumb-item">
                        <span>{{ __('Интеграция с 1С') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">Yaratish</span>
                </nav>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Выбор организации (только суперадмин) --}}
        @if($isSuper)
            <form method="GET" class="filters-card-modern" style="margin-bottom:1rem;">
                <div class="filters-form-modern" style="display:flex;gap:.75rem;align-items:end;">
                    <div style="flex:1;">
                        <label>{{ __('Организация') }}</label>
                        <select name="org_unit_id" class="form-control-modern select2" onchange="this.form.submit()">
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" @selected($selectedOrgId == $org->id)>
                                    {{ $org->name_ru ?? $org->name_uz ?? ('#'.$org->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        @endif

        {{-- Блок токена --}}
        <div class="table-card-modern" style="padding:1.5rem;margin-bottom:1.25rem;">
            <h3 style="margin-top:0;">{{ __('Токен интеграции') }}</h3>

            @if(session('onec_token'))
                {{-- Только что сгенерированный --}}
                <p>{{ __('Токен сгенерирован. Скопируйте его — он показывается один раз.') }}</p>
                <div class="onec-token-box">
                    <code id="onecToken">{{ session('onec_token') }}</code>
                    <button type="button" class="btn-modern" onclick="copyOnecToken()">
                        <i data-lucide="copy"></i> {{ __('Копировать') }}
                    </button>
                    <span class="onec-copy-ok" id="onecCopyOk">✓ {{ __('Скопировано') }}</span>
                </div>
                <div class="onec-expire">
                    {{ __('Действует до') }}: {{ \Carbon\Carbon::parse(session('onec_expires'))->format('d.m.Y H:i') }}
                    ({{ __('1 час') }})
                </div>
                @if(session('onec_emailed'))
                    <div style="color:#16a34a;font-size:13px;margin-top:.35rem;">
                        ✓ {{ __('Токен также отправлен на вашу почту') }}
                    </div>
                @endif
            @elseif($activeToken)
                {{-- Есть активный, но значение не показываем (хранится как есть; для удобства покажем) --}}
                <p>{{ __('Есть активный токен для этой организации.') }}</p>
                <div class="onec-token-box">
                    <code id="onecToken">{{ $activeToken->token }}</code>
                    <button type="button" class="btn-modern" onclick="copyOnecToken()">
                        <i data-lucide="copy"></i> {{ __('Копировать') }}
                    </button>
                    <span class="onec-copy-ok" id="onecCopyOk">✓ {{ __('Скопировано') }}</span>
                </div>
                <div class="onec-expire">
                    {{ __('Действует до') }}: {{ $activeToken->expires_at->format('d.m.Y H:i') }}
                </div>
            @else
                <p style="color:#6b7280;">{{ __('Активного токена нет. Сгенерируйте новый.') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.onec.generate') }}" style="margin-top:1rem;">
                @csrf
                @if($isSuper)
                    <input type="hidden" name="org_unit_id" value="{{ $selectedOrgId }}">
                @endif
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i data-lucide="refresh-cw"></i> {{ __('Сгенерировать новый токен') }}
                </button>
            </form>

            <p style="color:#6b7280;font-size:13px;margin-top:.75rem;">
                {{ __('В 1С передавайте токен в заголовке') }} <code>X-1C-Token</code>.
                {{ __('Срок жизни — 1 час.') }}
            </p>
        </div>

        {{-- Табы с пришедшими данными --}}
        <div class="table-card-modern" style="padding:1.5rem;">
            <h3 style="margin-top:0;">{{ __('Полученные данные из 1С') }}</h3>

            <div class="onec-tabs">
                <div class="onec-tab active" data-pane="companies">{{ __('Компании') }} ({{ $incoming['companies']->count() }})</div>
                <div class="onec-tab" data-pane="divisions">{{ __('Отделы') }} ({{ $incoming['divisions']->count() }})</div>
                <div class="onec-tab" data-pane="positions">{{ __('Должности') }} ({{ $incoming['positions']->count() }})</div>
                <div class="onec-tab" data-pane="persons">{{ __('Физлица') }} ({{ $incoming['persons']->count() }})</div>
                <div class="onec-tab" data-pane="employees">{{ __('Сотрудники') }} ({{ $incoming['employees']->count() }})</div>
                <div class="onec-tab onec-tab-problem" data-pane="problems">
                    {{ __('Проблемные записи') }} ({{ $incoming['problems']->count() }})
                </div>
            </div>

            {{-- companies --}}
            <div class="onec-pane active" id="pane-companies">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead><tr><th>ID (1С)</th><th>{{ __('Название') }}</th><th>{{ __('ИНН') }}</th><th>{{ __('Статус') }}</th><th>{{ __('Обновлено') }}</th></tr></thead>
                        <tbody>
                        @forelse($incoming['companies'] as $row)
                            <tr><td>{{ Str::limit($row->onec_id, 12) }}</td><td>{{ $row->name }}</td><td>{{ $row->tax_id }}</td><td>{{ $row->sync_status }}</td><td>{{ $row->updated_at?->format('d.m.Y H:i') }}</td></tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;color:#6b7280;">{{ __('Нет данных') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- divisions --}}
            <div class="onec-pane" id="pane-divisions">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead><tr><th>ID (1С)</th><th>{{ __('Код') }}</th><th>{{ __('Название') }}</th><th>{{ __('Статус') }}</th><th>{{ __('Обновлено') }}</th></tr></thead>
                        <tbody>
                        @forelse($incoming['divisions'] as $row)
                            <tr><td>{{ Str::limit($row->onec_id, 12) }}</td><td>{{ $row->code }}</td><td>{{ $row->name }}</td><td>{{ $row->sync_status }}</td><td>{{ $row->updated_at?->format('d.m.Y H:i') }}</td></tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;color:#6b7280;">{{ __('Нет данных') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- positions --}}
            <div class="onec-pane" id="pane-positions">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead><tr><th>ID (1С)</th><th>{{ __('Название') }}</th><th>{{ __('Статус') }}</th><th>{{ __('Обновлено') }}</th></tr></thead>
                        <tbody>
                        @forelse($incoming['positions'] as $row)
                            <tr><td>{{ Str::limit($row->onec_id, 12) }}</td><td>{{ $row->name }}</td><td>{{ $row->sync_status }}</td><td>{{ $row->updated_at?->format('d.m.Y H:i') }}</td></tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:#6b7280;">{{ __('Нет данных') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- persons --}}
            <div class="onec-pane" id="pane-persons">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead><tr><th>ID (1С)</th><th>{{ __('ФИО') }}</th><th>{{ __('ИНН') }}</th><th>{{ __('Телефон') }}</th><th>{{ __('Статус') }}</th></tr></thead>
                        <tbody>
                        @forelse($incoming['persons'] as $row)
                            <tr><td>{{ Str::limit($row->onec_id, 12) }}</td><td>{{ $row->last_name }} {{ $row->first_name }} {{ $row->patronymic }}</td><td>{{ $row->tax_id }}</td><td>{{ $row->mobile_phone_number }}</td><td>{{ $row->sync_status }}</td></tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;color:#6b7280;">{{ __('Нет данных') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- employees --}}
            <div class="onec-pane" id="pane-employees">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead><tr><th>ID (1С)</th><th>personID</th><th>{{ __('Дата приёма') }}</th><th>{{ __('Уволен') }}</th><th>{{ __('Статус') }}</th></tr></thead>
                        <tbody>
                        @forelse($incoming['employees'] as $row)
                            <tr><td>{{ Str::limit($row->onec_id, 12) }}</td><td>{{ Str::limit($row->person_id, 12) }}</td><td>{{ $row->employment_date?->format('d.m.Y') }}</td><td>{{ $row->dismissed ? __('Да') : __('Нет') }}</td><td>{{ $row->sync_status }}</td></tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;color:#6b7280;">{{ __('Нет данных') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- problems: pending/error --}}
            <div class="onec-pane" id="pane-problems">
                @if(session('onec_retry_result'))
                    <div class="onec-retry-banner">
                        ✓ {{ __('Повтор выполнен') }}:
                        @foreach(session('onec_retry_result') as $ent => $cnt)
                            <span>{{ $ent }}: {{ $cnt['synced'] ?? 0 }} ✓ / {{ $cnt['pending'] ?? 0 }} ⏳ / {{ $cnt['error'] ?? 0 }} ✗</span>@if(!$loop->last), @endif
                        @endforeach
                    </div>
                @endif

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;gap:1rem;flex-wrap:wrap;">
                    <p style="color:#6b7280;margin:0;">
                        {{ __('Записи, которые не попали в рабочие таблицы — ждут зависимости или содержат ошибку. Нажмите «Повторить», когда недостающие данные придут из 1С.') }}
                    </p>
                    <form method="POST" action="{{ route('admin.onec.retry') }}" style="margin:0;flex:0 0 auto;">
                        @csrf
                        @if($isSuper)
                            <input type="hidden" name="org_unit_id" value="{{ $selectedOrgId }}">
                        @endif
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i data-lucide="refresh-cw"></i> {{ __('Повторить синхронизацию') }}
                        </button>
                    </form>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead><tr><th>{{ __('Тип') }}</th><th>ID (1С)</th><th>{{ __('Название') }}</th><th>{{ __('Статус') }}</th><th>{{ __('Причина') }}</th><th>{{ __('Попыток') }}</th></tr></thead>
                        <tbody>
                        @forelse($incoming['problems'] as $row)
                            <tr>
                                <td>{{ $row->label }}</td>
                                <td>{{ Str::limit($row->onec_id, 12) }}</td>
                                <td>{{ Str::limit($row->name, 30) }}</td>
                                <td>
                                    <span class="onec-badge onec-badge-{{ $row->status }}">{{ $row->status }}</span>
                                </td>
                                <td style="color:#dc2626;font-size:13px;">{{ $row->error }}</td>
                                <td style="text-align:center;">{{ $row->attempts }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;color:#16a34a;">✓ {{ __('Проблемных записей нет') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Табы
        document.querySelectorAll('.onec-tab').forEach(function(tab){
            tab.addEventListener('click', function(){
                document.querySelectorAll('.onec-tab').forEach(t=>t.classList.remove('active'));
                document.querySelectorAll('.onec-pane').forEach(p=>p.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById('pane-' + tab.dataset.pane).classList.add('active');
            });
        });

        // Копирование токена (с fallback для HTTP, где clipboard API недоступен)
        function copyOnecToken(){
            var el = document.getElementById('onecToken');
            if(!el) return;
            var text = el.innerText.trim();

            function showOk(){
                var ok = document.getElementById('onecCopyOk');
                if(ok){ ok.style.display='inline'; setTimeout(function(){ ok.style.display='none'; }, 2000); }
            }
            function fallbackCopy(){
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.focus(); ta.select();
                try { document.execCommand('copy') ? showOk() : alert('Скопируйте вручную'); }
                catch(e){ alert('Скопируйте вручную'); }
                document.body.removeChild(ta);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(showOk).catch(fallbackCopy);
            } else {
                fallbackCopy();
            }
        }
        window.copyOnecToken = copyOnecToken;

        if (window.lucide) lucide.createIcons();
    </script>
@endsection
