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
     * Открыть редактор для нового документа
     */
    public function openWordEditor(): array
    {
        $key = uniqid() . '_' . time();
        $documentUrl = Storage::disk('internal')->url($this->appBlankDocPath);

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

        // Определяем URL документа
        $documentUrl = $this->getEditDocumentUrl($documentOnly);

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
     * Конвертировать DOCX в HTML
     */
    public function createWordToHtmlConverted(string $documentKey): bool
    {
        $path = $this->getTodayPath();
        $wordFilePath = $path . $documentKey . '.docx';
        $htmlPath = $path . $documentKey . '.html';

        // Проверяем, существует ли уже HTML
        if (Storage::disk('ftp')->exists($htmlPath)) {
            Log::info('HTML file already exists', ['key' => $documentKey]);
            return true;
        }

        // ВАЖНО: Используем HTTP URL через прокси
        $documentUrl = $this->getDocumentUrl($wordFilePath);

        // Выполняем конвертацию
        $this->convertDocument($documentKey, $documentUrl, 'html', 'docx', $htmlPath);

        // Сохраняем JSON с токеном
        $this->saveConversionToken($documentKey, $documentUrl, 'html', 'docx', $path);

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
