<?php

use App\Support\Dtr;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tests\TestCase is applied to the whole Unit folder in tests/Pest.php.
// Declaring it again here is what Pest rejects as a duplicate test case.

beforeEach(function () {
    Schema::dropIfExists('attendances');

    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('employee_id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->date('date');
        $table->time('time_in')->nullable();
        $table->time('time_out')->nullable();
        $table->time('am_in_time')->nullable();
        $table->time('am_out_time')->nullable();
        $table->time('pm_in_time')->nullable();
        $table->time('pm_out_time')->nullable();
        $table->decimal('hours_rendered', 5, 2)->default(0);
        $table->unsignedInteger('lateness_minutes')->default(0);
        $table->unsignedInteger('undertime_minutes')->default(0);
        $table->unsignedInteger('overtime_minutes')->default(0);
        $table->decimal('total_hours', 5, 2)->default(0);
        $table->string('status')->default('present');
        $table->text('remarks')->nullable();
        $table->string('course', 50);
        $table->string('employee_name')->nullable();
        $table->string('employee_type', 50)->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('attendances');
});

test('DTR keeps employee types isolated when their numeric ids match', function () {
    DB::table('attendances')->insert([
        [
            'employee_id' => 12,
            'date' => '2026-08-03',
            'am_in_time' => '08:00',
            'am_out_time' => '12:00',
            'pm_in_time' => '13:00',
            'pm_out_time' => '17:00',
            'course' => 'BSIT',
            'employee_name' => 'Alice Fulltime',
            'employee_type' => 'Fulltime',
            'status' => 'present',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'employee_id' => 12,
            'date' => '2026-08-03',
            'am_in_time' => '09:00',
            'am_out_time' => '12:00',
            'pm_in_time' => '13:00',
            'pm_out_time' => '16:00',
            'course' => 'BSIT',
            'employee_name' => 'Pat Parttime',
            'employee_type' => 'Parttime',
            'status' => 'present',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $month = Carbon::create(2026, 8, 1);
    $fulltime = Dtr::build(12, 'BSIT', $month, 'Fulltime');
    $parttime = Dtr::build(12, 'BSIT', $month, 'Parttime');

    expect($fulltime['employee'])
        ->name->toBe('Alice Fulltime')
        ->type->toBe('Fulltime')
        ->and($fulltime['totals']['days'])->toBe(1)
        ->and($fulltime['totals']['worked'])->toBe(480)
        ->and($fulltime['totals']['minutes'])->toBe(0)
        ->and($parttime['employee'])
        ->name->toBe('Pat Parttime')
        ->type->toBe('Parttime')
        ->and($parttime['totals']['days'])->toBe(1)
        ->and($parttime['totals']['worked'])->toBe(360)
        ->and($parttime['totals']['minutes'])->toBe(120);
});

test('worked minutes require complete punch pairs and reject reversed spans', function () {
    expect(Dtr::workedMinutes('08:15', '12:00', '13:00', '17:00'))->toBe(465)
        ->and(Dtr::workedMinutes('08:00', null, '13:00', '17:00'))->toBe(240)
        ->and(Dtr::workedMinutes('12:00', '08:00', '17:00', '13:00'))->toBe(0);
});

test('DTR metrics do not let overtime offset undertime', function () {
    $lateWithOvertime = Dtr::metrics('09:00', '12:00', '13:00', '18:00', 'present');
    $statusOnly = Dtr::metrics(null, null, null, null, 'present');

    expect($lateWithOvertime)
        ->worked->toBe(480)
        ->lateness->toBe(60)
        ->undertime->toBe(60)
        ->overtime->toBe(60)
        ->and($statusOnly)
        ->present->toBeTrue()
        ->undertime->toBe(480);
});
