<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('verification_expires_at')->nullable()->after('verification_token');
        });

        // Preserve previously issued links while moving the database away from
        // bearer secrets stored in plaintext. They receive a short migration
        // grace period; every new link is limited to one hour.
        DB::table('users')
            ->whereNotNull('verification_token')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $token = (string) $user->verification_token;
                    $isSha256 = (bool) preg_match('/^[a-f0-9]{64}$/i', $token);

                    DB::table('users')->where('id', $user->id)->update([
                        'verification_token' => $isSha256 ? $token : hash('sha256', $token),
                        'verification_expires_at' => now()->addDay(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('verification_expires_at');
        });
    }
};
