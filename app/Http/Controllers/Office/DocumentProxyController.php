<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentProxyController extends Controller
{
    /**
     * Проксирует файлы с FTP через HTTP для OnlyOffice
     */
    public function show(string $encodedPath): StreamedResponse
    {
        try {
            // Декодируем путь
            $filePath = base64_decode($encodedPath);

            // Валидация пути (защита от path traversal)
            if (str_contains($filePath, '..') || !str_starts_with($filePath, 'ood/')) {
                Log::warning('Invalid file path attempt', ['path' => $filePath]);
                abort(403, 'Доступ запрещён');
            }

            $disk = Storage::disk('ftp');

            if (!$disk->exists($filePath)) {
                Log::error('File not found on FTP', ['path' => $filePath]);
                abort(404, 'Файл не найден');
            }

            // Получаем MIME тип
            $mimeType = $disk->mimeType($filePath) ?: 'application/octet-stream';
            $fileName = basename($filePath);

            // Стримим файл напрямую (эффективнее для больших файлов)
            return response()->stream(
                function () use ($disk, $filePath) {
                    $stream = $disk->readStream($filePath);
                    fpassthru($stream);
                    fclose($stream);
                },
                200,
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    'Cache-Control' => 'public, max-age=3600',
                    'X-Content-Type-Options' => 'nosniff',
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error serving document', [
                'path' => $encodedPath,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Ошибка при получении файла');
        }
    }
}
