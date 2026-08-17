<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Step-up email verification for payslips.
 *
 * A payslip carries net pay, government deduction figures and a home address,
 * so reaching one takes more than an authenticated session: the employee must
 * prove they still control the mailbox the payslip was sent to. Logging in
 * already costs one OTP, so this is a second, narrower factor that guards only
 * the payslip endpoints.
 *
 * All state lives in the session rather than on the users table. That keeps the
 * feature free of a migration, and it makes the unlock naturally per-device —
 * unlocking on a phone does not unlock a shared desktop someone left logged in.
 * The trade-off is that clearing cookies resets the attempt counter, which is
 * why sending is rate limited separately: a fresh guess budget still costs the
 * attacker a fresh code delivered to an inbox they do not control.
 *
 * The code itself is never stored in plaintext — only a hash, so a leaked
 * session file does not hand over a working code.
 */
final class PayslipGate
{
    /** Session key holding the pending challenge (hash, expiry, attempts). */
    private const CHALLENGE = 'payslip:challenge';

    /** Session key holding the unlock expiry timestamp. */
    private const UNLOCKED_UNTIL = 'payslip:unlocked_until';

    /** How long a correct code keeps payslips open. */
    public const UNLOCK_MINUTES = 10;

    /** How long an emailed code stays valid. */
    public const CODE_MINUTES = 5;

    /** Wrong guesses allowed against one code before it is burned. */
    public const MAX_ATTEMPTS = 5;

    /** Minimum gap between two code emails, in seconds. */
    public const RESEND_COOLDOWN = 60;

    /**
     * Is the current session allowed to read payslips right now?
     */
    public static function unlocked(Request $request): bool
    {
        $until = $request->session()->get(self::UNLOCKED_UNTIL);

        if (!$until) {
            return false;
        }

        if (Carbon::now()->greaterThanOrEqualTo(Carbon::parse($until))) {
            $request->session()->forget(self::UNLOCKED_UNTIL);
            return false;
        }

        return true;
    }

    /**
     * Seconds until the current unlock lapses (0 when locked).
     */
    public static function unlockedFor(Request $request): int
    {
        if (!self::unlocked($request)) {
            return 0;
        }

        $until = Carbon::parse($request->session()->get(self::UNLOCKED_UNTIL));

        return max(0, (int) round(Carbon::now()->diffInSeconds($until, false)));
    }

    /**
     * Seconds the caller must wait before another code may be sent (0 when free).
     */
    public static function resendWaitSeconds(Request $request): int
    {
        $challenge = $request->session()->get(self::CHALLENGE);

        if (!$challenge || empty($challenge['sent_at'])) {
            return 0;
        }

        $elapsed = Carbon::now()->diffInSeconds(Carbon::parse($challenge['sent_at']), false);
        $elapsed = abs((int) round($elapsed));

        return max(0, self::RESEND_COOLDOWN - $elapsed);
    }

    /**
     * Record a freshly issued code. Only its hash is kept.
     */
    public static function issue(Request $request, string $code): void
    {
        $request->session()->put(self::CHALLENGE, [
            'hash'       => Hash::make($code),
            'expires_at' => Carbon::now()->addMinutes(self::CODE_MINUTES)->toIso8601String(),
            'attempts'   => 0,
            'sent_at'    => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Check a submitted code and, when it matches, open the unlock window.
     *
     * @return array{ok:bool, error:?string, remaining:?int}
     */
    public static function attempt(Request $request, string $code): array
    {
        $challenge = $request->session()->get(self::CHALLENGE);

        if (!$challenge) {
            return ['ok' => false, 'error' => 'No verification code has been requested yet. Send a new code to continue.', 'remaining' => null];
        }

        if (Carbon::now()->greaterThanOrEqualTo(Carbon::parse($challenge['expires_at']))) {
            $request->session()->forget(self::CHALLENGE);
            return ['ok' => false, 'error' => 'That code has expired. Send a new one to continue.', 'remaining' => null];
        }

        if (!Hash::check($code, $challenge['hash'])) {
            $challenge['attempts']++;

            if ($challenge['attempts'] >= self::MAX_ATTEMPTS) {
                $request->session()->forget(self::CHALLENGE);
                return ['ok' => false, 'error' => 'Too many incorrect codes. Send a new one to try again.', 'remaining' => 0];
            }

            $request->session()->put(self::CHALLENGE, $challenge);
            $remaining = self::MAX_ATTEMPTS - $challenge['attempts'];

            return [
                'ok'        => false,
                'error'     => "That code is not correct. {$remaining} attempt(s) left before it is cancelled.",
                'remaining' => $remaining,
            ];
        }

        // Correct. Burn the challenge so the same code cannot be replayed, and
        // open the window.
        $request->session()->forget(self::CHALLENGE);
        $request->session()->put(
            self::UNLOCKED_UNTIL,
            Carbon::now()->addMinutes(self::UNLOCK_MINUTES)->toIso8601String()
        );

        return ['ok' => true, 'error' => null, 'remaining' => null];
    }

    /**
     * Drop both the unlock and any pending challenge.
     *
     * Called on logout and whenever the employee explicitly re-locks.
     */
    public static function clear(Request $request): void
    {
        $request->session()->forget([self::CHALLENGE, self::UNLOCKED_UNTIL]);
    }

    /**
     * Mask an address for display: jaylianbacolod096@gmail.com -> j••••••••••096@gmail.com
     *
     * The last three characters of the local part are kept so the employee can
     * tell which of their addresses received the code without the page itself
     * disclosing the full address to someone reading over their shoulder.
     */
    public static function maskEmail(?string $email): string
    {
        if (!$email || !str_contains($email, '@')) {
            return 'your email address';
        }

        [$local, $domain] = explode('@', $email, 2);

        if (strlen($local) <= 4) {
            return substr($local, 0, 1) . str_repeat('•', max(1, strlen($local) - 1)) . '@' . $domain;
        }

        return substr($local, 0, 1)
            . str_repeat('•', strlen($local) - 4)
            . substr($local, -3)
            . '@' . $domain;
    }
}
