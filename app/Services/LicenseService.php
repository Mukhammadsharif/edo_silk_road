<?php

namespace App\Services;

use App\Models\Admin\License;
use App\Models\Admin\Subscriptions;
use App\Models\Admin\Tariffs;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Генерация и верификация лицензий.
 *
 * Файл .lic — это base64( JSON payload ) + "." + HMAC-SHA256 подпись.
 * Подделать payload без секретного ключа невозможно.
 */
class LicenseService
{
    /** Секрет для HMAC. Берём APP_KEY чтобы не плодить env-переменные. */
    private function secret(): string
    {
        return (string) config('app.key');
    }

    /**
     * Сгенерировать лицензию: создаёт запись в БД + возвращает содержимое .lic файла.
     */
    public function generate(int $orgUnitId, int $maxUsers, int $maxDocuments, int $durationDays, ?int $generatedBy = null): array
    {
        // Уникальный публичный ключ: XXXX-XXXX-XXXX-XXXX
        $licenseKey = $this->makeKey();

        $payload = [
            'key'           => $licenseKey,
            'org_unit_id'   => $orgUnitId,
            'max_users'     => $maxUsers,
            'max_documents' => $maxDocuments,
            'duration_days' => $durationDays,
            'issued_at'     => now()->toIso8601String(),
            'v'             => 1,
        ];

        $signature = $this->sign($payload);

        // Сохраняем в БД
        $license = License::create([
            'license_key'   => $licenseKey,
            'org_unit_id'   => $orgUnitId,
            'max_users'     => $maxUsers,
            'max_documents' => $maxDocuments,
            'duration_days' => $durationDays,
            'signature'     => $signature,
            'status'        => 1, // выпущена
            'generated_by'  => $generatedBy,
        ]);

        $fileContent = $this->buildFile($payload, $signature);

        return [
            'license'      => $license,
            'file_content' => $fileContent,
            'file_name'    => 'license_' . $licenseKey . '.lic',
        ];
    }

    /**
     * Собрать содержимое .lic файла: base64(payload).signature + читаемый заголовок.
     */
    public function buildFile(array $payload, string $signature): string
    {
        $encoded = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $token   = $encoded . '.' . $signature;

        // Человеко-читаемый заголовок + сам токен
        $lines = [
            '----- EDOLINE LICENSE FILE -----',
            'Bu fayl litsenziya kalitidir. O\'zgartirmang.',
            'Tashkilot ID: ' . ($payload['org_unit_id'] ?? '-'),
            'Maks. xodimlar: ' . ($payload['max_users'] ?? '-'),
            'Maks. hujjatlar: ' . ($payload['max_documents'] ?? '-'),
            'Muddat (kun): ' . ($payload['duration_days'] ?? '-'),
            'Kalit: ' . ($payload['key'] ?? '-'),
            '--------------------------------',
            $token,
            '--------------------------------',
        ];

        return implode("\n", $lines);
    }

    /**
     * Распарсить .lic файл, извлечь token и проверить подпись.
     * Возвращает payload или бросает Exception.
     */
    public function parseAndVerify(string $fileContent): array
    {
        // Токен — самая длинная строка содержащая '.'
        $token = null;
        foreach (preg_split('/\r\n|\r|\n/', trim($fileContent)) as $line) {
            $line = trim($line);
            if (str_contains($line, '.') && strlen($line) > 40 && !str_contains($line, ' ')) {
                $token = $line;
                break;
            }
        }
        if (!$token) {
            // Может прислали голый токен
            $token = trim($fileContent);
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new \RuntimeException('Litsenziya fayli formati noto\'g\'ri.');
        }

        [$encoded, $signature] = $parts;
        $json = base64_decode($encoded, true);
        if ($json === false) {
            throw new \RuntimeException('Litsenziya fayli buzilgan.');
        }
        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            throw new \RuntimeException('Litsenziya ma\'lumotlari o\'qilmadi.');
        }

        // Проверка подписи
        $expected = $this->sign($payload);
        if (!hash_equals($expected, $signature)) {
            throw new \RuntimeException('Litsenziya imzosi yaroqsiz (fayl o\'zgartirilgan).');
        }

        return $payload;
    }

    /**
     * Активировать лицензию для организации.
     * Создаёт tariff + subscription (status=1) и связывает с лицензией.
     */
    public function activate(string $fileContent, int $orgUnitId, ?int $activatedBy = null): Subscriptions
    {
        $payload = $this->parseAndVerify($fileContent);

        $licenseKey = $payload['key'] ?? null;
        if (!$licenseKey) {
            throw new \RuntimeException('Litsenziya kaliti topilmadi.');
        }

        $license = License::where('license_key', $licenseKey)->first();
        if (!$license) {
            throw new \RuntimeException('Bunday litsenziya tizimda mavjud emas.');
        }

        if ((int) $license->status === 2) {
            throw new \RuntimeException('Bu litsenziya allaqachon faollashtirilgan.');
        }
        if ((int) $license->status === 0) {
            throw new \RuntimeException('Bu litsenziya bekor qilingan.');
        }

        // Лицензия привязана к конкретной организации
        if ((int) $license->org_unit_id !== (int) $orgUnitId) {
            throw new \RuntimeException('Bu litsenziya sizning tashkilotingiz uchun emas.');
        }

        // Создаём виртуальный тариф под лимиты лицензии (переиспользуем проверки подписки)
        $tariff = Tariffs::create([
            'code'          => 'LIC-' . $licenseKey,
            'name'          => 'Litsenziya (' . $licenseKey . ')',
            'max_users'     => $license->max_users,
            'max_documents' => $license->max_documents,
            'price_monthly' => 0,
            'price_yearly'  => 0,
            'status'        => 1,
        ]);

        $startsAt = Carbon::now();
        $endsAt   = Carbon::now()->addDays((int) $license->duration_days);

        $subscription = Subscriptions::create([
            'org_unit_id' => $orgUnitId,
            'tariff_id'   => $tariff->id,
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
            'status'      => 1, // активна сразу
        ]);

        $license->update([
            'status'          => 2,
            'subscription_id' => $subscription->id,
            'activated_by'    => $activatedBy,
            'activated_at'    => now(),
        ]);

        return $subscription;
    }

    private function makeKey(): string
    {
        do {
            $raw = strtoupper(Str::random(16));
            $key = implode('-', str_split($raw, 4));
        } while (License::where('license_key', $key)->exists());

        return $key;
    }

    private function sign(array $payload): string
    {
        // Канонический порядок ключей для стабильной подписи
        ksort($payload);
        $data = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return hash_hmac('sha256', $data, $this->secret());
    }
}
