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
        Schema::table('parttime_timesheets', function (Blueprint $table) {
            if (!Schema::hasColumn('parttime_timesheets', 'year')) {
                $table->integer('year')->after('month');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parttime_timesheets', function (Blueprint $table) {
            if (Schema::hasColumn('parttime_timesheets', 'year')) {
                $table->dropColumn('year');
            }
        });
    }
};