<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class VerificationController extends Controller
{
    public function verify(Request $request, string $token)
    {
        $query = User::where('verification_token', hash('sha256', $token));

        // Only enforce expiry once the expiry column exists in this database.
        if (Schema::hasColumn('users', 'verification_expires_at')) {
            $query->where('verification_expires_at', '>', now());
        }

        $user = $query->first();

        if ($user && $user->role === 'employee') {
            $user->email_verified_at = now();
            $user->verification_token = null;
            if (Schema::hasColumn('users', 'verification_expires_at')) {
                $user->verification_expires_at = null;
            }
            $user->status = 'active';
            $user->save();

            // Verification proves inbox ownership; it is not a login. The user
            // must still complete the normal password and OTP sign-in flow.
            $request->session()->regenerateToken();

            return redirect()->route('employee.login.form')->with('success', 'Your email has been verified. You can now sign in.');
        }

        Log::notice('Invalid or expired email verification link used', ['ip' => $request->ip()]);

        return redirect()->route('employee.login.form')->with('error', 'This verification link is invalid or has expired. Request a new link to continue.');
    }

}