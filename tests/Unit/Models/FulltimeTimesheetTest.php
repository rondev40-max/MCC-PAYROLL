<?php

use App\Models\FulltimeTimesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('FulltimeTimesheet Model (no days feature)', function () {
    it('can create a fulltime timesheet with required fields', function () {
        $timesheet = FulltimeTimesheet::create([
            'employee_name' => 'John Doe',
            'designation' => 'Senior Instructor',
            'prov_abr' => 'ABC123',
            'department' => 'BSIT',
            'details' => 'Teaching advanced programming courses',
            'total_hour' => 40,
            'rate_per_hour' => 35.50,
            'deduction' => 100.00,
            'total_honorarium' => 1320.00,
        ]);

        expect($timesheet)->toBeInstanceOf(FulltimeTimesheet::class)
            ->and($timesheet->employee_name)->toBe('John Doe')
            ->and($timesheet->designation)->toBe('Senior Instructor')
            ->and($timesheet->prov_abr)->toBe('ABC123')
            ->and($timesheet->department)->toBe('BSIT')
            ->and($timesheet->details)->toBe('Teaching advanced programming courses')
            ->and($timesheet->total_hour)->toBe(40)
            ->and($timesheet->rate_per_hour)->toBe(35.50)
            ->and($timesheet->deduction)->toBe(100.00)
            ->and($timesheet->total_honorarium)->toBe(1320.00);
    });

    it('has correct fillable attributes', function () {
        $timesheet = new FulltimeTimesheet();
        $expected = [
            'employee_id',
            'employee_name',
            'email',
            'designation',
            'prov_abr',
            'department',
            'details',
            'total_hour',
            'rate_per_hour',
            'deduction',
            'total_honorarium',
        ];

        expect($timesheet->getFillable())->toBe($expected);
    });

    it('can handle numeric values correctly', function () {
        $timesheet = FulltimeTimesheet::create([
            'employee_name' => 'Alice Brown',
            'designation' => 'Professor',
            'prov_abr' => 'JKL012',
            'department' => 'BSHM',
            'details' => 'Physics lectures',
            'total_hour' => 24,
            'rate_per_hour' => 45.75,
            'deduction' => 125.50,
            'total_honorarium' => 972.50,
        ]);

        expect($timesheet->total_hour)->toBeInt()
            ->and($timesheet->rate_per_hour)->toBeFloat()
            ->and($timesheet->deduction)->toBeFloat()
            ->and($timesheet->total_honorarium)->toBeFloat();
    });

    it('can handle null values in optional fields', function () {
        $timesheet = FulltimeTimesheet::create([
            'employee_name' => 'David Evans',
            'designation' => 'Lecturer',
            'prov_abr' => 'PQR678',
            'department' => 'EDUCATION',
            'details' => null,
            'total_hour' => 20,
            'rate_per_hour' => 28.00,
            'deduction' => null,
            'total_honorarium' => 560.00,
        ]);

        expect($timesheet->details)->toBeNull()
            ->and($timesheet->deduction)->toBeNull()
            ->and($timesheet->employee_name)->not->toBeNull();
    });

    it('has timestamps', function () {
        $timesheet = FulltimeTimesheet::create([
            'employee_name' => 'Test Employee',
            'designation' => 'Test Position',
            'prov_abr' => 'TEST123',
            'department' => 'BSBA',
            'details' => 'Test details',
            'total_hour' => 8,
            'rate_per_hour' => 20.00,
            'deduction' => 0,
            'total_honorarium' => 160.00,
        ]);

        expect($timesheet->created_at)->not->toBeNull()
            ->and($timesheet->updated_at)->not->toBeNull();
    });
});