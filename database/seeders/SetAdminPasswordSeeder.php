<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetAdminPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'dave.alagban@mcclawis.edu.ph';
        $name = 'Dave Alagban';

        // Use the precomputed secure hash (Argon2id) — DO NOT store plain passwords in repo.
        $passwordHash = '$argon2id$v=19$m=65536,t=4,p=3$S2lzNEh4Zy5JcXlZZzVGVQ$GLJ4/6uZd4lEnWeDXSa0ZUCOpESf6kWbnbTfm7czx5s';

        // Update existing user or create a new admin user if none exists
        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing) {
            DB::table('users')->where('email', $email)->update([
                'name' => $name,
                'password' => $passwordHash,
                'role' => 'admin',
                'email_verified_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'role' => 'admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Admin user set for {$email}");
    }
}
