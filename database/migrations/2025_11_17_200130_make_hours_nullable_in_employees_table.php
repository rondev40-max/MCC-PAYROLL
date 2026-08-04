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
            // Change the 'hours' column to be nullable.
            // We assume it's a decimal column based on the form fields.
            $table->decimal('hours', 8, 2)->nullable()->change();
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
            // Revert the column to be not nullable.
            // You might need to handle existing null values if you ever run this.
            $table->decimal('hours', 8, 2)->nullable(false)->change();
        });
    }
};
