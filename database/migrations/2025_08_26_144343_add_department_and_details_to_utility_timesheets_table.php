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
        Schema::table('utility_timesheets', function (Blueprint $table) {
            // Add department column if it doesn't exist
            if (!Schema::hasColumn('utility_timesheets', 'department')) {
                $table->string('department')->nullable()->after('prov_abr');
            }
            
            // Add details column if it doesn't exist
            if (!Schema::hasColumn('utility_timesheets', 'details')) {
                $table->string('details')->nullable()->after('days');
            }
            
            // Add employee_id column if it doesn't exist
            if (!Schema::hasColumn('utility_timesheets', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utility_timesheets', function (Blueprint $table) {
            // Remove added columns
            if (Schema::hasColumn('utility_timesheets', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('utility_timesheets', 'details')) {
                $table->dropColumn('details');
            }
            if (Schema::hasColumn('utility_timesheets', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
        });
    }
};
