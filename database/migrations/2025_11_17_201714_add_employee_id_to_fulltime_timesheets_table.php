<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the table exists before trying to modify it.
        if (Schema::hasTable('fulltime_timesheets')) {
            Schema::table('fulltime_timesheets', function (Blueprint $table) {
                // Add the employee_id column if it doesn't already exist.
                if (!Schema::hasColumn('fulltime_timesheets', 'employee_id')) {
                    // This links the timesheet to an employee.
                    // onDelete('cascade') means if an employee is deleted, their timesheets are also deleted.
                    $table->foreignId('employee_id')
                          ->nullable()
                          ->constrained('employees')
                          ->onDelete('cascade')
                          ->after('id'); // Placing it after the 'id' column for convention.
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('fulltime_timesheets') && Schema::hasColumn('fulltime_timesheets', 'employee_id')) {
            Schema::table('fulltime_timesheets', function (Blueprint $table) {
                // It's good practice to drop the foreign key constraint before dropping the column.
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }
    }
};
