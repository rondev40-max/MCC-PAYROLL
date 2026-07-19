<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date', // <--- DAPAT NANDITO ITO
        'name', // <--- DAPAT NANDITO ITO
    ];

    // Optional: Type casting (para siguradong date ang date)
    protected $casts = [
        'date' => 'date',
    ];
}