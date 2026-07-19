<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = [
        'user_id',
        'respondent_role',
        'usability_scores',
        'efficiency_scores',
        'satisfaction_scores',
        'feedback',
        'avg_usability',
        'avg_efficiency',
        'avg_satisfaction',
        'overall_avg',
    ];

    protected $casts = [
        'usability_scores' => 'array',
        'efficiency_scores' => 'array',
        'satisfaction_scores' => 'array',
        'feedback' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

