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
        Schema::table('utility_timesheets', function (Blueprint $table) {
            if (Schema::hasColumn('utility_timesheets', 'department')) {
                $table->dropColumn('department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utility_timesheets', function (Blueprint $table) {
            $table->enum('department', ['BSIT', 'BSBA', 'BSHM', 'EDUCATION'])->default('BSIT')->after('prov_abr');
        });
    }
};