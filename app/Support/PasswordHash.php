<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Algorithm-tolerant password verification.
 *
 * Laravel's BcryptHasher::check() throws a RuntimeException when the stored
 * hash is not bcrypt (config/hashing.php -> 'bcrypt.verify' => true). That is
 * a useful guard, but it turns a legacy or foreign hash into a 500 on a public
 * login form. This helper keeps the guard by dispatching to the hasher that
 * actually matches the stored hash, and never lets an exception escape.
 */
final class PasswordHash
{
    /**
     * password_get_info()['algoName'] => Laravel hashing driver name.
     *
     * Laravel registers argon2i under the driver name "argon", not "argon2i" —
     * Hash::driver('argon2i') would throw "Driver [argon2i] not supported".
     */
    private const DRIVERS = [
        'bcrypt'   => 'bcrypt',
        'argon2i'  => 'argon',
        'argon2id' => 'argon2id',
    ];

    /**
     * Verify a plaintext value against a stored hash of any supported
     * algorithm. Never throws: null, empty, corrupt or unsupported hashes all
     * verify as false.
     */
    public static function check(?string $plain, ?string $hashed): bool
    {
        if ($plain === null || $plain === '' || $hashed === null || $hashed === '') {
            return false;
        }

        $algo = self::algorithm($hashed);

        if (!isset(self::DRIVERS[$algo])) {
            // Either not a hash at all, or an algorithm this PHP build cannot
            // read — a host without argon2 compiled in reports "unknown" for an
            // "$argon2id$..." string. Either way we cannot verify it.
            Log::warning('PasswordHash: unverifiable stored hash', ['algo' => $algo]);

            return false;
        }

        try {
            return Hash::driver(self::DRIVERS[$algo])->check($plain, $hashed);
        } catch (Throwable $e) {
            Log::error('PasswordHash: hasher threw during check: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * The algorithm name of a stored hash: "bcrypt", "argon2i", "argon2id",
     * "unknown" (unreadable or unsupported) or "none" (null / empty column).
     */
    public static function algorithm(?string $hashed): string
    {
        if ($hashed === null || $hashed === '') {
            return 'none';
        }

        $algo = password_get_info($hashed)['algoName'] ?? 'unknown';

        return $algo === '' ? 'unknown' : (string) $algo;
    }

    /**
     * Algorithm plus cost parameters, for diagnostics. Never includes the hash.
     */
    public static function describe(?string $hashed): string
    {
        $algo = self::algorithm($hashed);

        if ($algo === 'none' || $algo === 'unknown') {
            return $algo;
        }

        $options = password_get_info($hashed)['options'] ?? [];

        if ($options === []) {
            return $algo;
        }

        $parts = [];
        foreach ($options as $key => $value) {
            $parts[] = "{$key}={$value}";
        }

        return $algo . ' (' . implode(', ', $parts) . ')';
    }

    /**
     * True when the stored hash is not in the application's current default
     * driver and options. Hash::needsRehash() delegates to
     * password_needs_rehash(), which returns true rather than throwing when the
     * stored value uses a different algorithm.
     */
    public static function needsUpgrade(?string $hashed): bool
    {
        if ($hashed === null || $hashed === '') {
            return true;
        }

        try {
            return Hash::needsRehash($hashed);
        } catch (Throwable $e) {
            return true;
        }
    }

    /**
     * Verify against an Eloquent user and, on success, transparently upgrade the
     * stored hash to the default driver so legacy rows self-heal on next login.
     * A failed upgrade is logged but never fails the login.
     *
     * @param  \App\Models\User|null  $user
     */
    public static function checkAndUpgrade(?string $plain, $user): bool
    {
        if (!$user || !self::check($plain, $user->password ?? null)) {
            return false;
        }

        if (self::needsUpgrade($user->password ?? null)) {
            try {
                // Hash::make() first so the User model's 'password' => 'hashed'
                // cast sees an already-correct value and passes it through.
                // saveQuietly() keeps this silent upgrade out of model events.
                $user->forceFill(['password' => Hash::make($plain)])->saveQuietly();
            } catch (Throwable $e) {
                Log::error('PasswordHash: rehash persist failed for user '
                    . ($user->id ?? '?') . ': ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Query-builder variant, for rows read through DB::table() rather than
     * Eloquent. The upgrade is written back through the primary key; a row with
     * no id still verifies, it just does not self-heal.
     */
    public static function checkAndUpgradeRow(?string $plain, ?object $row, string $table = 'users'): bool
    {
        if (!$row || !self::check($plain, $row->password ?? null)) {
            return false;
        }

        if (isset($row->id) && self::needsUpgrade($row->password ?? null)) {
            try {
                DB::table($table)->where('id', $row->id)->update([
                    'password'   => Hash::make($plain),
                    'updated_at' => now(),
                ]);
            } catch (Throwable $e) {
                Log::error('PasswordHash: rehash persist failed for ' . $table
                    . ' row ' . $row->id . ': ' . $e->getMessage());
            }
        }

        return true;
    }
}
