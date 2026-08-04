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
        Schema::table('payslip_histories', function (Blueprint $table) {
            $table->string('designation')->nullable()->after('employee_type');
            $table->string('pay_period')->nullable()->after('designation');
            $table->decimal('rate', 10, 2)->nullable()->after('pay_period');
            $table->decimal('total_hours_or_days', 10, 2)->nullable()->after('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslip_histories', function (Blueprint $table) {
            $table->dropColumn(['designation', 'pay_period', 'rate', 'total_hours_or_days']);
        });
    }
};
