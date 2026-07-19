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
        Schema::table('staff_timesheets', function (Blueprint $table) {
            // Add department column if it doesn't exist
            if (!Schema::hasColumn('staff_timesheets', 'department')) {
                $table->string('department')->nullable()->after('prov_abr');
            }
            
            // Add details column if it doesn't exist
            if (!Schema::hasColumn('staff_timesheets', 'details')) {
                $table->string('details')->nullable()->after('days');
            }
            
            // Add employee_id column if it doesn't exist
            if (!Schema::hasColumn('staff_timesheets', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('id');
            }
            
            // Update existing per-hour columns to per-day if they exist
            if (Schema::hasColumn('staff_timesheets', 'total_hour')) {
                $table->renameColumn('total_hour', 'total_days');
            }
            if (Schema::hasColumn('staff_timesheets', 'rate_per_hour')) {
                $table->renameColumn('rate_per_hour', 'rate_per_day');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_timesheets', function (Blueprint $table) {
            // Remove added columns
            if (Schema::hasColumn('staff_timesheets', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('staff_timesheets', 'details')) {
                $table->dropColumn('details');
            }
            if (Schema::hasColumn('staff_timesheets', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
            
            // Revert per-day columns back to per-hour
            if (Schema::hasColumn('staff_timesheets', 'total_days')) {
                $table->renameColumn('total_days', 'total_hour');
            }
            if (Schema::hasColumn('staff_timesheets', 'rate_per_day')) {
                $table->renameColumn('rate_per_day', 'rate_per_hour');
            }
        });
    }
};
