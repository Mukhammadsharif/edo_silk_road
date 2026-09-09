/**
 * Предупреждение о слишком большом файле — в момент выбора, а не отправки.
 *
 * ПОЧЕМУ НЕ ПЕРЕХВАТЫВАЕМ submit.
 * Первая версия вешала обработчик на submit в фазе capture. Это оказалось
 * опасно: формы документов отправляются не кнопкой, а программно —
 * setFormAction() меняет form.action и вызывает form.submit(). Программный
 * submit() события не порождает, так что проверка всё равно не работала,
 * зато обработчик вмешивался в чужие формы.
 *
 * Здесь другой подход: слушаем только change у файловых полей. Ни один
 * обработчик отправки не трогается, помешать сохранению документа скрипт
 * физически не может.
 *
 * ПОДКЛЮЧЕНИЕ: один раз в layout, перед </body>.
 */
(function () {
    'use strict';

    // Должно совпадать с upload_max_filesize в php.ini
    var MAX_FILE_MB = 30;

    var MB = 1024 * 1024;

    var messages = {
        ru: 'Файл «%s» весит %sМБ — это больше допустимых %sМБ. Он не загрузится.',
        uz: '«%s» fayli %sMB — ruxsat etilgan %sMB dan katta. U yuklanmaydi.',
        en: 'File "%s" is %sMB — larger than the allowed %sMB. It will not upload.'
    };

    function text() {
        var lang = (document.documentElement.lang || 'ru').substring(0, 2);
        return messages[lang] || messages.ru;
    }

    function format(template, name, size, limit) {
        return template
            .replace('%s', name)
            .replace('%s', size)
            .replace('%s', limit);
    }

    /** Сообщение тем способом, который есть на странице. */
    function warn(message) {
        if (window.toastr && typeof window.toastr.warning === 'function') {
            window.toastr.warning(message);
            return;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon: 'warning', text: message });
            return;
        }

        alert(message);
    }

    /**
     * Проверка в момент выбора файла.
     *
     * Делегирование на document: файловые поля добавляются динамически
     * (кнопка «+» в формах документов, AJAX-загрузка формы типа),
     * поэтому вешать обработчик на каждое поле при загрузке страницы нельзя.
     *
     * Фаза всплытия, без preventDefault и stopPropagation — чужие обработчики
     * change продолжают работать как прежде.
     */
    document.addEventListener('change', function (event) {
        var input = event.target;

        if (!input || input.type !== 'file' || !input.files) {
            return;
        }

        for (var i = 0; i < input.files.length; i++) {
            var file = input.files[i];

            if (file.size > MAX_FILE_MB * MB) {
                warn(format(
                    text(),
                    file.name,
                    (file.size / MB).toFixed(1),
                    MAX_FILE_MB
                ));

                // Поле не очищаем: решение оставляем пользователю.
                // Если он всё же отправит форму, сервер вернёт его назад
                // с сообщением — обработчик PostTooLargeException
                // в bootstrap/app.php.
                return;
            }
        }
    }, false);
})();
