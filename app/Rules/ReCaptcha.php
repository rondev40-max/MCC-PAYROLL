<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Verifies a reCAPTCHA v3 token with Google.
 *
 * Only applied when both keys are configured — see [isConfigured]. Without that
 * guard, a deployment with no keys would reject every sign-in, which is a worse
 * outage than the bots the rule exists to stop.
 *
 * Failure handling is deliberately split:
 *
 *   - Google explicitly says the token is bad, or the score is below the
 *     threshold  ->  reject. This is the case the rule is for.
 *   - Google cannot be reached at all (DNS, timeout, 5xx)  ->  allow, and log
 *     it. A payroll system that locks every employee out because a third party
 *     is down has traded a small risk for a large one. Set
 *     services.recaptcha.fail_open to false if you would rather be strict.
 */
class ReCaptcha implements ValidationRule
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct(
        private readonly ?string $expectedAction = null,
        private readonly ?string $remoteIp = null,
    ) {
    }

    /** Both keys present, or the rule must not be applied at all. */
    public static function isConfigured(): bool
    {
        return filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret_key'));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || trim($value) === '') {
            $fail('Please complete the verification check and try again.');

            return;
        }

        try {
            $response = Http::asForm()
                // Without a timeout a slow Google would hold the login request
                // open until PHP's own limit killed it.
                ->timeout((int) config('services.recaptcha.timeout', 5))
                ->post(self::VERIFY_URL, array_filter([
                    'secret'   => config('services.recaptcha.secret_key'),
                    'response' => $value,
                    'remoteip' => $this->remoteIp,
                ]));

            if ($response->failed()) {
                $this->handleUnreachable('siteverify returned HTTP ' . $response->status(), $fail);

                return;
            }

            $body = $response->json();
        } catch (Throwable $e) {
            $this->handleUnreachable($e->getMessage(), $fail);

            return;
        }

        if (!is_array($body) || !($body['success'] ?? false)) {
            Log::info('reCAPTCHA rejected a token', [
                'errors' => $body['error-codes'] ?? null,
            ]);

            $fail('Verification failed. Please try again.');

            return;
        }

        // A token minted on a different page would otherwise pass here, so the
        // action it was issued for is checked when we know what to expect.
        if ($this->expectedAction !== null
            && isset($body['action'])
            && $body['action'] !== $this->expectedAction) {
            Log::warning('reCAPTCHA action mismatch', [
                'expected' => $this->expectedAction,
                'received' => $body['action'],
            ]);

            $fail('Verification failed. Please try again.');

            return;
        }

        // v3 always returns a score; v2 does not, so treat its absence as a pass
        // rather than comparing null against the threshold.
        if (array_key_exists('score', $body)) {
            $minimum = (float) config('services.recaptcha.min_score', 0.5);

            if ((float) $body['score'] < $minimum) {
                Log::info('reCAPTCHA score below threshold', [
                    'score'   => $body['score'],
                    'minimum' => $minimum,
                ]);

                $fail('This request looked automated. Please try again.');
            }
        }
    }

    /**
     * Google was unreachable. Allow or reject according to configuration, but
     * always leave a trace — silent fail-open is how a broken integration goes
     * unnoticed for months.
     */
    private function handleUnreachable(string $reason, Closure $fail): void
    {
        Log::warning('reCAPTCHA verification unreachable', ['reason' => $reason]);

        if (!config('services.recaptcha.fail_open', true)) {
            $fail('Verification is unavailable right now. Please try again shortly.');
        }
    }
}
