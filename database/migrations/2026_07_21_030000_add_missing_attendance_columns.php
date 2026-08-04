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
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('employee_id');
                }

                if (!Schema::hasColumn('attendances', 'hours_rendered')) {
                    $table->decimal('hours_rendered', 5, 2)->nullable()->default(0)->after('time_out');
                }

                // Ensure indexes
                try {
                    $table->index('user_id', 'idx_attendances_user');
                } catch (\Exception $e) {
                    // ignore if index exists or cannot be created
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_attendances_user');
                } catch (\Exception $e) {
                    // ignore
                }

                if (Schema::hasColumn('attendances', 'hours_rendered')) {
                    $table->dropColumn('hours_rendered');
                }

                if (Schema::hasColumn('attendances', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};
