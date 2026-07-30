<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdjustment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * NOTE: The migration (2025_08_23_152435) currently only defines
     * id + timestamps. Add columns here as the migration evolves,
     * e.g. 'employee_id', 'adjustment_type', 'amount', 'effective_date', 'reason'
     */
    protected $fillable = [
        //
    ];

    protected $casts = [
        //
    ];
}
