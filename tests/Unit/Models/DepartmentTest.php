<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\UtilityTimesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Department Model', function () {
    it('can create a department with required fields', function () {
        $department = Department::create([
            'name' => 'Computer Science',
            'code' => 'CS',
            'description' => 'Computer Science Department',
            'is_active' => true,
        ]);

        expect($department)->toBeInstanceOf(Department::class)
            ->and($department->name)->toBe('Computer Science')
            ->and($department->code)->toBe('CS')
            ->and($department->description)->toBe('Computer Science Department')
            ->and($department->is_active)->toBe(true);
    });

    it('casts is_active as boolean', function () {
        $department = Department::create([
            'name' => 'Mathematics',
            'code' => 'MATH',
            'description' => 'Mathematics Department',
            'is_active' => 1,
        ]);

        expect($department->is_active)->toBeTrue()
            ->and($department->getAttributes()['is_active'])->toBe(1);
    });

    it('has correct fillable attributes', function () {
        $department = new Department();
        $expected = ['name', 'code', 'description', 'is_active'];
        
        expect($department->getFillable())->toBe($expected);
    });

    it('has correct casts', function () {
        $department = new Department();
        $expected = ['is_active' => 'boolean'];
        
        expect($department->getCasts())->toMatchArray($expected);
    });

    it('has many employees relationship', function () {
        $department = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Engineering Department',
            'is_active' => true,
        ]);

        $employee1 = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'position' => 'Instructor',
            'hourly_salary' => 25.00,
            'department_id' => $department->id,
        ]);

        $employee2 = Employee::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'position' => 'Staff',
            'hourly_salary' => 20.00,
            'department_id' => $department->id,
        ]);

        expect($department->employees())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class)
            ->and($department->employees)->toHaveCount(2)
            ->and($department->employees->first()->name)->toBe('John Doe')
            ->and($department->employees->last()->name)->toBe('Jane Smith');
    });

    it('has fulltime timesheets relationship', function () {
        $department = Department::create([
            'name' => 'Science',
            'code' => 'SCI',
            'description' => 'Science Department',
            'is_active' => true,
        ]);

        FulltimeTimesheet::create([
            'employee_name' => 'Test Employee',
            'designation' => 'Instructor',
            'prov_abr' => 'ABC',
            'department' => 'SCI',
            'days' => json_encode(['Monday', 'Tuesday']),
            'details' => 'Test details',
            'total_hour' => 40,
            'rate_per_hour' => 25.00,
            'deduction' => 0,
            'total_honorarium' => 1000.00,
        ]);

        expect($department->fulltimeTimesheets())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class)
            ->and($department->fulltimeTimesheets)->toHaveCount(1);
    });

    it('has parttime timesheets relationship', function () {
        $department = Department::create([
            'name' => 'Business',
            'code' => 'BUS',
            'description' => 'Business Department',
            'is_active' => true,
        ]);

        ParttimeTimesheet::create([
            'employee_name' => 'Part Time Employee',
            'designation' => 'Instructor',
            'prov_abr' => 'DEF',
            'department' => 'BUS',
            'days' => json_encode(['Wednesday', 'Thursday']),
            'details' => 'Part time details',
            'total_hour' => 20,
            'rate_per_hour' => 30.00,
            'deduction' => 50,
            'total_honorarium' => 550.00,
        ]);

        expect($department->parttimeTimesheets())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class)
            ->and($department->parttimeTimesheets)->toHaveCount(1);
    });

    it('scopes to active departments only', function () {
        Department::create([
            'name' => 'Active Department',
            'code' => 'ACT',
            'description' => 'Active Department',
            'is_active' => true,
        ]);

        Department::create([
            'name' => 'Inactive Department',
            'code' => 'INACT',
            'description' => 'Inactive Department',
            'is_active' => false,
        ]);

        $activeDepartments = Department::active()->get();

        expect($activeDepartments)->toHaveCount(1)
            ->and($activeDepartments->first()->name)->toBe('Active Department')
            ->and($activeDepartments->first()->is_active)->toBe(true);
    });

    it('uses HasFactory trait', function () {
        expect(Department::factory())->toBeInstanceOf(\Illuminate\Database\Eloquent\Factories\Factory::class);
    });

    it('has timestamps', function () {
        $department = Department::create([
            'name' => 'Test Department',
            'code' => 'TEST',
            'description' => 'Test Description',
            'is_active' => true,
        ]);

        expect($department->created_at)->not->toBeNull()
            ->and($department->updated_at)->not->toBeNull();
    });
});