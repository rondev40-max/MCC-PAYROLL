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
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'am_in_time')) {
                    $table->time('am_in_time')->nullable()->after('time_in');
                }
                if (!Schema::hasColumn('attendances', 'am_out_time')) {
                    $table->time('am_out_time')->nullable()->after('am_in_time');
                }
                if (!Schema::hasColumn('attendances', 'pm_in_time')) {
                    $table->time('pm_in_time')->nullable()->after('am_out_time');
                }
                if (!Schema::hasColumn('attendances', 'pm_out_time')) {
                    $table->time('pm_out_time')->nullable()->after('pm_in_time');
                }
                if (!Schema::hasColumn('attendances', 'lateness_minutes')) {
                    $table->integer('lateness_minutes')->nullable()->default(0)->after('pm_out_time');
                }
                if (!Schema::hasColumn('attendances', 'undertime_minutes')) {
                    $table->integer('undertime_minutes')->nullable()->default(0)->after('lateness_minutes');
                }
                if (!Schema::hasColumn('attendances', 'overtime_minutes')) {
                    $table->integer('overtime_minutes')->nullable()->default(0)->after('undertime_minutes');
                }
                if (!Schema::hasColumn('attendances', 'total_hours')) {
                    $table->decimal('total_hours', 5, 2)->nullable()->default(0)->after('overtime_minutes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn([
                    'am_in_time',
                    'am_out_time',
                    'pm_in_time',
                    'pm_out_time',
                    'lateness_minutes',
                    'undertime_minutes',
                    'overtime_minutes',
                    'total_hours',
                ]);
            });
        }
    }
};
