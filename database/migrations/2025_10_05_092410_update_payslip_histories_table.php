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
        if (Schema::hasIndex('payslip_histories', 'payslip_histories_batch_id_index')) {
            Schema::table('payslip_histories', function (Blueprint $table) {
                $table->dropIndex('payslip_histories_batch_id_index');
            });
        }

        Schema::table('payslip_histories', function (Blueprint $table) {
            $table->dropColumn(['batch_id', 'status', 'period']);
            $table->integer('days')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslip_histories', function (Blueprint $table) {
            $table->string('batch_id')->index();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->string('period')->nullable();
            $table->dropColumn('days');
        });
    }
};
