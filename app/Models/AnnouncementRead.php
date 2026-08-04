<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementRead extends Model
{
    protected $fillable = [
        'announcement_id',
        'employee_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
