<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulltimeTimesheet extends Model
{
    protected $fillable = [
        'employee_id',
        'employee_name',
        'email',
        'designation',
        'prov_abr',
        'department',
        'date',
        'period',
        'days',
        // Per-weekday hour columns (mon..sun) added in migration
        'mon_hours',
        'tue_hours',
        'wed_hours',
        'thu_hours',
        'fri_hours',
        'sat_hours',
        'sun_hours',
        'working_days',
        'number_of_days',
        'details',
        'total_hour',
        'rate_per_hour',
        'deduction',
        'total_honorarium',
        // Government Deductions
        'withholding_tax',
        'gsis',
        'philhealth',
        'pag_ibig',
        'sss',
    ];

    protected $casts = [
        'days' => 'array',
        'working_days' => 'array',
    ];

    /**
     * Get the employee that owns the timesheet
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}