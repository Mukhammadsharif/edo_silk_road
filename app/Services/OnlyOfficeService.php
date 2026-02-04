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
        $this->appInternalUrl           = config('only-office.APP_INTERNAL_URL');
        $this->appBlankDocPath          = config('only-office.APP_BLANK_DOC_PATH');
        $this->onlyOfficeInternalUrl    = config('only-office.ONLY_OFFICE_INTERNAL_URL');
        $this->onlyOfficeExternalUrl    = config('only-office.ONLY_OFFICE_EXTERNAL_URL');
        $this->onlyOfficeJwtSecret      = config('only-office.ONLY_OFFICE_JWT_SECRET');
    }

    public function openWordEditor(): array
    {
        //
        $key = uniqid().'_'.time();

        $only_office_url = $this->onlyOfficeInternalUrl;

        $secret_key = $this->onlyOfficeJwtSecret;

        $call_back_url = $this->appInternalUrl.'/api/callback';

        $document_blank_url = Storage::disk('internal')->url($this->appBlankDocPath);

        $now = time();

        $payload = [
            "document" => [
                "fileType" => "docx",
                "key" => $key,
                "title" => "Yangi hujjat",
                "url" => $document_blank_url,
                'permissions' => [
                    'edit' => true,
                    'download' => true,
                    'save' => true
                ]
            ],
            "documentType" => "word",
            "editorConfig" => [
                "callbackUrl" => $call_back_url,
                "mode" => "edit",
                "lang" => "ru",
                "user" => [
                    "id" => Auth::id(),
                    "name" => Auth::user()->username
                ],
                'customization' => [
                    'autosave' => false,
                    'forcesave' => true
                ],
                'coEditing' => [
                    'mode' => 'strict',
                    'change' => false
                ]
            ],
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $token = JWT::encode($payload, $secret_key, 'HS256');

        $document_params = [
            'fileType' => 'docx',
            'title' => 'Yangi hujjat',
            'key' => $key,
            'url' => $document_blank_url,
            'call_back_url' => $call_back_url,
            'only_office_url' => $only_office_url,
            'only_office_js_url' => url($this->onlyOfficeExternalUrl.'/web-apps/apps/api/documents/api.js'),
            "id" => Auth::id(),
            "name" => Auth::user()->username,
            'permissions' => [
                'edit' => true,
            ],
        ];

        return ['token' => $token, 'document_params' => $document_params];
    }

    public function editWordEditor($j_id, $doc_type): array
    {
        $key = uniqid().'_'.time();

        if ($doc_type === 'DOC') {
            $journal = DocumentJournals::findOrFail($j_id);
            $documentOnly = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->firstOrFail();
        } else {
            $protocol = ProtocolDocument::findOrFail($j_id);
            $documentOnly = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $protocol->id)->firstOrFail();
        }

        $only_office_url = $this->onlyOfficeInternalUrl;

        $secret_key = $this->onlyOfficeJwtSecret;

        $call_back_url = $this->appInternalUrl.'/api/callback';

        $document_blank_url = Storage::disk('internal')->url($this->appBlankDocPath);

        $doc_path = $documentOnly->doc_path.$documentOnly->doc_key.'.docx';

        if (Storage::disk('ftp')->exists($doc_path)) {
            $document_blank_url = Storage::disk('ftp')->url($doc_path);
        }

        $now = time();

        $payload = [
            "document" => [
                "fileType" => "docx",
                "key" => $key,
                "title" => "Hujjat tahrirlash",
                "url" => $document_blank_url,
                'permissions' => [
                    'edit' => true,
                    'download' => true,
                    'save' => true
                ]
            ],
            "documentType" => "word",
            "editorConfig" => [
                "callbackUrl" => $call_back_url,
                "mode" => "edit",
                "lang" => "ru",
                "user" => [
                    "id" => Auth::id(),
                    "name" => Auth::user()->username
                ],
                'customization' => [
                    'autosave' => false,
                    'forcesave' => true
                ],
                'coEditing' => [
                    'mode' => 'strict',
                    'change' => false
                ]
            ],
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $token = JWT::encode($payload, $secret_key, 'HS256');

        $document_params = [
            'fileType' => 'docx',
            'title' => 'Hujjat tahrirlash',
            'key' => $key,
            'url' => $document_blank_url,
            'call_back_url' => $call_back_url,
            'only_office_url' => $only_office_url,
            'only_office_js_url' => url($this->onlyOfficeExternalUrl.'/web-apps/apps/api/documents/api.js'),
            "id" => Auth::id(),
            "name" => Auth::user()->username,
            'permissions' => [
                'edit' => true,
            ],
        ];

        return ['token' => $token, 'document_params' => $document_params];
    }

    public function createWordToHtmlConverted($document_key): true
    {
        //
        $secret_key = $this->onlyOfficeJwtSecret;
        $today = Carbon::today();
        $year = $today->year;
        $month = $today->month;
        $day = $today->day;
        $path = 'ood/' . $year . '/' . $month . '/' . $day . '/';
        $word_file_path = $path . $document_key . '.docx';
        $example_document_url = Storage::disk('ftp')->url($word_file_path);
        $html_path = $path . $document_key . '.html';

        // Create JWT token and save JSON file
        $body = [
            'async' => false,
            'url' => $example_document_url,
            'outputtype' => 'html',
            'filetype' => 'docx',
            'title' => $document_key,
            'key' => $document_key,
        ];
        $jwtToken = JWT::encode($body, $secret_key, 'HS256');
        $json_file_path = $path . $document_key . '.json';

        Storage::disk('ftp')->put($json_file_path, json_encode(['key' => $document_key, 'token' => $jwtToken]));

        // Check if HTML file already exists
        if (!Storage::disk('ftp')->exists($html_path)) {
            try {
                $options = ['timeout' => 60];
                $options['verify'] = false;
                $options['max_redirects'] = 10;
                $options['http_version'] = '1.1';
                if (config('only-office.HTTP_PROXY')) {
                    $options['proxy'] = config('only-office.HTTP_PROXY');
                }
                $response = Http::withToken($jwtToken)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                    ->timeout(5)
                    ->withOptions($options)
                    ->post($this->onlyOfficeInternalUrl . '/converter', [
                        'async' => false,
                        'url' => $example_document_url,
                        'outputtype' => 'html',
                        'filetype' => 'docx',
                        'title' => $document_key,
                        'key' => $document_key
                    ]);

                if (!$response->failed()) {
                    $response_decode = $response->json();
                    if (isset($response_decode['fileUrl'])) {
                        $convertedUrl = str_replace(
                            $this->onlyOfficeExternalUrl,
                            $this->onlyOfficeInternalUrl,
                            $response_decode['fileUrl']
                        );

                        try {
                            $options1 = ['timeout' => 60];
                            if (config('only-office.HTTP_PROXY')) {
                                $options1['proxy'] = config('only-office.HTTP_PROXY');
                            }

                            $convertedContent = Http::withOptions($options1)->get($convertedUrl)->body();

                            Storage::disk('ftp')->put($html_path, $convertedContent);

                        } catch (\Exception $e) {
                            Log::error('Failed to fetch converted content from: ' . $convertedUrl . ' - Error: ' . $e->getMessage());
                        }
                    }
                }

            } catch (\Exception $e) {
                Log::error('HTTP error during conversion for key: ' . $document_key . ' - ' . $e->getMessage());
            }
        } else {
            Log::info('HTML file already exists for: ' . $document_key);
        }

        return true;
    }

    public function editWordToHtmlConverted($document_key, $doc_id, $doc_type): true
    {
        //
        $model = OnlyOfficeDoc::where('doc_type', $doc_type)->where('doc_id', $doc_id)->first();
        if ($model) {
            // Delete old Word file
            $word_path = $model->doc_path . $model->doc_key . '.docx';
            if (Storage::disk('ftp')->exists($word_path)) {
                //Storage::disk('public')->delete($word_path);
            }

            // Delete old JSON file
            $json_path = $model->doc_path . $model->doc_key . '.json';
            if (Storage::disk('ftp')->exists($json_path)) {
                Storage::disk('ftp')->delete($json_path);
            }

            // Delete old HTML file
            $html_path = $model->doc_path . $model->doc_key . '.html';
            if (Storage::disk('ftp')->exists($html_path)) {
                Storage::disk('ftp')->delete($html_path);
            }

            // OnlyOffice Document JSON file save
            $secret_key = $this->onlyOfficeJwtSecret;
            $today = Carbon::today();
            $year = $today->year;
            $month = $today->month;
            $day = $today->day;
            $path = 'ood/' . $year . '/' . $month . '/' . $day . '/';
            $filename = $document_key . '.docx';
            $example_document_url = Storage::disk('ftp')->url($path . $filename);
            $html_path = $path . $document_key . '.html';

            $body = [
                'async' => false,
                'url' => $example_document_url,
                'outputtype' => 'html',
                'filetype' => 'docx',
                'title' => $document_key,
                'key' => $document_key,
            ];
            $jwtToken = JWT::encode($body, $secret_key, 'HS256');
            $json = json_encode(['key' => $document_key, 'token' => $jwtToken]);
            $json_file_path = $path . $document_key . '.json';
            Storage::disk('ftp')->put($json_file_path, $json);

            // Update document key and path in the database
            $model->update([
                'doc_key' => $document_key,
                'doc_path' => $path,
            ]);

            // Check if HTML file already exists
            if (!Storage::disk('ftp')->exists($html_path)) {
                try {
                    $options = ['timeout' => 60];
                    $options['verify'] = false;
                    $options['max_redirects'] = 10;
                    $options['http_version'] = '1.1';
                    if (config('only-office.HTTP_PROXY')) {
                        $options['proxy'] = config('only-office.HTTP_PROXY');
                    }

                    $response = Http::withToken($jwtToken)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                        ])
                        ->withOptions($options)
                        ->post($this->onlyOfficeInternalUrl . '/converter', [
                            'async' => false,
                            'url' => $example_document_url,
                            'outputtype' => 'html',
                            'filetype' => 'docx',
                            'title' => $document_key,
                            'key' => $document_key
                        ]);

                    if (!$response->failed()) {
                        $response_decode = $response->json();
                        if (isset($response_decode['fileUrl'])) {
                            $convertedUrl = str_replace(
                                $this->onlyOfficeExternalUrl,
                                $this->onlyOfficeInternalUrl,
                                $response_decode['fileUrl']
                            );

                            try {
                                $options1 = ['timeout' => 60];
                                if (config('only-office.HTTP_PROXY')) {
                                    $options1['proxy'] = config('only-office.HTTP_PROXY');
                                }
                                $convertedContent = Http::withOptions($options1)->get($convertedUrl)->body();
                                Storage::disk('ftp')->put($html_path, $convertedContent);
                            } catch (\Exception $e) {
                                Log::error('Failed to fetch converted content from: ' . $convertedUrl . ' - Error: ' . $e->getMessage());
                            }
                        } else {
                            Log::error('No fileUrl in response for key: ' . $document_key . ' - Response: ' . json_encode($response_decode));
                        }
                    }

                } catch (\Exception $e) {
                    Log::error('HTTP error during conversion for key: ' . $document_key . ' - ' . $e->getMessage());
                }
            } else {
                Log::info('HTML file already exists for: ' . $document_key);
            }
        }

        return true;
    }

    public function createHtmlToPDFConverted($document_key, $document_path): string
    {
        //
        $secret_key = $this->onlyOfficeJwtSecret;
        $today = Carbon::today();
        $year = $today->year;
        $month = $today->month;
        $day = $today->day;
        $path = 'ood/' . $year . '/' . $month . '/' . $day . '/';
        $html_file_path = $document_path . $document_key . '.html';
        $example_document_url = Storage::disk('ftp')->url($html_file_path);
        $pdf_path = $path . $document_key . '.pdf';

        // Create JWT token and save JSON file
        $body = [
            'async' => false,
            'url' => $example_document_url,
            'outputtype' => 'pdf',
            'filetype' => 'html',
            'title' => $document_key,
            'key' => $document_key,
        ];
        $jwtToken = JWT::encode($body, $secret_key, 'HS256');

        // Check if HTML file already exists
        if (!Storage::disk('ftp')->exists($pdf_path)) {
            try {
                $options = ['timeout' => 60];
                $options['verify'] = false;
                $options['max_redirects'] = 10;
                $options['http_version'] = '1.1';
                if (config('only-office.HTTP_PROXY')) {
                    $options['proxy'] = config('only-office.HTTP_PROXY');
                }
                $response = Http::withToken($jwtToken)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                    ->withOptions($options)
                    ->post($this->onlyOfficeInternalUrl . '/converter', [
                        'async' => false,
                        'url' => $example_document_url,
                        'outputtype' => 'pdf',
                        'filetype' => 'html',
                        'title' => $document_key,
                        'key' => $document_key
                    ]);

                if (!$response->failed()) {
                    $response_decode = $response->json();
                    if (isset($response_decode['fileUrl'])) {
                        $convertedUrl = str_replace(
                            $this->onlyOfficeExternalUrl,
                            $this->onlyOfficeInternalUrl,
                            $response_decode['fileUrl']
                        );

                        try {
                            $options1 = ['timeout' => 60];
                            if (config('only-office.HTTP_PROXY')) {
                                $options1['proxy'] = config('only-office.HTTP_PROXY');
                            }
                            $convertedContent = Http::withOptions($options1)->get($convertedUrl)->body();
                            Storage::disk('ftp')->put($pdf_path, $convertedContent);
                        } catch (\Exception $e) {
                            Log::error('Failed to fetch converted content from: ' . $convertedUrl . ' - Error: ' . $e->getMessage());
                        }
                    }

                }
            } catch (\Exception $e) {
                Log::error('HTTP error during conversion for key: ' . $document_key . ' - ' . $e->getMessage());
            }
        } else {
            Log::info('HTML file already exists for: ' . $document_key);
        }

        return $pdf_path;
    }

}
