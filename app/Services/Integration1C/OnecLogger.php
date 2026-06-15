<?php

namespace App\Services\Integration1C;

use Illuminate\Support\Facades\Log;

/**
 * Централизованное логирование 1С-интеграции в канал 'onec' (logs/onec.log).
 * Все случаи: приём, валидация, синк, удаление, исходящие, ошибки.
 */
class OnecLogger
{
    private const CHANNEL = 'onec';

    private function enabled(): bool
    {
        return (bool) config('onec.log_requests', true);
    }

    private function log(string $level, string $event, array $context = []): void
    {
        if (!$this->enabled()) return;
        try {
            Log::channel(self::CHANNEL)->{$level}("[1C] {$event}", $context);
        } catch (\Throwable $e) {
            // fallback в общий лог если канал недоступен
            Log::{$level}("[1C] {$event}", $context);
        }
    }

    /** Входящий запрос принят */
    public function inboundReceived(string $entity, string $method, array $context = []): void
    {
        $this->log('info', "INBOUND {$method} {$entity}", $context);
    }

    /** Ошибка валидации */
    public function validationFailed(string $entity, ?string $key, array $errors): void
    {
        $this->log('warning', "VALIDATION FAILED {$entity}", [
            'key'    => $key,
            'errors' => $errors,
        ]);
    }

    /** Успешный upsert */
    public function upserted(string $entity, ?string $key, $localId, bool $created = false): void
    {
        $this->log('info', "UPSERT OK {$entity}", [
            'key'      => $key,
            'local_id' => $localId,
            'action'   => $created ? 'created' : 'updated',
        ]);
    }

    /** Успешный синк в рабочую таблицу */
    public function synced(string $entity, ?string $key, string $targetTable, $localId): void
    {
        $this->log('info', "SYNC OK {$entity} → {$targetTable}", [
            'key'      => $key,
            'local_id' => $localId,
        ]);
    }

    /** Синк пропущен (зависимость не найдена и т.п.) */
    public function syncSkipped(string $entity, ?string $key, string $reason, array $context = []): void
    {
        $this->log('warning', "SYNC SKIPPED {$entity}", array_merge([
            'key'    => $key,
            'reason' => $reason,
        ], $context));
    }

    /** Удаление */
    public function deleted(string $entity, string $key, int $count): void
    {
        $this->log('info', "DELETE {$entity}", ['key' => $key, 'deleted' => $count]);
    }

    /** Исходящий запрос в 1С */
    public function outbound(string $entity, string $endpoint, int $status, array $context = []): void
    {
        $level = $status >= 400 ? 'error' : 'info';
        $this->log($level, "OUTBOUND {$entity} {$endpoint}", array_merge(['status' => $status], $context));
    }

    /** Любая ошибка с трейсом */
    public function error(string $event, \Throwable $e, array $context = []): void
    {
        $this->log('error', "ERROR {$event}", array_merge([
            'message' => $e->getMessage(),
            'file'    => $e->getFile() . ':' . $e->getLine(),
        ], $context));
    }

    /** Произвольное событие */
    public function event(string $event, array $context = [], string $level = 'info'): void
    {
        $this->log($level, $event, $context);
    }
}
