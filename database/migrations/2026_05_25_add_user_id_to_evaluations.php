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
        // Only add user_id column if it doesn't exist
        if (!Schema::hasColumn('evaluations', 'user_id')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->unique('user_id');
            });
        }

        // Update feedback column to JSON if it's text
        if (Schema::hasColumn('evaluations', 'feedback')) {
            // This is handled during the initial migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('evaluations', 'user_id')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropUnique(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
