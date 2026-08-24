<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payroll History Records</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  {{-- The devtools-detect script that used to load here has been removed. It
       pulled unpinned JavaScript from a personal GitHub repo on a public CDN
       straight into an authenticated admin page — whoever controls that repo
       could run code in an admin's session — and all it bought was replacing
       the page with "Access Denied" when DevTools opened, which stops nobody
       (the HTML is already delivered) while breaking the page for the admin
       trying to diagnose a payroll problem. --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Sora:wght@600;700&display=swap" rel="stylesheet">

  <style>
    /* Tokens lifted from admin/dashboard.blade.php so this page belongs to the
       admin portal rather than being a stock-Bootstrap page bolted onto it. */
    :root {
      --brand: #2563eb; --brand-dark: #1d4ed8; --brand-light: #eff6ff;
      --accent: #059669; --danger: #dc2626; --warn: #b45309;
      --bg: #f1f5f9; --card: #ffffff;
      --text: #0f172a; --text-2: #475569; --text-3: #94a3b8;
      --border: #e2e8f0; --border-2: #cbd5e1;
      --r-sm: 8px; --r-md: 12px;
    }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: .875rem;
        -webkit-font-smoothing: antialiased;
    }

    h1, h2, h3, .fw-display { font-family: 'Sora', sans-serif; letter-spacing: -.02em; }

    .pagination-sm .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    /* ── Summary strip ── */
    .summary-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1px;
        background: var(--border);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .summary-item { background: var(--card); padding: .85rem 1rem; }
    .summary-label {
        font-size: .6875rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: .08em; color: var(--text-3);
    }
    .summary-value {
        font-family: 'Sora', sans-serif; font-size: 1.25rem; font-weight: 700;
        margin-top: .15rem; letter-spacing: -.03em;
        font-variant-numeric: tabular-nums;
    }
    .summary-value.is-net    { color: var(--accent); }
    .summary-value.is-deduct { color: var(--danger); }
    .summary-value.is-alert  { color: var(--warn); }

    /* Compact Table Styles */
    .payroll-table {
        font-size: .8125rem;
        border-color: var(--border);
    }

    .payroll-table th {
        background-color: #f8fafc;
        color: var(--text-2);
        font-weight: 600;
        font-size: 0.6875rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        padding: 8px 6px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        border-color: var(--border);
    }

    .payroll-table td {
        padding: 6px;
        vertical-align: middle;
        font-size: 0.75rem;
        border-color: var(--border);
    }

    /* Money lines up in its columns. */
    .col-rate, .col-deduction, .col-honorarium { font-variant-numeric: tabular-nums; }

    /* Column Widths */
    .col-name { width: 11%; min-width: 110px; }
    .col-designation { width: 9%; }
    .col-dept { width: 7%; }
    .col-timesheet { width: 40%; }
    .col-total { width: 6%; text-align: center; }
    .col-rate { width: 7%; text-align: right; }
    .col-deduction { width: 7%; text-align: right; }
    .col-honorarium { width: 8%; text-align: right; }

    /* Timesheet Grid Styles */
    .timesheet-container {
        padding: 2px;
    }
    
    .timesheet-grid {
        display: grid;
        gap: 0;
        font-size: 0.7rem;
        border: 1px solid #dee2e6;
    }
    
    .timesheet-row {
        display: contents;
    }
    
    .timesheet-cell {
        border: 1px solid #dee2e6;
        padding: 2px;
        text-align: center;
        background: white;
    }
    
    .timesheet-header {
        background-color: #b0c4de !important;
        font-weight: bold;
    }
    
    .day-number {
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .day-abbr {
        font-size: 0.65rem;
    }
    
    .day-value {
        font-weight: 600;
        color: #000;
    }
    
    .day-sunday, .day-holiday {
        background-color: #555 !important;
        color: white !important;
        font-weight: bold;
    }
    
    .day-absent {
        color: #dc3545;
        font-weight: bold;
    }
    
    .timesheet-meta {
        font-size: 0.65rem;
        color: #6c757d;
        margin-top: 3px;
        padding: 2px 4px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }

    /* Footer/Grand Total Styles */
    .grand-total-row {
        background-color: #f8fafc;
        font-weight: 600;
    }

    .grand-total-label {
        text-align: right;
        font-size: .8125rem;
        padding: 10px 8px;
        color: var(--text-2);
    }

    /* Was pink-on-maroon (#ffe6f0 / #880044) — the loudest thing on a payroll
       document, and a colour used nowhere else in the admin portal. */
    .grand-total-value {
        font-family: 'Sora', sans-serif;
        font-size: .9375rem;
        font-weight: 700;
        text-align: right;
        padding: 10px 8px;
        font-variant-numeric: tabular-nums;
    }
    
    /* Status Badges */
    .badge-sent {
        background-color: #28a745;
        padding: 4px 8px;
    }
    
    .badge-failed {
        background-color: #dc3545;
        padding: 4px 8px;
    }
  </style>
</head>
<body>
<div class="container-fluid mt-4">
    {{-- Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="card-title mb-0">Payroll history</h3>
                <p class="text-muted mb-0" style="font-size:.8125rem;">
                    Every payslip released, with the daily timesheet it was computed from.
                </p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to dashboard
            </a>
        </div>

        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.payroll.history') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="month" class="form-label">Select Month</label>
                        <select name="month" id="month" class="form-select">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $num == $selectedMonth ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="year" class="form-label">Select Year</label>
                        <select name="year" id="year" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="period" class="form-label">Pay Period</label>
                        <select name="period" id="period" class="form-select">
                            <option value="all" {{ ($selectedPeriod ?? 'all') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="1-15" {{ ($selectedPeriod ?? 'all') == '1-15' ? 'selected' : '' }}>1-15</option>
                            <option value="16-end" {{ ($selectedPeriod ?? 'all') == '16-end' ? 'selected' : '' }}>16 - End of Month</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.payroll.export', ['month' => $selectedMonth, 'year' => $selectedYear, 'period' => $selectedPeriod ?? 'all']) }}" class="btn btn-success w-100">
                            <i class="bi bi-file-earmark-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            </form>

            {{-- What the filter adds up to, before the table. The totals only
                 existed in the footer, below fifteen rows of daily grids, so the
                 numbers an admin opens this page for were the last thing on it. --}}
            @if($totals && $totals->record_count > 0)
                <div class="summary-strip">
                    <div class="summary-item">
                        <div class="summary-label">Payslips</div>
                        <div class="summary-value">{{ number_format($totals->record_count) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Gross</div>
                        <div class="summary-value">₱{{ number_format($totals->gross, 2) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Deductions</div>
                        <div class="summary-value is-deduct">₱{{ number_format($totals->deductions, 2) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Net released</div>
                        <div class="summary-value is-net">₱{{ number_format($totals->net, 2) }}</div>
                    </div>
                    @if($totals->failed > 0)
                        <div class="summary-item">
                            <div class="summary-label">Failed to send</div>
                            <div class="summary-value is-alert">{{ number_format($totals->failed) }}</div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover payroll-table">
                    <thead>
                        {{-- The DESIGNATION column printed employee_type and the
                             DEPARTMENT column printed designation — two headers
                             each naming the other's data, over a table nobody
                             could reconcile. payslip_histories has no department
                             column at all, so that heading described nothing. --}}
                        <tr>
                            <th rowspan="2" class="col-name">NAME</th>
                            <th rowspan="2" class="col-designation">DESIGNATION</th>
                            <th rowspan="2" class="col-dept">EMPLOYMENT</th>
                            <th colspan="1" class="col-timesheet">DAILY HOURS/ATTENDANCE</th>
                            <th rowspan="2" class="col-total">TOTAL<br>Hour/Days</th>
                            <th rowspan="2" class="col-rate">RATE</th>
                            <th rowspan="2" class="col-honorarium">GROSS</th>
                            <th rowspan="2" class="col-deduction">DEDUC.</th>
                            <th rowspan="2" class="col-honorarium">NET PAY</th>
                        </tr>
                        <tr>
                            <th class="col-timesheet">
                                Period: {{ \Carbon\Carbon::createFromDate(null, $selectedMonth, 1)->format('F') }} {{ $selectedYear }}
                                @if(($selectedPeriod ?? 'all') !== 'all')
                                    ({{ $selectedPeriod == '16-end' ? '16 - End' : $selectedPeriod }})
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $isStaffUtility = in_array(strtolower($record->employee_type), ['staff', 'utility']);
                            @endphp
                            <tr>
                                <td class="col-name">
                                    {{ $record->name }}
                                    @if($record->error)
                                        <span class="badge bg-danger" title="{{ $record->error }}">Send failed</span>
                                    @endif
                                </td>
                                <td class="col-designation">{{ $record->designation ?: '—' }}</td>
                                <td class="col-dept">{{ $record->employee_type ?: '—' }}</td>

                                {{-- Timesheet Column --}}
                                <td class="col-timesheet">
                                    @if($record->timesheet && !empty($record->days_in_period))
                                        <div class="timesheet-container">
                                            @php
                                                $dayCount = count($record->days_in_period);
                                                $gridCols = "repeat({$dayCount}, 1fr)";
                                            @endphp
                                            
                                            <div class="timesheet-grid" style="grid-template-columns: {{ $gridCols }};">
                                                {{-- Day Numbers --}}
                                                <div class="timesheet-row">
                                                    @foreach($record->days_in_period as $day)
                                                        <div class="timesheet-cell timesheet-header">
                                                            <span class="day-number">{{ $day['number'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                
                                                {{-- Day Abbreviations --}}
                                                <div class="timesheet-row">
                                                    @foreach($record->days_in_period as $day)
                                                        @php
                                                            $isSunday = $day['is_sunday'];
                                                            $isHoliday = in_array($day['date'], $holidays ?? []);
                                                            $cellClass = ($isSunday || $isHoliday) ? 'day-sunday' : '';
                                                        @endphp
                                                        <div class="timesheet-cell timesheet-header {{ $cellClass }}">
                                                            <span class="day-abbr">{{ $day['abbr'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                
                                                {{-- Values --}}
                                                <div class="timesheet-row">
                                                    @foreach($record->days_in_period as $day)
                                                            @php
                                                                $isSunday = $day['is_sunday'];
                                                                $isHoliday = in_array($day['date'], $holidays ?? []);
                                                            
                                                                $dayAbbrFull = strtolower(\Carbon\Carbon::parse($day['date'])->format('D'));
                                                                $field = $dayAbbrFull . '_hours';
                                                                $value = $record->timesheet->$field ?? 0;
                                                            
                                                                if ($isStaffUtility) {
                                                                    $displayValue = ($value > 0) ? '1' : '0';
                                                                    $cellClass = ($value > 0) ? 'day-value' : 'day-absent';
                                                                } else {
                                                                    $displayValue = number_format($value, 2, '.', '');
                                                                    if ($isSunday || $isHoliday) {
                                                                        $cellClass = 'day-sunday';
                                                                    } elseif ($value == 0) {
                                                                        $cellClass = 'day-absent';
                                                                    } else {
                                                                        $cellClass = 'day-value';
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="timesheet-cell {{ $cellClass }}">
                                                                {{ $displayValue }}
                                                            </div>
                                                        @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="timesheet-meta">
                                                {{ $record->pay_period ?? 'N/A' }}
                                                @if($isStaffUtility)
                                                    (P=Present, A=Absent)
                                                @else
                                                    (Hours)
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-2">
                                            {{ $record->pay_period ?? 'N/A' }}<br>
                                            <span class="badge bg-warning">No Timesheet</span>
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="col-total">
                                    {{ number_format($record->total_hours_or_days ?? 0, 2) }}
                                    {{ $isStaffUtility ? 'days' : 'hrs' }}
                                </td>
                                <td class="col-rate">₱{{ number_format($record->rate ?? 0, 2) }}</td>
                                <td class="col-honorarium">₱{{ number_format($record->gross_pay ?? $record->total_honorarium ?? 0, 2) }}</td>
                                {{-- Was the literal string ₱0.00, hard-coded into
                                     every row. The figures are on the payslip now
                                     (see App\Support\WageLiquidation); a payslip
                                     issued before that shows an em dash rather
                                     than claiming nothing was withheld. --}}
                                <td class="col-deduction">
                                    @if($record->total_deductions !== null)
                                        ₱{{ number_format($record->total_deductions, 2) }}
                                    @else
                                        <span class="text-muted" title="Issued before deductions were itemised">—</span>
                                    @endif
                                </td>
                                <td class="col-honorarium fw-semibold">₱{{ number_format($record->takeHome(), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    No payroll records found for {{ \Carbon\Carbon::createFromDate(null, $selectedMonth, 1)->format('F') }} {{ $selectedYear }}
                                    @if(($selectedPeriod ?? 'all') !== 'all')
                                        for period {{ $selectedPeriod == '16-end' ? '16 - End of Month' : $selectedPeriod }}.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    {{-- Totals for everything the filter matches, not just this
                         page. The old footer added up the 15 rows being rendered
                         and called the result the grand total, so it changed as
                         you paged. It also printed **GRAND TOTAL HONORARIUM:**
                         with the markdown asterisks showing. --}}
                    @if($totals && $totals->record_count > 0)
                        <tfoot>
                            <tr class="grand-total-row">
                                <td colspan="6" class="grand-total-label">
                                    TOTAL for {{ number_format($totals->record_count) }}
                                    payslip{{ $totals->record_count == 1 ? '' : 's' }} in this period
                                </td>
                                <td class="grand-total-value">₱{{ number_format($totals->gross, 2) }}</td>
                                <td class="grand-total-value">₱{{ number_format($totals->deductions, 2) }}</td>
                                <td class="grand-total-value">₱{{ number_format($totals->net, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $records->appends(['month' => $selectedMonth, 'year' => $selectedYear, 'period' => $selectedPeriod ?? 'all'])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

</body>
</html>