<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_name',
        'email',
        'employee_type',
        'designation',
        'department',
        'attendance_date',
        'is_present',
        'hours_worked',
        'time_in',
        'time_out',
        'status',
        'remarks',
        'location'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'hours_worked' => 'decimal:2',
        'is_present' => 'boolean'
    ];

    public function scopeInDateRange($query, $startDate, $endDate = null)
    {
        if (!$endDate) {
            $endDate = $startDate;
        }
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function scopeForEmployee($query, $email)
    {
        return $query->where('email', $email);
    }

    public function getDailyStats()
    {
        return [
            'hours_worked' => $this->hours_worked,
            'is_present' => $this->is_present,
            'status' => $this->status,
            'time_in' => $this->time_in?->format('H:i'),
            'time_out' => $this->time_out?->format('H:i'),
            'remarks' => $this->remarks
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'email', 'email');
    }
}
