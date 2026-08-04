<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('utility_timesheets')) {
            Schema::table('utility_timesheets', function (Blueprint $table) {
                if (!Schema::hasColumn('utility_timesheets', 'month')) {
                    $table->integer('month')->nullable()->after('id');
                }
                if (!Schema::hasColumn('utility_timesheets', 'year')) {
                    $table->integer('year')->nullable()->after('month');
                }
                if (!Schema::hasColumn('utility_timesheets', 'period')) {
                    // store periods like "1-15" or "16-31"
                    $table->string('period')->nullable()->after('year');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('utility_timesheets')) {
            Schema::table('utility_timesheets', function (Blueprint $table) {
                if (Schema::hasColumn('utility_timesheets', 'period')) {
                    $table->dropColumn('period');
                }
                if (Schema::hasColumn('utility_timesheets', 'year')) {
                    $table->dropColumn('year');
                }
                if (Schema::hasColumn('utility_timesheets', 'month')) {
                    $table->dropColumn('month');
                }
            });
        }
    }
};
