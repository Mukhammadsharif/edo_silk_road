<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>E-IMZO — Imzolash</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .top-bar {
            background: #007AFF; color: #fff;
            padding: 14px 16px; font-size: 16px; font-weight: 700;
            display: flex; align-items: center; gap: 10px;
            position: sticky; top: 0; z-index: 100;
        }

        .card {
            background: #fff; border-radius: 14px; padding: 16px;
            margin: 12px 12px 0; box-shadow: 0 1px 6px rgba(0,0,0,0.07);
        }

        .section-title {
            font-size: 11px; font-weight: 700; color: #999;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;
        }

        .doc-name   { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; line-height: 1.4; }
        .doc-number { font-size: 13px; color: #666; margin-bottom: 10px; }

        .role-badge { display: inline-block; padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .role-SIGN  { background: #e3f2fd; color: #1565c0; }
        .role-RESOL { background: #f3e5f5; color: #6a1b9a; }
        .role-AGREE { background: #e8f5e9; color: #2e7d32; }

        .doc-body-wrap {
            background: #fff; border-radius: 14px; margin: 12px 12px 0;
            overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.07);
        }
        .doc-body-header { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-body-content { padding: 16px; font-size: 13px; line-height: 1.7; color: #333; max-height: 300px; overflow-y: auto; }
        .doc-body-content img { max-width: 100%; }

        .file-row { display: flex; gap: 10px; align-items: center; padding: 9px 0; border-bottom: 1px solid #f5f5f5; font-size: 13px; color: #333; }
        .file-row:last-child { border-bottom: none; }

        .qr-section { display: none; text-align: center; padding: 20px 16px; }
        .qr-section.active { display: block; }

        #eimzo-qrcode {
            display: inline-block; padding: 14px;
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.12);
        }

        .hash-display {
            background: #1e1e2e; color: #00c68e;
            font-family: 'Courier New', monospace; font-size: 11px;
            border-radius: 10px; padding: 10px 12px; margin-top: 10px;
            word-break: break-all; text-align: left; line-height: 1.8;
        }
        .hash-display .digit { color: #e0e0e0; }

        /* Deeplink кнопка — прямой <a> тег */
        .btn-deeplink {
            display: block; width: 100%; padding: 15px;
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: #fff; border-radius: 12px; font-size: 15px; font-weight: 700;
            text-align: center; text-decoration: none; margin-top: 12px;
            -webkit-tap-highlight-color: transparent;
        }

        .btn { width: 100%; padding: 15px; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 10px; text-align: center; display: block; }
        .btn-primary { background: linear-gradient(135deg, #007AFF, #0056CC); color: #fff; box-shadow: 0 4px 14px rgba(0,122,255,0.3); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }

        .status-bar { display: none; align-items: center; gap: 10px; background: #fff8e1; border-left: 4px solid #ffc107; border-radius: 8px; padding: 12px 14px; margin-top: 10px; font-size: 13px; color: #856404; }
        .status-bar.active { display: flex; }
        .spinner-sm { width: 16px; height: 16px; flex-shrink: 0; border: 2px solid #ffc107; border-top-color: #856404; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .result-box { display: none; border-radius: 10px; padding: 14px; margin-top: 10px; font-size: 13px; }
        .result-box.active  { display: block; }
        .result-box.success { background: #e8f5e9; border-left: 4px solid #28a745; color: #1b5e20; }
        .result-box.error   { background: #ffebee; border-left: 4px solid #ef5350; color: #b71c1c; }

        .success-screen { display: none; text-align: center; padding: 80px 20px; }
        .success-screen.active { display: block; }
        .success-screen .icon  { font-size: 64px; margin-bottom: 16px; }
        .success-screen .title { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .success-screen .sub   { font-size: 14px; color: #888; }

        /* Debug log */
        #debug-log {
            background: #1e1e2e; color: #00c68e; font-family: monospace;
            font-size: 11px; padding: 10px; margin: 10px; border-radius: 8px;
            max-height: 120px; overflow-y: auto;
            word-break: break-all; white-space: pre-wrap;
        }

        .pb { padding-bottom: 20px; }
    </style>
</head>
<body>

<div id="debug-log"></div>

<div class="success-screen" id="success-screen">
    <div class="icon">✅</div>
    <div class="title">Muvaffaqiyatli imzolandi!</div>
    <div class="sub">Telegramga qaytishingiz mumkin</div>
</div>

<div id="main-content">
    <div class="top-bar">🔐 E-IMZO — Imzolash</div>

    <div class="card">
        <div class="section-title">Hujjat</div>
        <div class="doc-name">{{ $doc->doc_name ?? 'Hujjat' }}</div>
        @if(!empty($doc->doc_number))
            <div class="doc-number">№ {{ $doc->doc_number }}</div>
        @endif
        @php
            $roleMap = [
                'sign_resolution'    => ['label' => '📋 Rezolyutsiya', 'class' => 'role-RESOL'],
                'send_to_agreement'  => ['label' => '✅ Kelishish',    'class' => 'role-AGREE'],
                'send_to_ag_out_doc' => ['label' => '✅ Kelishish',    'class' => 'role-AGREE'],
            ];
            $roleInfo = $roleMap[$doc_type] ?? ['label' => '✍️ Imzolash', 'class' => 'role-SIGN'];
        @endphp
        <span class="role-badge {{ $roleInfo['class'] }}">{{ $roleInfo['label'] }}</span>
    </div>

    @if(!empty($html_doc))
        <div class="doc-body-wrap">
            <div class="doc-body-header">📄 Hujjat matni</div>
            <div class="doc-body-content">{!! $html_doc !!}</div>
        </div>
    @endif

    @if($files->isNotEmpty())
        <div class="card">
            <div class="section-title">Fayllar</div>
            @foreach($files as $file)
                <div class="file-row">
                    <span>📎</span>
                    <span>{{ $file->original_name ?? $file->file_name }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card pb" id="sign-card">
        <div class="section-title">E-IMZO ID-CARD orqali imzolash</div>

        <div class="qr-section" id="qr-section">
            <p style="font-size:13px;color:#555;margin-bottom:14px;">
                E-IMZO ilovasida QR-kodni skaning qiling yoki quyidagi tugmani bosing
            </p>
            <div id="eimzo-qrcode"></div>
            <div class="hash-display" id="hash-display"></div>

            {{-- Прямой <a> тег — браузер сам откроет eimzo:// схему --}}
            <a id="deeplink-btn" href="#" class="btn-deeplink">
                📱 E-IMZO da ochish
            </a>
        </div>

        <div class="status-bar" id="status-bar">
            <div class="spinner-sm"></div>
            <span id="status-text">Imzo kutilmoqda...</span>
        </div>

        <div class="result-box" id="result-box"></div>

        <button class="btn btn-primary" id="btn-start" onclick="startSign()">
            🔐 Imzolashni boshlash
        </button>
    </div>
</div>

<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script src="{{ asset('js/pkcs.js') }}"></script>
<script src="{{ asset('js/crc32.js') }}"></script>

<script>
    // ── log() определяем ПЕРВЫМ ───────────────────────────────────────────────
    function log(msg) {
        var el = document.getElementById('debug-log');
        if (el) {
            el.textContent += new Date().toLocaleTimeString() + ' → ' + msg + '\n';
            el.scrollTop = el.scrollHeight;
        }
    }

    // ── Telegram WebApp init ──────────────────────────────────────────────────
    var tgApp = window.Telegram && window.Telegram.WebApp ? window.Telegram.WebApp : null;
    if (tgApp) {
        tgApp.ready();
        log('TG WebApp OK, version=' + tgApp.version);
    } else {
        log('TG WebApp NOT available');
    }

    // ── Config ────────────────────────────────────────────────────────────────
    var BASE_URL   = '{{ rtrim(config("app.url"), "/") }}';
    var DOC_ID     = {{ $journal->doc_id }};
    var DOC_TYPE   = '{{ $doc_type }}';
    var SIGN_TOKEN = '{{ $token }}';
    var API_TOKEN  = '{{ $api_token }}';

    log('API_TOKEN=' + (API_TOKEN ? 'OK(' + API_TOKEN.length + 'chars)' : 'EMPTY'));

    // ── State ─────────────────────────────────────────────────────────────────
    var currentDocumentId = null;
    var currentDeeplink   = null;
    var statusInterval    = null;
    var checkCount        = 0;
    var MAX_CHECKS        = 120;

    // ── QR Generation ─────────────────────────────────────────────────────────
    function generateQRCode(documentId, siteId, data) {
        var el = document.getElementById('eimzo-qrcode');
        el.innerHTML = '';

        var hasher   = new GostHash();
        var textHash = hasher.gosthash(data.toString());
        var code     = siteId + documentId + textHash;
        var crc32    = new CRC32().calcHex(code);
        var final    = code + crc32;

        new QRCode(el, {
            text: final, width: 260, height: 260,
            colorDark: '#000000', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        displayHash(textHash);
        return final;
    }

    function displayHash(hash) {
        var el     = document.getElementById('hash-display');
        var chunks = hash.match(/.{1,16}/g) || [];
        var html   = '';
        chunks.forEach(function(chunk) {
            chunk.split('').forEach(function(c) {
                html += /\d/.test(c) ? '<span class="digit">' + c + '</span>' : c;
            });
            html += '\n';
        });
        el.innerHTML = html;
    }

    // ── Deeplink — открываем через iframe trick (работает в WebView) ──────────
    function openDeeplink() {
        if (!currentDeeplink) return;

        // Строим HTTPS redirect URL — tg.openLink работает только с https://
        var redirectUrl = BASE_URL + '/mobile/redirect?url=' + encodeURIComponent(currentDeeplink);
        log('openDeeplink via redirect: ' + redirectUrl.substring(0, 60));

        if (tgApp && tgApp.openLink) {
            // Открывает Safari/Chrome с HTTPS страницей
            // Та страница автоматически сделает redirect на eimzo://
            tgApp.openLink(redirectUrl);
        } else {
            window.location.href = currentDeeplink;
        }
    }

    // ── Main flow ─────────────────────────────────────────────────────────────
    function startSign() {
        log('startSign called');
        document.getElementById('btn-start').disabled    = true;
        document.getElementById('btn-start').textContent = 'Yuklanmoqda...';
        hideResult();

        fetch(BASE_URL + '/api/v1/ecp/mobile/sign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + API_TOKEN,
                'Accept': 'application/json',
            }
        })
            .then(function(r) {
                log('mobile/sign status=' + r.status);
                return r.json();
            })
            .then(function(response) {
                log('documentId=' + response.documentId + ' siteId=' + response.siteId + ' status=' + response.status);

                if (response.status !== 1 || !response.documentId) {
                    showResult('Server xatosi: ' + (response.message || 'documentId olinmadi'), true);
                    resetBtn();
                    return;
                }

                currentDocumentId = response.documentId;

                var docContent = btoa(String.fromCharCode.apply(null,
                    new TextEncoder().encode('doc_id:' + DOC_ID + ':' + DOC_TYPE)
                ));

                var qrCode = generateQRCode(currentDocumentId, response.siteId, docContent);
                currentDeeplink = 'eimzo://sign?qc=' + qrCode;

                log('deeplink built: ' + currentDeeplink.substring(0, 50) + '...');

                // Устанавливаем href на кнопку напрямую
                var btn = document.getElementById('deeplink-btn');
                btn.href = currentDeeplink;
                btn.onclick = function(e) {
                    e.preventDefault();
                    openDeeplink();
                };

                document.getElementById('qr-section').classList.add('active');
                showStatus('QR-kodni skaning qiling yoki E-IMZO da oching...');

                // Авто-открытие на мобильном

                startPolling(docContent);
            })
            .catch(function(e) {
                log('fetch error: ' + e.message);
                showResult('Ulanishda xatolik: ' + e.message, true);
                resetBtn();
            });
    }

    function startPolling(docContent) {
        checkCount = 0;
        statusInterval = setInterval(function() {
            checkCount++;
            if (checkCount > MAX_CHECKS) {
                stopPolling();
                showResult('Vaqt tugadi. Qayta urinib ko\'ring.', true);
                resetBtn();
                return;
            }
            showStatus('Imzo kutilmoqda... (' + checkCount + 's)');

            fetch(BASE_URL + '/api/v1/ecp/mobile/status', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({documentId: currentDocumentId})
            })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.status === 1) {
                        log('Signature received! Verifying...');
                        stopPolling();
                        document.getElementById('qr-section').classList.remove('active');
                        showStatus('Imzo tekshirilmoqda...');
                        verifyAndSign(docContent);
                    } else if (data.status < 0) {
                        stopPolling();
                        showResult('Imzolashda xatolik: ' + (data.message || 'Noma\'lum xato'), true);
                        resetBtn();
                    }
                })
                .catch(function() {});
        }, 1000);
    }

    function stopPolling() {
        if (statusInterval) { clearInterval(statusInterval); statusInterval = null; }
    }

    function verifyAndSign(docContent) {
        log('verifyAndSign: docId=' + DOC_ID + ' type=' + DOC_TYPE);
        fetch(BASE_URL + '/api/v1/ecp/mobile/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + API_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                documentId:    currentDocumentId,
                document:      docContent,
                ecp_sign_type: DOC_TYPE,
                ecp_doc_id:    DOC_ID,
            })
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                log('verify response: status=' + data.status + ' success=' + data.success);
                hideStatusBar();
                if (data.status === 1 || data.success === 200) {
                    onSuccess();
                } else {
                    showResult(data.message || 'Imzolashda xatolik', true);
                    resetBtn();
                }
            })
            .catch(function(e) {
                log('verify error: ' + e.message);
                hideStatusBar();
                showResult('Server bilan aloqa uzildi', true);
                resetBtn();
            });
    }

    function onSuccess() {
        log('SUCCESS!');
        fetch(BASE_URL + '/mobile/sign/' + SIGN_TOKEN + '/complete', {method: 'POST'});
        document.getElementById('main-content').style.display = 'none';
        document.getElementById('success-screen').classList.add('active');
        if (tgApp) {
            setTimeout(function() { tgApp.close(); }, 2000);
        }
    }

    function showStatus(msg) {
        document.getElementById('status-text').textContent = msg;
        document.getElementById('status-bar').classList.add('active');
    }
    function hideStatusBar() { document.getElementById('status-bar').classList.remove('active'); }
    function showResult(msg, isError) {
        var el = document.getElementById('result-box');
        el.textContent = msg;
        el.className = 'result-box active ' + (isError ? 'error' : 'success');
        hideStatusBar();
    }
    function hideResult() { document.getElementById('result-box').className = 'result-box'; }
    function resetBtn() {
        document.getElementById('btn-start').disabled    = false;
        document.getElementById('btn-start').textContent = '🔐 Qayta urinish';
    }

    window.addEventListener('beforeunload', stopPolling);
</script>
</body>
</html>
