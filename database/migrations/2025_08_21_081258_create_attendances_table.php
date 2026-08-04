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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->date('date')->index();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('hours_rendered', 5, 2)->nullable()->default(0);
            $table->string('status')->default('present'); // present, absent, late
            $table->string('remarks')->nullable();
            $table->string('course')->nullable()->index();
            $table->string('employee_name')->nullable();
            $table->string('employee_type')->nullable(); // Full-time, Part-time, Staff, Utility
            $table->timestamps();
            
            // Indexes for queries
            $table->index(['date', 'status']);
            $table->index(['employee_id', 'date']);
            $table->index(['course', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

