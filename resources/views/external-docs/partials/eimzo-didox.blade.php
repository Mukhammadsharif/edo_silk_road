{{-- 
  EimzoDidox — промис-обёртка над официальным EIMZOClient (e-imzo.js)
  для подписания документов Didox.

  В проекте уже есть @include('e-imzo.e-imzo') с колбэчным API
  (listAllUserKeys / loadKey / createPkcs7). Этот партиал переиспользует
  тот же EIMZOClient, но даёт компактный Promise-интерфейс:

    EimzoDidox.listKeys()            → [{label, vo}]
    EimzoDidox.sign(keyIndex, b64)   → {pkcs7, signatureHex}
    EimzoDidox.toBase64(str)         → base64 (UTF-8 safe)

  Подключение официального e-imzo.js — забота layout'а или соседнего
  @include('e-imzo.e-imzo'); здесь только логика Didox-подписи.
--}}

@include('e-imzo.e-imzo')

<script>
window.EimzoDidox = (function () {
    let cachedKeys = [];

    /** UTF-8-безопасный base64 (btoa падает на кириллице). */
    function toBase64(str) {
        return btoa(unescape(encodeURIComponent(str)));
    }

    /** Список ключей E-IMZO → [{label, vo}]. */
    function listKeys() {
        return new Promise((resolve, reject) => {
            if (typeof EIMZOClient === 'undefined') {
                reject(new Error('EIMZOClient не загружен (e-imzo.js)'));
                return;
            }

            const items = [];
            EIMZOClient.listAllUserKeys(
                function (o, i) { return 'itm-' + o.serialNumber + '-' + i; },
                function (itemId, vo) {
                    const label = (vo.CN || vo.O || vo.TIN || 'ключ') +
                        ' [' + (vo.TIN || vo.PINFL || '') + ']' +
                        (vo.validTo ? ' до ' + new Date(vo.validTo).toLocaleDateString() : '');
                    items.push({ label: label, vo: vo });
                    return itemId;
                },
                function (firstItemId) {
                    cachedKeys = items;
                    resolve(items);
                },
                function (e, r) {
                    reject(new Error(r || e || 'E-IMZO: ошибка получения ключей'));
                }
            );
        });
    }

    /** Загрузить ключ по индексу из listKeys() → keyId. */
    function loadKey(index) {
        return new Promise((resolve, reject) => {
            const item = cachedKeys[index];
            if (!item) { reject(new Error('Ключ не найден (индекс ' + index + ')')); return; }

            EIMZOClient.loadKey(item.vo,
                function (keyId) { resolve(keyId); },
                function (e, r)  { reject(new Error(r || e || 'E-IMZO: не удалось загрузить ключ')); }
            );
        });
    }

    /**
     * Подписать данные (base64) выбранным ключом → {pkcs7, signatureHex}.
     * createPkcs7 официального клиента возвращает pkcs7; signature_hex
     * приходит в сыром ответе — берём через CAPIWS напрямую для надёжности.
     */
    function sign(index, dataB64) {
        return loadKey(index).then(keyId => new Promise((resolve, reject) => {
            CAPIWS.callFunction(
                {
                    plugin: 'pkcs7',
                    name: 'create_pkcs7',
                    arguments: [dataB64, keyId, 'no'],
                },
                function (event, data) {
                    if (data.success) {
                        resolve({ pkcs7: data.pkcs7_64, signatureHex: data.signature_hex });
                    } else {
                        reject(new Error(data.reason || 'E-IMZO: create_pkcs7 неуспешен'));
                    }
                },
                function (e) {
                    reject(new Error('E-IMZO: ' + (e || 'ошибка соединения')));
                }
            );
        }));
    }

    return { listKeys, sign, toBase64 };
})();
</script>
