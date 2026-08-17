<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes critical database schema issues that cause:
     * - HTTP 500 errors when fetching attendance data
     * - Missing timestamp columns
     * - Inconsistent column names across timesheet tables
     */
    public function up(): void
    {
        // FIX #1: Ensure attendances table has required columns
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                // Add missing created_at if it doesn't exist
                if (!Schema::hasColumn('attendances', 'created_at')) {
                    $table->timestamp('created_at')->nullable()->after('updated_at');
                }
                
                // Add missing updated_at if it doesn't exist
                if (!Schema::hasColumn('attendances', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }

                // Ensure employee_name and employee_type exist
                if (!Schema::hasColumn('attendances', 'employee_name')) {
                    $table->string('employee_name')->nullable()->after('employee_id');
                }

                if (!Schema::hasColumn('attendances', 'employee_type')) {
                    $table->string('employee_type')->nullable()->after('employee_name');
                }
            });
        }

        // FIX #2: Ensure attendance_histories table exists and has correct structure
        if (Schema::hasTable('attendance_histories')) {
            Schema::table('attendance_histories', function (Blueprint $table) {
                // Add missing columns
                if (!Schema::hasColumn('attendance_histories', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (!Schema::hasColumn('attendance_histories', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }

                if (!Schema::hasColumn('attendance_histories', 'location')) {
                    $table->string('location')->nullable();
                }

                if (!Schema::hasColumn('attendance_histories', 'remarks')) {
                    $table->text('remarks')->nullable();
                }
            });
        }

        // FIX #3: Ensure timesheet tables have consistent structure
        $timesheetTables = [
            'fulltime_timesheets',
            'parttime_timesheets',
            'staff_timesheets',
            'utility_timesheets',
        ];

        foreach ($timesheetTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table_schema) use ($table) {
                    // Ensure email column exists
                    if (!Schema::hasColumn($table, 'email')) {
                        $table_schema->string('email')->nullable();
                    }

                    // Ensure employee_name exists
                    if (!Schema::hasColumn($table, 'employee_name')) {
                        $table_schema->string('employee_name')->nullable();
                    }

                    // Ensure designation exists
                    if (!Schema::hasColumn($table, 'designation')) {
                        $table_schema->string('designation')->nullable();
                    }

                    // Ensure department exists
                    if (!Schema::hasColumn($table, 'department')) {
                        $table_schema->string('department')->nullable();
                    }

                    // Ensure days column is JSON
                    if (!Schema::hasColumn($table, 'days')) {
                        $table_schema->json('days')->nullable();
                    }

                    // For staff_timesheets: ensure employee_type exists
                    // (Note: It might not exist in original schema, but other fields should)
                });
            }
        }

        // FIX #4: Add indexes for better query performance
        if (Schema::hasTable('attendances')) {
            if (!Schema::hasIndex('attendances', 'idx_attendances_employee_date')) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->index(['employee_id', 'date'], 'idx_attendances_employee_date');
                });
            }

            if (!Schema::hasIndex('attendances', 'idx_attendances_course')) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->index(['course'], 'idx_attendances_course');
                });
            }
        }

        if (Schema::hasTable('attendance_histories')
            && !Schema::hasIndex('attendance_histories', 'idx_history_date')) {
            Schema::table('attendance_histories', function (Blueprint $table) {
                $table->index(['attendance_date'], 'idx_history_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only adds columns, so rollback is safe
        // Dropping columns might cause data loss, so we skip that
        
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                // Optionally drop indexes we created
                try {
                    $table->dropIndex('idx_attendances_employee_date');
                    $table->dropIndex('idx_attendances_course');
                } catch (\Exception $e) {
                    // Index might not exist, ignore
                }
            });
        }

        if (Schema::hasTable('attendance_histories')) {
            Schema::table('attendance_histories', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_history_date');
                } catch (\Exception $e) {
                    // Index might not exist, ignore
                }
            });
        }
    }
};
