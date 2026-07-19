<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParttimeTimesheet extends Model
{
    protected $fillable = [
        'employee_id',
        'employee_name',
        'email',
        'designation',
        'prov_abr',
        'department',
        'month', // Add month to fillable
        'year', // Add year to fillable
        'date',
        'period',
        'mon_hours',
        'tue_hours',
        'wed_hours',
        'thu_hours',
        'fri_hours',
        'sat_hours',
        'sun_hours',
        'details',
        'total_hour',
        'rate_per_hour',
        'deduction',
        'total_honorarium',
    ];

    protected $casts = [
        'date' => 'date',
        // 'days' and 'weekday_hours' are no longer needed as they are not columns.
    ];

        // Default hours per weekday
        public static $defaultWeekdayHours = [
            'mon' => 0,
            'tue' => 0,
            'wed' => 0,
            'thu' => 0,
            'fri' => 0,
            'sat' => 0,
            'sun' => 0
        ];
    /**
     * Get the employee that owns the timesheet
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}