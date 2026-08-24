<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'position', 'hourly_salary', 'department_id'];

    /**
     * The master-list row belonging to a signed-in account, or null.
     *
     * Email is compared case- and whitespace-insensitively. The master list is
     * typed in by hand from the admin side, so an instructor entered as
     * "Emely@Gmail.com " and signing in as "emely@gmail.com" is one person —
     * but a strict `where('email', …)` matched neither, and both the web portal
     * and the mobile API then had no employee record to show.
     *
     * It deliberately does not fall back to comparing employees.id with the
     * user's id. Callers used to pass an id that falls back to users.id when
     * nothing matched, so an account with no master-list row picked up whichever
     * unrelated employee happened to carry that number — someone else's name,
     * position and department.
     */
    public static function forAccount($user): ?self
    {
        $email = strtolower(trim((string) ($user->email ?? '')));

        $match = $email === ''
            ? null
            : static::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        // An id written onto the account itself is an explicit link, so it is
        // trusted when there is no address to match on.
        return $match ?? (($user->employee_id ?? null) ? static::find($user->employee_id) : null);
    }

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
