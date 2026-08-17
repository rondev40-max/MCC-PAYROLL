<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Resets admin account passwords to a known value.
 *
 * Run on demand only -- this is deliberately NOT registered in DatabaseSeeder:
 *
 *     php artisan db:seed --class=AdminPasswordResetSeeder
 *
 * To target one account instead of every admin, set ADMIN_RESET_EMAIL first.
 */
class AdminPasswordResetSeeder extends Seeder
{
    /**
     * The password every targeted account is reset to.
     */
    private const NEW_PASSWORD = 'Ronyl@07';

    /**
     * Account created only when the users table holds no admin at all.
     */
    private const FALLBACK_EMAIL = 'admin@mcc.edu.ph';

    /**
     * Mirrors the roles LoginController accepts on the admin portal, so this
     * seeder covers exactly the accounts that can sign in as an admin.
     */
    private const ADMIN_ROLES = ['admin', 'super_admin', 'superadmin', 'administrator'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hash = Hash::make(self::NEW_PASSWORD);
        $email = env('ADMIN_RESET_EMAIL');

        $targets = $this->targetQuery($email)->get(['id', 'email', 'role']);

        if ($targets->isEmpty()) {
            $this->createFallbackAdmin($hash, $email);

            return;
        }

        DB::table('users')
            ->whereIn('id', $targets->pluck('id'))
            ->update($this->resetPayload($hash));

        $this->command?->info('Password reset to "' . self::NEW_PASSWORD . '" for ' . $targets->count() . ' account(s):');

        foreach ($targets as $target) {
            $this->command?->line("  - {$target->email} ({$target->role})");
        }
    }

    /**
     * Build the set of accounts to reset.
     */
    private function targetQuery(?string $email)
    {
        $query = DB::table('users');

        return $email
            ? $query->where('email', $email)
            : $query->whereIn('role', self::ADMIN_ROLES);
    }

    /**
     * Columns to write on reset, limited to those the schema actually has.
     */
    private function resetPayload(string $hash): array
    {
        $payload = [
            'password' => $hash,
            'updated_at' => now(),
        ];

        // Force a clean login: drop remember-me, the bound session, and any
        // pending or locked-out OTP challenge. The otp_attempts and
        // otp_locked_until columns only exist after the Aug 2026 migrations,
        // so every one of these is schema-guarded.
        $optional = [
            'remember_token' => null,
            'session_id' => null,
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'otp_locked_until' => null,
        ];

        foreach ($optional as $column => $value) {
            if (Schema::hasColumn('users', $column)) {
                $payload[$column] = $value;
            }
        }

        return $payload;
    }

    /**
     * Seed an admin when there is nothing to reset, so a fresh database still
     * ends up with a usable login instead of silently doing nothing.
     */
    private function createFallbackAdmin(string $hash, ?string $email): void
    {
        $email = $email ?: self::FALLBACK_EMAIL;

        $row = [
            'name' => 'Admin User',
            'email' => $email,
            'password' => $hash,
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('users')->insert(array_filter(
            $row,
            fn ($column) => Schema::hasColumn('users', $column),
            ARRAY_FILTER_USE_KEY
        ));

        $this->command?->warn("No admin account found -- created {$email} with password \"" . self::NEW_PASSWORD . '".');
    }
}
