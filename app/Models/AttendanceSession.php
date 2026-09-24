<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'work_date' => 'date',
        'clocked_in_at' => 'datetime',
        'clocked_out_at' => 'datetime',
        'worked_seconds' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
