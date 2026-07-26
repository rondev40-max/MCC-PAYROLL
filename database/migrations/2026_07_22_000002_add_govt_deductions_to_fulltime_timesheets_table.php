<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fulltime_timesheets', function (Blueprint $table) {
            $table->decimal('withholding_tax', 10, 2)->default(0)->after('deduction');
            $table->decimal('gsis', 10, 2)->default(0)->after('withholding_tax');
            $table->decimal('philhealth', 10, 2)->default(0)->after('gsis');
            $table->decimal('pag_ibig', 10, 2)->default(0)->after('philhealth');
            $table->decimal('sss', 10, 2)->default(0)->nullable()->after('pag_ibig');
        });

        // Also add to parttime_timesheets for consistency (optional but added)
        if (Schema::hasTable('parttime_timesheets')) {
            Schema::table('parttime_timesheets', function (Blueprint $table) {
                if (!Schema::hasColumn('parttime_timesheets', 'withholding_tax')) {
                    $table->decimal('withholding_tax', 10, 2)->default(0)->after('deduction');
                }
                if (!Schema::hasColumn('parttime_timesheets', 'gsis')) {
                    $table->decimal('gsis', 10, 2)->default(0)->after('withholding_tax');
                }
                if (!Schema::hasColumn('parttime_timesheets', 'philhealth')) {
                    $table->decimal('philhealth', 10, 2)->default(0)->after('gsis');
                }
                if (!Schema::hasColumn('parttime_timesheets', 'pag_ibig')) {
                    $table->decimal('pag_ibig', 10, 2)->default(0)->after('philhealth');
                }
                if (!Schema::hasColumn('parttime_timesheets', 'sss')) {
                    $table->decimal('sss', 10, 2)->default(0)->nullable()->after('pag_ibig');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('fulltime_timesheets', function (Blueprint $table) {
            $table->dropColumn(['withholding_tax', 'gsis', 'philhealth', 'pag_ibig', 'sss']);
        });

        if (Schema::hasTable('parttime_timesheets')) {
            Schema::table('parttime_timesheets', function (Blueprint $table) {
                $table->dropColumn(['withholding_tax', 'gsis', 'philhealth', 'pag_ibig', 'sss']);
            });
        }
    }
};

