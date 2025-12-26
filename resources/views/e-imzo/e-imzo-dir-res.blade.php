{{--EMIZO Error Modal--}}
<div class="modal fade" id="imzo-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-yellow">
                <h4 class="modal-title"><i class="fa fa-key"></i> e-imzo</h4>
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
            'edo.uzmrc.uz', 'CAB45E8188946090375002584A826A07E0C53FC5C2FFD0A93D3449356EDE7160BDA63041EC87A618191C0D78480D4FDB188597A54E136478E8C00C44238D53EB'
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
        //alert(message);
        if (message === 'API-key для домена недействителен.') {
            message = 'e-imzo kaliti ushbu sayt (domen) uchun ruhsat berilmagan';
        } else if (message === 'Ввод пароля отменен') {
            message = 'Parolni kiritish bekor qilindi';
        }
        $('#imzo-msg').html(message);
        $('#imzo-modal').modal('show');
    }

    var uiLoading = function () {
        var l = document.getElementById('message');
        l.innerHTML = 'Загрузка ...';
        l.style.color = 'red';
    }

    var uiNotLoaded = function (e) {
        var l = document.getElementById('message');
        l.innerHTML = '';
        if (e) {
            wsError(e);
        } else {
            uiShowMessage(errorBrowserWS);
        }
    }

    var uiUpdateApp = function () {
        var l = document.getElementById('message');
        l.innerHTML = errorUpdateApp;
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
            id.setAttribute('selected', 'true');
        }
    }

    var cbChanged = function (c) {
        document.getElementById('keyId').innerHTML = '';
    }

    var uiClearCombo = function () {
        var combo = document.testform.key;
        combo.length = 0;
    }

    var uiFillCombo = function (items) {
        var combo = document.testform.key;
        for (var itm in items) {
            combo.append(items[itm]);
        }
    }

    var uiLoaded = function () {
        var l = document.getElementById('message');
        l.innerHTML = '';
    }

    var uiCreateItem = function (itmkey, vo) {
        var now = new Date();
        vo.expired = dates.compare(now, vo.validTo) > 0;
        var itm = document.createElement("option");
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

    var successCheckSignPost = function (pkcs7, ecp_doc_id, ecp_doc_type, ecp_confirm_id, ecp_r_text, r_type) {
        $.ajax({
            url: '/ecp-sign-doc',
            type: 'POST',
            data: {
                pk_data: pkcs7,
                ecp_doc_id: ecp_doc_id,
                ecp_doc_type: ecp_doc_type,
                ecp_confirm_id: ecp_confirm_id,
                ecp_r_text: ecp_r_text,
                r_type: r_type,
            },
            beforeSend: function () {
                $('.loadingGif').show();
                $('.modal-bg-class').hide();
            },
            success: function (response) {
                console.log(response);
                if (response.success === 200) {
                    /*$('.openConfirmForm').hide();
                    $('.loadModal').modal('hide');
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    console.log('auto sub');
                    document.getElementById("resForm").submit();*/
                    $('.loadingGif').hide();


                    let btnConfLogin = document.getElementById('btnConfLogin');
                    btnConfLogin.disabled = true;
                    btnConfLogin.innerText = "{{ __('to_sign') }} (LOGIN)";

                    let btnConfEri = document.getElementById('btnConfEri');
                    btnConfEri.disabled = true;
                    btnConfEri.innerText = "{{ __('to_sign') }} (ERI)...";
                    document.getElementById("resForm").submit();

                    setTimeout(function () {
                        //location.reload();
                    }, 2000);
                } else if (response.success === 201) {
                    $('.openConfirmForm').hide();
                    $('.loadingGif').hide();
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
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
            var r_type = document.testform.r_type.value;
            var keyId = document.getElementById('keyId').innerHTML;
            if (keyId) {
                EIMZOClient.createPkcs7(keyId, ecp_doc_id, null, function (pkcs7) {
                    document.testform.pkcs7.value = pkcs7;
                    successCheckSignPost(pkcs7, ecp_doc_id, ecp_doc_type, ecp_confirm_id, ecp_r_text, r_type);
                }, function (e, r) {
                    if (r) {
                        if (r.indexOf("BadPaddingException") != -1) {
                            uiShowMessage(errorWrongPassword);
                        } else {
                            uiShowMessage(r);
                        }
                    } else {
                        document.getElementById('keyId').innerHTML = '';
                        uiShowMessage(errorBrowserWS);
                    }
                    if (e) wsError(e);
                });
            } else {
                console.log(1)
                EIMZOClient.loadKey(vo, function (id) {
                    document.getElementById('keyId').innerHTML = id;
                    EIMZOClient.createPkcs7(id, ecp_doc_id, null, function (pkcs7) {
                        document.testform.pkcs7.value = pkcs7;
                        successCheckSignPost(pkcs7, ecp_doc_id, ecp_doc_type, ecp_confirm_id, ecp_r_text, r_type);

                        console.log(11)
                    }, function (e, r) {

                        console.log(2)
                        if (r) {
                            if (r.indexOf("BadPaddingException") != -1) {
                                uiShowMessage(errorWrongPassword);
                            } else {
                                uiShowMessage(r);
                            }
                        } else {
                            document.getElementById('keyId').innerHTML = '';
                            uiShowMessage(errorBrowserWS);

                            console.log(3)
                        }
                        if (e) wsError(e);
                    });
                }, function (e, r) {

                    console.log(22)
                    if (r) {

                        console.log(4)
                        if (r.indexOf("BadPaddingException") != -1) {
                            uiShowMessage(errorWrongPassword);
                        } else {
                            uiShowMessage(r);
                        }
                    } else {

                        console.log(5)
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
        var r_type = document.testform.r_type.value;
        var pkcs7 = '';
        console.log(ecp_r_text);
        $.ajax({
            url: '/ecp-sign-doc',
            type: 'POST',
            data: {
                pk_data: pkcs7,
                ecp_doc_id: ecp_doc_id,
                ecp_doc_type: ecp_doc_type,
                ecp_confirm_id: ecp_confirm_id,
                ecp_r_text: ecp_r_text,
                r_type: r_type
            },
            beforeSend: function () {
                //$('.loadModal').modal('show');
            },
            success: function (response) {
                console.log(response);
                if (response.success === 200) {
                    $('.openConfirmForm').hide();
                    let btnConfLogin = document.getElementById('btnConfLogin');
                    btnConfLogin.disabled = true;
                    btnConfLogin.innerText = "{{ __('to_sign') }} (LOGIN)...";

                    let btnConfEri = document.getElementById('btnConfEri');
                    btnConfEri.disabled = true;
                    btnConfEri.innerText = "{{ __('to_sign') }} (ERI)";
                    document.getElementById("resForm").submit();

                    setTimeout(function () {
                        //location.reload();
                    }, 2000);

                } else if (response.success === 201) {
                    $('.openConfirmForm').hide();
                    $('.loadModal').modal('hide');
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
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
