<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payroll History Records</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <style>
    body {
        font-size: 0.85rem;
    }
    
    .pagination-sm .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    /* Compact Table Styles */
    .payroll-table {
        font-size: 0.8rem;
    }
    
    .payroll-table th {
        background-color: #d8e0ff;
        font-weight: bold;
        font-size: 0.75rem;
        padding: 6px 4px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .payroll-table td {
        padding: 4px;
        vertical-align: middle;
        font-size: 0.75rem;
    }
    
    /* Column Widths */
    .col-name { width: 10%; min-width: 100px; }
    .col-designation { width: 7%; }
    .col-prev-abs { width: 4%; text-align: center; }
    .col-dept { width: 8%; }
    .col-timesheet { width: 45%; }
    .col-total { width: 6%; text-align: center; }
    .col-rate { width: 7%; text-align: right; }
    .col-deduction { width: 6%; text-align: right; }
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
        background-color: #e6e6e6;
        font-weight: bold;
    }
    
    .grand-total-label {
        text-align: right;
        font-size: 0.9rem;
        padding: 8px;
    }
    
    .grand-total-value {
        background-color: #ffe6f0 !important;
        color: #880044;
        font-size: 1rem;
        font-weight: bold;
        text-align: right;
        padding: 8px;
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
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title mb-0">📋 Payroll History Records</h3>
                <p class="text-muted mb-0">Detailed timesheet records with daily breakdown</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
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

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover payroll-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-name">NAME</th>
                            <th rowspan="2" class="col-designation">DESIGNATION</th>
                            <th rowspan="2" class="col-prev-abs">PREV.<br>ABS.</th>
                            <th rowspan="2" class="col-dept">DEPARTMENT</th>
                            <th colspan="1" class="col-timesheet">DAILY HOURS/ATTENDANCE</th>
                            <th rowspan="2" class="col-total">TOTAL<br>Hour/Days</th>
                            <th rowspan="2" class="col-rate">RATE</th>
                            <th rowspan="2" class="col-deduction">DEDUC.</th>
                            <th rowspan="2" class="col-honorarium">TOTAL<br>HONORARIUM</th>
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
                        @php
                            $grandTotal = 0;
                        @endphp
                        
                        @forelse ($records as $record)
                            @php
                                $grandTotal += $record->total_honorarium ?? 0;
                                $isStaffUtility = in_array(strtolower($record->employee_type), ['staff', 'utility']);
                            @endphp
                            <tr>
                                <td class="col-name">{{ $record->name }}</td>
                                <td class="col-designation">{{ $record->employee_type }}</td>
                                <td class="col-prev-abs">0.00</td>
                                <td class="col-dept">{{ $record->designation ?? 'N/A' }}</td>
                                
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
                                <td class="col-deduction">₱0.00</td>
                                <td class="col-honorarium">₱{{ number_format($record->total_honorarium ?? 0, 2) }}</td>
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
                    
                    {{-- Footer with Grand Total --}}
                    @if(count($records) > 0)
                        <tfoot>
                            <tr class="grand-total-row">
                                <td colspan="8" class="grand-total-label">
                                    **GRAND TOTAL HONORARIUM:**
                                </td>
                                <td class="grand-total-value">
                                    ₱{{ number_format($grandTotal, 2) }}
                                </td>
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

<script>
devtools.detect(function(status){
  if(status){
    document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; font-family: Arial;"><h1>Access Denied</h1></div>';
    document.body.style.overflow = 'hidden';
  }
});
</script>

</body>
</html>