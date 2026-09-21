<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PasswordHash;
use Illuminate\Http\Request;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !PasswordHash::checkAndUpgrade($request->password, $user)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // A password alone must not issue an API token for an account whose inbox has
        // never been verified (for example a just-registered, still-pending employee).
        // Otherwise anyone could register a roster email with their own password and
        // read that employee's payslips. One web sign-in with the emailed code
        // verifies the account.
        $status = strtolower((string) ($user->status ?? 'active'));
        if (!$user->email_verified_at || $status !== 'active') {
            return response()->json([
                'message' => 'Please sign in once on the web portal and enter the emailed code to activate your account.',
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
            ],
        ]);
    }
}