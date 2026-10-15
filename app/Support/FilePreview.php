<?php

namespace App\Support;

/**
 * Как показывать вложение по его расширению.
 *
 * Один справочник на весь проект: им пользуется и FileController (какой
 * Content-Type отдать), и шаблон просмотра (какой виджет нарисовать).
 * Раньше эти решения были продублированы в полутора десятках blade-файлов
 * через @switch, и любой тип кроме pdf/jpg/png просто проваливался мимо.
 */
class FilePreview
{
    /** Картинка — рисуется тегом <img>. */
    public const IMAGE = 'image';

    /** PDF — встроенный просмотрщик браузера в <iframe>. */
    public const PDF = 'pdf';

    /** Офисный документ — OnlyOffice в режиме чтения. */
    public const OFFICE = 'office';

    /** Текст — показывается как есть в <pre>. */
    public const TEXT = 'text';

    public const AUDIO = 'audio';
    public const VIDEO = 'video';

    /** Просмотр невозможен — только карточка со скачиванием. */
    public const NONE = 'none';

    /**
     * расширение => [вид просмотра, MIME-тип]
     */
    private const MAP = [
        /* ─── Картинки ─── */
        'jpg'  => [self::IMAGE, 'image/jpeg'],
        'jpeg' => [self::IMAGE, 'image/jpeg'],
        'png'  => [self::IMAGE, 'image/png'],
        'gif'  => [self::IMAGE, 'image/gif'],
        'webp' => [self::IMAGE, 'image/webp'],
        'bmp'  => [self::IMAGE, 'image/bmp'],
        'tif'  => [self::IMAGE, 'image/tiff'],
        'tiff' => [self::IMAGE, 'image/tiff'],
        'heic' => [self::IMAGE, 'image/heic'],
        // SVG отдаётся с запретом скриптов — см. isSandboxed()
        'svg'  => [self::IMAGE, 'image/svg+xml'],

        /* ─── PDF ─── */
        'pdf'  => [self::PDF, 'application/pdf'],

        /* ─── Офисные документы (OnlyOffice) ─── */
        'doc'  => [self::OFFICE, 'application/msword'],
        'docx' => [self::OFFICE, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'dot'  => [self::OFFICE, 'application/msword'],
        'dotx' => [self::OFFICE, 'application/vnd.openxmlformats-officedocument.wordprocessingml.template'],
        'odt'  => [self::OFFICE, 'application/vnd.oasis.opendocument.text'],
        'rtf'  => [self::OFFICE, 'application/rtf'],

        'xls'  => [self::OFFICE, 'application/vnd.ms-excel'],
        'xlsx' => [self::OFFICE, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'xlsm' => [self::OFFICE, 'application/vnd.ms-excel.sheet.macroEnabled.12'],
        'ods'  => [self::OFFICE, 'application/vnd.oasis.opendocument.spreadsheet'],

        'ppt'  => [self::OFFICE, 'application/vnd.ms-powerpoint'],
        'pptx' => [self::OFFICE, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'odp'  => [self::OFFICE, 'application/vnd.oasis.opendocument.presentation'],

        /* ─── Текст ─── */
        'txt'  => [self::TEXT, 'text/plain; charset=utf-8'],
        'csv'  => [self::TEXT, 'text/plain; charset=utf-8'],
        'log'  => [self::TEXT, 'text/plain; charset=utf-8'],
        'md'   => [self::TEXT, 'text/plain; charset=utf-8'],
        'json' => [self::TEXT, 'text/plain; charset=utf-8'],
        'xml'  => [self::TEXT, 'text/plain; charset=utf-8'],
        'yml'  => [self::TEXT, 'text/plain; charset=utf-8'],
        'yaml' => [self::TEXT, 'text/plain; charset=utf-8'],
        'ini'  => [self::TEXT, 'text/plain; charset=utf-8'],
        'sql'  => [self::TEXT, 'text/plain; charset=utf-8'],
        // HTML показываем как ТЕКСТ, а не как страницу: чужая разметка
        // в нашем домене — это чужой JavaScript с нашими куками
        'html' => [self::TEXT, 'text/plain; charset=utf-8'],
        'htm'  => [self::TEXT, 'text/plain; charset=utf-8'],

        /* ─── Медиа ─── */
        'mp3'  => [self::AUDIO, 'audio/mpeg'],
        'wav'  => [self::AUDIO, 'audio/wav'],
        'ogg'  => [self::AUDIO, 'audio/ogg'],
        'm4a'  => [self::AUDIO, 'audio/mp4'],
        'amr'  => [self::AUDIO, 'audio/amr'],

        'mp4'  => [self::VIDEO, 'video/mp4'],
        'webm' => [self::VIDEO, 'video/webm'],
        'ogv'  => [self::VIDEO, 'video/ogg'],
        'mov'  => [self::VIDEO, 'video/quicktime'],
    ];

    /** Какой тип редактора OnlyOffice нужен для расширения. */
    private const OFFICE_TYPES = [
        'doc' => 'word',  'docx' => 'word',  'dot' => 'word',
        'dotx' => 'word', 'odt'  => 'word',  'rtf' => 'word',
        'xls' => 'cell',  'xlsx' => 'cell',  'xlsm' => 'cell', 'ods' => 'cell',
        'ppt' => 'slide', 'pptx' => 'slide', 'odp'  => 'slide',
    ];

    public static function normalize(?string $ext): string
    {
        return strtolower(trim((string) $ext, " \t\n\r\0\x0B."));
    }

    /** Вид просмотра: image | pdf | office | text | audio | video | none */
    public static function kind(?string $ext): string
    {
        return self::MAP[self::normalize($ext)][0] ?? self::NONE;
    }

    /** MIME-тип для отдачи inline. null — отдавать файл на скачивание. */
    public static function mime(?string $ext): ?string
    {
        return self::MAP[self::normalize($ext)][1] ?? null;
    }

    /** Можно ли показать прямо в браузере без посредников. */
    public static function isInline(?string $ext): bool
    {
        return in_array(
            self::kind($ext),
            [self::IMAGE, self::PDF, self::TEXT, self::AUDIO, self::VIDEO],
            true
        );
    }

    /**
     * Нужен ли жёсткий запрет скриптов при отдаче.
     *
     * SVG — полноценный XML-документ: внутри может лежать <script>, и при
     * открытии ссылки «посмотреть» он выполнится в нашем домене, с нашей
     * сессионной кукой. Поэтому такие файлы отдаются с CSP sandbox.
     */
    public static function isSandboxed(?string $ext): bool
    {
        return in_array(self::normalize($ext), ['svg', 'html', 'htm'], true);
    }

    /** word | cell | slide — для конфигурации OnlyOffice. */
    public static function officeDocumentType(?string $ext): string
    {
        return self::OFFICE_TYPES[self::normalize($ext)] ?? 'word';
    }

    /** Имя иконки lucide для карточки файла. */
    public static function icon(?string $ext): string
    {
        return match (self::kind($ext)) {
            self::IMAGE  => 'image',
            self::PDF    => 'file-text',
            self::OFFICE => match (self::officeDocumentType($ext)) {
                'cell'  => 'table',
                'slide' => 'presentation',
                default => 'file-type',
            },
            self::TEXT   => 'file-code',
            self::AUDIO  => 'music',
            self::VIDEO  => 'video',
            default      => 'file',
        };
    }
}
