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
            $table->string('employee_name');
            $table->string('designation');
            $table->string('prov_abr')->nullable();
            $table->json('days')->nullable();
            $table->text('details')->nullable();
            $table->integer('total_hour')->default(0);
            $table->decimal('rate_per_hour', 8, 2)->default(0);
            $table->decimal('deduction', 8, 2)->default(0);
            $table->decimal('total_honorarium', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fulltime_timesheets', function (Blueprint $table) {
            $table->dropColumn([
                'employee_name',
                'designation', 
                'prov_abr',
                'days',
                'details',
                'total_hour',
                'rate_per_hour',
                'deduction',
                'total_honorarium'
            ]);
        });
    }
};
