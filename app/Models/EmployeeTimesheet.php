<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTimesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'employee_name',
        'email',
        'date',
        'time_in',
        'time_out',
        'work_type',
        'task',
        'remarks',
        'hours',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'float',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
