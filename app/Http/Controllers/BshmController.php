<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;

class BshmController extends Controller
{
    public function index()
    {
        // Fetch only BSHM department timesheets
        $fulltimeTimesheets = FulltimeTimesheet::where('department', 'BSHM')->get();
        $parttimeTimesheets = ParttimeTimesheet::where('department', 'BSHM')->get();
        
        // Combine and transform the data for attendance display
        $attendanceRecords = collect();
        
        // Process fulltime timesheets
        foreach ($fulltimeTimesheets as $timesheet) {
            $attendanceRecords->push([
                'id' => 'FT-' . $timesheet->id,
                'employee_name' => $timesheet->employee_name,
                'designation' => $timesheet->designation,
                'department' => $timesheet->department ?? 'BSHM',
                'employee_type' => 'Full-time',
                'prov_abr' => $timesheet->prov_abr,
                'attendance' => $this->buildAttendance($timesheet->days),
                'days_worked' => $this->calculateDaysWorked($timesheet->days),
                'total_hours' => $timesheet->total_hour,
                'rate_per_hour' => $timesheet->rate_per_hour,
                'total_honorarium' => $timesheet->total_honorarium,
                'deduction' => $timesheet->deduction,
                'details' => $timesheet->details,
                'status' => $this->determineStatus($timesheet->days),
                'created_at' => $timesheet->created_at,
                'updated_at' => $timesheet->updated_at,
                'original_type' => 'fulltime',
                'original_id' => $timesheet->id
            ]);
        }
        
        // Process parttime timesheets
        foreach ($parttimeTimesheets as $timesheet) {
            $attendanceRecords->push([
                'id' => 'PT-' . $timesheet->id,
                'employee_name' => $timesheet->employee_name,
                'designation' => $timesheet->designation,
                'department' => $timesheet->department ?? 'BSHM',
                'employee_type' => 'Part-time',
                'prov_abr' => $timesheet->prov_abr,
                'attendance' => $this->buildAttendance($timesheet->days),
                'days_worked' => $this->calculateDaysWorked($timesheet->days),
                'total_hours' => $timesheet->total_hour,
                'rate_per_hour' => $timesheet->rate_per_hour,
                'total_honorarium' => $timesheet->total_honorarium,
                'deduction' => $timesheet->deduction,
                'details' => $timesheet->details,
                'status' => $this->determineStatus($timesheet->days),
                'created_at' => $timesheet->created_at,
                'updated_at' => $timesheet->updated_at,
                'original_type' => 'parttime',
                'original_id' => $timesheet->id
            ]);
        }
        
        // Sort by employee name
        $attendanceRecords = $attendanceRecords->sortBy('employee_name');
        
        return view('bshm.index', compact('attendanceRecords'));
    }
    
    /**
     * Convert days array (hours) to boolean attendance per weekday
     */
    private function buildAttendance($days): array
    {
        if (is_string($days)) {
            $days = json_decode($days, true) ?: [];
        }
        if (!is_array($days)) {
            $days = [];
        }
        return [
            'monday' => (int)($days['monday'] ?? 0) > 0,
            'tuesday' => (int)($days['tuesday'] ?? 0) > 0,
            'wednesday' => (int)($days['wednesday'] ?? 0) > 0,
            'thursday' => (int)($days['thursday'] ?? 0) > 0,
            'friday' => (int)($days['friday'] ?? 0) > 0,
            'saturday' => (int)($days['saturday'] ?? 0) > 0,
        ];
    }

    /**
     * Calculate the number of days worked from the days JSON data
     */
    private function calculateDaysWorked($days)
    {
        if (is_string($days)) {
            $days = json_decode($days, true) ?: [];
        }
        if (!$days || !is_array($days)) {
            return 0;
        }
        
        $daysWorked = 0;
        foreach ($days as $day => $hours) {
            if ((int)$hours > 0) {
                $daysWorked++;
            }
        }
        
        return $daysWorked;
    }
    
    /**
     * Determine attendance status based on days worked
     */
    private function determineStatus($days)
    {
        $daysWorked = $this->calculateDaysWorked($days);
        
        if ($daysWorked == 0) {
            return 'absent';
        } elseif ($daysWorked < 10) { // Less than 10 days in a 15-day period
            return 'irregular';
        } else {
            return 'regular';
        }
    }
}
