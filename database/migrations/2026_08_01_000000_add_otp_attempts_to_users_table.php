<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Number of consecutive wrong OTP guesses since the code was last (re)issued.
            $table->unsignedTinyInteger('otp_attempts')->default(0);

            // While set and in the future, OTP verification is blocked outright,
            // even if the correct code is entered. Cleared on a successful verify
            // or when a fresh code is sent via resend/login.
            $table->timestamp('otp_locked_until')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_attempts', 'otp_locked_until']);
        });
    }
};
