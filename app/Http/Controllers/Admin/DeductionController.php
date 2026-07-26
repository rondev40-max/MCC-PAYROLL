<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeductionSetting;
use App\Models\FulltimeTimesheet;
use App\Models\Holiday;
use Carbon\Carbon;

class DeductionController extends Controller
{
    /**
     * Display the main tax & government deductions management page.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $period = $request->get('period', 'auto');

        $settings = DeductionSetting::all()->keyBy('deduction_type');

        // Get employees for the selected period across all categories
        $employees = $this->getEmployeesForPeriod($month, $year, $period);

        // Calculate deductibles for each employee
        $employees = $employees->map(function ($emp) use ($settings) {
            $grossPay = (float)($emp->total_honorarium ?? 0);
            if ($grossPay <= 0) {
                if (isset($emp->total_days) && isset($emp->rate_per_day)) {
                    // Calculate from days * rate - other deductions (for Staff and Utility)
                    $totalDays = (float)($emp->total_days ?? 0);
                    $rate = (float)($emp->rate_per_day ?? 0);
                    $otherDed = (float)($emp->deduction ?? 0);
                    $grossPay = max(0, ($totalDays * $rate) - $otherDed);
                } else {
                    // Calculate from hours * rate - other deductions (for Fulltime and Parttime)
                    $totalHours = (float)($emp->total_hour ?? 0);
                    $rate = (float)($emp->rate_per_hour ?? 0);
                    $otherDed = (float)($emp->deduction ?? 0);
                    $grossPay = max(0, ($totalHours * $rate) - $otherDed);
                }
            }

            $emp->gross_pay = $grossPay;
            $emp->withholding_tax_val = DeductionSetting::calculateDeduction('withholding_tax', $grossPay);
            $emp->gsis_val = DeductionSetting::calculateDeduction('gsis', $grossPay);
            $emp->philhealth_val = DeductionSetting::calculateDeduction('philhealth', $grossPay);
            $emp->pag_ibig_val = DeductionSetting::calculateDeduction('pag_ibig', $grossPay);
            $emp->sss_val = DeductionSetting::calculateDeduction('sss', $grossPay);
            $emp->total_govt_ded = $emp->withholding_tax_val + $emp->gsis_val + $emp->philhealth_val + $emp->pag_ibig_val + $emp->sss_val;
            $emp->net_pay = $grossPay - $emp->total_govt_ded;
            return $emp;
        });

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::createFromDate(null, $m, 1)->format('F');
        }
        $years = range(now()->year - 2, now()->year + 1);

        // Summary stats
        $stats = [
            'total_employees' => $employees->count(),
            'total_gross' => $employees->sum('gross_pay'),
            'total_wtax' => $employees->sum('withholding_tax_val'),
            'total_gsis' => $employees->sum('gsis_val'),
            'total_philhealth' => $employees->sum('philhealth_val'),
            'total_pagibig' => $employees->sum('pag_ibig_val'),
            'total_sss' => $employees->sum('sss_val'),
            'total_govt_ded' => $employees->sum('total_govt_ded'),
            'total_net_pay' => $employees->sum('net_pay'),
        ];

        return view('admin.deductions.index', compact(
            'settings', 'employees', 'month', 'year', 'period', 'months', 'years', 'stats'
        ));
    }

    /**
     * Update deduction settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'deduction_type' => 'required|array',
            'deduction_type.*' => 'required|string|in:withholding_tax,gsis,philhealth,pag_ibig,sss',
            'rate_type' => 'required|array',
            'rate_type.*' => 'required|string|in:percentage,fixed',
            'rate_value' => 'required|array',
            'rate_value.*' => 'required|numeric|min:0|max:100',
            'min_amount' => 'nullable|array',
            'min_amount.*' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|array',
            'max_amount.*' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|array',
        ]);

        $types = $request->deduction_type;

        foreach ($types as $index => $type) {
            $setting = DeductionSetting::where('deduction_type', $type)->first();
            if ($setting) {
                $setting->update([
                    'rate_type' => $request->rate_type[$index] ?? 'percentage',
                    'rate_value' => $request->rate_value[$index] ?? 0,
                    'min_amount' => $request->min_amount[$index] ?? null,
                    'max_amount' => $request->max_amount[$index] ?? null,
                    'is_active' => isset($request->is_active[$index]) ? true : false,
                ]);
            }
        }

        return redirect()->route('admin.deductions.index')->with('success', 'Deduction settings updated successfully.');
    }

    /**
     * Apply computed deductions to employee records for a given period.
     */
    public function applyDeductions(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $period = $request->get('period', 'auto');

        $employees = $this->getEmployeesForPeriod($month, $year, $period);
        $settings = DeductionSetting::all()->keyBy('deduction_type');

        $appliedCount = 0;

        foreach ($employees as $emp) {
            $grossPay = (float)($emp->total_honorarium ?? 0);
            if ($grossPay <= 0) {
                if (isset($emp->total_days) && isset($emp->rate_per_day)) {
                    $totalDays = (float)($emp->total_days ?? 0);
                    $rate = (float)($emp->rate_per_day ?? 0);
                    $otherDed = (float)($emp->deduction ?? 0);
                    $grossPay = max(0, ($totalDays * $rate) - $otherDed);
                } else {
                    $totalHours = (float)($emp->total_hour ?? 0);
                    $rate = (float)($emp->rate_per_hour ?? 0);
                    $otherDed = (float)($emp->deduction ?? 0);
                    $grossPay = max(0, ($totalHours * $rate) - $otherDed);
                }
            }

            $wtax = DeductionSetting::calculateDeduction('withholding_tax', $grossPay);
            $gsis = DeductionSetting::calculateDeduction('gsis', $grossPay);
            $philhealth = DeductionSetting::calculateDeduction('philhealth', $grossPay);
            $pagibig = DeductionSetting::calculateDeduction('pag_ibig', $grossPay);
            $sss = DeductionSetting::calculateDeduction('sss', $grossPay);

            // Update the timesheet record with computed govt deductions
            $emp->update([
                'withholding_tax' => $wtax,
                'gsis' => $gsis,
                'philhealth' => $philhealth,
                'pag_ibig' => $pagibig,
                'sss' => $sss,
            ]);

            $appliedCount++;
        }

        return redirect()->route('admin.deductions.index', ['month' => $month, 'year' => $year, 'period' => $period])
            ->with('success', "Government deductions applied to {$appliedCount} employee(s) successfully.");
    }

    /**
     * Monthly deduction summary report.
     */
    public function summary(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $employees = $this->getEmployeesForPeriod($month, $year, 'all');

        $summary = $employees->map(function ($emp) {
            $grossPay = (float)($emp->total_honorarium ?? 0);
            if ($grossPay <= 0) {
                if (isset($emp->total_days) && isset($emp->rate_per_day)) {
                    $totalDays = (float)($emp->total_days ?? 0);
                    $rate = (float)($emp->rate_per_day ?? 0);
                    $otherDed = (float)($emp->deduction ?? 0);
                    $grossPay = max(0, ($totalDays * $rate) - $otherDed);
                } else {
                    $totalHours = (float)($emp->total_hour ?? 0);
                    $rate = (float)($emp->rate_per_hour ?? 0);
                    $otherDed = (float)($emp->deduction ?? 0);
                    $grossPay = max(0, ($totalHours * $rate) - $otherDed);
                }
            }

            return [
                'name' => $emp->employee_name,
                'designation' => $emp->designation,
                'gross_pay' => $grossPay,
                'withholding_tax' => (float)($emp->withholding_tax ?? 0),
                'gsis' => (float)($emp->gsis ?? 0),
                'philhealth' => (float)($emp->philhealth ?? 0),
                'pag_ibig' => (float)($emp->pag_ibig ?? 0),
                'sss' => (float)($emp->sss ?? 0),
                'other_deduction' => (float)($emp->deduction ?? 0),
                'total_govt_ded' => (float)($emp->withholding_tax ?? 0) + (float)($emp->gsis ?? 0) + (float)($emp->philhealth ?? 0) + (float)($emp->pag_ibig ?? 0) + (float)($emp->sss ?? 0),
                'net_pay' => $grossPay - ((float)($emp->withholding_tax ?? 0) + (float)($emp->gsis ?? 0) + (float)($emp->philhealth ?? 0) + (float)($emp->pag_ibig ?? 0) + (float)($emp->sss ?? 0) + (float)($emp->deduction ?? 0)),
            ];
        });

        $totals = [
            'employees' => $summary->count(),
            'gross_pay' => $summary->sum('gross_pay'),
            'withholding_tax' => $summary->sum('withholding_tax'),
            'gsis' => $summary->sum('gsis'),
            'philhealth' => $summary->sum('philhealth'),
            'pag_ibig' => $summary->sum('pag_ibig'),
            'sss' => $summary->sum('sss'),
            'other_deduction' => $summary->sum('other_deduction'),
            'total_govt_ded' => $summary->sum('total_govt_ded'),
            'net_pay' => $summary->sum('net_pay'),
        ];

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::createFromDate(null, $m, 1)->format('F');
        }
        $years = range(now()->year - 2, now()->year + 1);

        return view('admin.deductions.summary', compact('summary', 'totals', 'month', 'year', 'months', 'years'));
    }

    /**
     * Get employees for a given period.
     */
    private function getEmployeesForPeriod($month, $year, $period)
    {
        $queries = [
            \App\Models\FulltimeTimesheet::query(),
            \App\Models\ParttimeTimesheet::query(),
            \App\Models\StaffTimesheet::query(),
            \App\Models\UtilityTimesheet::query(),
        ];

        $employees = collect();

        foreach ($queries as $query) {
            $query->where('year', $year)->where('month', $month);
            
            if ($period !== 'all' && $period !== 'auto') {
                $query->where('period', $period);
            }
            
            $employees = $employees->merge($query->get());
        }

        return $employees->sortBy('employee_name')->unique('employee_name')->values();
    }
}

