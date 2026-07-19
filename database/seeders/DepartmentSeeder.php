<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Bachelor of Science in Information Technology',
                'code' => 'BSIT',
                'description' => 'Information Technology Department',
                'is_active' => true,
            ],
            [
                'name' => 'Bachelor of Science in Business Administration',
                'code' => 'BSBA',
                'description' => 'Business Administration Department',
                'is_active' => true,
            ],
            [
                'name' => 'Bachelor of Science in Hotel Management',
                'code' => 'BSHM',
                'description' => 'Hotel Management Department',
                'is_active' => true,
            ],
            [
                'name' => 'Bachelor of Secondary Education',
                'code' => 'BSED',
                'description' => 'Secondary Education Department',
                'is_active' => true,
            ],
            [
                'name' => 'Bachelor of Elementary Education',
                'code' => 'BEED',
                'description' => 'Elementary Education Department',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            \App\Models\Department::create($department);
        }
    }
}
