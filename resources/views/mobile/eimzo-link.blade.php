<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0a0e1a">
    <title>E-IMZO ID-Card — Telegram</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --surface-0:   var(--tg-theme-bg-color, #f5f7fb);
            --surface-1:   rgba(255, 255, 255, 0.72);
            --surface-2:   var(--tg-theme-secondary-bg-color, #ffffff);
            --text:        var(--tg-theme-text-color, #0a0e1a);
            --text-muted:  var(--tg-theme-hint-color, #64748b);
            --text-subtle: rgba(10, 14, 26, 0.55);
            --brand-1:     #3b82f6;
            --brand-2:     #8b5cf6;
            --brand-3:     #ec4899;
            --success:     #10b981;
            --success-2:   #059669;
            --warning:     #f59e0b;
            --danger:      #ef4444;
            --border:      rgba(15, 23, 42, 0.06);
            --border-glass: rgba(255, 255, 255, 0.45);
            --shadow-1:    0 0 0 1px rgba(15, 23, 42, 0.04), 0 2px 4px rgba(15, 23, 42, 0.04);
            --shadow-2:    0 0 0 1px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.08), 0 24px 40px -16px rgba(15, 23, 42, 0.18);
            --r-md: 18px;
            --r-lg: 24px;
            --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --smooth: cubic-bezier(0.22, 1, 0.36, 1);
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --surface-0:   var(--tg-theme-bg-color, #0f1629);
                --surface-1:   rgba(30, 41, 59, 0.72);
                --surface-2:   var(--tg-theme-secondary-bg-color, #1e293b);
                --text:        var(--tg-theme-text-color, #f1f5f9);
                --text-muted:  var(--tg-theme-hint-color, #94a3b8);
                --text-subtle: rgba(241, 245, 249, 0.55);
                --border:      rgba(255, 255, 255, 0.08);
                --border-glass: rgba(255, 255, 255, 0.10);
                --shadow-1:    0 0 0 1px rgba(255, 255, 255, 0.05), 0 2px 4px rgba(0, 0, 0, 0.3);
                --shadow-2:    0 0 0 1px rgba(255, 255, 255, 0.05), 0 4px 12px rgba(0, 0, 0, 0.4), 0 24px 40px -16px rgba(0, 0, 0, 0.6);
            }
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        html, body { background: var(--surface-0); color: var(--text); min-height: 100vh; min-height: 100dvh; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Segoe UI', Roboto, sans-serif;
            font-size: 14px; line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            position: relative; overflow-x: hidden;
            padding: 14px 14px max(14px, env(safe-area-inset-bottom));
            padding-top: max(14px, env(safe-area-inset-top));
        }

        .bg-blobs { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
        .blob { position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.55; will-change: transform; }
        .blob-1 { width: 360px; height: 360px; top: -120px; right: -80px; background: radial-gradient(circle, var(--brand-1), transparent 70%); animation: float-1 18s ease-in-out infinite; }
        .blob-2 { width: 320px; height: 320px; top: 40%; left: -80px; background: radial-gradient(circle, var(--brand-2), transparent 70%); animation: float-2 22s ease-in-out infinite; }
        @media (prefers-color-scheme: dark) { .blob { opacity: 0.30; } }
        @keyframes float-1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-30px,40px) scale(1.1); } }
        @keyframes float-2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(40px,-30px) scale(0.9); } }

        .header {
            background: linear-gradient(180deg, rgba(255,255,255,0.85), rgba(255,255,255,0.60));
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--border-glass);
            border-radius: var(--r-lg);
            padding: 18px 20px;
            margin-bottom: 14px;
            box-shadow: var(--shadow-1);
        }
        @media (prefers-color-scheme: dark) {
            .header { background: linear-gradient(180deg, rgba(15,22,41,0.85), rgba(15,22,41,0.60)); }
        }
        .header-row { display: flex; align-items: center; gap: 12px; }
        .logo-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: #fff; box-shadow: 0 8px 20px -4px rgba(59,130,246,0.5);
        }
        .logo-icon svg { width: 22px; height: 22px; }
        .header-title { font-size: 16px; font-weight: 700; letter-spacing: -0.01em; color: var(--text); }
        .header-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        .card {
            background: var(--surface-1);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid var(--border-glass);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-2);
            padding: 24px 20px;
        }

        .step {
            display: none;
            opacity: 0; transform: translateY(8px);
        }
        .step.active {
            display: block;
            animation: stepIn 0.45s var(--smooth) forwards;
        }
        @keyframes stepIn { to { opacity: 1; transform: translateY(0); } }

        /* Intro */
        .intro-icon-wrap {
            width: 96px; height: 96px;
            margin: 0 auto 22px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            box-shadow: 0 16px 40px -12px rgba(59,130,246,0.55);
            animation: pop 0.6s var(--spring) forwards;
        }
        .intro-icon-wrap::before {
            content: ""; position: absolute; inset: -10px; border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            filter: blur(20px); opacity: 0.45;
        }
        .intro-icon-wrap svg {
            width: 48px; height: 48px; stroke: #fff; stroke-width: 2.4;
            fill: none; stroke-linecap: round; stroke-linejoin: round; position: relative; z-index: 1;
        }
        @keyframes pop { 0% { transform: scale(0); opacity: 0; } 60% { transform: scale(1.12); opacity: 1; } 100% { transform: scale(1); opacity: 1; } }
        .intro-title {
            font-size: 22px; font-weight: 700;
            text-align: center; letter-spacing: -0.02em;
            margin-bottom: 8px; color: var(--text);
        }
        .intro-sub {
            font-size: 14px; color: var(--text-muted);
            text-align: center; line-height: 1.55;
            max-width: 320px; margin: 0 auto 20px;
        }
        .intro-list {
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: var(--r-md);
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: 13px; line-height: 1.7;
            color: var(--text-muted);
        }
        .intro-list ol { padding-left: 18px; }
        .intro-list li { margin-bottom: 4px; }

        /* Buttons */
        .btn-primary {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            padding: 16px 18px;
            border: none;
            border-radius: var(--r-md);
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: #fff;
            font-size: 15px; font-weight: 700; letter-spacing: -0.01em;
            cursor: pointer; font-family: inherit;
            position: relative; overflow: hidden;
            box-shadow:
                0 1px 0 rgba(255,255,255,0.18) inset,
                0 12px 32px -8px rgba(59,130,246,0.55),
                0 4px 8px -2px rgba(139,92,246,0.35);
            transition: transform 0.15s var(--smooth);
        }
        .btn-primary:active { transform: scale(0.97); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-primary svg { width: 18px; height: 18px; }

        .btn-secondary {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            width: 100%;
            padding: 12px 18px;
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            background: transparent;
            color: var(--text);
            font-size: 13px; font-weight: 600;
            cursor: pointer; font-family: inherit;
            margin-top: 10px;
            transition: background 0.18s ease;
        }
        .btn-secondary:hover { background: rgba(15,23,42,0.04); }
        .btn-secondary svg { width: 14px; height: 14px; }

        /* QR section */
        .qr-wrap {
            background: #fff;
            border-radius: var(--r-md);
            padding: 16px;
            margin: 0 auto 16px;
            width: fit-content;
            box-shadow: 0 8px 24px -6px rgba(15,23,42,0.18);
        }
        #eimzo-qrcode { width: 240px; height: 240px; display: block; }
        #eimzo-qrcode img, #eimzo-qrcode canvas { width: 100% !important; height: 100% !important; }

        .qr-title {
            font-size: 16px; font-weight: 700;
            text-align: center; margin-bottom: 6px;
            color: var(--text); letter-spacing: -0.01em;
        }
        .qr-sub {
            font-size: 13px; color: var(--text-muted);
            text-align: center; margin-bottom: 16px;
        }

        .status-line {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            font-size: 13px; color: var(--text-muted);
            padding: 10px;
            background: rgba(59, 130, 246, 0.06);
            border-radius: 10px;
            margin-bottom: 14px;
        }
        .status-line.error {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.08);
        }
        .pulse {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--brand-1);
            animation: pulse 1.4s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100% { opacity: 0.4; transform: scale(0.85); } 50% { opacity: 1; transform: scale(1.15); } }

        .hash-box {
            font-family: 'SF Mono', Menlo, monospace;
            font-size: 11px;
            color: var(--text-subtle);
            background: rgba(15,23,42,0.04);
            border-radius: 10px;
            padding: 10px 12px;
            white-space: pre-wrap;
            word-break: break-all;
            margin-bottom: 14px;
            line-height: 1.6;
        }
        @media (prefers-color-scheme: dark) {
            .hash-box { background: rgba(255,255,255,0.04); }
        }
        .hash-box .digit { color: var(--brand-2); font-weight: 600; }

        /* Success / Error states */
        .icon-success-wrap, .icon-error-wrap {
            width: 96px; height: 96px;
            margin: 0 auto 22px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative;
            animation: pop 0.6s var(--spring);
        }
        .icon-success-wrap {
            background: linear-gradient(135deg, var(--success), var(--success-2));
            box-shadow: 0 16px 40px -12px rgba(16,185,129,0.55);
        }
        .icon-success-wrap::before {
            content: ""; position: absolute; inset: -10px; border-radius: 50%;
            background: linear-gradient(135deg, var(--success), var(--success-2));
            filter: blur(20px); opacity: 0.45;
        }
        .icon-error-wrap {
            background: linear-gradient(135deg, var(--warning), var(--danger));
            box-shadow: 0 16px 40px -12px rgba(239,68,68,0.45);
        }
        .icon-error-wrap::before {
            content: ""; position: absolute; inset: -10px; border-radius: 50%;
            background: linear-gradient(135deg, var(--warning), var(--danger));
            filter: blur(20px); opacity: 0.4;
        }
        .icon-success-wrap svg, .icon-error-wrap svg {
            width: 48px; height: 48px; stroke: #fff; stroke-width: 2.4;
            fill: none; stroke-linecap: round; stroke-linejoin: round;
            position: relative; z-index: 1;
        }
        .result-title {
            font-size: 22px; font-weight: 700;
            text-align: center; letter-spacing: -0.02em;
            margin-bottom: 8px; color: var(--text);
        }
        .result-sub {
            font-size: 14px; color: var(--text-muted);
            text-align: center; line-height: 1.55;
            max-width: 320px; margin: 0 auto 24px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
            .blob { animation: none !important; }
        }
    </style>
</head>
<body>

<div class="bg-blobs" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="header">
    <div class="header-row">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="14" rx="2"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div>
            <div class="header-title">E-IMZO ID-Card</div>
            <div class="header-sub">Telegram аккаунт привязка</div>
        </div>
    </div>
</div>

<div class="card">
    @if($expired)
        {{-- Token истёк или невалиден --}}
        <div class="step active" id="step-expired">
            <div class="icon-error-wrap">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="6" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="result-title">Ссылка устарела</div>
            <div class="result-sub">
                Время действия ссылки истекло (5 минут). Вернитесь в Telegram и нажмите /start заново.
            </div>
            <button class="btn-primary" onclick="closeApp()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                Закрыть
            </button>
        </div>
    @else
        {{-- Шаг 1: интро --}}
        <div class="step active" id="step-intro">
            <div class="intro-icon-wrap">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <circle cx="8" cy="15" r="1.2" fill="#fff"/>
                </svg>
            </div>
            <div class="intro-title">Привязка через E-IMZO</div>
            <div class="intro-sub">
                Авторизуйтесь с помощью ID-Card, чтобы получать уведомления о документах в Telegram.
            </div>
            <div class="intro-list">
                <ol>
                    <li>Нажмите «Начать»</li>
                    <li>Откройте E-IMZO ID-Card на телефоне</li>
                    <li>Отсканируйте QR-код</li>
                    <li>Подтвердите подпись</li>
                </ol>
            </div>
            <button class="btn-primary" id="btn-start" onclick="startFlow()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                <span>Начать авторизацию</span>
            </button>
        </div>

        {{-- Шаг 2: QR --}}
        <div class="step" id="step-qr">
            <div class="qr-title">Отсканируйте QR-код</div>
            <div class="qr-sub">Используйте мобильное приложение E-IMZO ID-Card</div>
            <div class="qr-wrap">
                <div id="eimzo-qrcode"></div>
            </div>
            <div class="status-line" id="qr-status">
                <div class="pulse"></div>
                <span id="qr-status-text">Ожидание подписи...</span>
            </div>
            <div class="hash-box" id="hash-display"></div>
            <button class="btn-primary" id="btn-deeplink">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                </svg>
                Открыть E-IMZO
            </button>
            <button class="btn-secondary" onclick="cancelFlow()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                Отменить
            </button>
        </div>

        {{-- Шаг 3: успех --}}
        <div class="step" id="step-success">
            <div class="icon-success-wrap">
                <svg viewBox="0 0 24 24">
                    <path d="M5 12.5l4.5 4.5L19 7.5"/>
                </svg>
            </div>
            <div class="result-title">Аккаунт привязан!</div>
            <div class="result-sub" id="success-msg">Вернитесь в Telegram, чтобы продолжить работу.</div>
            <button class="btn-primary" onclick="closeApp()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l5 5L21 4"/></svg>
                Готово
            </button>
        </div>

        {{-- Шаг 4: ошибка --}}
        <div class="step" id="step-error">
            <div class="icon-error-wrap">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="6" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="result-title">Ошибка</div>
            <div class="result-sub" id="error-msg">Не удалось привязать аккаунт.</div>
            <button class="btn-primary" onclick="retryFlow()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><polyline points="3 4 3 10 9 10"/></svg>
                Попробовать снова
            </button>
            <button class="btn-secondary" onclick="closeApp()">Закрыть</button>
        </div>
    @endif
</div>

@if(!$expired)
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script src="{{ asset('js/pkcs.js') }}"></script>
    <script src="{{ asset('js/crc32.js') }}"></script>

    <script>
        var tg = window.Telegram?.WebApp;
        if (tg) {
            tg.ready();
            tg.expand();
            try { tg.setHeaderColor && tg.setHeaderColor('secondary_bg_color'); } catch (e) {}
        }

        function haptic(type) {
            if (!tg || !tg.HapticFeedback) return;
            try {
                if (type === 'success' || type === 'error' || type === 'warning') {
                    tg.HapticFeedback.notificationOccurred(type);
                } else {
                    tg.HapticFeedback.impactOccurred(type || 'light');
                }
            } catch (e) {}
        }

        var BASE_URL   = '{{ url('/') }}';
        var LINK_TOKEN = @json($link_token);
        var currentDocumentId = null;
        var currentDeeplink   = null;
        var statusInterval    = null;
        var checkCount        = 0;
        var MAX_CHECKS        = 120;

        function showStep(id) {
            document.querySelectorAll('.step').forEach(function(el) {
                el.classList.remove('active');
            });
            var el = document.getElementById('step-' + id);
            if (el) {
                el.offsetHeight;
                el.classList.add('active');
            }
        }

        function setStatus(text, isError) {
            var line = document.getElementById('qr-status');
            var txt  = document.getElementById('qr-status-text');
            if (txt) txt.textContent = text;
            if (line) line.classList.toggle('error', !!isError);
        }

        function displayHash(hash) {
            var el = document.getElementById('hash-display');
            if (!el || !hash) return;
            var chunks = hash.match(/.{1,16}/g) || [];
            var html = '';
            chunks.forEach(function(chunk) {
                chunk.split('').forEach(function(c) {
                    html += /\d/.test(c) ? '<span class="digit">' + c + '</span>' : c;
                });
                html += '\n';
            });
            el.innerHTML = html;
        }

        function generateQRCode(documentId, siteId, data) {
            var el = document.getElementById('eimzo-qrcode');
            el.innerHTML = '';
            var hasher   = new GostHash();
            var textHash = hasher.gosthash(data.toString());
            var code     = siteId + documentId + textHash;
            var crc32    = new CRC32().calcHex(code);
            var final    = code + crc32;

            new QRCode(el, {
                text: final, width: 240, height: 240,
                colorDark: '#000000', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
            displayHash(textHash);
            return final;
        }

        function startFlow() {
            haptic('medium');
            var btn = document.getElementById('btn-start');
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Yuklanmoqda...';

            fetch(BASE_URL + '/api/telegram/eimzo-link/auth-init', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({link_token: LINK_TOKEN})
            })
                .then(function(r) { return r.json().then(function(d){ return {ok: r.ok, data: d}; }); })
                .then(function(res) {
                    if (!res.ok || !res.data.success) {
                        throw new Error(res.data.message || 'Ошибка инициализации');
                    }
                    currentDocumentId = res.data.documentId;
                    // QR строится из challenge (как в обычном E-IMZO mobile-auth flow)
                    var qr = generateQRCode(res.data.documentId, res.data.siteId, res.data.challenge);
                    currentDeeplink = 'eimzo://sign?qc=' + qr;

                    var deeplinkBtn = document.getElementById('btn-deeplink');
                    deeplinkBtn.onclick = function() {
                        haptic('light');
                        openDeeplink();
                    };

                    showStep('qr');
                    startPolling();
                })
                .catch(function(e) {
                    haptic('error');
                    document.getElementById('error-msg').textContent = e.message || 'Ошибка соединения';
                    showStep('error');
                });
        }

        function openDeeplink() {
            if (!currentDeeplink) return;
            var redirectUrl = BASE_URL + '/mobile/redirect?url=' + encodeURIComponent(currentDeeplink);
            if (tg && tg.openLink) {
                tg.openLink(redirectUrl);
            } else {
                window.location.href = currentDeeplink;
            }
        }

        function startPolling() {
            checkCount = 0;
            statusInterval = setInterval(function() {
                checkCount++;
                if (checkCount > MAX_CHECKS) {
                    stopPolling();
                    setStatus('Время ожидания истекло', true);
                    haptic('error');
                    document.getElementById('error-msg').textContent = 'Время ожидания истекло. Попробуйте снова.';
                    showStep('error');
                    return;
                }
                setStatus('Ожидание подписи... (' + checkCount + 's)');

                // Шаг 1: проверка статуса (frontend) — корректно отдаёт pending/ready
                fetch(BASE_URL + '/api/telegram/eimzo-link/status', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    body: JSON.stringify({
                        link_token: LINK_TOKEN,
                        documentId: currentDocumentId
                    })
                })
                    .then(function(r) { return r.json().then(function(d){ return {ok: r.ok, data: d}; }); })
                    .then(function(res) {
                        var data = res.data || {};

                        if (data.state === 'pending') {
                            return;
                        }

                        if (data.state === 'ready') {
                            // Шаг 2: финализация (backend) — извлечение сертификата + привязка
                            stopPolling();
                            setStatus('Подпись получена, проверка данных...');
                            finalizeAuth();
                            return;
                        }

                        // state === 'error'
                        stopPolling();
                        haptic('error');
                        document.getElementById('error-msg').textContent = data.message || 'Ошибка идентификации';
                        showStep('error');
                    })
                    .catch(function() {
                        // Транзиентная ошибка — продолжаем polling
                    });
            }, 1500);
        }

        function finalizeAuth() {
            fetch(BASE_URL + '/api/telegram/eimzo-link/check-auth', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({
                    link_token: LINK_TOKEN,
                    documentId: currentDocumentId
                })
            })
                .then(function(r) { return r.json().then(function(d){ return {ok: r.ok, data: d}; }); })
                .then(function(res) {
                    var data = res.data || {};

                    if (data.state === 'linked' && data.success) {
                        haptic('success');
                        var name = data.user && data.user.username ? data.user.username : '';
                        document.getElementById('success-msg').textContent =
                            name ? ('Здравствуйте, ' + name + '! Вернитесь в Telegram.') : 'Вернитесь в Telegram, чтобы продолжить работу.';
                        showStep('success');
                        return;
                    }

                    haptic('error');
                    document.getElementById('error-msg').textContent = data.message || 'Ошибка финализации';
                    showStep('error');
                })
                .catch(function(e) {
                    haptic('error');
                    document.getElementById('error-msg').textContent = e.message || 'Ошибка соединения';
                    showStep('error');
                });
        }

        function stopPolling() {
            if (statusInterval) clearInterval(statusInterval);
            statusInterval = null;
        }

        function cancelFlow() {
            stopPolling();
            showStep('intro');
            var btn = document.getElementById('btn-start');
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Начать авторизацию';
        }

        function retryFlow() {
            stopPolling();
            showStep('intro');
            var btn = document.getElementById('btn-start');
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Начать авторизацию';
        }

        function closeApp() {
            if (tg && tg.close) {
                tg.close();
            } else {
                window.history.back();
            }
        }
    </script>
@else
    <script>
        var tg = window.Telegram?.WebApp;
        if (tg) { tg.ready(); tg.expand(); }
        function closeApp() {
            if (tg && tg.close) tg.close();
            else window.history.back();
        }
    </script>
@endif

</body>
</html>
