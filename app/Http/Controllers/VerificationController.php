<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

            return redirect('/dashboard')->with('success', 'Your email has been verified!');
        }

        return redirect('/')->with('error', 'Invalid verification token.');
    }
}