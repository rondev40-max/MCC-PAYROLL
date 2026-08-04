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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'employee_id')) {
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('attendances', 'date')) {
                $table->date('date');
            }
            if (!Schema::hasColumn('attendances', 'time_in')) {
                $table->time('time_in')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'time_out')) {
                $table->time('time_out')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'status')) {
                $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('absent');
            }
            if (!Schema::hasColumn('attendances', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn([
                'employee_id',
                'date',
                'time_in',
                'time_out',
                'status',
                'remarks'
            ]);
        });
    }
};
