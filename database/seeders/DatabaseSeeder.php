<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //
        $this->call([
            LanguageLineSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            IconListSeeder::class,
            DocumentJournalSeeder::class,
        ]);

        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = ? AND column_default LIKE 'nextval%'", [$tableName]);

            foreach ($columns as $column) {
                $columnName = $column->column_name;
                $sequenceName = DB::selectOne("SELECT pg_get_serial_sequence(?, ?)", [$tableName, $columnName])->pg_get_serial_sequence;

                if ($sequenceName) {
                    DB::statement("SELECT setval('$sequenceName', COALESCE((SELECT MAX($columnName) FROM $tableName) + 1, 1))");
                }
            }
        }

    }
}
