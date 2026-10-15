<?php

namespace App\Services;

use App\Models\Document\DocumentFiles;
use App\Support\FilePreview;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

/**
 * Просмотр вложений Word / Excel / PowerPoint через OnlyOffice.
 *
 * Отдельный сервис, а не метод в OnlyOfficeService: тот работает с
 * телом документа — диск ftp, ключи, конвертация в HTML, обратные
 * вызовы сохранения. Здесь задача принципиально проще и безопаснее:
 * показать вложение с диска ftp_13 только на чтение, ничего не
 * сохраняя. Смешивать их означало бы тащить в просмотр callbackUrl
 * и режим правки, которых тут быть не должно.
 */
class OnlyOfficeViewerService
{
    /** Сколько живёт подписанная ссылка на исходник. */
    private const SOURCE_TTL_MINUTES = 180;

    /**
     * Масштаб документа при открытии.
     *   -1 — вписать страницу целиком
     *   -2 — по ширине страницы
     *  100 — натуральная величина
     *
     * По ширине: окно просмотра у нас узкое (центральная колонка между
     * двумя боковыми панелями), и при натуральном масштабе документ
     * обрезается справа.
     */
    private const ZOOM_FIT_WIDTH = -2;

    /**
     * Настроен ли OnlyOffice в этом окружении.
     * Если нет — просмотрщик офисных файлов не рисуется, остаётся
     * карточка со скачиванием, и страница продолжает работать.
     */
    public function isEnabled(): bool
    {
        return (bool) config('only-office.ONLY_OFFICE_EXTERNAL_URL')
            && (bool) config('only-office.ONLY_OFFICE_JWT_SECRET');
    }

    /** Адрес api.js редактора. */
    public function apiJsUrl(): string
    {
        return rtrim((string) config('only-office.ONLY_OFFICE_EXTERNAL_URL'), '/')
            . '/web-apps/apps/api/documents/api.js';
    }

    /**
     * Конфигурация редактора в режиме чтения для одного вложения.
     * Отдаётся в шаблон и уходит в DocsAPI.DocEditor как есть.
     */
    public function configFor(DocumentFiles $file): array
    {
        $ext = FilePreview::normalize($file->file_ext);

        $config = [
            'document' => [
                'fileType' => $ext,
                'key'      => $this->documentKey($file),
                'title'    => $file->file_name,
                'url'      => $this->sourceUrl($file),
                'permissions' => [
                    'edit'      => false,
                    'review'    => false,
                    'comment'   => false,
                    'fillForms' => false,
                    'download'  => true,
                    'print'     => true,
                ],
            ],

            'documentType' => FilePreview::officeDocumentType($ext),
            'type'         => 'desktop',
            'width'        => '100%',
            'height'       => '100%',

            'editorConfig' => [
                'mode' => 'view',
                'lang' => $this->editorLang(),
                'user' => [
                    'id'   => (string) (Auth::id() ?? 'guest'),
                    'name' => Auth::user()?->userPersonal?->shortName()
                        ?? Auth::user()?->username
                            ?? '—',
                ],
                'customization' => [
                    // Масштаб по ширине — см. ZOOM_FIT_WIDTH.
                    // В таблицах параметр игнорируется, это нормально.
                    'zoom' => self::ZOOM_FIT_WIDTH,

                    // Убираем всё, что не нужно для чтения: так окну
                    // просмотра достаётся больше высоты
                    'hideRulers'    => true,
                    'compactHeader' => true,
                    'toolbarNoTabs' => true,
                    'hideRightMenu' => true,

                    'chat'     => false,
                    'comments' => false,
                    'help'     => false,
                    'plugins'  => false,
                ],
            ],
        ];

        // Document Server проверяет подпись всей конфигурации.
        // Без token сервер ответит «The document security token is not
        // correctly formed», если в его настройках включён JWT.
        $config['token'] = JWT::encode(
            $config,
            (string) config('only-office.ONLY_OFFICE_JWT_SECRET'),
            'HS256'
        );

        return $config;
    }

    /**
     * Ключ документа. Document Server кэширует файл по ключу, поэтому
     * ключ обязан меняться вместе с содержимым — иначе после замены
     * вложения пользователь увидит старую версию. file_hash — имя
     * файла на диске, оно у каждой загрузки своё.
     */
    private function documentKey(DocumentFiles $file): string
    {
        return substr(
            md5($file->id . '|' . $file->file_hash . '|' . $file->updated_at),
            0,
            20
        );
    }

    /** Временная подписанная ссылка, по которой DS заберёт файл. */
    private function sourceUrl(DocumentFiles $file): string
    {
        return URL::temporarySignedRoute(
            'file.office-source',
            now()->addMinutes(self::SOURCE_TTL_MINUTES),
            ['id' => $file->id]
        );
    }

    /**
     * Язык интерфейса редактора.
     * Локаль kr (узбекская кириллица) OnlyOffice не знает — показываем
     * ему русский, иначе редактор молча падает на английский.
     */
    private function editorLang(): string
    {
        return match (app()->getLocale()) {
            'uz'    => 'uz',
            'en'    => 'en',
            default => 'ru',
        };
    }
}
