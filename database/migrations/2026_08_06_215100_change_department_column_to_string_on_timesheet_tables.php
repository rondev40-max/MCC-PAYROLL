<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Change the department column from ENUM('BSIT','BSBA','BSHM','EDUCATION')
     * to VARCHAR/string(50) on all timesheet tables so that BSED, BEED, and any
     * future departments are accepted without needing another migration.
     */
    public function up(): void
    {
        $tables = [
            'fulltime_timesheets',
            'parttime_timesheets',
            'utility_timesheets',
            'staff_timesheets',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'department')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('department', 50)->default('BSIT')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For the reverse we use raw SQL only on MySQL since ENUM is MySQL-specific
        $tables = [
            'fulltime_timesheets',
            'parttime_timesheets',
            'utility_timesheets',
            'staff_timesheets',
        ];

        $driver = DB::connection()->getDriverName();

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'department')) {
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE `{$tableName}` MODIFY `department` ENUM('BSIT','BSBA','BSHM','EDUCATION','BSED','BEED') DEFAULT 'BSIT'");
                }
                // On SQLite the column is already a string, nothing to revert
            }
        }
    }
};
