/**
 * Словарь mini app: uz (lotin) / cyr (kirill) / ru / en.
 *
 * ЗАЧЕМ ЛОКАЛЬНО: эндпоинта /api/telegram/translations на бэкенде нет,
 * а в таблице language_lines отсутствуют и группа `mini.*`, и язык `cyr`.
 * Старый index.html это маскировал — тексты были захардкожены прямо
 * в разметке на узбекском, поэтому переключатель языка фактически
 * ничего не переводил. Здесь словарь полный, и язык переключается реально.
 *
 * Если позже появится эндпоинт — его ответ ПЕРЕКРОЕТ значения отсюда
 * (см. I18nProvider), так что переводы можно будет вынести в БД
 * без единого изменения в компонентах.
 */
export const DICTIONARY = {
    /* ─── Меню и категории ─── */
    'menu.home':             { uz: 'Bosh sahifa', cyr: 'Бош саҳифа', ru: 'Главная', en: 'Home' },
    'menu.for_resolution':   { uz: 'Tasdiqlash uchun', cyr: 'Тасдиқлаш учун', ru: 'Для подтверждения', en: 'To confirm' },
    'menu.for_sign':         { uz: 'Imzolash uchun', cyr: 'Имзолаш учун', ru: 'Для подписания', en: 'For signing' },
    'menu.for_coillaborate': { uz: 'Kelishish uchun', cyr: 'Келишиш учун', ru: 'Для согласования', en: 'For approval' },
    'menu.my_docs':          { uz: 'Mening hujjatlarim', cyr: 'Менинг ҳужжатларим', ru: 'Мои документы', en: 'My documents' },

    /* ─── Общие действия ─── */
    'action.back':      { uz: 'Orqaga', cyr: 'Орқага', ru: 'Назад', en: 'Back' },
    'action.save':      { uz: 'Saqlash', cyr: 'Сақлаш', ru: 'Сохранить', en: 'Save' },
    'action.load_more': { uz: 'Yana yuklash', cyr: 'Яна юклаш', ru: 'Загрузить ещё', en: 'Load more' },

    /* ─── Поля ─── */
    'field.doc_date':  { uz: 'Sana', cyr: 'Сана', ru: 'Дата', en: 'Date' },
    'field.doc_name':  { uz: 'Hujjat nomi', cyr: 'Ҳужжат номи', ru: 'Наименование документа', en: 'Document name' },
    'field.not_found': { uz: "Ma'lumot topilmadi", cyr: 'Маълумот топилмади', ru: 'Информация не найдена', en: 'Not found' },

    /* ─── Навигация и статусы приложения ─── */
    'mini.documents':      { uz: 'Hujjatlar', cyr: 'Ҳужжатлар', ru: 'Документы', en: 'Documents' },
    'mini.create':         { uz: 'Yaratish', cyr: 'Яратиш', ru: 'Создать', en: 'Create' },
    'mini.profile':        { uz: 'Profil', cyr: 'Профил', ru: 'Профиль', en: 'Profile' },
    'mini.employee_info':  { uz: "Xodim ma'lumotlari", cyr: 'Ходим маълумотлари', ru: 'Данные сотрудника', en: 'Employee info' },
    'mini.loading':        { uz: 'Yuklanmoqda...', cyr: 'Юкланмоқда...', ru: 'Загрузка...', en: 'Loading...' },
    'mini.retry':          { uz: 'Qayta urinish', cyr: 'Қайта уриниш', ru: 'Повторить', en: 'Retry' },
    'mini.all':            { uz: 'Barchasi', cyr: 'Барчаси', ru: 'Все', en: 'All' },
    'mini.filters_loading':{ uz: 'Filtrlar yuklanmoqda…', cyr: 'Филтрлар юкланмоқда…', ru: 'Загрузка фильтров…', en: 'Loading filters…' },
    'mini.cancel':         { uz: 'Bekor qilish', cyr: 'Бекор қилиш', ru: 'Отмена', en: 'Cancel' },
    'mini.delete':         { uz: "O'chirish", cyr: 'Ўчириш', ru: 'Удалить', en: 'Delete' },
    'mini.view':           { uz: "Ko'rish", cyr: 'Кўриш', ru: 'Просмотр', en: 'View' },

    /* ─── Пустые состояния ─── */
    'mini.no_documents':     { uz: "Hujjatlar yo'q", cyr: 'Ҳужжатлар йўқ', ru: 'Документов нет', en: 'No documents' },
    'mini.no_documents_sub': { uz: 'Bu kategoriyada hujjat topilmadi', cyr: 'Бу категорияда ҳужжат топилмади', ru: 'В этой категории документов не найдено', en: 'No documents in this category' },
    'mini.not_linked_title': { uz: "Akkaunt bog'lanmagan", cyr: 'Аккаунт боғланмаган', ru: 'Аккаунт не привязан', en: 'Account not linked' },
    'mini.not_linked_sub':   { uz: "Botga /start yuboring va akkauntingizni bog'lash uchun login va parolingizni kiriting", cyr: 'Ботга /start юборинг ва аккаунтингизни боғлаш учун логин ва паролингизни киритинг', ru: 'Отправьте боту /start и введите логин и пароль для привязки аккаунта', en: 'Send /start to the bot and enter your login and password to link your account' },

    /* ─── Ошибки ─── */
    'mini.error':               { uz: 'Xatolik', cyr: 'Хатолик', ru: 'Ошибка', en: 'Error' },
    'mini.err_generic':         { uz: 'Xatolik yuz berdi', cyr: 'Хатолик юз берди', ru: 'Произошла ошибка', en: 'An error occurred' },
    'mini.err_only_telegram':   { uz: 'Bu ilova faqat Telegram orqali ishlaydi', cyr: 'Бу илова фақат Telegram орқали ишлайди', ru: 'Приложение работает только через Telegram', en: 'This app works only inside Telegram' },
    'mini.err_auth':            { uz: 'Avtorizatsiya xatosi', cyr: 'Авторизация хатоси', ru: 'Ошибка авторизации', en: 'Authorization error' },
    'mini.err_connect':         { uz: "Serverga ulanib bo'lmadi", cyr: 'Серверга уланиб бўлмади', ru: 'Не удалось подключиться к серверу', en: 'Could not connect to the server' },
    'mini.err_load':            { uz: 'Yuklashda xatolik', cyr: 'Юклашда хатолик', ru: 'Ошибка загрузки', en: 'Loading error' },
    'mini.err_not_loaded':      { uz: "Ma'lumotlar yuklanmadi", cyr: 'Маълумотлар юкланмади', ru: 'Данные не загружены', en: 'Data not loaded' },
    'mini.err_open':            { uz: 'Ochishda xatolik', cyr: 'Очишда хатолик', ru: 'Ошибка при открытии', en: 'Error opening' },
    'mini.err_doc_open':        { uz: "Hujjatni ochib bo'lmadi", cyr: 'Ҳужжатни очиб бўлмади', ru: 'Не удалось открыть документ', en: 'Could not open the document' },
    'mini.err_profile_load':    { uz: 'Profil yuklanmadi', cyr: 'Профил юкланмади', ru: 'Профиль не загружен', en: 'Profile not loaded' },
    'mini.err_save':            { uz: 'Saqlashda xatolik', cyr: 'Сақлашда хатолик', ru: 'Ошибка сохранения', en: 'Error saving' },
    'mini.err_send':            { uz: 'Yuborishda xatolik', cyr: 'Юборишда хатолик', ru: 'Ошибка отправки', en: 'Error sending' },
    'mini.err_sign_url':        { uz: "Imzolash havolasini olib bo'lmadi", cyr: 'Имзолаш ҳаволасини олиб бўлмади', ru: 'Не удалось получить ссылку для подписи', en: 'Could not get the signing link' },
    'mini.err_upload':          { uz: 'Faylni yuklashda xatolik', cyr: 'Файлни юклашда хатолик', ru: 'Ошибка загрузки файла', en: 'Upload error' },
    'mini.err_init':            { uz: "Ma'lumotlar yuklanmadi", cyr: 'Маълумотлар юкланмади', ru: 'Справочники не загружены', en: 'Could not load reference data' },
    'mini.err_fill_all':        { uz: "Barcha maydonlarni to'ldiring", cyr: 'Барча майдонларни тўлдиринг', ru: 'Заполните все поля', en: 'Fill in all fields' },
    'mini.err_pw_short':        { uz: "Parol kamida 6 ta belgidan iborat bo'lsin", cyr: 'Парол камида 6 та белгидан иборат бўлсин', ru: 'Пароль минимум 6 символов', en: 'Password must be at least 6 characters' },
    'mini.err_pw_mismatch':     { uz: 'Parollar mos kelmadi', cyr: 'Пароллар мос келмади', ru: 'Пароли не совпадают', en: 'Passwords do not match' },
    'mini.err_avatar_too_big':  { uz: 'Rasm hajmi 5 MB dan oshmasin', cyr: 'Расм ҳажми 5 MB дан ошмасин', ru: 'Размер изображения не более 5 МБ', en: 'Image must be under 5 MB' },
    'mini.err_file_too_big':    { uz: 'Fayl hajmi 50 MB dan oshmasin', cyr: 'Файл ҳажми 50 MB дан ошмасин', ru: 'Размер файла не более 50 МБ', en: 'File must be under 50 MB' },
    'mini.err_only_doc':        { uz: 'Faqat .doc yoki .docx fayl', cyr: 'Фақат .doc ёки .docx файл', ru: 'Только .doc или .docx', en: 'Only .doc or .docx files' },
    'mini.err_select_journal':  { uz: 'Jurnal turini tanlang', cyr: 'Журнал турини танланг', ru: 'Выберите тип журнала', en: 'Select the journal type' },
    'mini.err_select_journal2': { uz: 'Jurnal turini tanlang', cyr: 'Журнал турини танланг', ru: 'Выберите тип журнала', en: 'Select the journal type' },
    'mini.err_select_resol':    { uz: 'Rezolyutsiya beruvchini tanlang', cyr: 'Резолюция берувчини танланг', ru: 'Выберите резолютора', en: 'Select the resolution author' },
    'mini.pw_changed':          { uz: 'Parol yangilandi', cyr: 'Парол янгиланди', ru: 'Пароль обновлён', en: 'Password updated' },

    /* ─── Карточка документа ─── */
    'mini.doc_info':   { uz: "Hujjat haqida ma'lumot", cyr: 'Ҳужжат ҳақида маълумот', ru: 'Информация о документе', en: 'Document info' },
    'mini.doc_type':   { uz: 'Hujjat turi', cyr: 'Ҳужжат тури', ru: 'Тип документа', en: 'Document type' },
    'mini.doc_view':   { uz: 'Hujjat', cyr: 'Ҳужжат', ru: 'Документ', en: 'Document' },
    'mini.doc_text':   { uz: 'Hujjat matni', cyr: 'Ҳужжат матни', ru: 'Текст документа', en: 'Document text' },
    'mini.files':      { uz: 'Fayllar', cyr: 'Файллар', ru: 'Файлы', en: 'Files' },
    'mini.actions':    { uz: 'Amallar', cyr: 'Амаллар', ru: 'Действия', en: 'Actions' },
    'mini.signed_by':  { uz: 'Kim imzolagan', cyr: 'Ким имзолаган', ru: 'Кто подписал', en: 'Signed by' },
    'mini.signed_at':  { uz: 'Imzolangan sana', cyr: 'Имзоланган сана', ru: 'Дата подписания', en: 'Signed at' },
    'mini.creator':    { uz: 'Yaratuvchi', cyr: 'Яратувчи', ru: 'Создатель', en: 'Created by' },
    'mini.sign_eimzo': { uz: 'E-IMZO ID-CARD bilan imzolash', cyr: 'E-IMZO ID-CARD билан имзолаш', ru: 'Подписать через E-IMZO ID-CARD', en: 'Sign with E-IMZO ID-CARD' },

    'mini.delete_doc_title': { uz: "Hujjatni o'chirish", cyr: 'Ҳужжатни ўчириш', ru: 'Удаление документа', en: 'Delete document' },
    'mini.delete_doc_text':  { uz: "Bu hujjat o'chiriladi va qaytarib bo'lmaydi. Davom etasizmi?", cyr: 'Бу ҳужжат ўчирилади ва қайтариб бўлмайди. Давом этасизми?', ru: 'Документ будет удалён безвозвратно. Продолжить?', en: 'This document will be permanently deleted. Continue?' },
    'mini.delete_avatar_text': { uz: "Profil rasmi o'chirilsinmi?", cyr: 'Профил расми ўчирилсинми?', ru: 'Удалить фото профиля?', en: 'Delete the profile photo?' },

    /* ─── Фильтры «Мои документы» ─── */
    'mini.st_new':      { uz: 'Yangi', cyr: 'Янги', ru: 'Новый', en: 'New' },
    'mini.st_in_resol': { uz: 'Rezolyutsiyada', cyr: 'Резолюцияда', ru: 'На резолюции', en: 'In resolution' },
    'mini.st_signed':   { uz: 'Imzolangan', cyr: 'Имзоланган', ru: 'Подписан', en: 'Signed' },
    'mini.st_overdue':  { uz: "Muddati o'tgan", cyr: 'Муддати ўтган', ru: 'Просрочен', en: 'Overdue' },
    'mini.st_pending':  { uz: 'Kutilmoqda', cyr: 'Кутилмоқда', ru: 'Ожидает', en: 'Pending' },

    /* ─── Профиль ─── */
    'mini.main_info':         { uz: "Asosiy ma'lumotlar", cyr: 'Асосий маълумотлар', ru: 'Основные данные', en: 'Main info' },
    'mini.account_settings':  { uz: 'Akkaunt sozlamalari', cyr: 'Аккаунт созламалари', ru: 'Настройки аккаунта', en: 'Account settings' },
    'mini.my_roles':          { uz: 'Mening faol rollarim', cyr: 'Менинг фаол ролларим', ru: 'Мои активные роли', en: 'My active roles' },
    'mini.no_roles':          { uz: "Rollar yo'q", cyr: 'Роллар йўқ', ru: 'Ролей нет', en: 'No roles' },
    'mini.hr_orders':         { uz: 'Xodim buyruqlari', cyr: 'Ходим буйруқлари', ru: 'Кадровые приказы', en: 'HR orders' },
    'mini.work_experience':   { uz: 'Ish tajribasi', cyr: 'Иш тажрибаси', ru: 'Опыт работы', en: 'Work experience' },
    'mini.fish':              { uz: 'F.I.SH.', cyr: 'Ф.И.Ш.', ru: 'Ф.И.О.', en: 'Full name' },
    'mini.org':               { uz: 'Tashkilot', cyr: 'Ташкилот', ru: 'Организация', en: 'Organization' },
    'mini.department':        { uz: "Bo'lim", cyr: 'Бўлим', ru: 'Отдел', en: 'Department' },
    'mini.position':          { uz: 'Lavozim', cyr: 'Лавозим', ru: 'Должность', en: 'Position' },
    'mini.email':             { uz: 'Email', cyr: 'Email', ru: 'Email', en: 'Email' },
    'mini.login':             { uz: 'Login', cyr: 'Логин', ru: 'Логин', en: 'Login' },
    'mini.phone':             { uz: 'Telefon', cyr: 'Телефон', ru: 'Телефон', en: 'Phone' },
    'mini.profile_photo':     { uz: 'Profil rasmi', cyr: 'Профил расми', ru: 'Фото профиля', en: 'Profile photo' },
    'mini.upload':            { uz: 'Yuklash', cyr: 'Юклаш', ru: 'Загрузить', en: 'Upload' },
    'mini.current_password':  { uz: 'Joriy parol', cyr: 'Жорий парол', ru: 'Текущий пароль', en: 'Current password' },
    'mini.new_password':      { uz: 'Yangi parol', cyr: 'Янги парол', ru: 'Новый пароль', en: 'New password' },
    'mini.confirm_password':  { uz: 'Yangi parolni tasdiqlang', cyr: 'Янги паролни тасдиқланг', ru: 'Подтвердите новый пароль', en: 'Confirm new password' },
    'mini.language':          { uz: 'Til', cyr: 'Тил', ru: 'Язык', en: 'Language' },
    'mini.theme':             { uz: 'Mavzu', cyr: 'Мавзу', ru: 'Тема', en: 'Theme' },
    'mini.theme_auto':        { uz: 'Avto', cyr: 'Авто', ru: 'Авто', en: 'Auto' },
    'mini.theme_light':       { uz: "Yorug'", cyr: 'Ёруғ', ru: 'Светлая', en: 'Light' },
    'mini.theme_dark':        { uz: "Qorong'i", cyr: 'Қоронғи', ru: 'Тёмная', en: 'Dark' },

    /* ─── Колонки таблиц профиля ─── */
    'mini.col_type':          { uz: 'Turi', cyr: 'Тури', ru: 'Тип', en: 'Type' },
    'mini.col_order_name':    { uz: 'Buyruq nomi', cyr: 'Буйруқ номи', ru: 'Наименование приказа', en: 'Order name' },
    'mini.col_number':        { uz: 'Raqami', cyr: 'Рақами', ru: 'Номер', en: 'Number' },
    'mini.col_date_from':     { uz: 'Boshlanish sanasi', cyr: 'Бошланиш санаси', ru: 'Дата начала', en: 'Start date' },
    'mini.col_date_to':       { uz: 'Tugash sanasi', cyr: 'Тугаш санаси', ru: 'Дата окончания', en: 'End date' },
    'mini.col_work_place':    { uz: 'Ish joyi', cyr: 'Иш жойи', ru: 'Место работы', en: 'Workplace' },
    'mini.col_position':      { uz: 'Lavozim', cyr: 'Лавозим', ru: 'Должность', en: 'Position' },
    'mini.col_position_desc': { uz: 'Lavozim tavsifi', cyr: 'Лавозим тавсифи', ru: 'Описание должности', en: 'Position description' },

    /* ─── Мастер создания: выбор типа ─── */
    'mini.create_title':        { uz: 'Hujjat turi', cyr: 'Ҳужжат тури', ru: 'Тип документа', en: 'Document type' },
    'mini.create_subtitle':     { uz: "Yaratmoqchi bo'lgan hujjatingiz turini tanlang", cyr: 'Яратмоқчи бўлган ҳужжатингиз турини танланг', ru: 'Выберите тип создаваемого документа', en: 'Choose the type of document to create' },
    'mini.type_internal':       { uz: 'Ichki hujjat', cyr: 'Ички ҳужжат', ru: 'Внутренний документ', en: 'Internal document' },
    'mini.type_internal_sub':   { uz: 'Tashkilot ichida tarqatish uchun', cyr: 'Ташкилот ичида тарқатиш учун', ru: 'Для рассылки внутри организации', en: 'For distribution inside the organization' },
    'mini.type_outgoing':       { uz: 'Chiquvchi hujjat', cyr: 'Чиқувчи ҳужжат', ru: 'Исходящий документ', en: 'Outgoing document' },
    'mini.type_outgoing_sub':   { uz: 'Tashqi tashkilotga yuboriladigan', cyr: 'Ташқи ташкилотга юбориладиган', ru: 'Отправляемый во внешнюю организацию', en: 'Sent to an external organization' },
    'mini.type_incoming':       { uz: 'Kiruvchi hujjat', cyr: 'Кирувчи ҳужжат', ru: 'Входящий документ', en: 'Incoming document' },
    'mini.type_incoming_sub':   { uz: 'Tashqi tashkilotdan keldi', cyr: 'Ташқи ташкилотдан келди', ru: 'Поступивший из внешней организации', en: 'Received from an external organization' },

    /* ─── Мастер создания: форма ─── */
    'mini.form_title_internal': { uz: "Ichki hujjat ma'lumotlari", cyr: 'Ички ҳужжат маълумотлари', ru: 'Данные внутреннего документа', en: 'Internal document details' },
    'mini.form_title_outgoing': { uz: "Chiquvchi hujjat ma'lumotlari", cyr: 'Чиқувчи ҳужжат маълумотлари', ru: 'Данные исходящего документа', en: 'Outgoing document details' },
    'mini.form_title_incoming': { uz: "Kiruvchi hujjat ma'lumotlari", cyr: 'Кирувчи ҳужжат маълумотлари', ru: 'Данные входящего документа', en: 'Incoming document details' },
    'mini.form_subtitle':       { uz: "Maydonlarni to'ldiring", cyr: 'Майдонларни тўлдиринг', ru: 'Заполните поля', en: 'Fill in the fields' },
    'mini.select_placeholder':  { uz: '— Tanlang —', cyr: '— Танланг —', ru: '— Выберите —', en: '— Select —' },

    'mini.f_journal':      { uz: 'Jurnal turi', cyr: 'Журнал тури', ru: 'Тип журнала', en: 'Journal type' },
    'mini.f_journal2':     { uz: 'Jurnal pastki turi', cyr: 'Журнал пастки тури', ru: 'Подтип журнала', en: 'Journal subtype' },
    'mini.f_doc_date':     { uz: 'Hujjat sanasi', cyr: 'Ҳужжат санаси', ru: 'Дата документа', en: 'Document date' },
    'mini.f_doc_name':     { uz: 'Hujjat nomi', cyr: 'Ҳужжат номи', ru: 'Наименование документа', en: 'Document name' },
    'mini.f_doc_name_ph':  { uz: 'Hujjat sarlavhasi', cyr: 'Ҳужжат сарлавҳаси', ru: 'Заголовок документа', en: 'Document title' },
    'mini.f_doc_number':   { uz: 'Hujjat raqami', cyr: 'Ҳужжат рақами', ru: 'Номер документа', en: 'Document number' },
    'mini.f_confidence':   { uz: 'Maxfiylik', cyr: 'Махфийлик', ru: 'Конфиденциальность', en: 'Confidentiality' },
    'mini.f_lang':         { uz: 'Til', cyr: 'Тил', ru: 'Язык', en: 'Language' },
    'mini.f_signing_term': { uz: 'Imzolash muddati', cyr: 'Имзолаш муддати', ru: 'Срок подписания', en: 'Signing deadline' },
    'mini.f_signing_term_ph': { uz: 'DD.MM.YYYY yoki kun soni (masalan 7)', cyr: 'DD.MM.YYYY ёки кун сони (масалан 7)', ru: 'ДД.ММ.ГГГГ или число дней (например 7)', en: 'DD.MM.YYYY or number of days (e.g. 7)' },
    'mini.f_doc_text':     { uz: 'Hujjat matni', cyr: 'Ҳужжат матни', ru: 'Текст документа', en: 'Document text' },
    'mini.f_doc_text_ph':  { uz: 'Qisqacha izoh', cyr: 'Қисқача изоҳ', ru: 'Краткое описание', en: 'Short description' },

    'mini.f_main_file':    { uz: 'Asosiy fayl (.doc, .docx)', cyr: 'Асосий файл (.doc, .docx)', ru: 'Основной файл (.doc, .docx)', en: 'Main file (.doc, .docx)' },
    'mini.f_select_file':  { uz: 'Faylni tanlang', cyr: 'Файлни танланг', ru: 'Выберите файл', en: 'Select a file' },
    'mini.f_file_hint':    { uz: 'Yoki "Kompyuterda ochish" tugmasi orqali web editor', cyr: 'Ёки "Компьютерда очиш" тугмаси орқали web editor', ru: 'Или через кнопку «Открыть на компьютере» — веб-редактор', en: 'Or use "Open on computer" for the web editor' },
    'mini.f_extra_files':  { uz: "Qo'shimcha fayllar (ixtiyoriy)", cyr: 'Қўшимча файллар (ихтиёрий)', ru: 'Дополнительные файлы (необязательно)', en: 'Additional files (optional)' },
    'mini.f_add_file':     { uz: "Fayl qo'shish", cyr: 'Файл қўшиш', ru: 'Добавить файл', en: 'Add a file' },
    'mini.f_files_hint':   { uz: 'PDF, DOCX, XLSX va h.k.', cyr: 'PDF, DOCX, XLSX ва ҳ.к.', ru: 'PDF, DOCX, XLSX и т.д.', en: 'PDF, DOCX, XLSX etc.' },

    'mini.f_resol_user':    { uz: 'Rezolyutsiya beruvchi', cyr: 'Резолюция берувчи', ru: 'Резолютор', en: 'Resolution author' },
    'mini.f_signatory':     { uz: 'Imzolovchi', cyr: 'Имзоловчи', ru: 'Подписант', en: 'Signatory' },
    'mini.f_agreement':     { uz: 'Kelishish (bir nechta tanlash mumkin)', cyr: 'Келишиш (бир нечта танлаш мумкин)', ru: 'Согласование (можно выбрать несколько)', en: 'Approval (multiple choice)' },
    'mini.f_agreement_add': { uz: "— Qo'shish uchun tanlang —", cyr: '— Қўшиш учун танланг —', ru: '— Выберите, чтобы добавить —', en: '— Select to add —' },
    'mini.f_org_from':      { uz: 'Yuboruvchi tashkilot', cyr: 'Юборувчи ташкилот', ru: 'Организация-отправитель', en: 'Sending organization' },
    'mini.f_org_to':        { uz: 'Qabul qiluvchi tashkilot', cyr: 'Қабул қилувчи ташкилот', ru: 'Организация-получатель', en: 'Receiving organization' },

    'mini.no_resol_users':  { uz: "Bu jurnal turi uchun rezolyutsiya beruvchi xodimlar yo'q. Avval rollarni sozlang.", cyr: 'Бу журнал тури учун резолюция берувчи ходимлар йўқ. Аввал ролларни созланг.', ru: 'Для этого типа журнала нет сотрудников с правом резолюции. Сначала настройте роли.', en: 'No resolution authors for this journal type. Configure the roles first.' },
    'mini.no_subscription': { uz: 'Hujjat yaratish uchun obuna kerak. Saytdan obunani tekshiring.', cyr: 'Ҳужжат яратиш учун обуна керак. Сайтдан обунани текширинг.', ru: 'Для создания документа нужна подписка. Проверьте подписку на сайте.', en: 'A subscription is required to create documents. Check it on the website.' },
    'mini.saving':          { uz: 'Saqlanmoqda...', cyr: 'Сақланмоқда...', ru: 'Сохранение...', en: 'Saving...' },
    'mini.created_title':   { uz: 'Hujjat yaratildi!', cyr: 'Ҳужжат яратилди!', ru: 'Документ создан!', en: 'Document created!' },

    'mini.web_editor_title':   { uz: 'Yoki Web editor orqali', cyr: 'Ёки Web editor орқали', ru: 'Или через веб-редактор', en: 'Or use the web editor' },
    'mini.web_editor_sub':     { uz: "Maydonlarni shu yerda to'ldiring va kompyuterda OnlyOffice editorda davom eting", cyr: 'Майдонларни шу ерда тўлдиринг ва компьютерда OnlyOffice едиторда давом этинг', ru: 'Заполните поля здесь и продолжите в редакторе OnlyOffice на компьютере', en: 'Fill in the fields here and continue in the OnlyOffice editor on your computer' },
    'mini.open_on_computer':   { uz: 'Kompyuterda ochish', cyr: 'Компьютерда очиш', ru: 'Открыть на компьютере', en: 'Open on computer' },
};
