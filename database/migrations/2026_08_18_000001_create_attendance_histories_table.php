<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_histories')) {
            return;
        }

        Schema::create('attendance_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_name');
            $table->string('email')->nullable();
            $table->string('employee_type', 50);
            $table->string('designation')->nullable();
            $table->string('department', 100);
            $table->date('attendance_date');
            $table->boolean('is_present')->default(false);
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('status', 50)->default('present');
            $table->text('remarks')->nullable();
            $table->string('location')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('email');
            $table->index('attendance_date');
            $table->index(['department', 'attendance_date']);
            $table->unique(
                ['employee_id', 'department', 'employee_type', 'attendance_date'],
                'attendance_history_identity_unique'
            );
        });
    }

    public function down(): void
    {
        // A legacy deployment may already own this table. Avoid deleting its
        // audit history when this compatibility migration is rolled back.
    }
};
