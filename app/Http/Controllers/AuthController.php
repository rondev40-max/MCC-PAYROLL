<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            // Pinapayagan ang super_admin_key na optional
            'super_admin_key' => 'nullable|string|max:255', 
            
            // FIXED LINE: Idinagdag ang 'super_admin' sa listahan ng valid roles
            'role' => 'required|in:super_admin,admin,attendance_checker',
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
            'role.in' => 'Invalid role selected.',
            'course.required' => 'Please select a department for attendance checker role.',
            'course.in' => 'Invalid department selected.',
        ]);

        // 2. Determine the Final Role (Super Admin Logic)
        $finalRole = $request->role;
        $secretKey = env('SUPER_ADMIN_REGISTRATION_KEY');

        // Check if a Super Admin Key was provided and if it is correct
        if (!empty($request->super_admin_key)) {
            if ($request->super_admin_key === $secretKey) {
                // SUCCESS: Overwrite the role to Super Admin
                $finalRole = 'super_admin';
            } else {
                // FAILED: Maling key, i-return ang error at ipaubaya sa default role validation
                return back()
                    ->withInput()
                    ->withErrors(['super_admin_key' => 'Invalid Super Admin Registration Key.']);
            }
        }

        // 3. Enforce Role-Based Account Limits (Excluding Super Admin)
        
        // Hindi i-check ang limit kung ang role ay magiging super_admin
        if ($finalRole !== 'super_admin') {
            $roleLimits = [
                'admin' => 2,
                'attendance_checker' => 8,
            ];
            
            // Tiyakin na ang $finalRole ay may entry sa $roleLimits bago gamitin
            if (isset($roleLimits[$finalRole])) {
                $limit = $roleLimits[$finalRole];
                $existingCount = DB::table('users')
                    ->where('role', $finalRole)
                    ->count();

                if ($existingCount >= $limit) {
                    $errorMessage = $finalRole === 'admin'
                        ? 'Administrator account limit has been reached. Please contact the system administrator.'
                        : 'Attendance Checker account limit has been reached. Please contact the system administrator.';

                    return back()
                        ->withInput()
                        ->with('error', $errorMessage);
                }
            }
        }
        
        // 4. Hash password with Argon2id
        $hashedPassword = password_hash($request->password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,      // 4 iterations
            'threads' => 3         // 3 threads
        ]);

        // 5. Prepare data for insertion
        $userData = [
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $hashedPassword,
            // Gamitin ang FINAL ROLE na na-determine
            'role'      => $finalRole, 
            // Ang course ay kailangan lang kung ang final role ay 'attendance_checker'
            'course'    => $finalRole === 'attendance_checker' ? $request->course : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert into database
        DB::table('users')->insert($userData);

        // 6. Create success message based on role
        $roleText = ucfirst(str_replace('_', ' ', $finalRole)); // I-format ang role (e.g., Super Admin)
        $courseText = $finalRole === 'attendance_checker' ? ' for ' . strtoupper($request->course) : '';
        
        $successMessage = $roleText . ' account created successfully' . $courseText . '! You can now login.';

        return redirect('/')->with('success', $successMessage);
    }
}