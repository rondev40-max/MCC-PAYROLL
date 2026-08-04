<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'attachment',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Per-employee read receipts. Use this instead of the legacy
     * `is_read` column, which is global and not scoped to a user.
     */
    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function isReadBy(int $employeeId): bool
    {
        return $this->reads->contains('employee_id', $employeeId);
    }
}
