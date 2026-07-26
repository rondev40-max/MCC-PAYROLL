<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserVerificationEmail;

class VerificationController extends Controller
{
    public function verify($token)
    {
        $user = User::where('verification_token', $token)->first();

        if ($user) {
            $user->email_verified_at = now();
            $user->verification_token = null;
            $user->save();

            Auth::login($user);

            if ($user->role === 'employee') {
                return redirect()->route('employee.dashboard')->with('success', 'Your email has been verified successfully!');
            }

            return redirect('/dashboard')->with('success', 'Your email has been verified!');
        }

        return redirect('/')->with('error', 'Invalid verification token.');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return back()->with('error', 'Please log in to resend verification email.');
        }

        if ($user->email_verified_at) {
            return back()->with('info', 'Your email address is already verified.');
        }

        $token = Str::random(60);
        $user->verification_token = $token;
        $user->save();

        try {
            Mail::to($user->email)->send(new UserVerificationEmail($user, $token));
            return back()->with('success', 'A new verification link has been sent to your email address (' . $user->email . ').');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send verification email: ' . $e->getMessage());
        }
    }
}