<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('work_date');
            $table->dateTime('clocked_in_at');
            $table->dateTime('clocked_out_at')->nullable();
            $table->unsignedInteger('worked_seconds')->default(0);
            $table->string('status')->default('open');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'work_date']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->date('attendance_payroll_from')->nullable();
        });
        Schema::table('payslip_histories', function (Blueprint $table) {
            $table->json('attendance_snapshot')->nullable();
            $table->decimal('total_hours_or_days', 12, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
        Schema::table('employees', fn (Blueprint $table) => $table->dropColumn('attendance_payroll_from'));
        Schema::table('payslip_histories', function (Blueprint $table) {
            $table->dropColumn('attendance_snapshot');
            $table->decimal('total_hours_or_days', 10, 2)->nullable()->change();
        });
    }
};
