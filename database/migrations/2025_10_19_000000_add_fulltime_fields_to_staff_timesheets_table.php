<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_timesheets', function (Blueprint $table) {
            // Add date field
            if (!Schema::hasColumn('staff_timesheets', 'date')) {
                $table->date('date')->nullable()->after('department');
            }

            // Add period field
            if (!Schema::hasColumn('staff_timesheets', 'period')) {
                $table->enum('period', ['1-15', '16-end'])->default('1-15')->after('date');
            }

            // Add daily hours fields
            if (!Schema::hasColumn('staff_timesheets', 'mon_hours')) {
                $table->decimal('mon_hours', 5, 2)->default(0);
                $table->decimal('tue_hours', 5, 2)->default(0);
                $table->decimal('wed_hours', 5, 2)->default(0);
                $table->decimal('thu_hours', 5, 2)->default(0);
                $table->decimal('fri_hours', 5, 2)->default(0);
                $table->decimal('sat_hours', 5, 2)->default(0);
                $table->decimal('sun_hours', 5, 2)->default(0);
            }

            // Add working_days field
            if (!Schema::hasColumn('staff_timesheets', 'working_days')) {
                $table->json('working_days')->nullable()->after('period');
            }

            // Add month and year fields for filtering
            if (!Schema::hasColumn('staff_timesheets', 'month')) {
                $table->integer('month')->nullable()->after('working_days');
            }

            if (!Schema::hasColumn('staff_timesheets', 'year')) {
                $table->integer('year')->nullable()->after('month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_timesheets', function (Blueprint $table) {
            // Remove all added columns
            $table->dropColumn([
                'date',
                'period',
                'working_days',
                'mon_hours',
                'tue_hours',
                'wed_hours',
                'thu_hours',
                'fri_hours',
                'sat_hours',
                'sun_hours',
                'month',
                'year'
            ]);
        });
    }
};