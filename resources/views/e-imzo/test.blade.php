@extends('layouts.app')

@section('content')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .eimzo-test-container {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .eimzo-test-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        .eimzo-test-box h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .eimzo-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .eimzo-button-group {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .eimzo-btn {
            flex: 1;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .eimzo-btn-auth {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .eimzo-btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .eimzo-btn-sign {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .eimzo-btn-sign:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.4);
        }

        .eimzo-qr-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            display: none;
        }

        .eimzo-qr-section.active {
            display: block;
        }

        .eimzo-qr-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        #eimzo-qrcode {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .eimzo-deeplink-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #ddd;
        }

        .eimzo-deeplink-btn {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .eimzo-deeplink-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .eimzo-status-section {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            display: none;
        }

        .eimzo-status-section.active {
            display: block;
        }

        .eimzo-status-text {
            color: #856404;
            font-weight: 500;
        }

        .eimzo-result-section {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            display: none;
        }

        .eimzo-result-section.active {
            display: block;
        }

        .eimzo-result-section.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .eimzo-result-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #155724;
        }

        .eimzo-result-section.error .eimzo-result-title {
            color: #721c24;
        }

        .eimzo-result-content {
            font-size: 14px;
            color: #155724;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .eimzo-result-section.error .eimzo-result-content {
            color: #721c24;
        }

        .eimzo-info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .eimzo-info-box p {
            margin: 5px 0;
            color: #0d47a1;
            font-size: 14px;
        }

        .eimzo-loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: eimzo-spin 1s linear infinite;
            margin: 20px auto;
            display: none;
        }

        .eimzo-loader.active {
            display: block;
        }

        @keyframes eimzo-spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .eimzo-hash-display {
            background: #2c3e50;
            color: #00C68E;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            margin-top: 15px;
        }

        .eimzo-hash-display .digit {
            color: #ecf0f1;
        }

        @media (max-width: 600px) {
            .eimzo-test-box {
                padding: 25px;
            }

            .eimzo-button-group {
                flex-direction: column;
            }

            .eimzo-btn {
                width: 100%;
            }
        }
    </style>

    <div class="eimzo-test-container">
        <div class="eimzo-test-box">
            <h1>🔐 E-IMZO Mobile Test</h1>
            <p class="eimzo-subtitle">Тестирование авторизации и подписания через ID-CARD</p>

            <div class="eimzo-info-box">
                <p><strong>Убедитесь что:</strong></p>
                <p>✓ У вас установлено приложение E-IMZO ID-CARD</p>
                <p>✓ У вас есть ID-карта с действующим сертификатом</p>
                <p>✓ На телефоне включен NFC</p>
            </div>

            <div class="eimzo-button-group">
                <button class="eimzo-btn eimzo-btn-auth" onclick="startAuth()">
                    🔑 Авторизация
                </button>
                <button class="eimzo-btn eimzo-btn-sign" onclick="startSign()">
                    ✍️ Подписание документа
                </button>
            </div>

            <div id="eimzo-qrSection" class="eimzo-qr-section">
                <div class="eimzo-qr-title" id="eimzo-qrTitle">Сканируйте QR-код</div>
                <div id="eimzo-qrcode"></div>
                <div id="eimzo-hashDisplay" class="eimzo-hash-display"></div>
                <div class="eimzo-deeplink-section">
                    <p style="color: #666; margin-bottom: 10px;">Или используйте deeplink:</p>
                    <a href="#" id="eimzo-deeplinkBtn" class="eimzo-deeplink-btn"
                       onclick="openDeeplink(); return false;">
                        📱 Открыть в E-IMZO
                    </a>
                </div>
            </div>

            <div class="eimzo-loader" id="eimzo-loader"></div>

            <div id="eimzo-statusSection" class="eimzo-status-section">
                <div class="eimzo-status-text" id="eimzo-statusText">Ожидание подписи...</div>
            </div>

            <div id="eimzo-resultSection" class="eimzo-result-section">
                <div class="eimzo-result-title" id="eimzo-resultTitle">Результат</div>
                <div class="eimzo-result-content" id="eimzo-resultContent"></div>
            </div>
        </div>
    </div>
@endsection

<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script src="{{ asset('js/pkcs.js') }}"></script>
<script src="{{ asset('js/crc32.js') }}"></script>

<script>
    // Configuration
    var API_BASE_URL = '{{ url("/api/v1/ecp/mobile") }}';
    var AUTH_URL = API_BASE_URL + '/auth';
    var SIGN_URL = API_BASE_URL + '/sign';
    var STATUS_URL = API_BASE_URL + '/status';
    var AUTHENTICATE_URL = API_BASE_URL + '/authenticate';
    var VERIFY_URL = API_BASE_URL + '/verify';

    // Global variables - use var for better compatibility
    var currentDocumentId = null;
    var currentSiteId = null;
    var currentChallenge = null;
    var currentMode = null;
    var statusCheckInterval = null;
    var qrCodeInstance = null;
    var currentDeeplink = null;

    // CSRF Token setup
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    function showLoader() {
        $('#eimzo-loader').addClass('active');
    }

    function hideLoader() {
        $('#eimzo-loader').removeClass('active');
    }

    function showQRSection() {
        $('#eimzo-qrSection').addClass('active');
    }

    function hideQRSection() {
        $('#eimzo-qrSection').removeClass('active');
    }

    function showStatus(message) {
        $('#eimzo-statusText').text(message);
        $('#eimzo-statusSection').addClass('active');
    }

    function hideStatus() {
        $('#eimzo-statusSection').removeClass('active');
    }

    function showResult(title, content, isError = false) {
        $('#eimzo-resultTitle').text(title);
        $('#eimzo-resultContent').text(
            typeof content === 'object' ? JSON.stringify(content, null, 2) : content
        );

        if (isError) {
            $('#eimzo-resultSection').addClass('error');
        } else {
            $('#eimzo-resultSection').removeClass('error');
        }

        $('#eimzo-resultSection').addClass('active');
        hideLoader();
        hideStatus();
    }

    function hideResult() {
        $('#eimzo-resultSection').removeClass('active');
    }

    function generateQRCode(documentId, siteId, data) {
        const qrcodeElement = document.getElementById('eimzo-qrcode');
        qrcodeElement.innerHTML = '';

        const hasher = new GostHash();
        const textHash = hasher.gosthash(data.toString());

        const code = siteId + documentId + textHash;
        const crcer = new CRC32();
        const crc32 = crcer.calcHex(code);
        const finalCode = code + crc32;

        qrCodeInstance = new QRCode(qrcodeElement, {
            text: finalCode,
            width: 300,
            height: 300,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        displayHash(textHash);

        return {textHash, qrCode: finalCode};
    }

    function displayHash(hash) {
        const hashDisplay = document.getElementById('eimzo-hashDisplay');
        const arrayOf16 = hash.match(/.{1,16}/g);

        let htmlContent = '';
        arrayOf16.forEach(chunk => {
            const chars = chunk.split('');
            let line = '';
            chars.forEach(char => {
                if (/^\d+$/.test(char)) {
                    line += `<span class="digit">${char}</span>`;
                } else {
                    line += char;
                }
            });
            htmlContent += line + '\n';
        });

        hashDisplay.innerHTML = htmlContent;
    }

    function createDeeplink(mode, qrCode) {
        return `eimzo://${mode}?qc=${qrCode}`;
    }

    function openDeeplink() {
        if (currentDeeplink) {
            window.location.href = currentDeeplink;
        }
    }

    function startStatusCheck(documentId, mode) {
        if (!documentId) {
            console.error('DocumentID is null or undefined');
            showResult('Ошибка', 'Не удалось получить DocumentID', true);
            return;
        }

        stopStatusCheck();

        var checkCount = 0;
        var maxChecks = 120;

        statusCheckInterval = setInterval(function () {
            checkCount++;

            if (checkCount > maxChecks) {
                stopStatusCheck();
                showResult('Ошибка', 'Время ожидания истекло. Попробуйте снова.', true);
                hideQRSection();
                return;
            }

            $.ajax({
                url: STATUS_URL,
                type: 'POST',
                data: {documentId: documentId},
                success: function (response) {
                    console.log('Status check:', response);

                    if (response.status === 1) {
                        stopStatusCheck();
                        showStatus('Получена подпись! Проверка данных...');

                        if (mode === 'auth') {
                            authenticateUser(documentId);
                        } else if (mode === 'sign') {
                            verifySignature(documentId);
                        }

                        hideQRSection();
                    } else if (response.status === 2) {
                        showStatus('Ожидание подписи... (' + checkCount + 'с)');
                    } else if (response.status < 0) {
                        stopStatusCheck();
                        showResult('Ошибка', 'Код ошибки: ' + response.status + '\n' + (response.message || 'Неизвестная ошибка'), true);
                        hideQRSection();
                    }
                },
                error: function (error) {
                    console.error('Status check error:', error);
                }
            });
        }, 1000);
    }

    function stopStatusCheck() {
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
            statusCheckInterval = null;
        }
    }

    function authenticateUser(documentId) {
        showLoader();

        $.ajax({
            url: AUTHENTICATE_URL + '/' + documentId,
            type: 'GET',
            success: function (response) {
                if (response.status === 1 && response.subjectCertificateInfo) {
                    const userInfo = response.subjectCertificateInfo;
                    const displayInfo = `
ФИО: ${userInfo.subjectName?.CN || 'N/A'}
Имя: ${userInfo.subjectName?.Name || 'N/A'}
Фамилия: ${userInfo.subjectName?.SURNAME || 'N/A'}
ПИНФЛ: ${userInfo.subjectName?.UID || 'N/A'}
Серийный номер: ${userInfo.serialNumber || 'N/A'}
Действителен с: ${userInfo.validFrom || 'N/A'}
Действителен до: ${userInfo.validTo || 'N/A'}
                    `;
                    showResult('✅ Авторизация успешна!', displayInfo);
                } else {
                    showResult('Ошибка авторизации', response.message || 'Не удалось получить данные пользователя', true);
                }
            },
            error: function (error) {
                showResult('Ошибка', 'Не удалось выполнить авторизацию: ' + error.responseText, true);
            }
        });
    }

    function verifySignature(documentId) {
        showLoader();

        const sampleDocument = btoa('Это тестовый документ для подписания через E-IMZO Mobile');

        $.ajax({
            url: VERIFY_URL,
            type: 'POST',
            data: {
                documentId: documentId,
                document: sampleDocument
            },
            success: function (response) {
                if (response.status === 1 && response.subjectCertificateInfo) {
                    const userInfo = response.subjectCertificateInfo;
                    const verifyInfo = response.verificationInfo;

                    const displayInfo = `
Подписант:
ФИО: ${userInfo.subjectName?.CN || 'N/A'}
ПИНФЛ: ${userInfo.subjectName?.UID || 'N/A'}
Серийный номер: ${userInfo.serialNumber || 'N/A'}

Информация о подписи:
Время подписания: ${verifyInfo?.signingTime || 'N/A'}
Время метки: ${verifyInfo?.timestampedTime || 'N/A'}

Подпись действительна!
                    `;
                    showResult('✅ Документ успешно подписан!', displayInfo);
                } else {
                    showResult('Ошибка подписи', response.message || 'Подпись недействительна', true);
                }
            },
            error: function (error) {
                showResult('Ошибка', 'Не удалось проверить подпись: ' + error.responseText, true);
            }
        });
    }

    function startAuth() {
        console.log('Starting authentication...');
        hideResult();
        hideQRSection();
        hideStatus();
        showLoader();

        $.ajax({
            url: AUTH_URL,
            type: 'POST',
            success: function (response) {
                console.log('Auth response:', response);

                if (response.status === 1) {
                    currentDocumentId = response?.documentId;
                    currentSiteId = response?.siteId;
                    currentChallenge = response.challenge;
                    currentMode = 'auth';

                    console.log('DocumentID:', currentDocumentId);
                    console.log('SiteID:', currentSiteId);

                    if (!currentDocumentId || !currentSiteId || !currentChallenge) {
                        hideLoader();
                        showResult('Ошибка', 'Не получены необходимые данные от сервера', true);
                        return;
                    }

                    hideLoader();

                    var qrData = generateQRCode(currentDocumentId, currentSiteId, currentChallenge);

                    currentDeeplink = createDeeplink('auth', qrData.qrCode);
                    $('#eimzo-deeplinkBtn').attr('href', currentDeeplink);

                    $('#eimzo-qrTitle').text('🔑 Сканируйте для авторизации');
                    showQRSection();

                    if (isMobileDevice()) {
                        setTimeout(function () {
                            openDeeplink();
                        }, 500);
                    }

                    startStatusCheck(currentDocumentId, 'auth');
                    showStatus('Отсканируйте QR-код или используйте deeplink...');
                } else {
                    hideLoader();
                    showResult('Ошибка', response.message || 'Не удалось создать запрос на авторизацию', true);
                }
            },
            error: function (error) {
                console.error('Auth error:', error);
                hideLoader();
                showResult('Ошибка', 'Не удалось выполнить запрос: ' + (error.responseText || error.statusText), true);
            }
        });
    }

    function startSign() {
        hideResult();
        hideQRSection();
        hideStatus();
        showLoader();

        $.ajax({
            url: SIGN_URL,
            type: 'POST',
            success: function (response) {
                if (response.status === 1) {
                    currentDocumentId = response.documentId;
                    currentSiteId = response.siteId;
                    currentMode = 'sign';

                    hideLoader();

                    const sampleDocument = 'Это тестовый документ для подписания через E-IMZO Mobile';
                    const {qrCode} = generateQRCode(currentDocumentId, currentSiteId, sampleDocument);

                    currentDeeplink = createDeeplink('sign', qrCode);
                    $('#eimzo-deeplinkBtn').attr('href', currentDeeplink);

                    $('#eimzo-qrTitle').text('✍️ Сканируйте для подписания');
                    showQRSection();

                    if (isMobileDevice()) {
                        setTimeout(() => {
                            openDeeplink();
                        }, 500);
                    }

                    startStatusCheck(currentDocumentId, 'sign');
                    showStatus('Отсканируйте QR-код или используйте deeplink...');
                } else {
                    hideLoader();
                    showResult('Ошибка', response.message || 'Не удалось создать запрос на подписание', true);
                }
            },
            error: function (error) {
                hideLoader();
                showResult('Ошибка', 'Не удалось выполнить запрос: ' + error.responseText, true);
            }
        });
    }

    $(window).on('beforeunload', function () {
        stopStatusCheck();
    });
</script>
