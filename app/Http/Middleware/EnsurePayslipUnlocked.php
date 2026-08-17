<?php

namespace App\Http\Middleware;

use App\Support\PayslipGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks payslip endpoints until the session has cleared an emailed code.
 *
 * Applied to the read routes only (json / view / download). The listing route
 * stays open so the portal can still show which payslips exist and when they
 * were sent — that metadata is already on the dashboard, and hiding it would
 * force a code prompt before the employee even knows there is anything to open.
 */
class EnsurePayslipUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (PayslipGate::unlocked($request)) {
            return $next($request);
        }

        // The portal opens payslips over fetch(), so answer in the shape the
        // caller can actually act on rather than redirecting an XHR to HTML.
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error'          => 'payslip_locked',
                'message'        => 'Verify your email to open this payslip.',
                'masked_email'   => PayslipGate::maskEmail($request->user()?->email),
                'resend_in'      => PayslipGate::resendWaitSeconds($request),
            ], 423); // 423 Locked — the request is valid, the resource is sealed.
        }

        return redirect()
            ->route('employee.dashboard', ['tab' => 'payslips', 'unlock' => $request->route('payslip')?->id])
            ->with('warning', 'Verify your email to open that payslip.');
    }
}
