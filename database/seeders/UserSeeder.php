<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Kept equal to PasswordResetSeeder::NEW_PASSWORD so that re-running
     * `db:seed` cannot quietly revert accounts that reset seeder just changed.
     */
    private const PASSWORD = 'Ronyl@07';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@mcclawis.edu.ph',
                'role' => 'admin',
                'course' => null,
            ],
            [
                'name' => 'BSIT Attendance Checker',
                'email' => 'bsit@mcclawis.edu.ph',
                'role' => 'attendance_checker',
                'course' => 'bsit',
            ],
            [
                'name' => 'BSBA Attendance Checker',
                'email' => 'bsba@mcclawis.edu.ph',
                'role' => 'attendance_checker',
                'course' => 'bsba',
            ],
            [
                'name' => 'BSHM Attendance Checker',
                'email' => 'bshm@mcclawis.edu.ph',
                'role' => 'attendance_checker',
                'course' => 'bshm',
            ],
            [
                'name' => 'BSED Attendance Checker',
                'email' => 'bsed@mcclawis.edu.ph',
                'role' => 'attendance_checker',
                'course' => 'bsed',
            ],
        ];

        foreach ($users as $user) {
            // Matched on email so re-running `db:seed` updates the existing row
            // instead of violating users_email_unique. Passwords are handed over
            // in plain text on purpose -- the User model's 'hashed' cast hashes
            // them on write.
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => self::PASSWORD,
                    'role' => $user['role'],
                    'course' => $user['course'],
                ]
            );
        }
    }
}
