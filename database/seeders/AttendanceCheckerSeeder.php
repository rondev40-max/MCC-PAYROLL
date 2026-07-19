<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceCheckerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendanceCheckers = [
            [
                'name' => 'BSIT Attendance Checker',
                'email' => 'bsit@mcc.edu',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'attendance_checker',
                'course' => 'bsit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BSBA Attendance Checker',
                'email' => 'bsba@mcc.edu',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'attendance_checker',
                'course' => 'bsba',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BSHM Attendance Checker',
                'email' => 'bshm@mcc.edu',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'attendance_checker',
                'course' => 'bshm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BSED Attendance Checker',
                'email' => 'bsed@mcc.edu',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'attendance_checker',
                'course' => 'bsed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($attendanceCheckers as $checker) {
            // Check if user already exists
            $existingUser = \Illuminate\Support\Facades\DB::table('users')->where('email', $checker['email'])->first();
            if (!$existingUser) {
                \Illuminate\Support\Facades\DB::table('users')->insert($checker);
            }
        }
    }
}
