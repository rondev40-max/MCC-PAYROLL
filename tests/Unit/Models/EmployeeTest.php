<?php

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Employee Model', function () {
    it('can create an employee with required fields', function () {
        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Instructor',
            'hourly_salary' => 25.50,
        ]);

        expect($employee)->toBeInstanceOf(Employee::class)
            ->and($employee->name)->toBe('John Doe')
            ->and($employee->email)->toBe('john.doe@example.com')
            ->and($employee->position)->toBe('Instructor')
            ->and($employee->hourly_salary)->toBe(25.50);
    });

    it('can create an employee with department', function () {
        $department = Department::create([
            'name' => 'Computer Science',
            'code' => 'CS',
            'description' => 'Computer Science Department',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'position' => 'Staff',
            'hourly_salary' => 20.00,
            'department_id' => $department->id,
        ]);

        expect($employee->department_id)->toBe($department->id)
            ->and($employee->department)->toBeInstanceOf(Department::class)
            ->and($employee->department->name)->toBe('Computer Science');
    });

    it('belongs to a department', function () {
        $department = Department::create([
            'name' => 'Mathematics',
            'code' => 'MATH',
            'description' => 'Mathematics Department',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'name' => 'Bob Johnson',
            'email' => 'bob.johnson@example.com',
            'position' => 'Instructor',
            'hourly_salary' => 30.00,
            'department_id' => $department->id,
        ]);

        expect($employee->department())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class)
            ->and($employee->department)->toBeInstanceOf(Department::class)
            ->and($employee->department->id)->toBe($department->id);
    });

    it('can have null department_id', function () {
        $employee = Employee::create([
            'name' => 'Alice Brown',
            'email' => 'alice.brown@example.com',
            'position' => 'Utility',
            'hourly_salary' => 15.00,
            'department_id' => null,
        ]);

        expect($employee->department_id)->toBeNull()
            ->and($employee->department)->toBeNull();
    });

    it('has correct fillable attributes', function () {
        $employee = new Employee();
        $expected = ['name', 'email', 'position', 'hourly_salary', 'department_id'];
        
        expect($employee->getFillable())->toBe($expected);
    });

    it('uses HasFactory trait', function () {
        expect(Employee::factory())->toBeInstanceOf(\Illuminate\Database\Eloquent\Factories\Factory::class);
    });

    it('has timestamps', function () {
        $employee = Employee::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'position' => 'Staff',
            'hourly_salary' => 20.00,
        ]);

        expect($employee->created_at)->not->toBeNull()
            ->and($employee->updated_at)->not->toBeNull();
    });
});