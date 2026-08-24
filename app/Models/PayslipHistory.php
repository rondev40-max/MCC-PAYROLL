<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayslipHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'email', 
        'employee_type', 
        'total_honorarium', 
        'error', 
        'sent_at', 
        'days',
        // ⭐️ UPDATED FIELDS ⭐️
        'designation',
        'rate',
        'pay_period',
        'total_hours_or_days',
        // ⭐️ WAGE BREAKDOWN ⭐️
        // Copied off the timesheet when the payslip is generated, because
        // timesheets stay editable afterwards and a payslip has to keep saying
        // what was actually paid. See App\Support\WageLiquidation.
        'gross_pay',
        'withholding_tax',
        'gsis',
        'philhealth',
        'pag_ibig',
        'sss',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'rate_unit',
        'source_type',
        'source_id',
    ];

    protected $dates = [
        'sent_at',
    ];

    // CASTING: Tiyakin na float ang numeric values para tama ang number_format()
    //
    // The breakdown columns are deliberately NOT cast to float: they are
    // nullable, and 'float' would turn a NULL "never recorded" into 0.0, which
    // reads as "nothing was deducted". decimal:2 preserves the null.
    protected $casts = [
        'sent_at' => 'datetime',
        'total_honorarium' => 'float',
        'rate' => 'float',
        'total_hours_or_days' => 'float',
        'gross_pay' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'gsis' => 'decimal:2',
        'philhealth' => 'decimal:2',
        'pag_ibig' => 'decimal:2',
        'sss' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    /** The itemised wage for this payslip, or null when none was recorded. */
    public function liquidation(): ?array
    {
        return \App\Support\WageLiquidation::fromPayslip($this);
    }

    /**
     * What the employee was actually paid.
     *
     * Prefers the recorded net. Older payslips have only `total_honorarium`,
     * which the payslip email treats as gross — so with no breakdown to
     * subtract, that figure is the best available answer and the UI says so
     * rather than implying deductions were zero.
     */
    public function takeHome(): float
    {
        return (float) ($this->net_pay ?? $this->total_honorarium ?? 0);
    }
}