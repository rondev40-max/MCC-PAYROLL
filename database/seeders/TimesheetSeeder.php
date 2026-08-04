<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\UtilityTimesheet;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample fulltime employees for BSIT
        FulltimeTimesheet::create([
            'employee_name' => 'John Doe',
            'designation' => 'Senior Instructor',
            'department' => 'BSIT',
            'employee_type' => 'Full-time Faculty'
        ]);

        FulltimeTimesheet::create([
            'employee_name' => 'Jane Smith',
            'designation' => 'Professor',
            'department' => 'BSIT',
            'employee_type' => 'Full-time Faculty'
        ]);

        // Create sample fulltime employees for BSBA
        FulltimeTimesheet::create([
            'employee_name' => 'Mike Johnson',
            'designation' => 'Associate Professor',
            'department' => 'BSBA',
            'employee_type' => 'Full-time Faculty'
        ]);

        FulltimeTimesheet::create([
            'employee_name' => 'Sarah Wilson',
            'designation' => 'Business Instructor',
            'department' => 'BSBA',
            'employee_type' => 'Full-time Faculty'
        ]);

        // Create sample fulltime employees for BSHM
        FulltimeTimesheet::create([
            'employee_name' => 'David Brown',
            'designation' => 'Hospitality Professor',
            'department' => 'BSHM',
            'employee_type' => 'Full-time Faculty'
        ]);

        // Create sample fulltime employees for EDUCATION
        FulltimeTimesheet::create([
            'employee_name' => 'Lisa Garcia',
            'designation' => 'Education Professor',
            'department' => 'EDUCATION',
            'employee_type' => 'Full-time Faculty'
        ]);

        // Create sample parttime employees for BSIT
        ParttimeTimesheet::create([
            'employee_name' => 'Robert Lee',
            'designation' => 'Part-time IT Instructor',
            'department' => 'BSIT',
            'employee_type' => 'Part-time Faculty'
        ]);

        // Create sample parttime employees for BSBA
        ParttimeTimesheet::create([
            'employee_name' => 'Maria Rodriguez',
            'designation' => 'Part-time Business Lecturer',
            'department' => 'BSBA',
            'employee_type' => 'Part-time Faculty'
        ]);

        // Create sample parttime employees for BSHM
        ParttimeTimesheet::create([
            'employee_name' => 'James Taylor',
            'designation' => 'Part-time Hospitality Instructor',
            'department' => 'BSHM',
            'employee_type' => 'Part-time Faculty'
        ]);

        // Create sample parttime employees for EDUCATION
        ParttimeTimesheet::create([
            'employee_name' => 'Anna Davis',
            'designation' => 'Part-time Education Lecturer',
            'department' => 'EDUCATION',
            'employee_type' => 'Part-time Faculty'
        ]);

        // Create sample staff employees
        StaffTimesheet::create([
            'employee_name' => 'Lisa Garcia',
            'designation' => 'Administrative Assistant'
        ]);

        StaffTimesheet::create([
            'employee_name' => 'Robert Lee',
            'designation' => 'IT Support'
        ]);

        // Create sample utility employees (if table exists)
        try {
            UtilityTimesheet::create([
                'employee_name' => 'Maria Rodriguez',
                'designation' => 'Maintenance'
            ]);

            UtilityTimesheet::create([
                'employee_name' => 'James Taylor',
                'designation' => 'Security Guard'
            ]);
        } catch (\Exception $e) {
            // Skip utility employees if table doesn't have required columns
        }
    }
}