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
                // Add daily hours fields, similar to staff_timesheets
                if (!Schema::hasColumn('utility_timesheets', 'mon_hours')) {
                    $table->decimal('mon_hours', 5, 2)->default(0)->after('department');
                    $table->decimal('tue_hours', 5, 2)->default(0)->after('mon_hours');
                    $table->decimal('wed_hours', 5, 2)->default(0)->after('tue_hours');
                    $table->decimal('thu_hours', 5, 2)->default(0)->after('wed_hours');
                    $table->decimal('fri_hours', 5, 2)->default(0)->after('thu_hours');
                    $table->decimal('sat_hours', 5, 2)->default(0)->after('fri_hours');
                    $table->decimal('sun_hours', 5, 2)->default(0)->after('sat_hours');
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
                $table->dropColumn([
                    'mon_hours', 'tue_hours', 'wed_hours', 'thu_hours',
                    'fri_hours', 'sat_hours', 'sun_hours'
                ]);
            });
        }
    }
};