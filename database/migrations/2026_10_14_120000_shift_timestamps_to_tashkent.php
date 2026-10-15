<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Разовый перевод хранимого времени из UTC в Asia/Tashkent (+5).
 *
 * ЗАЧЕМ. Laravel писал время в UTC (config/app.php: 'timezone' => 'UTC'),
 * а колонки в PostgreSQL объявлены как `timestamp without time zone` —
 * то есть зону не хранят вовсе, в них лежит «голая» дата-время.
 * На страницах значение показывалось без обратного пересчёта, и
 * пользователь видел время на пять часов раньше настоящего.
 *
 * ПОЧЕМУ ОДНОЙ СМЕНЫ APP_TIMEZONE МАЛО. После смены Laravel начнёт
 * читать ту же строку '07:25' уже как ташкентскую и покажет те же
 * '07:25'. Поменяется только запись новых строк. Без этого сдвига в
 * базе окажутся вперемешку два разных смысла одного поля.
 *
 * ЧТО СДВИГАЕТСЯ. Только `timestamp without time zone`. Колонки типа
 * `date` (doc_date, sign_date, created_date) не трогаются намеренно:
 * документ, созданный в 02:00 UTC, уехал бы на соседний день.
 *
 * ЧТО НЕ СДВИГАЕТСЯ. Служебные таблицы фреймворка — см. SKIP_TABLES.
 * Сдвиг сроков в сессиях, кэше и токенах разлогинил бы всех разом
 * или, наоборот, продлил бы просроченные токены на пять часов.
 *
 * ПОРЯДОК ЗАПУСКА — в README. Коротко: бэкап, maintenance mode,
 * APP_TIMEZONE, миграция, config:clear.
 *
 * ПОВТОРНЫЙ ЗАПУСК. Laravel помнит выполненные миграции, поэтому
 * сама по себе она дважды не отработает. Но `migrate:refresh` или
 * `migrate:fresh` сдвинут время ещё раз — не используйте их на этой
 * базе. Откат: `php artisan migrate:rollback --step=1`.
 */
return new class extends Migration
{
    /** Насколько сдвигаем. Ташкент — UTC+5, перехода на летнее время нет. */
    private const SHIFT = '5 hours';

    /**
     * Служебные таблицы: их время живёт по своим правилам и к
     * отображению документов отношения не имеет.
     */
    private const SKIP_TABLES = [
        'migrations',
        'jobs',
        'job_batches',
        'failed_jobs',
        'cache',
        'cache_locks',
        'sessions',
        'password_reset_tokens',
        'password_resets',
        'personal_access_tokens',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
    ];

    public function up(): void
    {
        $this->shiftAll('+');
    }

    public function down(): void
    {
        $this->shiftAll('-');
    }

    /**
     * @param  string  $sign  '+' — из UTC в местное, '-' — обратно
     */
    private function shiftAll(string $sign): void
    {
        $columns = $this->timestampColumns();

        if (empty($columns)) {
            $this->line('Колонок для сдвига не найдено — нечего делать.');
            return;
        }

        $this->line(sprintf(
            'Сдвиг на %s%s: таблиц %d, колонок %d',
            $sign,
            self::SHIFT,
            count(array_unique(array_column($columns, 'table_name'))),
            count($columns)
        ));

        // Всё одной транзакцией: половинчатый результат хуже,
        // чем отсутствие результата — часть времени уехала бы,
        // часть осталась, и разобраться потом было бы нечем.
        DB::transaction(function () use ($columns, $sign) {
            foreach ($columns as $column) {
                $table  = $column->table_name;
                $field  = $column->column_name;

                $affected = DB::update(sprintf(
                    'update %s set %s = %s %s interval %s where %s is not null',
                    $this->quote($table),
                    $this->quote($field),
                    $this->quote($field),
                    $sign,
                    DB::getPdo()->quote(self::SHIFT),
                    $this->quote($field)
                ));

                $this->line(sprintf('  %-38s %s  строк: %d', $table, $field, $affected));
            }
        });

        $this->line('Готово.');
    }

    /**
     * Все колонки без зоны в схеме public, кроме служебных таблиц.
     *
     * Список берётся из information_schema, а не пишется руками:
     * таблиц в проекте много, и забытая колонка означала бы, что
     * в одном месте время местное, а в соседнем — всё ещё UTC.
     */
    private function timestampColumns(): array
    {
        $skip = array_map(fn ($t) => DB::getPdo()->quote($t), self::SKIP_TABLES);

        return DB::select(sprintf(
            "select c.table_name, c.column_name
               from information_schema.columns c
               join information_schema.tables t
                 on t.table_schema = c.table_schema
                and t.table_name   = c.table_name
                and t.table_type   = 'BASE TABLE'
              where c.table_schema = 'public'
                and c.data_type    = 'timestamp without time zone'
                and c.table_name not in (%s)
              order by c.table_name, c.column_name",
            implode(',', $skip)
        ));
    }

    /** Имя в двойных кавычках — в схеме могут быть смешанные регистры. */
    private function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    private function line(string $text): void
    {
        if (isset($this->output)) {
            $this->output->writeln($text);
            return;
        }

        echo $text . PHP_EOL;
    }
};
