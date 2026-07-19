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
        // Remove department column from staff_timesheets table
        Schema::table('staff_timesheets', function (Blueprint $table) {
            if (Schema::hasColumn('staff_timesheets', 'department')) {
                $table->dropColumn('department');
            }
        });

        // Remove department column from utility_timesheets table
        Schema::table('utility_timesheets', function (Blueprint $table) {
            if (Schema::hasColumn('utility_timesheets', 'department')) {
                $table->dropColumn('department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add department column back to staff_timesheets table
        Schema::table('staff_timesheets', function (Blueprint $table) {
            $table->enum('department', ['BSIT', 'BSBA', 'BSHM', 'EDUCATION'])->default('BSIT')->after('prov_abr');
        });

        // Add department column back to utility_timesheets table
        Schema::table('utility_timesheets', function (Blueprint $table) {
            $table->enum('department', ['BSIT', 'BSBA', 'BSHM', 'EDUCATION'])->default('BSIT')->after('prov_abr');
        });
    }
};
