@extends('layouts.app')

{{-- Вкладка «Внешний документооборот» (Didox, входящие) — дизайн-система modern --}}

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/working-documents-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <style>
        /* Статусы Didox: пилюли в стиле doc-status-badge-modern */
        .didox-status { display:inline-flex; align-items:center; gap:6px; padding:4px 12px;
            border-radius:20px; font-size:12px; font-weight:600; white-space:nowrap; }
        .didox-status i, .didox-status svg { width:14px; height:14px; }
        .didox-status.st-signed    { background:#dcfce7; color:#15803d; }
        .didox-status.st-review    { background:#fef9c3; color:#a16207; }
        .didox-status.st-notsigned { background:#fee2e2; color:#b91c1c; }
        .didox-status.st-approved  { background:#dcfce7; color:#15803d; }
        .didox-status.st-pending   { background:#f1f5f9; color:#64748b; }
        .modern-table tbody tr { cursor:pointer; }
    </style>

    <div class="document-page-modern">

        <!-- Заголовок страницы -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="globe"></i>
                    </div>
                    <div class="header-text">
                        <h1>Внешний документооборот</h1>
                        <p>{{ $org->name_ru ?? $org->name_uz }} · ИНН {{ $org->tax_number ?: 'не заполнен' }}</p>
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
                    <span class="breadcrumb-item active">Внешний документооборот</span>
                </nav>
            </div>
        </div>

        <div id="alertContainer" class="alert-container-modern"></div>

        <div class="content-wrapper-modern">

            <!-- Фильтры -->
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="filter"></i>
                        <span>{{ __('field.filters') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $documents->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('external-docs.index') }}" method="get" class="filters-form-modern">
                    <div class="advanced-filters-modern">
                        <div class="filter-item">
                            <label>
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </label>
                            <select name="list_status" class="filter-select-modern select2">
                                <option value="">{{ __('select.all_statuses') }}</option>
                                <option value="signed" {{ $listStatus === 'signed' ? 'selected' : '' }}>Подписан</option>
                                <option value="on_internal_review" {{ $listStatus === 'on_internal_review' ? 'selected' : '' }}>На внутреннем согласовании</option>
                                <option value="not_signed" {{ $listStatus === 'not_signed' ? 'selected' : '' }}>Не подписан</option>
                            </select>
                        </div>

                        <div class="filter-item actions" style="grid-column: span 2;">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i> {{ __('action.search') }}
                            </button>
                            <a href="{{ route('external-docs.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i> {{ __('action.clear') }}
                            </a>
                            @if($tinFilled && !$authorized && $canSign)
                                {{-- Вход в Didox — только ответственным (Super_Admin / Director / IT_Admin) --}}
                                <button type="button" class="btn-modern btn-primary" id="btn-open-login"
                                        onclick="didoxOpenLogin()">
                                    <i data-lucide="log-in"></i> Войти в Didox
                                </button>
                            @endif
                            <button type="button" class="btn-modern btn-primary" id="btn-sync"
                                    @if($authorized)
                                        onclick="didoxSyncNow(this)" title="Подтянуть входящие из Didox"
                                    @elseif($canSign && $tinFilled)
                                        onclick="didoxOpenLogin()" title="Сессия Didox истекла — нажмите, чтобы войти"
                                    @else
                                        disabled title="Нет активной авторизации в Didox"
                                @endif>
                                <i data-lucide="cloud-download"></i> Обновить из Didox
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Таблица -->
            <div class="table-card-modern">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th>№</th>
                            <th>{{ __('field.status') }}</th>
                            <th>{{ __('field.document_type') }}</th>
                            <th>{{ __('field.document_name') }}</th>
                            <th>{{ __('field.document_date') }}</th>
                            <th>Контрагент (ИНН)</th>
                            <th>Согласование</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($documents as $key => $d)
                            <tr onclick="window.location='{{ route('external-docs.show', $d->id) }}'">
                                <td class="number-cell">{{ $documents->firstItem() + $key }}</td>
                                <td>
                                    @if($d->list_status === 'signed')
                                        <span class="didox-status st-signed"><i data-lucide="check-circle"></i> Подписан</span>
                                    @elseif($d->list_status === 'on_internal_review')
                                        <span class="didox-status st-review"><i data-lucide="clock"></i> На согласовании</span>
                                    @else
                                        <span class="didox-status st-notsigned"><i data-lucide="circle-alert"></i> Не подписан</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="doc-type-badge-modern">{{ $typeLabels[$d->doc_type] ?? $d->doc_type }}</span>
                                </td>
                                <td>
                                    <div class="doc-name-cell-modern">
                                        <i data-lucide="file-text"></i>
                                        <span>№ {{ $d->doc_number ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span class="date">{{ optional($d->doc_date)->format('d.m.Y') ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>{{ $d->partner_tin ?? $d->seller_tin ?? '—' }}</td>
                                <td>
                                    @if($d->approval_status === 'approved')
                                        <span class="didox-status st-approved"><i data-lucide="check-check"></i> Согласован</span>
                                    @elseif($d->approval_status === 'on_review')
                                        <span class="didox-status st-review">
                                                <i data-lucide="users"></i> На согласовании
                                                @if($d->pending_approvers_count) ({{ $d->pending_approvers_count }}) @endif
                                            </span>
                                    @else
                                        <span class="didox-status st-pending"><i data-lucide="minus-circle"></i> Не отправлен</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="folder-x"></i>
                                        <p>Входящих документов нет. Нажмите «Обновить из Didox».</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($documents->hasPages())
                    <div class="pagination-wrapper-modern">
                        {!! $documents->links() !!}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Модалка входа в Didox --}}
    <div class="modal fade" id="didoxLoginModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:16px; border:none;">
                <div class="modal-header" style="border-bottom:1px solid var(--border, #e2e8f0);">
                    <h5 class="modal-title" style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="log-in"></i> Вход в Didox
                    </h5>
                    <button type="button" class="close" onclick="didoxCloseLogin()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="font-size:13px; color:#64748b; margin-bottom:14px;">
                        Организация: <b>{{ $org->name_ru ?? $org->name_uz }}</b> · ИНН {{ $org->tax_number }}.
                        Токен действует 360 минут.
                    </p>
                    <div class="filter-item">
                        <label><i data-lucide="lock"></i> Пароль регистрации Didox</label>
                        <input type="password" id="didox-password" class="filter-input-modern"
                               placeholder="Пароль организации в Didox" autocomplete="off"
                               onkeydown="if(event.key==='Enter'){didoxSubmitLogin();}">
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; margin:16px 0 12px; color:#94a3b8; font-size:12px;">
                        <span style="flex:1; height:1px; background:var(--border, #e2e8f0);"></span>
                        или по ЭЦП (E-IMZO)
                        <span style="flex:1; height:1px; background:var(--border, #e2e8f0);"></span>
                    </div>

                    {{-- Форму testform наполняет штатный партиал e-imzo; ключи фильтруются
                         по ИНН: остаются только сертификаты с ИНН этой организации --}}
                    <form name="testform" onsubmit="return false;">
                        <div class="filter-item">
                            <label><i data-lucide="key-round"></i> Ключ ЭЦП организации</label>
                            <label id="message" style="display:block; font-size:12px; color:#64748b;"></label>
                            <select name="key" id="didox-ecp-key" class="filter-select-modern"
                                    style="width:100% !important;" onchange="if(typeof cbChanged==='function')cbChanged(this)"></select>
                        </div>
                        <label id="keyId" hidden></label>
                        <textarea name="pkcs7" hidden></textarea>
                    </form>
                    <button type="button" class="btn-modern btn-secondary" id="btn-login-ecp"
                            style="width:100%; margin-top:4px;" onclick="didoxSubmitEcpLogin()">
                        <i data-lucide="pen-tool"></i> Войти по ЭЦП
                    </button>

                    <div id="login-progress" style="font-size:13px; color:#64748b; margin-top:10px; min-height:16px;"></div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border, #e2e8f0);">
                    <button type="button" class="btn-modern btn-secondary" onclick="didoxCloseLogin()">Отмена</button>
                    <button type="button" class="btn-modern btn-primary" id="btn-login-password"
                            onclick="didoxSubmitLogin()">
                        <i data-lucide="log-in"></i> Войти
                    </button>
                </div>
            </div>
        </div>
    </div>
    @if($tinFilled && !$authorized && $canSign)
        {{-- Скрипты E-IMZO проекта: наполняют select[name=key] в testform ключами --}}
        @include('e-imzo.e-imzo')
    @endif

    {{-- Автономный JS входа: без jQuery/Bootstrap, работает при любом порядке загрузки --}}
    <script>
        function didoxOpenLogin() {
            var m = document.getElementById('didoxLoginModal');
            if (!m) return;
            m.classList.add('show');
            m.style.display = 'block';
            m.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');

            var bd = document.createElement('div');
            bd.className = 'modal-backdrop fade show';
            bd.id = 'didox-login-backdrop';
            bd.onclick = didoxCloseLogin;
            document.body.appendChild(bd);

            setTimeout(function () {
                var inp = document.getElementById('didox-password');
                if (inp) inp.focus();
            }, 100);

            didoxFilterEcpKeys(0);
        }

        function didoxCloseLogin() {
            var m = document.getElementById('didoxLoginModal');
            if (m) { m.classList.remove('show'); m.style.display = 'none'; }
            document.body.classList.remove('modal-open');
            var bd = document.getElementById('didox-login-backdrop');
            if (bd) bd.remove();
        }

        var DIDOX_ORG_TIN = '{{ trim($org->tax_number ?? '') }}';

        /* Признак протухшего/отсутствующего токена Didox в сообщении сервера. */
        function didoxIsAuthError(msg) {
            return /re-authentication|token expired|token.*missing|авторизац/i.test(String(msg || ''));
        }

        /* Уведомление без зависимостей: $.toast если доступен, иначе свой баннер. */
        function didoxNotify(text, type) {
            try {
                if (window.$ && $.toast) {
                    $.toast({ text: text, icon: type || 'info', position: 'top-right', hideAfter: 6000 });
                    return;
                }
            } catch (e) {}
            var colors = { warning: '#f59e0b', error: '#ef4444', success: '#22c55e', info: '#8b5cf6' };
            var b = document.createElement('div');
            b.style.cssText = 'position:fixed; top:18px; right:18px; z-index:99999; max-width:360px;'
                + 'background:#fff; border-left:4px solid ' + (colors[type] || colors.info) + ';'
                + 'box-shadow:0 8px 24px rgba(0,0,0,.14); border-radius:10px; padding:12px 16px;'
                + 'font-size:14px; color:#1e293b;';
            b.textContent = text;
            b.onclick = function () { b.remove(); };
            document.body.appendChild(b);
            setTimeout(function () { b.remove(); }, 6000);
        }

        /* Синхронизация входящих (кнопка «Обновить из Didox»), без jQuery. */
        function didoxSyncNow(btn) {
            if (btn) { btn.disabled = true; }
            didoxNotify('Синхронизация с Didox...', 'info');

            fetch('{{ route('external-docs.sync') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({}),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        location.reload();
                    } else if (didoxIsAuthError(data.message)) {
                        didoxNotify('Сессия Didox истекла — войдите заново.', 'warning');
                        didoxOpenLogin();
                        if (btn) btn.disabled = false;
                    } else {
                        didoxNotify('Ошибка синхронизации: ' + (data.message || 'неизвестно'), 'error');
                        if (btn) btn.disabled = false;
                    }
                })
                .catch(function (e) {
                    didoxNotify('Ошибка сети: ' + e.message, 'error');
                    if (btn) btn.disabled = false;
                });
        }

        /* Уведомление при загрузке страницы — автономно, без jQuery-ready. */
        document.addEventListener('DOMContentLoaded', function () {
            @if(!$tinFilled)
            didoxNotify('У организации не заполнен ИНН (tax_number) — интеграция с Didox недоступна.', 'error');
            @elseif(!$authorized && $canSign)
            didoxNotify('Сессия Didox истекла или отсутствует. Нажмите «Войти в Didox» или «Обновить из Didox».', 'warning');
            @endif
        });

        /* Фильтрация ключей: оставляем только сертификаты с ИНН организации.
           Партиал e-imzo наполняет select асинхронно — ждём появления опций. */
        function didoxFilterEcpKeys(attempt) {
            attempt = attempt || 0;
            var sel = document.getElementById('didox-ecp-key');
            if (!sel) return;

            if (!sel.options.length) {
                if (attempt < 60) setTimeout(function () { didoxFilterEcpKeys(attempt + 1); }, 300);
                else sel.innerHTML = '<option value="">E-IMZO недоступен или ключи не найдены</option>';
                return;
            }

            var kept = 0;
            Array.from(sel.options).forEach(function (opt) {
                var vo = null;
                try { vo = JSON.parse(opt.getAttribute('vo') || 'null'); } catch (e) {}
                var keyTin = vo ? String(vo.TIN || '').trim() : '';
                if (keyTin !== DIDOX_ORG_TIN) {
                    opt.remove();
                } else {
                    kept++;
                }
            });

            if (!kept) {
                sel.innerHTML = '<option value="">Нет ключей ЭЦП с ИНН ' + DIDOX_ORG_TIN + '</option>';
                sel.dataset.hasKeys = '';
            } else {
                sel.dataset.hasKeys = '1';
                if (typeof cbChanged === 'function') cbChanged(sel);
            }
        }

        function didoxSubmitEcpLogin() {
            var sel  = document.getElementById('didox-ecp-key');
            var btn  = document.getElementById('btn-login-ecp');
            var prog = document.getElementById('login-progress');
            var say  = function (t) { if (prog) prog.textContent = t; };

            if (!sel || !sel.dataset.hasKeys || !sel.value) {
                say('Выберите ключ ЭЦП организации.');
                return;
            }
            var opt = document.getElementById(sel.value);
            if (!opt) { say('Ключ ЭЦП не найден.'); return; }
            var vo;
            try { vo = JSON.parse(opt.getAttribute('vo')); } catch (e) { say('Ошибка чтения ключа.'); return; }

            if (btn) btn.disabled = true;

            var post = function (url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(body),
                }).then(function (r) { return r.json(); });
            };

            say('Шаг 1/3: подписываем ИНН через E-IMZO...');
            EIMZOClient.loadKey(vo, function (keyId) {
                CAPIWS.callFunction(
                    { plugin: 'pkcs7', name: 'create_pkcs7',
                        arguments: [window.btoa(DIDOX_ORG_TIN), keyId, 'no'] },
                    function (ev, data) {
                        if (!data.success) { say('E-IMZO: ' + (data.reason || 'ошибка подписи')); if (btn) btn.disabled = false; return; }

                        say('Шаг 2/3: прикрепляем timestamp...');
                        post('{{ route('external-docs.timestamp') }}',
                            { pkcs7: data.pkcs7_64, signatureHex: data.signature_hex })
                            .then(function (ts) {
                                if (!ts.success) throw new Error(JSON.stringify(ts.error || ts.message));
                                var token = ts.data && ts.data.timeStampTokenB64;
                                if (!token) throw new Error('пустой timeStampTokenB64');

                                say('Шаг 3/3: авторизация в Didox...');
                                return post('{{ route('external-docs.auth.ecp') }}', { signature: token });
                            })
                            .then(function (auth) {
                                if (!auth.success) throw new Error(auth.message || 'неизвестно');
                                say('Успешно! Обновляем страницу...');
                                setTimeout(function () { location.reload(); }, 400);
                            })
                            .catch(function (e) {
                                say('Ошибка: ' + e.message);
                                if (btn) btn.disabled = false;
                            });
                    },
                    function (e) { say('E-IMZO: ' + e); if (btn) btn.disabled = false; }
                );
            }, function (e, r) { say('E-IMZO loadKey: ' + (r || e)); if (btn) btn.disabled = false; });
        }

        function didoxSubmitLogin() {
            var inp = document.getElementById('didox-password');
            var btn = document.getElementById('btn-login-password');
            var prog = document.getElementById('login-progress');
            var password = (inp && inp.value || '').trim();

            if (!password) {
                if (prog) prog.textContent = 'Введите пароль регистрации Didox.';
                return;
            }

            if (btn) btn.disabled = true;
            if (prog) prog.textContent = 'Авторизация в Didox...';

            fetch('{{ route('external-docs.auth.password') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ password: password }),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        if (prog) prog.textContent = 'Успешно! Обновляем страницу...';
                        setTimeout(function () { location.reload(); }, 400);
                    } else {
                        if (prog) prog.textContent = 'Ошибка: ' + (data.message || 'неизвестно');
                        if (btn) btn.disabled = false;
                    }
                })
                .catch(function (e) {
                    if (prog) prog.textContent = 'Ошибка сети: ' + e.message;
                    if (btn) btn.disabled = false;
                });
        }
    </script>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('.select2').select2({
                    placeholder: "{{ __('action.choose') }}",
                    allowClear: true
                });

            });

            function toastOrAlert(text, type) {
                if ($.toast) {
                    $.toast({ text: text, icon: type || 'info', position: 'top-right', hideAfter: 5000 });
                } else {
                    alert(text);
                }
            }
        </script>
    @endpush
@endsection
