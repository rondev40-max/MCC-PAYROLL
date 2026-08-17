<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Resets EVERY user password -- admins, attendance checkers and employees
 * alike -- to a single known value.
 *
 * Run on demand only. This is deliberately NOT registered in DatabaseSeeder,
 * so a routine `php artisan db:seed` cannot wipe every password by accident:
 *
 *     php artisan db:seed --class=PasswordResetSeeder
 *
 * Narrow the blast radius with PASSWORD_RESET_EMAIL (one account) or
 * PASSWORD_RESET_ROLE (one role) when you do not want the whole table.
 */
class PasswordResetSeeder extends Seeder
{
    /**
     * The password every targeted account is reset to.
     */
    private const NEW_PASSWORD = 'Ronyl@07';

    /**
     * Account created only when the users table is completely empty.
     */
    private const FALLBACK_EMAIL = 'admin@mcc.edu.ph';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hash = Hash::make(self::NEW_PASSWORD);

        $email = env('PASSWORD_RESET_EMAIL');
        $role = env('PASSWORD_RESET_ROLE');

        $summary = $this->scopedQuery($email, $role)
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->orderBy('role')
            ->get();

        $total = (int) $summary->sum('total');

        if ($total === 0) {
            $this->reportEmptyScope($hash, $email, $role);

            return;
        }

        $this->scopedQuery($email, $role)->update($this->resetPayload($hash));

        $this->command?->info(sprintf(
            'Password reset to "%s" for %d account(s):',
            self::NEW_PASSWORD,
            $total
        ));

        foreach ($summary as $group) {
            $this->command?->line("  - {$group->role}: {$group->total}");
        }
    }

    /**
     * The accounts to reset: the whole table unless a filter narrows it.
     *
     * Rebuilt per call rather than shared, so the counting pass and the update
     * pass cannot interfere with each other.
     */
    private function scopedQuery(?string $email, ?string $role)
    {
        $query = DB::table('users');

        if ($email) {
            $query->where('email', $email);
        }

        if ($role) {
            $query->where('role', $role);
        }

        return $query;
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
     * Nothing matched. A filter that matches nothing is a typo worth naming;
     * an empty table instead gets a usable admin so the app stays reachable.
     */
    private function reportEmptyScope(string $hash, ?string $email, ?string $role): void
    {
        if ($email || $role) {
            $filter = $email ? "email {$email}" : "role {$role}";
            $this->command?->warn("No user matched {$filter} -- nothing was reset.");

            return;
        }

        $this->createFallbackAdmin($hash);
    }

    /**
     * Seed an admin when the table is empty, so a fresh database still ends up
     * with a usable login instead of silently doing nothing.
     */
    private function createFallbackAdmin(string $hash): void
    {
        $row = [
            'name' => 'Admin User',
            'email' => self::FALLBACK_EMAIL,
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

        $this->command?->warn(
            'Users table was empty -- created ' . self::FALLBACK_EMAIL
            . ' with password "' . self::NEW_PASSWORD . '".'
        );
    }
}
