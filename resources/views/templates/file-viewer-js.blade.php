{{--
 | Поведение просмотрщика вложений.
 | Подключать ОДИН раз на страницу, после всех карточек:
 |     @include('templates.file-viewer-js')
 --}}

<script>
    (function () {
        'use strict';

        /* За сколько до появления в поле зрения начинать загрузку.
           200px — примерно пол-экрана прокрутки: документ успевает
           открыться к тому моменту, когда до него доходит взгляд. */
        const PRELOAD_MARGIN = '200px';

        /* ─────────── OnlyOffice ───────────
         | api.js грузим один раз и только тогда, когда на странице
         | действительно есть офисный документ. Повторные вызовы
         | переиспользуют то же обещание, параллельных загрузок нет.
         */
        let docsApiPromise = null;

        function ensureDocsApi(jsUrl) {
            if (window.DocsAPI) return Promise.resolve();
            if (docsApiPromise) return docsApiPromise;

            docsApiPromise = new Promise(function (resolve, reject) {
                const script = document.createElement('script');
                script.src = jsUrl;
                script.onload = resolve;
                script.onerror = function () {
                    docsApiPromise = null;   // дать шанс повторить
                    reject(new Error('OnlyOffice недоступен'));
                };
                document.head.appendChild(script);
            });

            return docsApiPromise;
        }

        function showError(target, text) {
            target.innerHTML = '';
            const box = document.createElement('div');
            box.className = 'file-viewer-fallback';
            box.textContent = text;
            target.appendChild(box);
        }

        function mountOffice(mount) {
            if (mount.dataset.mounted === '1') return;

            const configEl = document.getElementById(mount.id + '-config');
            if (!configEl) return;

            let config;
            try {
                config = JSON.parse(configEl.textContent);
            } catch (e) {
                showError(mount, 'Не удалось прочитать настройки просмотра');
                return;
            }

            mount.dataset.mounted = '1';

            // Ошибку редактора показываем в карточке, а не в консоли:
            // иначе пользователь видит пустой прямоугольник и не понимает,
            // открылся документ или нет.
            config.events = {
                onError: function () {
                    showError(mount, 'Просмотрщик не смог открыть документ. Скачайте файл.');
                }
            };

            ensureDocsApi(@json(app(\App\Services\OnlyOfficeViewerService::class)->apiJsUrl()))
                .then(function () {
                    // DocEditor сам заменит содержимое элемента своим iframe
                    new DocsAPI.DocEditor(mount.id, config);
                })
                .catch(function () {
                    showError(mount, 'Сервис просмотра документов недоступен. Скачайте файл.');
                });
        }

        /* ─────────── Текстовые файлы ─────────── */
        function loadText(pre) {
            if (pre.dataset.loaded === '1') return;
            pre.dataset.loaded = '1';
            pre.textContent = 'Загрузка…';

            // credentials нужны: маршрут предпросмотра живёт за авторизацией
            fetch(pre.dataset.textSrc, { credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) throw new Error(r.status);
                    return r.text();
                })
                .then(function (text) {
                    pre.textContent = text;
                })
                .catch(function () {
                    pre.dataset.loaded = '0';
                    pre.textContent = 'Не удалось загрузить содержимое файла.';
                });
        }

        /* ─────────── Загрузка по появлению в поле зрения ───────────
         | Так вложения открываются сами, но не все разом: каждый
         | открытый офисный документ занимает соединение Document
         | Server, а их в бесплатной сборке считанное число.
         */
        function autoLoad(selector, handler) {
            const nodes = document.querySelectorAll(selector);
            if (!nodes.length) return;

            // Старый браузер без IntersectionObserver — грузим сразу,
            // лучше так, чем не показать документ вообще
            if (!('IntersectionObserver' in window)) {
                nodes.forEach(handler);
                return;
            }

            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    observer.unobserve(entry.target);
                    handler(entry.target);
                });
            }, { rootMargin: PRELOAD_MARGIN });

            nodes.forEach(function (node) { observer.observe(node); });
        }

        function init() {
            autoLoad('[data-office-auto="1"]', mountOffice);
            autoLoad('[data-text-auto="1"]', loadText);

            // Кнопка остаётся только у крупных текстовых файлов
            document.addEventListener('click', function (event) {
                const textBtn = event.target.closest('[data-text-open]');
                if (!textBtn) return;

                event.preventDefault();
                const pre = document.getElementById(textBtn.dataset.textOpen);
                if (pre) loadText(pre);
                textBtn.remove();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
