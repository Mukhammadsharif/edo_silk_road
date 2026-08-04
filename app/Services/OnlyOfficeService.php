<?php

namespace App\Services;

use App\Models\Document\DocumentJournals;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocument;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OnlyOfficeService
{
    protected string $appInternalUrl;
    protected string $appBlankDocPath;
    protected string $onlyOfficeInternalUrl;
    protected string $onlyOfficeExternalUrl;
    protected string $onlyOfficeJwtSecret;

    public function __construct()
    {
        $this->appInternalUrl = config('only-office.APP_INTERNAL_URL');
        $this->appBlankDocPath = config('only-office.APP_BLANK_DOC_PATH');
        $this->onlyOfficeInternalUrl = config('only-office.ONLY_OFFICE_INTERNAL_URL');
        $this->onlyOfficeExternalUrl = config('only-office.ONLY_OFFICE_EXTERNAL_URL');
        $this->onlyOfficeJwtSecret = config('only-office.ONLY_OFFICE_JWT_SECRET');
    }

    /**
     * Генерирует HTTP URL для документа через прокси
     */
    protected function getDocumentUrl(string $filePath): string
    {
        $encodedPath = base64_encode($filePath);

        /* v63.1: URL для Document Server строим на ВНУТРЕННЕМ адресе
         | приложения (only-office.APP_INTERNAL_URL — тот же, на котором
         | работает callback сохранения), а НЕ через route() (APP_URL):
         | при локальной разработке APP_URL=127.0.0.1:8000, и изнутри
         | контейнера OnlyOffice это сам контейнер -> DS не мог скачать
         | документ («не удалось загрузить документ»). Fallback на
         | route(), если внутренний адрес не задан.
         */
        if (!empty($this->appInternalUrl)) {
            return rtrim($this->appInternalUrl, '/') . '/proxy-document/' . $encodedPath;
        }

        return route('document.proxy', ['path' => $encodedPath]);
    }

    /**
     * Генерирует JWT токен для OnlyOffice
     */
    protected function generateJwtToken(array $payload): string
    {
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + 3600;

        return JWT::encode($payload, $this->onlyOfficeJwtSecret, 'HS256');
    }

    /**
     * Базовая конфигурация для документа
     */
    protected function getBaseDocumentConfig(string $key, string $title, string $documentUrl): array
    {
        return [
            'document' => [
                'fileType' => 'docx',
                'key' => $key,
                'title' => $title,
                'url' => $documentUrl,
                'permissions' => [
                    'edit' => true,
                    'download' => true,
                    'save' => true
                ]
            ],
            'documentType' => 'word',
            'editorConfig' => [
                'callbackUrl' => $this->appInternalUrl . '/api/callback',
                'mode' => 'edit',
                'lang' => 'ru',
                'user' => [
                    'id' => Auth::id(),
                    'name' => Auth::user()->username
                ],
                'customization' => [
                    'autosave' => false,
                    'forcesave' => true
                ],
                'coEditing' => [
                    'mode' => 'strict',
                    'change' => false
                ]
            ]
        ];
    }

    /**
     * Сохранить загруженный пользователем основной файл (main_file) на FTP
     * под сегодняшним путём с новым ключом. Возвращает ключ и URL для редактора.
     *
     * Используется, когда документ ЗАГРУЖАЕТСЯ файлом и должен открыться
     * в OnlyOffice (а не сохраняться напрямую).
     */
    public function storeUploadedMainFile($file): array
    {
        $key = uniqid() . '_' . time();
        $path = $this->getTodayPath();
        $docxPath = $path . $key . '.docx';

        Storage::disk('ftp')->put($docxPath, file_get_contents($file->getRealPath()));

        return [
            'key' => $key,
            'path' => $path,
            'url' => $this->getDocumentUrl($docxPath),
        ];
    }

    /**
     * Копия ШАБЛОНА типа (v63) для открытия в редакторе нового документа.
     * Зеркало storeUploadedMainFile, но источник — docx-шаблон на FTP
     * (journal_type_templates.file_path).
     *
     * @return array{key:string,path:string,url:string}|null null — файла нет
     */
    public function storeTemplateCopy(string $ftpTemplatePath): ?array
    {
        if (!\Illuminate\Support\Facades\Storage::disk('ftp')->exists($ftpTemplatePath)) {
            \Illuminate\Support\Facades\Log::warning('[oo-template] файл шаблона не найден на FTP', [
                'path' => $ftpTemplatePath,
            ]);
            return null;
        }

        $key = uniqid() . '_' . time();
        $path = $this->getTodayPath();
        $docxPath = $path . $key . '.docx';

        \Illuminate\Support\Facades\Storage::disk('ftp')->put(
            $docxPath,
            \Illuminate\Support\Facades\Storage::disk('ftp')->get($ftpTemplatePath)
        );

        return [
            'key' => $key,
            'path' => $path,
            'url' => $this->getDocumentUrl($docxPath),
        ];
    }

    /**
     * Открыть редактор для нового документа.
     *
     * @param string|null $documentUrl URL уже загруженного документа (если открываем загруженный файл);
     *                                 null — открыть пустой шаблон.
     * @param string|null $key         Ключ документа; null — сгенерировать новый.
     */
    public function openWordEditor(?string $documentUrl = null, ?string $key = null): array
    {
        $key = $key ?: (uniqid() . '_' . time());
        $documentUrl = $documentUrl ?: Storage::disk('internal')->url($this->appBlankDocPath);

        $payload = $this->getBaseDocumentConfig($key, 'Yangi hujjat', $documentUrl);
        $token = $this->generateJwtToken($payload);

        $documentParams = [
            'fileType' => 'docx',
            'title' => 'Yangi hujjat',
            'key' => $key,
            'url' => $documentUrl,
            'call_back_url' => $this->appInternalUrl . '/api/callback',
            'only_office_url' => $this->onlyOfficeInternalUrl,
            'only_office_js_url' => url($this->onlyOfficeExternalUrl . '/web-apps/apps/api/documents/api.js'),
            'id' => Auth::id(),
            'name' => Auth::user()->userPersonal->shortName(),
            'permissions' => ['edit' => true],
        ];

        return ['token' => $token, 'document_params' => $documentParams];
    }

    /**
     * Редактировать существующий документ
     */
    public function editWordEditor(int $jId, string $docType): array
    {
        $key = uniqid() . '_' . time();

        // Получаем документ
        $documentOnly = $this->getDocument($jId, $docType);

        // Копируем текущий docx под НОВЫМ ключом в сегодняшний путь.
        // Так файл today/{K2}.docx гарантированно существует с самого начала
        // (документ не пропадёт), а редактор и сохранение работают с одним ключом.
        $documentUrl = $this->prepareEditDocumentUrl($documentOnly, $key);

        $payload = $this->getBaseDocumentConfig($key, 'Hujjat tahrirlash', $documentUrl);
        $token = $this->generateJwtToken($payload);

        $documentParams = [
            'fileType' => 'docx',
            'title' => 'Hujjat tahrirlash',
            'key' => $key,
            'url' => $documentUrl,
            'call_back_url' => $this->appInternalUrl . '/api/callback',
            'only_office_url' => $this->onlyOfficeInternalUrl,
            'only_office_js_url' => url($this->onlyOfficeExternalUrl . '/web-apps/apps/api/documents/api.js'),
            'id' => Auth::id(),
            'name' => Auth::user()->username,
            'permissions' => ['edit' => true],
        ];

        return ['token' => $token, 'document_params' => $documentParams];
    }

    /**
     * Принудительно сохранить документ в редакторе (forcesave) через
     * OnlyOffice Command Service. Вызывается, пока редактор ещё открыт
     * (перед отправкой формы «Продолжить»), чтобы свежая версия docx
     * гарантированно была сохранена ДО конвертации в HTML.
     *
     * error=0 — сохранено; error=4 — изменений не было (docx уже актуален).
     */
    public function forceSave(string $key): array
    {
        // Тело команды. JWT-токен ВСЕГО тела кладётся в поле token
        // (стандартный формат Command Service). Заголовок Authorization
        // НЕ используем — некоторые сборки его отвергают.
        $payload = ['c' => 'forcesave', 'key' => $key];
        $token = JWT::encode($payload, $this->onlyOfficeJwtSecret, 'HS256');
        $payload['token'] = $token;

        // URL с гарантированной схемой
        $base = $this->onlyOfficeInternalUrl;
        if (!preg_match('#^https?://#i', $base)) {
            $base = 'http://' . $base;
        }
        $url = rtrim($base, '/') . '/coauthoring/CommandService.ashx';

        $options = [
            'timeout' => 30,
            'verify' => false,
            'http_version' => '1.1',
        ];
        if (config('only-office.HTTP_PROXY')) {
            $options['proxy'] = config('only-office.HTTP_PROXY');
        }

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withOptions($options)
                ->post($url, $payload);

            $data = is_array($response->json()) ? $response->json() : [];
            $error = $data['error'] ?? null;

            // 0 — сохранено; 4 — изменений не было (оба варианта приемлемы)
            $ok = in_array($error, [0, 4], true);

            // Логируем всегда — для диагностики работы Command Service
            Log::info('forcesave response', [
                'key' => $key,
                'url' => $url,
                'status' => $response->status(),
                'body' => $data,
            ]);

            return ['ok' => $ok, 'error' => $error, 'raw' => $data];
        } catch (\Throwable $e) {
            Log::error('forcesave request failed', ['key' => $key, 'url' => $url, 'error' => $e->getMessage()]);
            return ['ok' => false, 'error' => 'exception'];
        }
    }

    /**
     * Получить модель документа
     */
    protected function getDocument(int $jId, string $docType): OnlyOfficeDoc
    {
        if ($docType === 'DOC') {
            $journal = DocumentJournals::findOrFail($jId);
            return OnlyOfficeDoc::where('doc_type', 'DOC')
                ->where('doc_id', $journal->doc_id)
                ->firstOrFail();
        }

        $protocol = ProtocolDocument::findOrFail($jId);
        return OnlyOfficeDoc::where('doc_type', 'PRT')
            ->where('doc_id', $protocol->id)
            ->firstOrFail();
    }

    /**
     * Получить URL для редактирования документа
     */
    protected function getEditDocumentUrl(OnlyOfficeDoc $documentOnly): string
    {
        $docPath = $documentOnly->doc_path . $documentOnly->doc_key . '.docx';

        if (Storage::disk('ftp')->exists($docPath)) {
            // ВАЖНО: Используем HTTP прокси вместо FTP URL
            return $this->getDocumentUrl($docPath);
        }

        // Если файл не найден, используем пустой шаблон
        return Storage::disk('internal')->url($this->appBlankDocPath);
    }

    /**
     * Подготовить документ к редактированию: скопировать текущий docx
     * под новым ключом в сегодняшний путь и вернуть его proxy-URL.
     *
     * Гарантирует, что today/{newKey}.docx существует ещё до открытия
     * редактора — поэтому при «Продолжить» конвертация в HTML всегда
     * находит исходный файл, и документ не пропадает.
     */
    protected function prepareEditDocumentUrl(OnlyOfficeDoc $documentOnly, string $newKey): string
    {
        $oldDocxPath = $documentOnly->doc_path . $documentOnly->doc_key . '.docx';
        $newDocxPath = $this->getTodayPath() . $newKey . '.docx';

        if (Storage::disk('ftp')->exists($oldDocxPath)) {
            try {
                // Не копируем сам в себя (если путь/ключ совпали)
                if ($oldDocxPath !== $newDocxPath) {
                    $content = Storage::disk('ftp')->get($oldDocxPath);
                    Storage::disk('ftp')->put($newDocxPath, $content);
                }
                return $this->getDocumentUrl($newDocxPath);
            } catch (\Throwable $e) {
                Log::error('Failed to copy docx for edit', [
                    'old' => $oldDocxPath,
                    'new' => $newDocxPath,
                    'error' => $e->getMessage(),
                ]);
                // Фолбэк: открыть исходный файл напрямую
                return $this->getDocumentUrl($oldDocxPath);
            }
        }

        // Старого файла нет — открываем пустой шаблон
        return Storage::disk('internal')->url($this->appBlankDocPath);
    }

    /**
     * Конвертировать DOCX в HTML
     */
    public function createWordToHtmlConverted(string $documentKey): bool
    {
        $path = $this->getTodayPath();
        $wordFilePath = $path . $documentKey . '.docx';
        $htmlPath = $path . $documentKey . '.html';

        $docxExists = Storage::disk('ftp')->exists($wordFilePath);
        $docxSize = $docxExists ? Storage::disk('ftp')->size($wordFilePath) : 0;
        Log::info('convert start', [
            'key' => $documentKey,
            'docx' => $wordFilePath,
            'docx_exists' => $docxExists,
            'docx_size' => $docxSize,
            'html_already' => Storage::disk('ftp')->exists($htmlPath),
        ]);

        // Проверяем, существует ли уже HTML
        if (Storage::disk('ftp')->exists($htmlPath)) {
            Log::info('HTML file already exists', ['key' => $documentKey]);
            return true;
        }

        // Защита: исходный docx обязан существовать, иначе документ «пропадёт».
        // Не конвертируем впустую — возвращаем false, чтобы вызывающий код
        // не переключал документ на отсутствующий HTML.
        if (!$docxExists) {
            Log::error('Source docx not found, skip conversion', [
                'key' => $documentKey,
                'path' => $wordFilePath,
            ]);
            return false;
        }

        // ВАЖНО: Используем HTTP URL через прокси
        $documentUrl = $this->getDocumentUrl($wordFilePath);

        // Выполняем конвертацию
        $this->convertDocument($documentKey, $documentUrl, 'html', 'docx', $htmlPath);

        // Сохраняем JSON с токеном
        $this->saveConversionToken($documentKey, $documentUrl, 'html', 'docx', $path);

        // Проверяем, что HTML действительно создан
        $htmlMade = Storage::disk('ftp')->exists($htmlPath);
        Log::info('convert done', ['key' => $documentKey, 'html_made' => $htmlMade]);

        if (!$htmlMade) {
            Log::error('HTML not produced after conversion', ['key' => $documentKey]);
            return false;
        }

        return true;
    }

    /**
     * Обновить и конвертировать документ
     */
    public function editWordToHtmlConverted(string $documentKey, int $docId, string $docType): bool
    {
        $model = OnlyOfficeDoc::where('doc_type', $docType)
            ->where('doc_id', $docId)
            ->first();

        if (!$model) {
            return false;
        }

        // Удаляем старые файлы
        $this->deleteOldFiles($model);

        // Новый путь
        $path = $this->getTodayPath();
        $wordFilePath = $path . $documentKey . '.docx';
        $htmlPath = $path . $documentKey . '.html';

        // Обновляем модель
        $model->update([
            'doc_key' => $documentKey,
            'doc_path' => $path,
        ]);

        // ВАЖНО: Используем HTTP URL через прокси
        $documentUrl = $this->getDocumentUrl($wordFilePath);

        // Выполняем конвертацию
        if (!Storage::disk('ftp')->exists($htmlPath)) {
            $this->convertDocument($documentKey, $documentUrl, 'html', 'docx', $htmlPath);
        }

        // Сохраняем JSON с токеном
        $this->saveConversionToken($documentKey, $documentUrl, 'html', 'docx', $path);

        return true;
    }

    /**
     * Конвертировать HTML в PDF
     */
    public function createHtmlToPDFConverted(string $documentKey, string $documentPath): string
    {
        $path = $this->getTodayPath();
        $htmlFilePath = $documentPath . $documentKey . '.html';
        $pdfPath = $path . $documentKey . '.pdf';

        if (Storage::disk('ftp')->exists($pdfPath)) {
            Log::info('PDF file already exists', ['key' => $documentKey]);
            return $pdfPath;
        }

        // ВАЖНО: Используем HTTP URL через прокси
        $documentUrl = $this->getDocumentUrl($htmlFilePath);

        // Выполняем конвертацию
        $this->convertDocument($documentKey, $documentUrl, 'pdf', 'html', $pdfPath);

        return $pdfPath;
    }

    /**
     * Общий метод конвертации документов
     */
    protected function convertDocument(
        string $key,
        string $url,
        string $outputType,
        string $fileType,
        string $savePath
    ): void {
        $body = [
            'async' => false,
            'url' => $url,
            'outputtype' => $outputType,
            'filetype' => $fileType,
            'title' => $key,
            'key' => $key,
        ];

        $jwtToken = JWT::encode($body, $this->onlyOfficeJwtSecret, 'HS256');

        try {
            $response = $this->sendConversionRequest($jwtToken, $body);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['fileUrl'])) {
                    $this->downloadConvertedFile($responseData['fileUrl'], $savePath);
                } else {
                    Log::error('No fileUrl in conversion response', [
                        'key' => $key,
                        'response' => $responseData
                    ]);
                }
            } else {
                Log::error('Conversion request failed', [
                    'key' => $key,
                    'status' => $response->status()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Conversion error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Отправить запрос на конвертацию
     */
    protected function sendConversionRequest(string $token, array $body)
    {
        $options = [
            'timeout' => 60,
            'verify' => false,
            'max_redirects' => 10,
            'http_version' => '1.1',
        ];

        if (config('only-office.HTTP_PROXY')) {
            $options['proxy'] = config('only-office.HTTP_PROXY');
        }

        return Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withOptions($options)
            ->post($this->onlyOfficeInternalUrl . '/converter', $body);
    }

    /**
     * Скачать сконвертированный файл
     */
    protected function downloadConvertedFile(string $fileUrl, string $savePath): void
    {
        // Заменяем external URL на internal
        $convertedUrl = str_replace(
            $this->onlyOfficeExternalUrl,
            $this->onlyOfficeInternalUrl,
            $fileUrl
        );

        try {
            $options = ['timeout' => 60];

            if (config('only-office.HTTP_PROXY')) {
                $options['proxy'] = config('only-office.HTTP_PROXY');
            }

            $content = Http::withOptions($options)->get($convertedUrl)->body();
            Storage::disk('ftp')->put($savePath, $content);

        } catch (\Exception $e) {
            Log::error('Failed to download converted file', [
                'url' => $convertedUrl,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Сохранить токен конвертации
     */
    protected function saveConversionToken(
        string $key,
        string $url,
        string $outputType,
        string $fileType,
        string $path
    ): void {
        $body = [
            'async' => false,
            'url' => $url,
            'outputtype' => $outputType,
            'filetype' => $fileType,
            'title' => $key,
            'key' => $key,
        ];

        $jwtToken = JWT::encode($body, $this->onlyOfficeJwtSecret, 'HS256');
        $jsonFilePath = $path . $key . '.json';

        Storage::disk('ftp')->put(
            $jsonFilePath,
            json_encode(['key' => $key, 'token' => $jwtToken])
        );
    }

    /**
     * Удалить старые файлы документа
     */
    protected function deleteOldFiles(OnlyOfficeDoc $model): void
    {
        $files = [
            $model->doc_path . $model->doc_key . '.json',
            $model->doc_path . $model->doc_key . '.html',
        ];

        foreach ($files as $file) {
            if (Storage::disk('ftp')->exists($file)) {
                Storage::disk('ftp')->delete($file);
            }
        }
    }

    /**
     * Получить путь для сегодняшней даты
     */
    protected function getTodayPath(): string
    {
        $today = Carbon::today();
        return sprintf('ood/%d/%d/%d/', $today->year, $today->month, $today->day);
    }
}
