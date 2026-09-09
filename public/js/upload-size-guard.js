/**
 * Предупреждение о слишком большой загрузке — до отправки формы.
 *
 * ЗАЧЕМ.
 * PHP отбрасывает запрос тяжелее post_max_size ещё до Laravel: приходит
 * страница «Content Too Large», а вся заполненная форма теряется.
 * Серверный обработчик (bootstrap/app.php) возвращает пользователя назад
 * с понятным сообщением, но файлы браузер приложить заново не может.
 *
 * Поэтому считаем размер прямо в браузере и не даём отправить форму —
 * пользователь сразу видит, какой файл лишний, и ничего не теряет.
 *
 * ПОДКЛЮЧЕНИЕ: один раз в общем layout, перед закрывающим </body>.
 */
(function () {
    'use strict';

    // Должно совпадать с upload_max_filesize в php.ini
    var MAX_FILE_MB  = 30;
    // Чуть меньше post_max_size (40M): запас на текстовые поля формы
    var MAX_TOTAL_MB = 35;

    var MB = 1024 * 1024;

    var messages = {
        ru: {
            single: 'Файл «%s» весит %sМБ — это больше допустимых %sМБ.',
            total:  'Общий размер файлов %sМБ — это больше допустимых %sМБ. Прикрепите их по частям.'
        },
        uz: {
            single: '«%s» fayli %sMB — ruxsat etilgan %sMB dan katta.',
            total:  'Fayllarning umumiy hajmi %sMB — ruxsat etilgan %sMB dan katta. Ularni qismlarga bo\'lib biriktiring.'
        },
        en: {
            single: 'File "%s" is %sMB — larger than the allowed %sMB.',
            total:  'Total file size is %sMB — larger than the allowed %sMB. Please attach them in parts.'
        }
    };

    function t(key) {
        var lang = (document.documentElement.lang || 'ru').substring(0, 2);
        return (messages[lang] || messages.ru)[key];
    }

    function format(template) {
        var args = Array.prototype.slice.call(arguments, 1);
        var i = 0;
        return template.replace(/%s/g, function () {
            return args[i++];
        });
    }

    function mb(bytes) {
        return (bytes / MB).toFixed(1);
    }

    /** Показать сообщение тем способом, который есть на странице. */
    function warn(text) {
        if (window.toastr && typeof window.toastr.error === 'function') {
            window.toastr.error(text);
            return;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon: 'error', text: text });
            return;
        }

        alert(text);
    }

    /**
     * Проверка одной формы.
     * @returns {boolean} true — можно отправлять
     */
    function checkForm(form) {
        var inputs = form.querySelectorAll('input[type="file"]');
        var total  = 0;

        for (var i = 0; i < inputs.length; i++) {
            var files = inputs[i].files;

            if (!files) {
                continue;
            }

            for (var j = 0; j < files.length; j++) {
                var file = files[j];
                total += file.size;

                if (file.size > MAX_FILE_MB * MB) {
                    warn(format(t('single'), file.name, mb(file.size), MAX_FILE_MB));
                    return false;
                }
            }
        }

        if (total > MAX_TOTAL_MB * MB) {
            warn(format(t('total'), mb(total), MAX_TOTAL_MB));
            return false;
        }

        return true;
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || form.tagName !== 'FORM') {
            return;
        }

        // Формы без файлов не трогаем
        if (!form.querySelector('input[type="file"]')) {
            return;
        }

        if (!checkForm(form)) {
            event.preventDefault();
            event.stopPropagation();

            // Кнопку могли заблокировать в обработчике отправки — возвращаем
            var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');

            for (var i = 0; i < buttons.length; i++) {
                buttons[i].disabled = false;
            }
        }
    }, true); // capture: срабатываем раньше остальных обработчиков submit
})();
