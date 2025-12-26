<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCharColumnsToVarchar extends Migration
{
    public function up()
    {
        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\'');

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = ? AND data_type = 'character'", [$tableName]);

            if (!empty($columns)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        $columnName = $column->column_name;
                        $table->string($columnName)->change();
                    }
                });
            }
        }
    }

    public function down()
    {
        // Optionally, revert changes (though not recommended)
        // You can leave this empty if you don't need to revert
    }
}
