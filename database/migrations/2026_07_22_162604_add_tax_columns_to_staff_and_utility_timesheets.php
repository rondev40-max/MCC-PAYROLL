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
        Schema::table('staff_timesheets', function (Blueprint $table) {
            $table->decimal('withholding_tax', 10, 2)->nullable()->default(0)->after('deduction');
            $table->decimal('gsis', 10, 2)->nullable()->default(0)->after('withholding_tax');
            $table->decimal('philhealth', 10, 2)->nullable()->default(0)->after('gsis');
            $table->decimal('pag_ibig', 10, 2)->nullable()->default(0)->after('philhealth');
            $table->decimal('sss', 10, 2)->nullable()->default(0)->after('pag_ibig');
        });

        Schema::table('utility_timesheets', function (Blueprint $table) {
            $table->decimal('withholding_tax', 10, 2)->nullable()->default(0)->after('deduction');
            $table->decimal('gsis', 10, 2)->nullable()->default(0)->after('withholding_tax');
            $table->decimal('philhealth', 10, 2)->nullable()->default(0)->after('gsis');
            $table->decimal('pag_ibig', 10, 2)->nullable()->default(0)->after('philhealth');
            $table->decimal('sss', 10, 2)->nullable()->default(0)->after('pag_ibig');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_timesheets', function (Blueprint $table) {
            $table->dropColumn(['withholding_tax', 'gsis', 'philhealth', 'pag_ibig', 'sss']);
        });

        Schema::table('utility_timesheets', function (Blueprint $table) {
            $table->dropColumn(['withholding_tax', 'gsis', 'philhealth', 'pag_ibig', 'sss']);
        });
    }
};
