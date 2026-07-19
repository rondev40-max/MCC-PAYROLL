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
            $table->enum('period', ['1-15', '16-30'])->default('1-15');
            $table->json('working_days')->nullable(); // store array of working days
            $table->decimal('mon_hours', 5, 2)->default(0);
            $table->decimal('tue_hours', 5, 2)->default(0);
            $table->decimal('wed_hours', 5, 2)->default(0);
            $table->decimal('thu_hours', 5, 2)->default(0);
            $table->decimal('fri_hours', 5, 2)->default(0);
            $table->decimal('sat_hours', 5, 2)->default(0);
            $table->decimal('sun_hours', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fulltime_timesheets', function (Blueprint $table) {
            $table->dropColumn(['period', 'working_days', 'mon_hours', 'tue_hours', 'wed_hours', 'thu_hours', 'fri_hours', 'sat_hours', 'sun_hours']);
        });
    }
};
