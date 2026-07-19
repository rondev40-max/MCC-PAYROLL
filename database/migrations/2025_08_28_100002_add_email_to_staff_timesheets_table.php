<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_timesheets', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_timesheets', 'email')) {
                $table->string('email')->nullable()->after('employee_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_timesheets', function (Blueprint $table) {
            if (Schema::hasColumn('staff_timesheets', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};