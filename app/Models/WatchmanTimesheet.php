<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchmanTimesheet extends Model
{
    protected $fillable = [
        'employee_id',
        'employee_name',
        'email',
        'designation',
        'prov_abr',
        'department',
        'date',
        'month',
        'year',
        'period',
        'working_days',
        'days',
        'details',
        'total_days',
        'rate_per_day',
        'deduction',
        'tax_amount',
        'sss_amount',
        'phic_amount',
        'hdmf_amount',
        'total_honorarium',
        'mon_hours',
        'tue_hours',
        'wed_hours',
        'thu_hours',
        'fri_hours',
        'sat_hours',
        'sun_hours'
    ];

    protected $casts = [
        'days' => 'array', // JSON stored as array
        'working_days' => 'array', // JSON stored as array
        'date' => 'date'
    ];

    /**
     * Get the employee that owns the timesheet
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
