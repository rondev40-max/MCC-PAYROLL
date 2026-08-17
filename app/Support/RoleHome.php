<?php

namespace App\Support;

/**
 * Where each role lands after a successful sign-in.
 *
 * This mapping used to be an if-chain duplicated in LoginController and
 * OtpVerificationController, and both copies handled super_admin, admin and
 * attendance_checker then fell through to `redirect('/')`. There was no
 * `employee` branch in either — so an employee who entered a correct OTP was
 * logged in and then bounced to the public landing page, which looks from the
 * outside exactly like "nothing happened".
 *
 * Keeping the mapping here means the next role that gets added has one place to
 * be registered rather than two places to be forgotten.
 */
final class RoleHome
{
    /**
     * Named route to send a freshly authenticated user to.
     *
     * Falls back to the landing page only for a role this app does not know,
     * which is a genuine "nowhere to send you" case rather than a gap.
     */
    public static function routeFor(?string $role): string
    {
        return match ($role) {
            'super_admin'        => 'admin.user-management',
            'admin'              => 'admin.dashboard',
            'attendance_checker' => 'attendance.dashboard',
            'employee'           => 'employee.dashboard',
            default              => 'index',
        };
    }

    /**
     * Greeting to flash alongside the redirect.
     */
    public static function messageFor(?string $role): string
    {
        return match ($role) {
            'super_admin'        => 'Super Admin login successful!',
            'admin'              => 'Admin login successful!',
            'attendance_checker' => 'Attendance login successful!',
            'employee'           => 'Welcome back!',
            default              => 'Login successful!',
        };
    }
}
