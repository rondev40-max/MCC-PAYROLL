<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtilityTimesheet extends Model
{
    protected $fillable = [
        'employee_id',
        'employee_name',
        'designation',
        'prov_abr',
        'department',
        'month',
        'year',
        'period',
        'days',
        'details',
        'total_days',
        'rate_per_day',
        'deduction',
        'total_honorarium',
        'mon_hours',
        'tue_hours',
        'wed_hours',
        'thu_hours',
        'fri_hours',
        'sat_hours',
        'sun_hours',
    ];

    protected $casts = [
        'days' => 'array', // JSON stored as array
    ];

    /**
     * Get the employee that owns the timesheet
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}