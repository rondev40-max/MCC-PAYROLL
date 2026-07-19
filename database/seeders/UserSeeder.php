<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@mcc.edu.ph',
            'password' => Hash::make('admin123456'),
            'role' => 'admin',
            'course' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create attendance checker users for different courses
        DB::table('users')->insert([
            'name' => 'BSIT Attendance Checker',
            'email' => 'bsit@mcc.edu.ph',
            'password' => Hash::make('bsit123456'),
            'role' => 'attendance_checker',
            'course' => 'bsit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'BSBA Attendance Checker',
            'email' => 'bsba@mcc.edu.ph',
            'password' => Hash::make('bsba123456'),
            'role' => 'attendance_checker',
            'course' => 'bsba',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'BSHM Attendance Checker',
            'email' => 'bshm@mcc.edu.ph',
            'password' => Hash::make('bshm123456'),
            'role' => 'attendance_checker',
            'course' => 'bshm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'BSED Attendance Checker',
            'email' => 'bsed@mcc.edu.ph',
            'password' => Hash::make('bsed123456'),
            'role' => 'attendance_checker',
            'course' => 'bsed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
