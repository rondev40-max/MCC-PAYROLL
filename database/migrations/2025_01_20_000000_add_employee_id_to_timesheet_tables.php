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
        // Add employee_id to fulltime_timesheets (guard if table exists)
        if (Schema::hasTable('fulltime_timesheets')) {
            Schema::table('fulltime_timesheets', function (Blueprint $table) {
                if (!Schema::hasColumn('fulltime_timesheets', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable()->after('id');
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
                }
            });
        }

        // Add employee_id to parttime_timesheets
        if (Schema::hasTable('parttime_timesheets')) {
            Schema::table('parttime_timesheets', function (Blueprint $table) {
                if (!Schema::hasColumn('parttime_timesheets', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable()->after('id');
                }
            });
            // Add FK only if employees table exists
            if (Schema::hasTable('employees')) {
                Schema::table('parttime_timesheets', function (Blueprint $table) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
                });
            }
        }

        // Add employee_id to staff_timesheets
        if (Schema::hasTable('staff_timesheets')) {
            Schema::table('staff_timesheets', function (Blueprint $table) {
                if (!Schema::hasColumn('staff_timesheets', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable()->after('id');
                }
            });
            if (Schema::hasTable('employees')) {
                Schema::table('staff_timesheets', function (Blueprint $table) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
                });
            }
        }

        // Add employee_id to utility_timesheets
        if (Schema::hasTable('utility_timesheets')) {
            Schema::table('utility_timesheets', function (Blueprint $table) {
                if (!Schema::hasColumn('utility_timesheets', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable()->after('id');
                }
            });
            if (Schema::hasTable('employees')) {
                Schema::table('utility_timesheets', function (Blueprint $table) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fulltime_timesheets') && Schema::hasColumn('fulltime_timesheets', 'employee_id')) {
            Schema::table('fulltime_timesheets', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }

        if (Schema::hasTable('parttime_timesheets') && Schema::hasColumn('parttime_timesheets', 'employee_id')) {
            Schema::table('parttime_timesheets', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }

        if (Schema::hasTable('staff_timesheets') && Schema::hasColumn('staff_timesheets', 'employee_id')) {
            Schema::table('staff_timesheets', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }

        if (Schema::hasTable('utility_timesheets') && Schema::hasColumn('utility_timesheets', 'employee_id')) {
            Schema::table('utility_timesheets', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }
    }
};