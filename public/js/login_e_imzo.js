var EIMZO_MAJOR = 3;
var EIMZO_MINOR = 37;

var errorCAPIWS = 'Ошибка соединения с E-IMZO. Возможно у вас не установлен модуль E-IMZO.';
var errorBrowserWS = 'Браузер не поддерживает технологию WebSocket. Установите последнюю версию браузера.';
var errorUpdateApp = 'ВНИМАНИЕ !!! Установите новую версию приложения E-IMZO.<br /><a href="https://e-imzo.uz/main/downloads/" role="button">Скачать ПО E-IMZO</a>';
var errorWrongPassword = 'Пароль неверный.';

var eriKeysData = [];

var AppLoad = function () {
    EIMZOClient.API_KEYS = [
        'null', 'E0A205EC4E7B78BBB56AFF83A733A1BB9FD39D562E67978CC5E7D73B0951DB1954595A20672A63332535E13CC6EC1E1FC8857BB09E0855D7E76E411B6FA16E9D',
        'localhost', '96D0C1491615C82B9A54D9989779DF825B690748224C2B04F500F370D51827CE2644D8D4A82C18184D73AB8530BB8ED537269603F61DB0D03D2104ABF789970B',
        '127.0.0.1', 'A7BCFA5D490B351BE0754130DF03A068F855DB4333D43921125B9CF2670EF6A40370C646B90401955E1F7BC9CDBF59CE0B2C5467D820BE189C845D0B79CFC96F',
        'edo.turonbank.uz', '393D72E855EB9176332F3226547F50D37BB7AA80FE6D74A7A5D4832279676E55BD03BB536BC41FB1BB9D59F2707E365E067CDFC9A04F6004267632EC1133EB53',
    ];

    EIMZOClient.checkVersion(function (major, minor) {
        var newVersion = EIMZO_MAJOR * 100 + EIMZO_MINOR;
        var installedVersion = parseInt(major) * 100 + parseInt(minor);
        if (installedVersion < newVersion) {
            uiUpdateApp();
        } else {
            EIMZOClient.installApiKeys(function () {
                loadEriKeys();
            }, function (e, r) {
                if (r) {
                    uiShowMessage(r);
                } else {
                    wsError(e);
                }
            });
        }
    }, function (e, r) {
        if (r) {
            uiShowMessage(r);
        } else {
            uiNotLoaded(e);
        }
    });
}

var loadEriKeys = function () {
    eriKeysData = [];

    EIMZOClient.listAllUserKeys(
        function(o, i){
            return "itm-" + o.serialNumber + "-" + i;
        },
        function(itemId, vo){
            eriKeysData.push({
                id: itemId,
                vo: vo
            });
            return null;
        },
        function(items){
            console.log('ERI keys loaded:', eriKeysData);
            renderEriList();
        },
        function(e, r){
            if(e){
                uiShowMessage(errorCAPIWS + " : " + e);
            } else {
                console.log(r);
                document.getElementById('eriModalBody').innerHTML = '<div class="text-center py-4"><p>Xatolik yuz berdi. Qayta urinib ko\'ring.</p></div>';
            }
        }
    );
};

var uiShowMessage = function (message) {
    Swal.fire({
        text: message,
        icon: "warning",
        showCancelButton: false,
        confirmButtonText: "Yopish",
        buttonsStyling: true,
        showCloseButton: true
    });
};

var uiShowProgress = function () {
    Swal.fire({
        title: 'ERI kalit tekshirilmoqda...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

var uiHideProgress = function () {
    Swal.close();
};

var uiUpdateApp = function () {
    uiShowMessage(errorUpdateApp);
};

var uiNotLoaded = function (e) {
    const modalBody = document.getElementById('eriModalBody');
    modalBody.innerHTML = '<div class="text-center" style="padding:20px; background: #fff8e1; border-radius: 8px; border: 1px solid #f0e0a5;">' +
        '<span style="color: Tomato;"><i class="fa fa-exclamation-circle fa-3x"></i></span>' +
        '<p style="font-size: 14px; margin-top: 20px">Tizimni to\'g\'ri ishlashi uchun E-IMZO modulini yoki E-IMZO brauzerini ishga tushirishingiz kerak.</p>' +
        '<p style="font-size: 14px">E-IMZO modulini o\'rnatish bo\'yicha ko\'rsatmalarni <a href="https://e-imzo.uz/main/downloads/" style="color: #1271FF; text-decoration: none" rel="noopener noreferrer" target="_blank"> bu yerda </a> o\'qishingiz mumkin.</p>' +
        '</div>';
};

var wsError = function (e) {
    if (e) {
        uiShowMessage(errorCAPIWS + " : " + e);
    } else {
        uiShowMessage(errorBrowserWS);
    }
};

var uiHandleError = function (e, r) {
    uiHideProgress();
    if (r) {
        if (r.indexOf("BadPaddingException") != -1) {
            uiShowMessage("ERI paroli noto'g'ri");
        } else {
            uiShowMessage(r);
        }
    } else {
        uiShowMessage(errorBrowserWS);
    }
    if (e) wsError(e);
};

const formatDate = inputDate => {
    const date = new Date(inputDate);
    return `${date.getDate().toString().padStart(2, '0')}.${(date.getMonth() + 1).toString().padStart(2, '0')}.${date.getFullYear()}`;
};

// Tab switching
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    document.querySelectorAll('.auth-tab').forEach(btn => {
        btn.classList.remove('active');
    });

    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

// Password toggle
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.querySelector('.password-toggle');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('ik-eye');
        icon.classList.add('ik-eye-off');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('ik-eye-off');
        icon.classList.add('ik-eye');
    }
}

function openUsbToken() {
    const modal = document.getElementById('eriModal');
    modal.classList.add('show');

    eriKeysData = [];

    const modalBody = document.getElementById('eriModalBody');
    modalBody.innerHTML = '<div class="text-center py-4"><p>E-IMZO kalitlari yuklanmoqda...</p></div>';

    AppLoad();
}

function closeEriModal() {
    const modal = document.getElementById('eriModal');
    modal.classList.remove('show');
}

document.getElementById('eriModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEriModal();
    }
});

function renderEriList() {
    const modalBody = document.getElementById('eriModalBody');

    if (eriKeysData.length === 0) {
        modalBody.innerHTML = '<div class="text-center py-4"><p>ERI topilmadi</p></div>';
        return;
    }

    let html = '';

    eriKeysData.forEach(key => {
        const vo = key.vo;
        const now = new Date();
        const expired = dates.compare(now, vo.validTo) > 0;
        const isLegal = vo.O ? true : false;

        const typeClass = isLegal ? 'eri-type-legal' : 'eri-type-physical';
        const typeLabel = isLegal ? 'YURIDIK SHAXS' : 'JISMONIY SHAXS';
        const disabledClass = expired ? 'disabled' : '';

        html += `
                    <div class="eri-item ${disabledClass}" onclick="selectEri('${key.id}', ${expired})" data-key="${key.id}">
                        <div class="eri-item-header">
                            <img src="https://esi.uz/oauth2/assets/images/icons/pfx.ico" alt="pfx">
                            <strong>Sertifikat №:</strong> <span style="margin-left: 5px;">${vo.serialNumber}</span>
                        </div>

                        <div class="eri-item-name">${vo.CN}</div>
                        <span class="eri-item-type ${typeClass}">${typeLabel}</span>

                        ${isLegal ? `
                            <div class="eri-org-info">
                                <div><strong>INN:</strong> ${vo.TIN}</div>
                                <div><strong>Tashkilot:</strong> ${vo.O}</div>
                            </div>
                        ` : `
                            <div class="eri-item-details">
                                <div class="eri-detail-row">
                                    <span class="eri-detail-label">PINFL:</span>
                                    <span class="eri-detail-value">${vo.PINFL || '-'}</span>
                                </div>
                            </div>
                        `}

                        <div class="eri-item-footer">
                            <span class="eri-expiry ${expired ? 'expired' : ''}">
                                ${expired ? 'ERI ning amal qilish muddati o\'tgan: ' : 'Amal qilish muddati: '}
                                ${formatDate(vo.validTo)}
                            </span>
                        </div>
                    </div>
                `;
    });

    modalBody.innerHTML = html;
}

function selectEri(keyId, isExpired) {
    if (isExpired) return;

    const selectedKey = eriKeysData.find(k => k.id === keyId);
    if (!selectedKey) return;

    const eriSelect = document.getElementById('eriSelect');
    eriSelect.innerHTML = `<option value="${keyId}" selected>${selectedKey.vo.CN}</option>`;

    if (document.getElementById('eriLoginBtn')) {
        document.getElementById('eriLoginBtn').disabled = false;
    }

    closeEriModal();

    document.getElementById('selectedEri').value = keyId;
}

const signin = () => {
    let keyId = document.getElementById('selectedEri').value;
    const selectedKey = eriKeysData.find(k => k.id === keyId);
    if (!selectedKey) {
        uiShowMessage('ERI topilmadi');
        return;
    }

    const keyInfo = selectedKey.vo;
    const until = localStorage.getItem('rememberUntil');

    if (until && Date.now() < until) {
        const savedPin = localStorage.getItem("pfx_pin");
        frontChallenge(data => {
            if (data.status === 1) {
                createSignature(savedPin, data.challenge, (data) => {
                    if (data.status === 1) {
                        location.reload();
                    } else {
                        uiShowMessage(data.message);
                    }
                });
            }
        });
    } else {
        EIMZOClient.loadKey(keyInfo, (keyId) => {
            localStorage.setItem("pfx_pin", keyId);
            const SIX_HOURS = 6 * 60 * 60 * 1000;
            const now = Date.now();
            localStorage.setItem("rememberUntil", now + SIX_HOURS);
            frontChallenge(data => {
                if (data.status === 1) {
                    createSignature(keyId, data.challenge, (data) => {
                        if (data.status === 1) {
                            location.reload();
                        } else {
                            uiShowMessage(data.message);
                        }
                    });
                }
            });
        }, uiHandleError);
    }
};

const frontChallenge = (callback) => {
    $.get({
        url: '/e-imzo/frontend-challenge',
        dataType: 'json',
        success: (response) => {
            if (response.status === 1) {
                callback(response);
            }
        },
        error: (xhr, status, error) => {
            uiShowMessage('Xatolik yuz berdi: ' + error);
        }
    });
};

const createSignature = (keyId, challenge, callback) => {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    EIMZOClient.createPkcs7(keyId, challenge, null, (pkcs7) => {
        uiShowProgress();
        $.post({
            url: '/e-imzo/backend-auth',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: { pkcs7 },
            success: (response, textStatus, xhr) => {
                handleResponse(response, textStatus, xhr, callback);
            },
            error: (xhr, status, error) => {
                uiHideProgress();
                uiShowMessage('Xatolik yuz berdi: ' + error);
            }
        });
    }, uiHandleError, false);
};

const handleResponse = (response, textStatus, xhr, onSuccess) => {
    console.log(response, textStatus, xhr, onSuccess);
    if (xhr.status !== 200) {
        uiHideProgress();
        uiShowMessage(`${xhr.status} - ${textStatus}`);
        return;
    }

    try {
        const data = typeof response === "string" ? JSON.parse(response) : response;
        if (data.status !== 1) {
            uiHideProgress();
            uiShowMessage(`${data.status} - ${data.message}`);
            return;
        }
        onSuccess(data);
    } catch (error) {
        uiHideProgress();
        uiShowMessage(`${xhr.status} - ${textStatus}: ${error}`);
    }
};

window.onload = function() {
    AppLoad();
};
