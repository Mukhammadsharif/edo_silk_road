<?php

namespace App\Console\Commands;

use App\Models\Didox\DidoxToken;
use App\Services\Didox\DidoxSyncService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Периодическая синхронизация документов Didox (входящих и исходящих).
 *
 * Проходит по всем живым токенам в didox_tokens и подтягивает входящие
 * для каждой пары (user, tin). Токены протухшие (>360 мин) пропускаются —
 * повторный логин требует участия пользователя (ЭЦП/пароль), делать это
 * из крона нельзя.
 *
 * Расписание (app/Console/Kernel.php):
 *   $schedule->command('didox:sync-incoming')->everyTenMinutes();
 *
 * Ручной запуск:
 *   php artisan didox:sync-incoming
 *   php artisan didox:sync-incoming --org=12
 *   php artisan didox:sync-incoming --direction=outgoing
 */
class DidoxSyncIncomingCron extends Command
{
    protected $signature = 'didox:sync-incoming
                            {--org= : Синхронизировать только указанную организацию (id)}
                            {--direction=both : Что синхронизировать: incoming | outgoing | both}';

    protected $description = 'Sync Didox documents (incoming/outgoing) into didox_documents';

    public function __construct(private DidoxSyncService $sync)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = DidoxToken::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now());

        if ($orgId = $this->option('org')) {
            $query->where('org_unit_id', (int) $orgId);
        }

        $tokens = $query->get();

        if ($tokens->isEmpty()) {
            $this->info('Нет живых токенов Didox — синхронизировать нечего.');
            return self::SUCCESS;
        }

        $direction = (string) $this->option('direction');
        if (!in_array($direction, ['incoming', 'outgoing', 'both'], true)) {
            $this->error('Недопустимое значение --direction. Допустимо: incoming, outgoing, both.');
            return self::FAILURE;
        }

        $totalSynced = 0;
        $failed      = 0;

        foreach ($tokens as $t) {
            try {
                $synced = 0;

                if ($direction === 'incoming' || $direction === 'both') {
                    $synced += $this->sync->pullIncoming($t->user_id, $t->org_unit_id);
                }
                if ($direction === 'outgoing' || $direction === 'both') {
                    $synced += $this->sync->pullOutgoing($t->user_id, $t->org_unit_id);
                }

                $totalSynced += $synced;
                $this->line("  [OK] org={$t->org_unit_id} tin={$t->tin} user={$t->user_id}: {$synced} документ(ов)");
            } catch (Throwable $e) {
                $failed++;
                $this->warn("  [!!] org={$t->org_unit_id} tin={$t->tin} user={$t->user_id}: {$e->getMessage()}");
            }
        }

        $this->info("Готово: синхронизировано {$totalSynced}, ошибок {$failed}, токенов обработано {$tokens->count()}.");

        return $failed > 0 && $totalSynced === 0 ? self::FAILURE : self::SUCCESS;
    }
}
