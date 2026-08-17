<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Resets ONLY administrator passwords to a known value.
 *
 * The existing PasswordResetSeeder targets the whole users table unless you
 * remember to pass PASSWORD_RESET_ROLE — forget it once and every employee and
 * attendance checker gets the admin password too. This one cannot do that: the
 * role filter is hardcoded, not an env var someone has to supply.
 *
 * Run on demand only. Deliberately NOT registered in DatabaseSeeder:
 *
 *     php artisan db:seed --class=AdminPasswordResetSeeder
 *
 * @see PasswordResetSeeder for the whole-table equivalent.
 */
class AdminPasswordResetSeeder extends Seeder
{
    /**
     * The password every administrator account is reset to.
     */
    private const NEW_PASSWORD = 'admin123';

    /**
     * Roles this seeder is allowed to touch. Both admin tiers, and nothing else.
     */
    private const ADMIN_ROLES = ['admin', 'super_admin'];

    /**
     * Account created only when no administrator exists at all, so a fresh
     * database still ends up reachable instead of silently doing nothing.
     */
    private const FALLBACK_EMAIL = 'admin@mcclawis.edu.ph';

    public function run(): void
    {
        $hash = Hash::make(self::NEW_PASSWORD);

        $accounts = DB::table('users')
            ->whereIn('role', self::ADMIN_ROLES)
            ->orderBy('role')
            ->get(['email', 'role']);

        if ($accounts->isEmpty()) {
            $this->createFallbackAdmin($hash);

            return;
        }

        DB::table('users')
            ->whereIn('role', self::ADMIN_ROLES)
            ->update($this->resetPayload($hash));

        $this->command?->info(sprintf(
            'Password reset to "%s" for %d administrator account(s):',
            self::NEW_PASSWORD,
            $accounts->count()
        ));

        foreach ($accounts as $account) {
            $this->command?->line("  - {$account->email} ({$account->role})");
        }

        $this->command?->warn('Change this password before the system is used for real payroll.');
    }

    /**
     * Columns to write on reset, limited to those the schema actually has.
     */
    private function resetPayload(string $hash): array
    {
        $payload = [
            'password'   => $hash,
            'updated_at' => now(),
        ];

        // Force a clean login: drop remember-me, the bound session, and any
        // pending or locked-out OTP challenge — otherwise an admin sitting in
        // an OTP lockout stays locked out despite the new password. Each column
        // is schema-guarded because they arrived in later migrations.
        $optional = [
            'remember_token'   => null,
            'session_id'       => null,
            'otp_code'         => null,
            'otp_expires_at'   => null,
            'otp_attempts'     => 0,
            'otp_locked_until' => null,
        ];

        foreach ($optional as $column => $value) {
            if (Schema::hasColumn('users', $column)) {
                $payload[$column] = $value;
            }
        }

        return $payload;
    }

    private function createFallbackAdmin(string $hash): void
    {
        $row = [
            'name'       => 'Admin User',
            'email'      => self::FALLBACK_EMAIL,
            'password'   => $hash,
            'role'       => 'admin',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('users')->insert(array_filter(
            $row,
            fn ($column) => Schema::hasColumn('users', $column),
            ARRAY_FILTER_USE_KEY
        ));

        $this->command?->warn(
            'No administrator existed — created ' . self::FALLBACK_EMAIL
            . ' with password "' . self::NEW_PASSWORD . '".'
        );
    }
}
