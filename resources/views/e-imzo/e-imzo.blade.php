{{--EMIZO Error Modal--}}
<div class="modal fade" id="imzo-modal" aria-hidden="true" tabindex="-1" role="dialog"
     aria-labelledby="imzoModalTitle" data-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-class">
            <div class="modal-header bg-yellow">
                <h4 class="modal-title" id="imzoModalTitle"><i data-lucide="key-round"></i> e-imzo</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 id="imzo-msg"></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light pull-right"
                        data-dismiss="modal">{{ __('action.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- e-imzo -->
<script src="{{ asset('/js/e_imzo.js') }}"></script>

<script src="{{ asset('/js/e_imzo_client.js') }}"></script>
<script type="text/javascript">
    var EIMZO_MAJOR = 3;
    var EIMZO_MINOR = 37;

    var errorCAPIWS = 'Sizda E-IMZO moduli yoki E-IMZO brauzeri o`rnatilmagan bo`lishi mumkin.';
    var errorBrowserWS = 'Brauzer WebSocket texnologiyasini qo`llab-quvvatlamaydi. Brauzerning so`nggi versiyasini o`rnating.';
    var errorUpdateApp = 'DIQQAT !!! E-IMZO ilovasining yangi versiyasini yoki E-IMZO brauzerini o`rnating.<br /><a href="https://e-imzo.uz/main/downloads/" role="button">E-IMZO dasturini yuklab oling</a>';
    var errorWrongPassword = 'Parol noto`g`ri kiritildi.';

    var AppLoad = function () {
        EIMZOClient.API_KEYS = [
            'edo.eai.uz', 'D42390118FC5EEF19265A41EF8227F661C431A5470E154CF06EFFC441E03CB0608A2FE92044F9983F4C3FE6AE1956EB9A75ED2AE570CB7B2369C9B36BBCBD43D'
        ];
        uiLoading();
        EIMZOClient.checkVersion(function (major, minor) {
            var newVersion = EIMZO_MAJOR * 100 + EIMZO_MINOR;
            var installedVersion = parseInt(major) * 100 + parseInt(minor);
            if (installedVersion < newVersion) {
                uiUpdateApp();
            } else {
                EIMZOClient.installApiKeys(function () {
                    uiLoadKeys();
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

    var uiShowMessage = function (message) {
        if (message === 'API-key для домена недействителен.') {
            message = 'e-imzo kaliti ushbu sayt (domen) uchun ruhsat berilmagan';
        } else if (message === 'Ввод пароля отменен') {
            message = 'Parolni kiritish bekor qilindi';
        }

        // Удаляем старые backdrop
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');

        $('#imzo-msg').html(message);

        // Показываем модалку правильно
        $('#imzo-modal').modal({
            backdrop: true,
            keyboard: true,
            show: true
        });
    }

    var uiLoading = function () {
        var l = document.getElementById('message');
        if (l) {
            l.innerHTML = 'Yuklanmoqda ...';
            l.style.color = 'red';
        }
    }

    var uiNotLoaded = function (e) {
        var l = document.getElementById('message');
        if (l) {
            l.innerHTML = '';
        }
        if (e) {
            wsError(e);
        } else {
            uiShowMessage(errorBrowserWS);
        }
    }

    var uiUpdateApp = function () {
        var l = document.getElementById('message');
        if (l) {
            l.innerHTML = errorUpdateApp;
        }
    }

    var uiLoadKeys = function () {
        uiClearCombo();
        EIMZOClient.listAllUserKeys(function (o, i) {
            var itemId = "itm-" + o.serialNumber + "-" + i;
            return itemId;
        }, function (itemId, v) {
            return uiCreateItem(itemId, v);
        }, function (items, firstId) {
            uiFillCombo(items);
            uiLoaded();
            uiComboSelect(firstId);
        }, function (e, r) {
            if (e) {
                uiShowMessage(errorCAPIWS + " : " + e);
            } else {
                uiShowMessage(r);
            }
        });
    }

    var uiComboSelect = function (itm) {
        if (itm) {
            var id = document.getElementById(itm);
            if (id) {
                id.setAttribute('selected', 'true');
            }
        }
    }

    var cbChanged = function (c) {
        var keyIdEl = document.getElementById('keyId');
        if (keyIdEl) {
            keyIdEl.innerHTML = '';
        }
    }

    var uiClearCombo = function () {
        if (document.testform && document.testform.key) {
            var combo = document.testform.key;
            combo.length = 0;
        }
    }

    var uiFillCombo = function (items) {
        if (document.testform && document.testform.key) {
            var combo = document.testform.key;
            for (var itm in items) {
                combo.append(items[itm]);
            }
        }
    }

    var uiLoaded = function () {
        var l = document.getElementById('message');
        if (l) {
            l.innerHTML = '';
        }
    }

    var uiCreateItem = function (itmkey, vo) {
        var now = new Date();
        vo.expired = dates.compare(now, vo.validTo) > 0;
        var itm = document.createElement("option");
        // console.log(vo);
        itm.value = itmkey;
        var CN = vo.CN;
        var PINFL = ' - (PINFL: '+vo.PINFL+ ')';
        if (vo.O) {
            CN = vo.O;
            PINFL = ' - (INN: ' + vo.TIN + ')';
        }
        itm.text = CN + PINFL;
        if (!vo.expired) {

        } else {
            itm.style.color = 'gray';
            itm.text = itm.text + ' (срок истек)';
        }
        itm.setAttribute('vo', JSON.stringify(vo));
        itm.setAttribute('id', itmkey);
        return itm;
    }

    var wsError = function (e) {
        if (e) {
            uiShowMessage(errorCAPIWS + " : " + e);
        } else {
            uiShowMessage(errorBrowserWS);
        }
    };

    var successCheckSignPost = function (pkcs7, ecp_doc_id, ecp_doc_type, ecp_confirm_id, ecp_r_text) {
        $.ajax({
            url: '/ecp-sign-doc',
            type: 'POST',
            data: {
                pk_data: pkcs7,
                ecp_doc_id: ecp_doc_id,
                ecp_doc_type: ecp_doc_type,
                ecp_confirm_id: ecp_confirm_id,
                ecp_r_text: ecp_r_text
            },
            beforeSend: function () {
                $('.loadingGif').show();
                $('.modal-bg-class').hide();
            },
            success: function (response) {
                console.log(response);

                // Удаляем старые backdrop
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');

                if (response.success === 200) {
                    $('.openConfirmForm').hide();
                    $('.loadingGif').hide();
                    $('.modal-content-class').removeClass('btn-yellow').addClass('bg-success');
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);

                    $('#responseModal').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });

                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else if (response.success === 201) {
                    $('.openConfirmForm').show();
                    $('.loadingGif').hide();
                    $('.modal-content-class').removeClass('bg-success').addClass('bg-yellow');
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);

                    $('#responseModal').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
                }
            },
            error: function (s) {
                console.log(s);
            },
            complete: function () {
                $('.loadingGif').hide();
            },
        });
    };

    sign = function () {
        var itm = document.testform.key.value;
        if (itm) {
            var id = document.getElementById(itm);
            var vo = JSON.parse(id.getAttribute('vo'));
            var ecp_doc_id = document.testform.ecp_doc_id.value;
            var ecp_doc_type = document.testform.ecp_doc_type.value;
            var ecp_confirm_id = document.testform.ecp_confirm_id.value;
            var ecp_r_text = document.testform.ecp_r_text.value;
            var keyIdEl = document.getElementById('keyId');
            var keyId = keyIdEl ? keyIdEl.innerHTML : '';

            if (keyId) {
                EIMZOClient.createPkcs7(keyId, ecp_doc_id, null, function (pkcs7) {
                    document.testform.pkcs7.value = pkcs7;
                    successCheckSignPost(pkcs7, ecp_doc_id, ecp_doc_type, ecp_confirm_id, ecp_r_text);
                }, function (e, r) {
                    if (r) {
                        if (r.indexOf("BadPaddingException") != -1) {
                            uiShowMessage(errorWrongPassword);
                        } else {
                            uiShowMessage(r);
                        }
                    } else {
                        if (keyIdEl) {
                            keyIdEl.innerHTML = '';
                        }
                        uiShowMessage(errorBrowserWS);
                    }
                    if (e) wsError(e);
                });
            } else {
                EIMZOClient.loadKey(vo, function (id) {
                    if (keyIdEl) {
                        keyIdEl.innerHTML = id;
                    }
                    EIMZOClient.createPkcs7(id, ecp_doc_id, null, function (pkcs7) {
                        document.testform.pkcs7.value = pkcs7;
                        successCheckSignPost(pkcs7, ecp_doc_id, ecp_doc_type, ecp_confirm_id, ecp_r_text);
                    }, function (e, r) {
                        if (r) {
                            if (r.indexOf("BadPaddingException") != -1) {
                                uiShowMessage(errorWrongPassword);
                            } else {
                                uiShowMessage(r);
                            }
                        } else {
                            if (keyIdEl) {
                                keyIdEl.innerHTML = '';
                            }
                            uiShowMessage(errorBrowserWS);
                        }
                        if (e) wsError(e);
                    });
                }, function (e, r) {
                    if (r) {
                        if (r.indexOf("BadPaddingException") != -1) {
                            uiShowMessage(errorWrongPassword);
                        } else {
                            uiShowMessage(r);
                        }
                    } else {
                        uiShowMessage(errorBrowserWS);
                    }
                    if (e) wsError(e);
                });
            }
        }
    };

    signLogin = function () {
        var ecp_doc_id = document.testform.ecp_doc_id.value;
        var ecp_doc_type = document.testform.ecp_doc_type.value;
        var ecp_confirm_id = document.testform.ecp_confirm_id.value;
        var ecp_r_text = document.testform.ecp_r_text.value;
        var pkcs7 = '';

        $.ajax({
            url: '/ecp-sign-doc',
            type: 'POST',
            data: {
                pk_data: pkcs7,
                ecp_doc_id: ecp_doc_id,
                ecp_doc_type: ecp_doc_type,
                ecp_confirm_id: ecp_confirm_id,
                ecp_r_text: ecp_r_text
            },
            beforeSend: function () {
                $('.loadModal').modal('show');
            },
            success: function (response) {
                console.log(response);

                // Удаляем старые backdrop
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');

                if (response.success === 200) {
                    $('.openConfirmForm').hide();
                    $('.loadModal').modal('hide');
                    $('.modal-content-class').removeClass('btn-yellow').addClass('bg-success');
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);

                    $('#responseModal').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });

                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else if (response.success === 201) {
                    $('.openConfirmForm').show();
                    $('.loadModal').modal('hide');
                    $('.modal-content-class').removeClass('bg-success').addClass('bg-yellow');
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);

                    $('#responseModal').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
                } else if (response.success === 202) {
                    $('.openConfirmForm').show();
                    $('.loadModal').modal('hide');
                    $('.modal-content-class').removeClass('btn-success').addClass('bg-yellow');
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);

                    $('#responseModal').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });

                    setTimeout(function () {
                        window.location.href = '/dep-head/ag-docs';
                    }, 5000);
                }
            },
            error: function (s) {
                console.log(s);
            },
            complete: function () {
                $("#loading").hide();
            },
        });
    };

    window.onload = AppLoad;

</script>
