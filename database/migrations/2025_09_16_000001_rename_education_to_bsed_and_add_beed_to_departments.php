<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        // Rename department code EDUCATION -> BSED
        $education = DB::table('departments')->where('code', 'EDUCATION')->first();
        if ($education) {
            DB::table('departments')->where('id', $education->id)->update([
                'code' => 'BSED',
                'name' => 'Bachelor of Secondary Education',
                'description' => $education->description ?: 'Secondary Education Department',
                'updated_at' => now(),
            ]);
        }

        // Add BEED if not exists
        $beed = DB::table('departments')->where('code', 'BEED')->first();
        if (!$beed) {
            DB::table('departments')->insert([
                'name' => 'Bachelor of Elementary Education',
                'code' => 'BEED',
                'description' => 'Elementary Education Department',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Align users.course from 'education' -> 'bsed' if the column exists
        if (Schema::hasTable('users')) {
            try {
                DB::table('users')->where('course', 'education')->update(['course' => 'bsed', 'updated_at' => now()]);
            } catch (\Throwable $e) {
                // Silently ignore if column doesn't exist or other issues
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        // Revert BSED -> EDUCATION only if it looks like our earlier change
        $bsed = DB::table('departments')->where('code', 'BSED')->first();
        if ($bsed) {
            DB::table('departments')->where('id', $bsed->id)->update([
                'code' => 'EDUCATION',
                'name' => 'Education Department',
                'updated_at' => now(),
            ]);
        }

        // Remove BEED that we added
        DB::table('departments')->where('code', 'BEED')->delete();

        // Revert users.course if applicable
        if (Schema::hasTable('users')) {
            try {
                DB::table('users')->where('course', 'bsed')->update(['course' => 'education', 'updated_at' => now()]);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};