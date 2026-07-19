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
            $table->json('weekday_hours')->nullable()->after('days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parttime_timesheets', function (Blueprint $table) {
            $table->dropColumn('weekday_hours');
        });
    }
};