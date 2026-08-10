<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_personnel_timesheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name', 255);
            $table->string('email')->nullable();
            $table->string('designation', 255)->nullable();
            $table->string('prov_abr', 255)->nullable();
            $table->string('department')->nullable();
            $table->date('date')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('period', 50)->nullable();
            
            $table->json('working_days')->nullable();
            $table->json('days')->nullable();
            $table->string('details', 255)->nullable();
            
            $table->decimal('total_days', 8, 2)->default(0);
            $table->decimal('rate_per_day', 8, 2)->default(0.00);
            
            $table->decimal('mon_hours', 8, 2)->default(0);
            $table->decimal('tue_hours', 8, 2)->default(0);
            $table->decimal('wed_hours', 8, 2)->default(0);
            $table->decimal('thu_hours', 8, 2)->default(0);
            $table->decimal('fri_hours', 8, 2)->default(0);
            $table->decimal('sat_hours', 8, 2)->default(0);
            $table->decimal('sun_hours', 8, 2)->default(0);
            
            $table->decimal('deduction', 8, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00)->nullable();
            $table->decimal('sss_amount', 10, 2)->default(0.00)->nullable();
            $table->decimal('phic_amount', 10, 2)->default(0.00)->nullable();
            $table->decimal('hdmf_amount', 10, 2)->default(0.00)->nullable();
            
            $table->decimal('total_honorarium', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_personnel_timesheets');
    }
};
