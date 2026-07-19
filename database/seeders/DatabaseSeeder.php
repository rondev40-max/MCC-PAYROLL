<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the UserSeeder to create admin and attendance users
        $this->call([
            UserSeeder::class,
            AttendanceCheckerSeeder::class,
            TimesheetSeeder::class,
        ]);
    }
}
