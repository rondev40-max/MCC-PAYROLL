<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name');
            $table->string('email')->index();
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('work_type')->nullable();
            $table->text('task')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('hours', 8, 2)->default(0);
            $table->string('status')->default('Submitted');
            $table->timestamps();

            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_timesheets');
    }
};
