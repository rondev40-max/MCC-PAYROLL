<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_name',
        'email',
        'type',
        'date_from',
        'date_to',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_note',
    ];

    protected $casts = [
        'date_from'   => 'date',
        'date_to'     => 'date',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Number of days requested (inclusive)
     */
    public function getDaysCountAttribute(): int
    {
        if (!$this->date_from || !$this->date_to) return 0;
        return $this->date_from->diffInDays($this->date_to) + 1;
    }
}
