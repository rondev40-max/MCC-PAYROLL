<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['fulltime_timesheets', 'parttime_timesheets', 'utility_timesheets', 'staff_timesheets'] as $table) {
            if (Schema::hasColumn($table, 'period')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('period', 20)->default('1-15')->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['fulltime_timesheets', 'parttime_timesheets', 'utility_timesheets', 'staff_timesheets'] as $table) {
            if (Schema::hasColumn($table, 'period')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->enum('period', ['1-15', '16-30'])->default('1-15')->change();
                });
            }
        }
    }
};