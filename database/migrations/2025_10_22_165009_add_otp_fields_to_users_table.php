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
        // OTP code, 6 digits
        $table->string('otp_code', 6)->nullable(); 
        
        // Expiration timestamp for the code
        $table->timestamp('otp_expires_at')->nullable();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['otp_code', 'otp_expires_at']);
    });
}
};
