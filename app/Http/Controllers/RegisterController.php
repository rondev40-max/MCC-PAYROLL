<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserVerificationEmail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation Rules
        $validationRules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'role' => 'required|in:super_admin,admin,attendance_checker,employee', // ✅ employee added
        ];

        // Add course validation only if role is attendance_checker
        if ($request->role === 'attendance_checker') {
            $validationRules['course'] = 'required|in:bsit,bsba,bshm,bsed,beed';
        }

        $request->validate($validationRules, [
            'name.regex' => 'Name can only contain letters and spaces.',
            'password.min' => 'Password must be at least 12 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
            'role.required' => 'Please select a user role.',
            'role.in' => 'Invalid role selected. Please choose a valid role.',
            'course.required' => 'Please select a department for attendance checker role.',
            'course.in' => 'Invalid department selected.',
        ]);

        $finalRole = $request->role;

        $hashedPassword = Hash::make($request->password);

        $token = Str::random(60);

        // Prepare data for insertion
        $user = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => $hashedPassword,
            'role'               => $finalRole,
            'course'             => $finalRole === 'attendance_checker' ? $request->course : null,
            'verification_token' => $token,
        ]);

        // Send verification email
        Mail::to($user->email)->send(new UserVerificationEmail($user, $token));

        // ✅ Redirect employees to employee login, others to main login
        if ($finalRole === 'employee') {
            return redirect()->route('employee.login.form')
                ->with('success', 'Registration successful! Please check your email to verify your account.');
        }

        return redirect('/')->with('success', 'Registration successful! Please check your email to verify your account.');
    }
}