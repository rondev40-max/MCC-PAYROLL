<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayslipHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'email', 
        'employee_type', 
        'total_honorarium', 
        'error', 
        'sent_at', 
        'days',
        // ⭐️ UPDATED FIELDS ⭐️
        'designation',
        'rate', 
        'pay_period', 
        'total_hours_or_days', 
    ];
    
    protected $dates = [
        'sent_at',
    ];

    // CASTING: Tiyakin na float ang numeric values para tama ang number_format()
    protected $casts = [
        'sent_at' => 'datetime',
        'total_honorarium' => 'float',
        'rate' => 'float',
        'total_hours_or_days' => 'float',
    ];
}