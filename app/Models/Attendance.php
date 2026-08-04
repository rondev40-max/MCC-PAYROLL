<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'date',
        'time_in',
        'time_out',
        'hours_rendered',
        'status',
        'remarks',
        'course',
        'employee_name',
        'employee_type',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i:s',
        'time_out' => 'datetime:H:i:s',
        'hours_rendered' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get attendance for a specific week
     */
    public function scopeForWeek($query, $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        return $query->whereBetween('date', [$weekStart, $weekEnd]);
    }

    /**
     * Get attendance for a specific month
     */
    public function scopeForMonth($query, $month = null, $year = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    /**
     * Filter by course
     */
    public function scopeByCourse($query, $course)
    {
        return $query->where('course', strtoupper($course));
    }

    /**
     * Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get summary statistics
     */
    public static function getSummaryForEmployee($employeeId, $startDate = null, $endDate = null)
    {
        $query = static::where('employee_id', $employeeId);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $attendances = $query->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'total_hours' => $attendances->sum('hours_rendered'),
            'average_hours' => $attendances->count() > 0 ? round($attendances->avg('hours_rendered'), 2) : 0,
        ];
    }
}

