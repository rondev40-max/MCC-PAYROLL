<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionSetting extends Model
{
    protected $fillable = [
        'deduction_type',
        'rate_type',
        'rate_value',
        'min_amount',
        'max_amount',
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate_value' => 'float',
        'min_amount' => 'float',
        'max_amount' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Get active deduction settings.
     */
    public static function getActiveSettings(): \Illuminate\Support\Collection
    {
        return self::where('is_active', true)->get()->keyBy('deduction_type');
    }

    /**
     * Calculate the deduction amount based on gross amount.
     */
    public static function calculateDeduction(string $deductionType, float $grossAmount): float
    {
        $setting = self::where('deduction_type', $deductionType)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return 0;
        }

        $amount = 0;

        if ($setting->rate_type === 'percentage') {
            $amount = $grossAmount * ($setting->rate_value / 100);
        } else {
            // Fixed amount
            $amount = $setting->rate_value;
        }

        // Apply min/max caps
        if ($setting->min_amount !== null) {
            $amount = max($amount, $setting->min_amount);
        }
        if ($setting->max_amount !== null) {
            $amount = min($amount, $setting->max_amount);
        }

        return round($amount, 2);
    }

    /**
     * Calculate all government deductions for a given gross amount.
     * Returns array with types as keys.
     */
    public static function calculateAllDeductions(float $grossAmount): array
    {
        $deductionTypes = [
            'withholding_tax',
            'gsis',
            'philhealth',
            'pag_ibig',
            'sss',
        ];

        $result = [];
        foreach ($deductionTypes as $type) {
            $result[$type] = self::calculateDeduction($type, $grossAmount);
        }

        return $result;
    }

    /**
     * Standard Philippine withholding tax computation (simplified).
     * Based on BIR graduated tax table (2023-2024 rates).
     */
    public static function calculateWithholdingTax(float $taxableIncome, string $frequency = 'monthly'): float
    {
        if ($taxableIncome <= 0) return 0;

        // Monthly withholding tax brackets (BIR RATE)
        $brackets = [
            ['min' => 0,       'max' => 20833,   'base' => 0,    'rate' => 0,    'excess' => 0],
            ['min' => 20833,   'max' => 33333,   'base' => 0,    'rate' => 0.15, 'excess' => 20833],
            ['min' => 33333,   'max' => 66667,   'base' => 1875, 'rate' => 0.20, 'excess' => 33333],
            ['min' => 66667,   'max' => 166667,  'base' => 8541.80, 'rate' => 0.25, 'excess' => 66667],
            ['min' => 166667,  'max' => 666667,  'base' => 33541.80, 'rate' => 0.30, 'excess' => 166667],
            ['min' => 666667,  'max' => PHP_FLOAT_MAX, 'base' => 183541.80, 'rate' => 0.35, 'excess' => 666667],
        ];

        // If annual, convert to monthly and back
        $factor = 1;
        if ($frequency === 'annual') {
            $taxableIncome = $taxableIncome / 12;
            $factor = 12;
        } elseif ($frequency === 'semi-monthly') {
            $taxableIncome = $taxableIncome * 2;
            $factor = 0.5;
        }

        foreach ($brackets as $bracket) {
            if ($taxableIncome >= $bracket['min'] && $taxableIncome <= $bracket['max']) {
                $tax = $bracket['base'] + (($taxableIncome - $bracket['excess']) * $bracket['rate']);
                return round($tax * $factor, 2);
            }
        }

        return 0;
    }
}

