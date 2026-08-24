<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Record how each wage was made up, on the payslip itself.
 *
 * `payslip_histories` stored one money figure — `total_honorarium` — so the
 * employee portal had nothing to itemise. The payslip PDF asked the model for
 * `withholding_tax`, `gsis`, `philhealth`, `pag_ibig` and `deduction`, none of
 * which were columns, and Eloquent returned null for every one: the deductions
 * table rendered ₱0.00 across the board with a "Gross Pay" identical to the
 * "Total Net Pay" above it.
 *
 * The figures do exist on the timesheet rows, but a payslip cannot be rebuilt
 * from those later. Timesheets stay editable after a payroll run, so
 * recomputing would show an employee something different from the payslip they
 * were emailed. A payslip is a record of what was paid, so the breakdown is
 * copied onto it at generation time and never recalculated.
 *
 * Every column is nullable on purpose. NULL means "this payslip predates the
 * breakdown" and reads differently from 0.00, which means "nothing was
 * deducted" — see App\Support\WageLiquidation::isItemised().
 */
return new class extends Migration
{
    /** The money columns, all of which follow the same shape. */
    private const AMOUNTS = [
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

    public function up(): void
    {
        Schema::table('payslip_histories', function (Blueprint $table) {
            foreach (self::AMOUNTS as $column) {
                if (!Schema::hasColumn('payslip_histories', $column)) {
                    $table->decimal($column, 12, 2)->nullable()->after('total_honorarium');
                }
            }

            // 'hour' or 'day'. The stored rate is meaningless without it —
            // ₱62.50 is a plausible hourly rate and an implausible daily one.
            if (!Schema::hasColumn('payslip_histories', 'rate_unit')) {
                $table->string('rate_unit', 8)->nullable()->after('rate');
            }

            // Which timesheet produced this payslip, for audit. Kept as a plain
            // type/id pair rather than a foreign key because the six timesheet
            // tables are separate.
            if (!Schema::hasColumn('payslip_histories', 'source_type')) {
                $table->string('source_type', 32)->nullable();
            }
            if (!Schema::hasColumn('payslip_histories', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payslip_histories', function (Blueprint $table) {
            $columns = array_merge(self::AMOUNTS, ['rate_unit', 'source_type', 'source_id']);

            $existing = array_values(array_filter(
                $columns,
                fn (string $column) => Schema::hasColumn('payslip_histories', $column)
            ));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
