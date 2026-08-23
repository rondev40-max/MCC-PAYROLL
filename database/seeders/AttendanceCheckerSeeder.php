<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AttendanceCheckerSeeder extends Seeder
{
    /**
     * The permanent sign-in password for every attendance checker account.
     *
     * This is the only place the plain text exists. It is hashed with
     * Hash::make() below and never written anywhere in readable form — not to
     * the database, and not to the console output.
     *
     * Give an entry its own 'password' key if one department needs a different
     * value; anything without one falls back to this.
     */
    private const DEFAULT_PASSWORD = 'bsit@12345';

    /**
     * Accounts are matched on `email`.
     *
     * That is the right key here: `users.email` is the only unique column on
     * the table, and LoginController resolves every sign-in with
     * User::where('email', ...). `users.employee_id` exists but is a nullable,
     * non-unique foreign key, so matching on it could update the wrong row or
     * none at all.
     */
    public function run(): void
    {
        $attendanceCheckers = [
            [
                'name' => 'BSIT Attendance Checker',
                'email' => 'bsit@gmail.com',
                'role' => 'attendance_checker',
                'course' => 'bsit',
            ],
            [
                'name' => 'BSBA Attendance Checker',
                'email' => 'bsba@gmail.com',
                'role' => 'attendance_checker',
                'course' => 'bsba',
            ],
            [
                'name' => 'BSHM Attendance Checker',
                'email' => 'bshm@gmail.com',
                'role' => 'attendance_checker',
                'course' => 'bshm',
            ],
            [
                'name' => 'BSED Attendance Checker',
                'email' => 'bsed@gmail.com',
                'role' => 'attendance_checker',
                'course' => 'bsed',
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($attendanceCheckers as $checker) {
            $email = $checker['email'];
            $plain = $checker['password'] ?? self::DEFAULT_PASSWORD;

            $existed = User::where('email', $email)->exists();

            // updateOrCreate keyed on the unique column, so re-running this
            // seeder resets the password of the existing account instead of
            // failing on users_email_unique or adding a duplicate.
            //
            // Hash::make() produces a bcrypt hash here. The User model also
            // casts `password` to 'hashed', and that cast passes an
            // already-hashed value straight through rather than hashing it
            // again, so the stored value is hashed exactly once.
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $checker['name'],
                    'password' => Hash::make($plain),
                    'role' => $checker['role'],
                    'course' => $checker['course'],
                ]
            );

            $existed ? $updated++ : $created++;
        }

        // Deliberately does not echo the password.
        $this->command?->info(
            "Attendance checkers seeded: {$created} created, {$updated} password(s) reset."
        );
    }
}
