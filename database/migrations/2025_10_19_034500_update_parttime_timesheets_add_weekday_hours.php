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
            // DATING ERROR: $table->enum('period', ['1-15', '16-end'])->default('1-15')->change();
            // INAYOS: Idinagdag ang 'period' column dahil wala pa ito.
            $table->enum('period', ['1-15', '16-end'])->default('1-15');
            
            // Ang 'month' at 'year' ay inilagay na AFTER ang 'period'
            $table->integer('month')->nullable()->after('period');
            $table->integer('year')->nullable()->after('month');
            
            // Iba pang columns na idinagdag mo
            $table->json('working_days')->nullable();
            $table->decimal('mon_hours', 5, 2)->default(0);
            $table->decimal('tue_hours', 5, 2)->default(0);
            $table->decimal('wed_hours', 5, 2)->default(0);
            $table->decimal('thu_hours', 5, 2)->default(0);
            $table->decimal('fri_hours', 5, 2)->default(0);
            $table->decimal('sat_hours', 5, 2)->default(0);
            $table->decimal('sun_hours', 5, 2)->default(0);
            $table->integer('number_of_days')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parttime_timesheets', function (Blueprint $table) {
            // Ito ay magtatanggal ng lahat ng column na idinagdag sa up() method
            $table->dropColumn([
                'period',
                'working_days',
                'month',
                'year',
                'mon_hours',
                'tue_hours',
                'wed_hours',
                'thu_hours',
                'fri_hours',
                'sat_hours',
                'sun_hours',
                'number_of_days'
            ]);
        });
    }
};