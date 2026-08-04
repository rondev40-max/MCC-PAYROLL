<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'position', 'hourly_salary', 'department_id'];

    /**
     * Get the department that owns the employee
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all fulltime timesheets for this employee
     */
    public function fulltimeTimesheets()
    {
        return $this->hasMany(FulltimeTimesheet::class);
    }

    /**
     * Get all attendance histories for this employee
     */
    public function attendanceHistories()
    {
        return $this->hasMany(AttendanceHistory::class);
    }

    /**
     * Get all parttime timesheets for this employee
     */
    public function parttimeTimesheets()
    {
        return $this->hasMany(ParttimeTimesheet::class);
    }

    /**
     * Get all staff timesheets for this employee
     */
    public function staffTimesheets()
    {
        return $this->hasMany(StaffTimesheet::class);
    }

    /**
     * Get all utility timesheets for this employee
     */
    public function utilityTimesheets()
    {
        return $this->hasMany(UtilityTimesheet::class);
    }


}
