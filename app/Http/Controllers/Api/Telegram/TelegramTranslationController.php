<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\TranslationLoader\LanguageLine;

class TelegramTranslationController extends Controller
{
    /**
     * Ключи UI, нужные Mini App. group.key из language_lines.
     * Для отсутствующих в БД — fallback в $fallbacks ниже.
     */
    private array $keys = [
        // Categories / menu
        'menu.for_resolution', 'menu.for_sign', 'menu.for_coillaborate', 'menu.my_docs',
        // Fields
        'field.status', 'field.document', 'field.doc_name', 'field.doc_date',
        'field.creator', 'field.created_at', 'field.not_found', 'field.signing_term',
        // Actions
        'action.search', 'action.clear', 'action.delete', 'action.edit',
        // Selects
        'select.status_all',
        // Document
        'document.document', 'document.doc_name',
    ];

    /**
     * Fallback для ключей, которых нет в language_lines.
     * Формат: [uz, ru, en]
     */
    private array $fallbacks = [
        'menu.home'              => ['Bosh sahifa', 'Главная', 'Home'],
        'action.filters'         => ['Filtrlar', 'Фильтры', 'Filters'],
        'action.back'            => ['Orqaga', 'Назад', 'Back'],
        'action.save'            => ['Saqlash', 'Сохранить', 'Save'],
        'action.load_more'       => ['Yana yuklash', 'Загрузить ещё', 'Load more'],
        'action.send_to_sign'    => ['Imzolovchiga yuborish', 'Отправить на подпись', 'Send to signer'],
        'action.send_to_ag'      => ['Kelishishga yuborish', 'Отправить на согласование', 'Send to approval'],
        'action.send_for_sig'    => ['Imzolash uchun yuborish', 'Отправить на подпись', 'Send for signature'],
        'mini.profile'           => ['Profil', 'Профиль', 'Profile'],
        'mini.documents'         => ['Hujjatlar', 'Документы', 'Documents'],
        'mini.main_info'         => ["Asosiy ma'lumotlar", 'Основная информация', 'Main info'],
        'mini.account_settings'  => ['Akkaunt sozlamalari', 'Настройки аккаунта', 'Account settings'],
        'mini.my_roles'          => ['Mening rollarim', 'Мои роли', 'My roles'],
        'mini.hr_orders'         => ['Xodim buyruqlari', 'Кадровые приказы', 'HR orders'],
        'mini.work_experience'   => ['Ish tajribasi', 'Опыт работы', 'Work experience'],
        'mini.theme'             => ['Tema', 'Тема', 'Theme'],
        'mini.theme_auto'        => ['Avto', 'Авто', 'Auto'],
        'mini.theme_light'       => ["Yorug'", 'Светлая', 'Light'],
        'mini.theme_dark'        => ["Qorong'i", 'Тёмная', 'Dark'],
        'mini.language'          => ['Til', 'Язык', 'Language'],
        'mini.org'               => ['Tashkilot', 'Организация', 'Organization'],
        'mini.department'        => ["Bo'lim", 'Отдел', 'Department'],
        'mini.position'          => ['Lavozim', 'Должность', 'Position'],
        'mini.email'             => ['Email', 'Email', 'Email'],
        'mini.login'             => ['Login', 'Логин', 'Login'],
        'mini.phone'             => ['Telefon', 'Телефон', 'Phone'],
        'mini.fish'              => ['F.I.SH.', 'Ф.И.О.', 'Full name'],
        'mini.current_password'  => ['Joriy parol', 'Текущий пароль', 'Current password'],
        'mini.new_password'      => ['Yangi parol', 'Новый пароль', 'New password'],
        'mini.confirm_password'  => ['Parolni tasdiqlang', 'Подтвердите пароль', 'Confirm password'],
        'mini.upload'            => ['Yuklash', 'Загрузить', 'Upload'],
        'mini.delete'            => ["O'chirish", 'Удалить', 'Delete'],
        'mini.profile_photo'     => ['Profil rasmi', 'Фото профиля', 'Profile photo'],
        'mini.actions'           => ['Amallar', 'Действия', 'Actions'],
        'mini.doc_text'          => ['Hujjat matni', 'Текст документа', 'Document text'],
        'mini.files'             => ['Fayllar', 'Файлы', 'Files'],
        'mini.no_documents'      => ['Hujjatlar topilmadi', 'Документы не найдены', 'No documents'],
        'mini.no_documents_sub'  => ['Tanlangan filtr bo\'yicha hujjat topilmadi', 'По выбранному фильтру документы не найдены', 'No documents for selected filter'],
        'mini.sign_eimzo'        => ['E-IMZO ID-CARD bilan imzolash', 'Подписать через E-IMZO ID-CARD', 'Sign with E-IMZO ID-CARD'],
        'mini.confirm_delete'    => ["Hujjatni o'chirish", 'Удалить документ', 'Delete document'],
        'mini.confirm_delete_sub'=> ['Bu amalni qaytarib bo\'lmaydi. Davom etasizmi?', 'Это действие необратимо. Продолжить?', 'This cannot be undone. Continue?'],
        'mini.cancel'            => ['Bekor qilish', 'Отмена', 'Cancel'],
        'mini.all'               => ['Barchasi', 'Все', 'All'],
        'mini.loading'           => ['Yuklanmoqda...', 'Загрузка...', 'Loading...'],
        'mini.error'             => ['Xatolik', 'Ошибка', 'Error'],
        // Alerts / validation
        'mini.err_select_journal' => ['Avval jurnal turini tanlang', 'Сначала выберите тип журнала', 'Select journal type first'],
        'mini.err_select_journal2'=> ['Jurnal turini tanlang', 'Выберите тип журнала', 'Select journal type'],
        'mini.err_only_doc'       => ['Faqat .doc yoki .docx fayllar ruxsat etilgan', 'Разрешены только файлы .doc или .docx', 'Only .doc or .docx files allowed'],
        'mini.err_file_too_big'   => ["Fayl hajmi 50MB dan katta bo'lmasligi kerak", 'Размер файла не должен превышать 50MB', 'File must not exceed 50MB'],
        'mini.err_avatar_too_big' => ["Faylning hajmi 5MB dan kichik bo'lishi kerak", 'Размер файла должен быть меньше 5MB', 'File must be smaller than 5MB'],
        'mini.err_select_resol'   => ['Rezolyutsiya beruvchini tanlang', 'Выберите автора резолюции', 'Select resolution author'],
        'mini.err_fill_all'       => ["Barcha maydonlarni to'ldiring", 'Заполните все поля', 'Fill in all fields'],
        'mini.err_pw_short'       => ["Yangi parol kamida 6 ta belgi bo'lishi kerak", 'Новый пароль минимум 6 символов', 'New password must be at least 6 characters'],
        'mini.err_pw_mismatch'    => ['Yangi parollar mos kelmadi', 'Новые пароли не совпадают', 'New passwords do not match'],
        // Fallback errors
        'mini.err_auth'           => ['Auth xatosi', 'Ошибка авторизации', 'Auth error'],
        'mini.err_doc_open'       => ['Hujjat ochilmadi', 'Не удалось открыть документ', 'Failed to open document'],
        'mini.err_init'           => ['Init xatosi', 'Ошибка инициализации', 'Init error'],
        'mini.err_open'           => ['Ochishda xatolik', 'Ошибка при открытии', 'Error opening'],
        'mini.err_profile_load'   => ['Profil yuklanmadi', 'Профиль не загружен', 'Profile not loaded'],
        'mini.err_save'           => ['Saqlashda xatolik', 'Ошибка при сохранении', 'Save error'],
        'mini.err_sign_url'       => ['Sign URL olinmadi', 'Не удалось получить URL подписи', 'Failed to get sign URL'],
        'mini.err_connect'        => ['Ulanishda xatolik', 'Ошибка подключения', 'Connection error'],
        'mini.err_send'           => ['Yuborishda xatolik', 'Ошибка при отправке', 'Send error'],
        'mini.err_load'           => ['Yuklashda xatolik', 'Ошибка загрузки', 'Loading error'],
        'mini.err_upload'         => ['Yuklash xatosi', 'Ошибка загрузки файла', 'Upload error'],
        'mini.err_not_loaded'     => ['Yuklanmadi', 'Не загружено', 'Not loaded'],
        'mini.err_only_telegram'  => ['Bu sahifa faqat Telegram ichida ishlaydi.', 'Эта страница работает только внутри Telegram.', 'This page works only inside Telegram.'],
        'mini.err_generic'        => ['Xatolik yuz berdi', 'Произошла ошибка', 'An error occurred'],
        // Misc UI
        'mini.retry'              => ['Qayta urinish', 'Повторить', 'Retry'],
        'mini.saving'            => ['Saqlanmoqda...', 'Сохранение...', 'Saving...'],
        'mini.open_in_pc'        => ['Kompyuterda ochish', 'Открыть на компьютере', 'Open on computer'],
        'mini.pw_changed'        => ["Parol o'zgartirildi", 'Пароль изменён', 'Password changed'],
        'mini.choose_file'       => ['Faylni tanlang', 'Выберите файл', 'Choose file'],
        'mini.add_file'          => ["Fayl qo'shish", 'Добавить файл', 'Add file'],
        'mini.select_placeholder'=> ['— Tanlang —', '— Выберите —', '— Select —'],
        'mini.not_linked_title'  => ["Akkaunt bog'lanmagan", 'Аккаунт не привязан', 'Account not linked'],
        'mini.not_linked_sub'    => ["Botga /start yuboring va akkauntingizni bog'lash uchun login va parolingizni kiriting", 'Отправьте боту /start и введите логин и пароль для привязки', 'Send /start to the bot and enter your login and password to link'],
        'mini.doc_created'       => ['Hujjat yaratildi!', 'Документ создан!', 'Document created!'],
        // Profile table columns
        'mini.col_type'          => ['Turi', 'Тип', 'Type'],
        'mini.col_order_name'    => ['Buyruq nomi', 'Название приказа', 'Order name'],
        'mini.col_number'        => ['Raqami', 'Номер', 'Number'],
        'mini.col_date_from'     => ['Boshlanish sanasi', 'Дата начала', 'Start date'],
        'mini.col_date_to'       => ['Tugash sanasi', 'Дата окончания', 'End date'],
        'mini.col_work_place'    => ['Ish joyi', 'Место работы', 'Workplace'],
        'mini.col_position'      => ['Lavozimi', 'Должность', 'Position'],
        'mini.col_position_desc' => ['Lavozim tavsifi', 'Описание должности', 'Position description'],
        'mini.no_roles'          => ["Faol rollar yo'q", 'Нет активных ролей', 'No active roles'],
        // Wizard / create
        'mini.doc_type'          => ['Hujjat turi', 'Тип документа', 'Document type'],
        'mini.choose_doc_type'   => ["Yaratmoqchi bo'lgan hujjatingiz turini tanlang", 'Выберите тип создаваемого документа', 'Choose the type of document to create'],
        'mini.type_internal'     => ['Ichki hujjat', 'Внутренний документ', 'Internal document'],
        'mini.type_internal_sub' => ['Tashkilot ichida tarqatish uchun', 'Для распространения внутри организации', 'For distribution within organization'],
        'mini.type_outgoing'     => ['Chiquvchi hujjat', 'Исходящий документ', 'Outgoing document'],
        'mini.type_outgoing_sub' => ['Tashqi tashkilotga yuboriladigan', 'Отправляется во внешнюю организацию', 'Sent to external organization'],
        'mini.type_incoming'     => ['Kiruvchi hujjat', 'Входящий документ', 'Incoming document'],
        'mini.type_incoming_sub' => ['Tashqi tashkilotdan keldi', 'Поступил из внешней организации', 'Received from external organization'],
        'mini.create'            => ['Yaratish', 'Создать', 'Create'],
        // My-docs status filter pills
        'mini.st_new'            => ['Yangi', 'Новый', 'New'],
        'mini.st_in_resol'       => ['Rezolyutsiyada', 'На резолюции', 'In resolution'],
        'mini.st_signed'         => ['Imzolangan', 'Подписан', 'Signed'],
        'mini.st_overdue'        => ["Muddati o'tgan", 'Просрочен', 'Overdue'],
    ];

    public function index(Request $request): JsonResponse
    {
        // Pull all needed DB-backed lines
        $lines = LanguageLine::whereIn('group', ['menu', 'field', 'action', 'select', 'document'])
            ->get();

        $byKey = [];
        foreach ($lines as $line) {
            $byKey[$line->group . '.' . $line->key] = $line->text; // text is array casted by Spatie
        }

        // Build result: { key: { uz, cyr, ru, en } }
        $out = [];

        foreach ($this->keys as $k) {
            $txt = $byKey[$k] ?? null;
            if (is_string($txt)) {
                $txt = json_decode($txt, true);
            }
            if (!is_array($txt)) {
                $txt = ['uz' => $k, 'ru' => $k, 'en' => $k];
            }
            $uz = $txt['uz'] ?? $k;
            $out[$k] = [
                'uz'  => $uz,
                'cyr' => $this->toCyrillic($uz),
                'ru'  => $txt['ru'] ?? $uz,
                'en'  => $txt['en'] ?? $uz,
            ];
        }

        // Add fallbacks (these always win for mini.* keys not in DB)
        foreach ($this->fallbacks as $k => $vals) {
            [$uz, $ru, $en] = $vals;
            $out[$k] = [
                'uz'  => $uz,
                'cyr' => $this->toCyrillic($uz),
                'ru'  => $ru,
                'en'  => $en,
            ];
        }

        return response()->json(['translations' => $out]);
    }

    /**
     * Транслитерация узбекской латиницы → кириллица.
     * Покрывает специальные диграфы и буквы узбекского алфавита.
     */
    public function toCyrillic(string $text): string
    {
        // Порядок важен: сначала диграфы и спец-сочетания
        $map = [
            // Digraphs (lowercase)
            "o'" => 'ў', "g'" => 'ғ', "sh" => 'ш', "ch" => 'ч', "ng" => 'нг',
            "yo" => 'ё', "yu" => 'ю', "ya" => 'я', "ye" => 'е', "ts" => 'ц',
            // Digraphs (capitalized)
            "O'" => 'Ў', "G'" => 'Ғ', "Sh" => 'Ш', "Ch" => 'Ч', "Ng" => 'Нг',
            "Yo" => 'Ё', "Yu" => 'Ю', "Ya" => 'Я', "Ye" => 'Е', "Ts" => 'Ц',
            // ё/ю/я uppercase fully
            "SH" => 'Ш', "CH" => 'Ч', "YO" => 'Ё', "YU" => 'Ю', "YA" => 'Я',
            // Single letters lowercase
            'a' => 'а', 'b' => 'б', 'd' => 'д', 'e' => 'е', 'f' => 'ф',
            'g' => 'г', 'h' => 'ҳ', 'i' => 'и', 'j' => 'ж', 'k' => 'к',
            'l' => 'л', 'm' => 'м', 'n' => 'н', 'o' => 'о', 'p' => 'п',
            'q' => 'қ', 'r' => 'р', 's' => 'с', 't' => 'т', 'u' => 'у',
            'v' => 'в', 'x' => 'х', 'y' => 'й', 'z' => 'з',
            // Single letters uppercase
            'A' => 'А', 'B' => 'Б', 'D' => 'Д', 'E' => 'Е', 'F' => 'Ф',
            'G' => 'Г', 'H' => 'Ҳ', 'I' => 'И', 'J' => 'Ж', 'K' => 'К',
            'L' => 'Л', 'M' => 'М', 'N' => 'Н', 'O' => 'О', 'P' => 'П',
            'Q' => 'Қ', 'R' => 'Р', 'S' => 'С', 'T' => 'Т', 'U' => 'У',
            'V' => 'В', 'X' => 'Х', 'Y' => 'Й', 'Z' => 'З',
            // Apostrophe (tutuq belgisi) → ъ
            "'" => 'ъ', "`" => 'ъ', "ʼ" => 'ъ',
        ];

        // strtr applies longest-match-first automatically for multi-char keys
        return strtr($text, $map);
    }
}
