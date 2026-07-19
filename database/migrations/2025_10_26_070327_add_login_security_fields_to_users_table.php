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
        Schema::table('users', function (Blueprint $table) {
            // Last Login at IP Address
            $table->timestamp('last_login_at')->nullable()->after('password'); // Para sa Last Login
            $table->string('last_login_ip')->nullable()->after('last_login_at'); // Para sa IP Address
            
            // Session ID
            $table->string('session_id')->nullable()->after('last_seen_at'); // Para sa Session ID
            
            // Status (Hal. para sa 'Suspended' o 'Active')
            $table->string('status')->default('active')->after('session_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_login_ip', 'session_id', 'status']);
        });
    }
};