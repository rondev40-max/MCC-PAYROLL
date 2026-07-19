<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get employees belonging to this department
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get fulltime timesheets for this department
     */
    public function fulltimeTimesheets()
    {
        return $this->hasMany(FulltimeTimesheet::class, 'department', 'code');
    }

    /**
     * Get parttime timesheets for this department
     */
    public function parttimeTimesheets()
    {
        return $this->hasMany(ParttimeTimesheet::class, 'department', 'code');
    }

    /**
     * Get staff timesheets for this department
     */
    public function staffTimesheets()
    {
        return $this->hasMany(StaffTimesheet::class, 'department', 'code');
    }

    /**
     * Get utility timesheets for this department
     */
    public function utilityTimesheets()
    {
        return $this->hasMany(UtilityTimesheet::class, 'department', 'code');
    }

    /**
     * Scope to get only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
