<?php

namespace App\Mail;

use App\Support\PayslipGate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The step-up code that unseals a payslip.
 *
 * Deliberately separate from OtpMail: that one says "finish signing in", which
 * would be the wrong thing to read when someone is already signed in and is
 * being asked to confirm a payslip. Wording matters here — an employee who gets
 * a login-shaped email out of nowhere reasonably assumes their account is being
 * broken into.
 */
class PayslipOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $minutes;

    public function __construct($otp)
    {
        $this->otp     = $otp;
        $this->minutes = PayslipGate::CODE_MINUTES;
    }

    public function build()
    {
        return $this->subject('Your payslip access code')
                    ->view('emails.payslip-otp');
    }
}
