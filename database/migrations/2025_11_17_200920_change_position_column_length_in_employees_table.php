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
        Schema::table('employees', function (Blueprint $table) {
            // Change the 'position' column to a longer string (255 characters)
            $table->string('position', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Revert back to a shorter string if needed.
            // The original length is unknown, so we'll guess a common default.
            // This is mainly for rollback purposes.
            $table->string('position', 50)->change();
        });
    }
};
