<?php

use App\Models\PayslipHistory;
use App\Support\WageLiquidation;

// Tests\TestCase is applied to the whole Unit folder in tests/Pest.php.
// Declaring it again here is what Pest rejects as a duplicate test case.

/**
 * The convention under test is the one resources/views/emails/payslip.blade.php
 * already sends to employees, and therefore the one they will hold payroll to:
 *
 *     gross = total_honorarium
 *     total = withholding_tax + gsis + philhealth + pag_ibig + sss + deduction
 *     net   = max(0, gross - total)
 */

/** A timesheet row using the fulltime/parttime/staff/utility column names. */
function standardTimesheet(array $overrides = []): object
{
    return (object) array_merge([
        'withholding_tax' => 1875.00,
        'gsis'            => 1980.00,
        'philhealth'      => 880.00,
        'pag_ibig'        => 200.00,
        'sss'             => 0.00,
        'deduction'       => 350.00,
    ], $overrides);
}

/** A watchman / admin_personnel row, which names the same figures differently. */
function watchmanTimesheet(array $overrides = []): object
{
    return (object) array_merge([
        'tax_amount'  => 500.00,
        'phic_amount' => 300.00,
        'hdmf_amount' => 100.00,
        'sss_amount'  => 450.00,
        'deduction'   => 0.00,
    ], $overrides);
}

test('gross less every deduction is the net', function () {
    $breakdown = WageLiquidation::fromTimesheet(standardTimesheet(), 22000.00);

    expect($breakdown['gross_pay'])->toBe(22000.00)
        ->and($breakdown['total_deductions'])->toBe(5285.00)
        ->and($breakdown['net_pay'])->toBe(16715.00)
        ->and($breakdown['gross_pay'] - $breakdown['total_deductions'])->toBe($breakdown['net_pay']);
});

test('the other-deductions column is subtracted, not already taken out of gross', function () {
    $withOther = WageLiquidation::fromTimesheet(standardTimesheet(['deduction' => 1000.00]), 22000.00);
    $without   = WageLiquidation::fromTimesheet(standardTimesheet(['deduction' => 0.00]), 22000.00);

    expect($withOther['gross_pay'])->toBe($without['gross_pay'])
        ->and($without['net_pay'] - $withOther['net_pay'])->toBe(1000.00);
});

test('watchman and admin personnel column names are read', function () {
    // These tables spell the same four deductions tax_amount / phic_amount /
    // hdmf_amount / sss_amount. Reading only the other spelling returned zero
    // for every one of them.
    $breakdown = WageLiquidation::fromTimesheet(watchmanTimesheet(), 12000.00);

    expect($breakdown['withholding_tax'])->toBe(500.00)
        ->and($breakdown['philhealth'])->toBe(300.00)
        ->and($breakdown['pag_ibig'])->toBe(100.00)
        ->and($breakdown['sss'])->toBe(450.00)
        ->and($breakdown['total_deductions'])->toBe(1350.00)
        ->and($breakdown['net_pay'])->toBe(10650.00);
});

test('a table with no GSIS column reports no GSIS rather than guessing', function () {
    expect(WageLiquidation::deductionFrom(watchmanTimesheet(), 'gsis'))->toBeNull()
        ->and(WageLiquidation::deductionFrom(standardTimesheet(), 'gsis'))->toBe(1980.00);
});

test('net pay never goes below zero', function () {
    $breakdown = WageLiquidation::fromTimesheet(standardTimesheet(), 1000.00);

    expect($breakdown['net_pay'])->toBe(0.00)
        ->and($breakdown['total_deductions'])->toBeGreaterThan(1000.00);
});

test('a payslip with no recorded breakdown is not itemised', function () {
    // The bug this replaces: the payslip PDF read deduction columns that did
    // not exist, so it printed ₱0.00 against each one and a ₱0.00 total. Null
    // has to stay distinguishable from a real zero.
    $payslip = new PayslipHistory(['total_honorarium' => 19500.00]);

    expect(WageLiquidation::isItemised($payslip))->toBeFalse()
        ->and(WageLiquidation::fromPayslip($payslip))->toBeNull()
        ->and($payslip->takeHome())->toBe(19500.00);
});

test('an itemised payslip reports its own recorded figures', function () {
    $payslip = new PayslipHistory([
        'total_honorarium' => 22000.00,
        'gross_pay'        => 22000.00,
        'withholding_tax'  => 1875.00,
        'gsis'             => 1980.00,
        'philhealth'       => 880.00,
        'pag_ibig'         => 200.00,
        'sss'              => 0.00,
        'other_deductions' => 350.00,
        'total_deductions' => 5285.00,
        'net_pay'          => 16715.00,
        'rate'             => 250.00,
        'rate_unit'        => 'hour',
        'total_hours_or_days' => 88.00,
    ]);

    $liquidation = WageLiquidation::fromPayslip($payslip);

    expect(WageLiquidation::isItemised($payslip))->toBeTrue()
        ->and($liquidation['gross'])->toBe(22000.00)
        ->and($liquidation['net'])->toBe(16715.00)
        ->and($liquidation['lines'])->toHaveCount(5)
        ->and(collect($liquidation['lines'])->firstWhere('key', 'gsis')['amount'])->toBe(1980.00)
        // takeHome() must prefer the net, not the gross honorarium.
        ->and($payslip->takeHome())->toBe(16715.00);
});

test('every deduction line is labelled and explained', function () {
    // A line item the employee cannot identify is not a liquidation.
    $payslip = new PayslipHistory(['gross_pay' => 100.00, 'net_pay' => 100.00]);

    foreach (WageLiquidation::fromPayslip($payslip)['lines'] as $line) {
        expect($line['label'])->not->toBeEmpty()
            ->and($line['note'])->not->toBeEmpty();
    }
});

test('units are stated with what they are units of', function () {
    expect(WageLiquidation::unitLabel('hour', 88.0))->toBe('88 hours')
        ->and(WageLiquidation::unitLabel('day', 1.0))->toBe('1 day')
        ->and(WageLiquidation::unitLabel('day', 10.5))->toBe('10.5 days')
        ->and(WageLiquidation::unitLabel(null, 3.0))->toBe('3 units');
});
