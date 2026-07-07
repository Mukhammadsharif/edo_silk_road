<?php

namespace App\Console\Commands;

use App\Models\Didox\DidoxToken;
use App\Services\Didox\DidoxSyncService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Периодическая синхронизация входящих документов Didox.
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
 *   php artisan didox:sync-incoming --tin=207119963
 */
class DidoxSyncIncomingCron extends Command
{
    protected $signature = 'didox:sync-incoming
                            {--org= : Синхронизировать только указанную организацию (id)}';

    protected $description = 'Sync incoming Didox documents into didox_documents (vkladka Vneshniy dokumentooborot)';

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

        $totalSynced = 0;
        $failed      = 0;

        foreach ($tokens as $t) {
            try {
                $synced = $this->sync->pullIncoming($t->user_id, $t->org_unit_id);
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
