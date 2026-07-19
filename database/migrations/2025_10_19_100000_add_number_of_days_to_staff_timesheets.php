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
            if (!Schema::hasColumn('staff_timesheets', 'number_of_days')) {
                $table->integer('number_of_days')->default(0)->after('working_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_timesheets', function (Blueprint $table) {
            $table->dropColumn('number_of_days');
        });
    }
};