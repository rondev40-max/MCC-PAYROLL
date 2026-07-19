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
        Schema::table('fulltime_timesheets', function (Blueprint $table) {
            $table->string('employee_type')->default('Full-time');
        });

        Schema::table('parttime_timesheets', function (Blueprint $table) {
            $table->string('employee_type')->default('Part-time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fulltime_timesheets', function (Blueprint $table) {
            $table->dropColumn('employee_type');
        });

        Schema::table('parttime_timesheets', function (Blueprint $table) {
            $table->dropColumn('employee_type');
        });
    }
};
