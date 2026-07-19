<?php

namespace App\Http\Controllers\Auth;

use App\Mail\OtpVerificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $otp = rand(100000, 999999);
        Cache::put('otp_' . $request->email, $otp, now()->addMinutes(10));
        Mail::to($request->email)->send(new OtpVerificationMail($otp));
        return response()->json(['success' => true]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required|digits:6']);
        $cachedOtp = Cache::get('otp_' . $request->email);
        if ($cachedOtp && $request->otp == $cachedOtp) {
            Cache::forget('otp_' . $request->email);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid OTP']);
    }
}
