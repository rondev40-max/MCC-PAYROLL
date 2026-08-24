<?php

namespace App\Support;

use App\Models\PayslipHistory;

/**
 * One pay period's wage, itemised: what was earned, what was withheld, and
 * what is left.
 *
 * "Liquidation" here is the accounting sense — the full accounting of a wage,
 * line by line, so an employee can see exactly how a gross figure became the
 * amount that reached them.
 *
 * ── Why this class exists ────────────────────────────────────────────────────
 *
 * The numbers were spread across six timesheet tables that do not agree on
 * column names. fulltime/parttime/staff/utility store `withholding_tax`,
 * `philhealth`, `pag_ibig` and `sss`; watchman and admin_personnel store the
 * same four as `tax_amount`, `phic_amount`, `hdmf_amount` and `sss_amount`, and
 * have no GSIS column at all. Every screen that wanted a breakdown was reading
 * one spelling and silently getting nothing for half the workforce.
 *
 * Worse, `payslip_histories` never stored a breakdown at all. The employee
 * payslip PDF asked it for `withholding_tax`, `gsis`, `philhealth`, `pag_ibig`
 * and `deduction` — none of which are columns on that table — so every
 * deduction row rendered ₱0.00 and "Total Deductions" always came to ₱0.00,
 * under a "Gross Pay" and a "Total Net Pay" that were the same number.
 *
 * ── The convention ───────────────────────────────────────────────────────────
 *
 * Set by resources/views/emails/payslip.blade.php, which is what employees
 * actually receive and therefore the figure they will hold you to:
 *
 *     gross = timesheet.total_honorarium
 *     total = withholding_tax + gsis + philhealth + pag_ibig + sss + deduction
 *     net   = max(0, gross - total)
 *
 * So `total_honorarium` is GROSS, and the `deduction` column is "other
 * deductions" — subtracted from gross alongside the statutory ones, not already
 * taken out of it. Everything that renders a wage goes through here so the
 * portal, the PDF and the email cannot drift apart.
 */
final class WageLiquidation
{
    /**
     * Statutory deduction lines, in the order a payslip should list them.
     *
     * `columns` is every spelling the timesheet tables use for that deduction;
     * the first is the canonical name and the one persisted on the payslip.
     * `note` is shown to the employee — a line item they cannot identify is not
     * a liquidation, it is just a smaller number.
     */
    public const STATUTORY = [
        'withholding_tax' => [
            'label'   => 'Withholding tax',
            'note'    => 'Income tax withheld and remitted to the BIR on your behalf.',
            'columns' => ['withholding_tax', 'tax_amount'],
        ],
        'gsis' => [
            'label'   => 'GSIS',
            'note'    => 'Government Service Insurance System premium.',
            'columns' => ['gsis'],
        ],
        'philhealth' => [
            'label'   => 'PhilHealth',
            'note'    => 'National health insurance premium.',
            'columns' => ['philhealth', 'phic_amount'],
        ],
        'pag_ibig' => [
            'label'   => 'Pag-IBIG (HDMF)',
            'note'    => 'Home Development Mutual Fund contribution.',
            'columns' => ['pag_ibig', 'hdmf_amount'],
        ],
        'sss' => [
            'label'   => 'SSS',
            'note'    => 'Social Security System contribution.',
            'columns' => ['sss', 'sss_amount'],
        ],
    ];

    /** The payslip columns this class writes and reads. */
    public const PAYSLIP_COLUMNS = [
        'gross_pay',
        'withholding_tax',
        'gsis',
        'philhealth',
        'pag_ibig',
        'sss',
        'other_deductions',
        'total_deductions',
        'net_pay',
    ];

    /**
     * Read a deduction off a timesheet row whatever that table calls it.
     *
     * Returns null when the row has no column for it, which is different from
     * the column being zero: watchman timesheets have no GSIS column, and
     * "GSIS was not deducted" should not read as "GSIS was ₱0.00 deducted".
     */
    public static function deductionFrom(object $timesheet, string $key): ?float
    {
        foreach (self::STATUTORY[$key]['columns'] ?? [] as $column) {
            // property_exists() misses Eloquent attributes, which live in an
            // internal array rather than as real properties.
            $value = $timesheet->{$column} ?? null;

            if ($value !== null) {
                return (float) $value;
            }
        }

        return null;
    }

    /**
     * Build the breakdown for a timesheet row, ready to persist on a payslip.
     *
     * $gross is passed in rather than re-derived because the payroll run has
     * already decided it (falling back to rate x units when the stored
     * honorarium is zero), and the payslip must record the figure the employee
     * was actually sent.
     *
     * @return array<string, float>
     */
    public static function fromTimesheet(?object $timesheet, float $gross): array
    {
        $breakdown = ['gross_pay' => round($gross, 2)];
        $statutory = 0.0;

        foreach (array_keys(self::STATUTORY) as $key) {
            $amount = $timesheet ? (self::deductionFrom($timesheet, $key) ?? 0.0) : 0.0;
            $breakdown[$key] = round($amount, 2);
            $statutory += $breakdown[$key];
        }

        $breakdown['other_deductions'] = round((float) ($timesheet->deduction ?? 0), 2);
        $breakdown['total_deductions'] = round($statutory + $breakdown['other_deductions'], 2);
        $breakdown['net_pay'] = round(max(0, $breakdown['gross_pay'] - $breakdown['total_deductions']), 2);

        return $breakdown;
    }

    /**
     * The breakdown to show for a stored payslip, or null when none was
     * recorded.
     *
     * Payslips issued before the breakdown columns existed have NULL in them.
     * That is deliberately not treated as zero — telling someone their
     * withholding tax was ₱0.00 when the truth is "we did not keep that
     * figure" is the bug this whole class replaces. Callers should render an
     * honest "not itemised" state instead.
     */
    public static function fromPayslip(PayslipHistory $payslip): ?array
    {
        if (!self::isItemised($payslip)) {
            return null;
        }

        $lines = [];

        foreach (self::STATUTORY as $key => $meta) {
            $lines[] = [
                'key'    => $key,
                'label'  => $meta['label'],
                'note'   => $meta['note'],
                'amount' => (float) ($payslip->{$key} ?? 0),
            ];
        }

        $gross = (float) ($payslip->gross_pay ?? 0);
        $other = (float) ($payslip->other_deductions ?? 0);
        $total = (float) ($payslip->total_deductions ?? 0);

        return [
            'gross'            => $gross,
            'lines'            => $lines,
            'other_deductions' => $other,
            'total_deductions' => $total,
            'net'              => (float) ($payslip->net_pay ?? 0),
            'rate'             => (float) ($payslip->rate ?? 0),
            'rate_unit'        => $payslip->rate_unit ?: null,
            'units'            => (float) ($payslip->total_hours_or_days ?? 0),
            // Lets a view show the arithmetic rather than asking for trust.
            'take_home_rate'   => $gross > 0 ? round((($gross - $total) / $gross) * 100, 1) : null,
        ];
    }

    /** True when this payslip carries a recorded breakdown. */
    public static function isItemised(PayslipHistory $payslip): bool
    {
        return $payslip->gross_pay !== null && $payslip->net_pay !== null;
    }

    /**
     * Units and their unit of measure, for the earnings line.
     *
     * Hourly staff are paid per hour and daily staff per day; a payslip that
     * says "12.00" without saying twelve of what is not self-explanatory.
     */
    public static function unitLabel(?string $rateUnit, float $units): string
    {
        $unit = $rateUnit === 'day' ? 'day' : ($rateUnit === 'hour' ? 'hour' : 'unit');

        return rtrim(rtrim(number_format($units, 2), '0'), '.') . ' ' . $unit . (abs($units) == 1.0 ? '' : 's');
    }
}
