<?php

namespace App\Http\Controllers;

use App\Mail\PayslipOtpMail;
use App\Support\PayslipGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Issues and checks the step-up code that unseals payslips.
 *
 * @see \App\Support\PayslipGate for the session state and its rationale.
 */
class PayslipAccessController extends Controller
{
    /**
     * Current lock state, so the portal can decide whether to prompt at all.
     */
    public function status(Request $request)
    {
        return response()->json([
            'unlocked'     => PayslipGate::unlocked($request),
            'expires_in'   => PayslipGate::unlockedFor($request),
            'resend_in'    => PayslipGate::resendWaitSeconds($request),
            'masked_email' => PayslipGate::maskEmail($request->user()?->email),
        ]);
    }

    /**
     * Email a fresh code to the address on the account.
     *
     * The destination is always the authenticated user's own address — it is
     * never taken from the request, so this cannot be steered into mailing a
     * code somewhere else.
     */
    public function send(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->email) {
            return response()->json([
                'ok'      => false,
                'message' => 'Your account has no email address on file. Contact the payroll office.',
            ], 422);
        }

        // Already open — no reason to mail another code.
        if (PayslipGate::unlocked($request)) {
            return response()->json([
                'ok'         => true,
                'unlocked'   => true,
                'expires_in' => PayslipGate::unlockedFor($request),
            ]);
        }

        if (($wait = PayslipGate::resendWaitSeconds($request)) > 0) {
            return response()->json([
                'ok'        => false,
                'message'   => "Please wait {$wait}s before requesting another code.",
                'resend_in' => $wait,
            ], 429);
        }

        $code = (string) random_int(100000, 999999);
        PayslipGate::issue($request, $code);

        try {
            Mail::to($user->email)->send(new PayslipOtpMail($code));
        } catch (\Throwable $e) {
            Log::error('Payslip OTP email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'We could not send the code right now. Please try again in a moment.',
            ], 502);
        }

        return response()->json([
            'ok'           => true,
            'unlocked'     => false,
            'masked_email' => PayslipGate::maskEmail($user->email),
            'expires_in'   => PayslipGate::CODE_MINUTES * 60,
            'resend_in'    => PayslipGate::RESEND_COOLDOWN,
        ]);
    }

    /**
     * Check a submitted code and open the unlock window when it matches.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $result = PayslipGate::attempt($request, (string) $request->input('code'));

        if (!$result['ok']) {
            return response()->json([
                'ok'        => false,
                'message'   => $result['error'],
                'remaining' => $result['remaining'],
                'resend_in' => PayslipGate::resendWaitSeconds($request),
            ], 422);
        }

        return response()->json([
            'ok'         => true,
            'unlocked'   => true,
            'expires_in' => PayslipGate::unlockedFor($request),
        ]);
    }

    /**
     * Re-seal payslips immediately, without waiting for the window to lapse.
     */
    public function lock(Request $request)
    {
        PayslipGate::clear($request);

        return response()->json(['ok' => true, 'unlocked' => false]);
    }
}
