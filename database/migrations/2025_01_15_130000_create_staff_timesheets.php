<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_timesheets', function (Blueprint $table) {
            $table->id();  // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('employee_name', 255);
            $table->string('designation', 255)->nullable();
            $table->string('prov_abr', 255)->nullable();
            $table->json('days')->nullable(); // Store daily hours in JSON (ex: {"1":8,"2":7,"3":0,...})
            $table->string('details', 255)->nullable();
            $table->decimal('total_hour', 8, 2)->default(0);
            $table->decimal('rate_per_hour', 8, 2)->default(150.00); // Staff rate
            $table->decimal('deduction', 8, 2)->default(0.00);
            $table->decimal('total_honorarium', 10, 2)->default(0.00);
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_timesheets');
    }
};