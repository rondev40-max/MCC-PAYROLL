@php 
    // Initialize grand total for calculations
    $grandTotalHonorarium = 0; 
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Print - Utility Timesheet</title>
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  @php
    // Ensure variables are defined with defaults if not passed from controller
    $month = $month ?? now()->format('F');
    $year = $year ?? now()->year;
    $period = $period ?? 'auto';
    $days = $days ?? [];
    $holidays = $holidays ?? [];
    $timesheets = $timesheets ?? collect();
  @endphp
  <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Arial", "Helvetica", sans-serif;
            font-size: 8px; 
            line-height: 1.1;
            color: #000;
            background: #fff;
            padding: 5px; 
        }

        /* Print-specific styles - COMPACT LANDSCAPE on Legal/Long Bond */
        @page {
            size: legal landscape; 
            margin: 0.3in;
        }
        
        @media print {
            body {
                padding: 0;
                font-size: 8px; 
            }
            
            .no-print {
                display: none !important;
            }
            
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            
            /* Ensure colors print (for Sunday/Holiday highlights) */
            th, td {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        /* Header styles - COMPACT */
        .print-header {
            text-align: center;
            margin-bottom: 5px; 
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .print-header h1 {
            font-size: 14px;
            margin-bottom: 1px;
        }

        .print-header h2 {
            font-size: 12px;
            margin-bottom: 2px;
        }

        .print-header .meta-info {
            font-size: 8px;
            margin-top: 2px;
        }

        .print-header .meta-info span {
            margin: 0 8px;
        }

        /* General Table styles - COMPACT */
        .table-container {
            width: 100%;
            overflow-x: hidden; 
            margin-bottom: 5px; 
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            white-space: nowrap; 
        }

        th {
            background-color: #d8e0ff; 
            font-weight: bold;
            font-size: 7px; 
            padding: 4px 1px; 
            line-height: 1;
        }
        
        /* New: Footer Row Styles */
        tfoot td {
            background-color: #e6e6e6; 
            font-weight: bold;
            font-size: 9px;
            padding: 4px 2px;
        }

        /* Day Headers */
        .day-header {
            background-color: #b0c4de !important;
        }
        
        /* Timesheet Table Specific Styles */
        .timesheet-table td {
            font-weight: 600; 
        }
        
        /* Column Sizing for Timesheet Table - MAXIMUM COMPRESSION */
        .timesheet-table td:nth-child(1), .timesheet-table th:nth-child(1) { 
            width: 12%; 
            max-width: 120px;
            white-space: normal;
            text-align: left;
            padding-left: 3px;
        }
        .timesheet-table td:nth-child(2), .timesheet-table th:nth-child(2) { 
            width: 8%; 
            max-width: 80px;
            white-space: normal;
        }
        .timesheet-table td:nth-child(3), .timesheet-table th:nth-child(3) { 
            width: 4%;
            max-width: 40px;
        }
        
        /* Daily Presence columns (15 columns: 4th to 18th) */
        .timesheet-table th:nth-child(n+4):nth-child(-n+18),
        .timesheet-table td:nth-child(n+4):nth-child(-n+18) { 
            width: 3.5%;
            padding: 2px 1px; 
            font-size: 7px;
        }
        
        /* Honorarium Columns (Last 4 columns) */
        .timesheet-table th:nth-child(n+19),
        .timesheet-table td:nth-child(n+19) {
            font-size: 7.5px;
            width: 7%;
            padding: 2px 1px;
        }

        /* Total Day Column */
        .timesheet-table td:nth-child(19) {
            font-weight: bold;
            color: #1a75ff;
        }

        .timesheet-table td:nth-child(22) { /* TOTAL HONORARIUM */
            font-weight: bold;
            color: #d63384; 
        }
        
        /* Grand Total Column (Sa TFOOT) - Huling Column */
        .grand-total-honorarium {
            color: #880044;
            font-size: 11px !important; 
            background-color: #ffe6f0 !important;
        }

        .absent-day {
            background-color: #555 !important; 
            color: white !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
        
        /* Day number/abbr spacing */
        .day-number {
            font-size: 10px;
            font-weight: bold;
            line-height: 1;
        }
        .day-abbr {
            font-size: 7px;
            line-height: 1;
        }
        .day-header > span {
            display: block;
        }

        /* Footer/Signatures Styles - LIFTED UP */
        .print-footer {
            margin-top: 15px; 
            text-align: center;
            padding-top: 5px;
            font-size: 8px;
        }

        .print-footer .signatures {
            display: flex;
            justify-content: space-around; 
            margin-top: 10px; 
            padding: 0 30px;
        }

        .signature-block {
            text-align: center;
            width: 180px; 
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 3px;
            height: 20px; 
        }
        
        .signature-block div:last-child {
            font-size: 8px;
            font-weight: bold;
        }

        /* Control buttons (non-print) */
        .print-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: #fff;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }

        .btn {
            padding: 6px 12px;
            font-size: 10px;
        }
  </style>
</head>
<body>
  <!-- Print Controls (hidden during print) -->
  <div class="print-controls no-print">
    <button onclick="window.print()" class="print-btn">
      🖨️ Print
    </button>
    <a href="{{ route('utility.index') }}" class="back-btn">
      ← Back to List
    </a>
  </div>

  <!-- Print Header -->
  <div class="print-header">
    <h1>MCC PAYROLL SYSTEM</h1>
    <h2>Utility Timesheet</h2>
    <div class="meta-info">
      <span><strong>Generated:</strong> {{ date('F j, Y \a\t g:i A') }}</span>
      <span><strong>Period:</strong> {{ strtoupper($period ?? 'Loading Period...') }}</span>
    </div>
  </div>

  <!-- Main Table -->
  <div class="table-container">
    <table class="timesheet-table">
      <thead>
        <tr>
          <th rowspan="2">NAMES</th>
          <th rowspan="2">DESIGNATION</th>
          <th rowspan="2">PREV.<br>ABS.</th>
          <th colspan="{{ count($days ?? []) }}" class="day-header">DAILY PRESENCE FOR CUT-OFF: {{ $period ?? 'N/A' }}</th>
          <th rowspan="2">TOTAL<br>Day</th>
          <th rowspan="2">Rate per<br>Day</th>
          <th rowspan="2">Deduc.<br>Prev.Cut Off</th>
          <th rowspan="2">TOTAL<br>HONORARIUM</th>
        </tr>
        <tr>
            @forelse($days as $day)
                @php
                    $isSunday = (\Carbon\Carbon::parse($day['date'])->dayOfWeekIso == 7);
                    $isHoliday = in_array($day['date'], $holidays ?? []);
                    $columnClass = $isSunday || $isHoliday ? 'absent-day' : 'day-header';
                @endphp
                <th class="{{ $columnClass }}">
                    <span class="day-number">{{ $day['number'] }}</span>
                    <br>
                    <span class="day-abbr">{{ $day['abbr'] }}</span>
                </th>
            @empty
                <th colspan="15">No days selected</th> 
            @endforelse
        </tr>
      </thead>
      <tbody>
        @forelse($timesheets as $timesheet)
            @php
                // Calculation logic for each row
                $calculatedTotalDays = 0;
                $ratePerDay = (float)($timesheet->rate_per_day ?? 0);
                $deduction = (float)($timesheet->deduction ?? 0); 
                $prevAbs = (float)($timesheet->prov_abr ?? 0);
                $weekdayMap = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];
            @endphp
            <tr>
                <td style="text-align: left;">{{ $timesheet->employee_name ?? 'N/A' }}</td>
                <td>{{ ucfirst($timesheet->designation ?? 'Utility') }}</td>
                <td>{{ number_format($prevAbs, 2) }}</td>
                
                {{-- Loop for daily presence --}}
                @foreach($days as $day)
                    @php
                        $currentDate = $day['date'];
                        $dateObj = \Carbon\Carbon::parse($currentDate); 
                        $isSunday = ($dateObj->dayOfWeekIso == 7);
                        $isHoliday = in_array($currentDate, $holidays ?? []);
                        $columnClass = $isSunday || $isHoliday ? 'absent-day' : '';
                        
                        $weekday = $dateObj->format('D');
                        $field = $weekdayMap[$weekday] ?? 'error_field';
                        
                        $dailyValue = (float)($timesheet->$field ?? 0);
                        
                        // For utility, 1 is present, 0 is absent.
                        $isPresent = ($dailyValue > 0);
                        
                        // Count the day if it's a working day and the person is present
                        if (!$isSunday && !$isHoliday && $isPresent) {
                            $calculatedTotalDays++;
                        }

                        // AYOS: Ipakita ang data number (1 o 0) sa halip na checkmark.
                        $displayValue = $isSunday || $isHoliday ? '-' : $dailyValue;
                    @endphp
                    <td class="{{ $columnClass }}">
                        {{ $displayValue }}
                    </td>
                @endforeach

                @php
                    // Final calculations for the row
                    $finalPayableDays = max(0, $calculatedTotalDays - $prevAbs);
                    $grossHonorarium = $finalPayableDays * $ratePerDay;
                    $netHonorarium = $grossHonorarium - $deduction;
                    
                    // Accumulate to grand total
                    $grandTotalHonorarium += $netHonorarium;
                @endphp

                {{-- Totals/Honorarium --}}
                <td>{{ number_format($finalPayableDays, 2) }}</td>
                <td>{{ number_format($ratePerDay, 2) }}</td>
                <td>{{ number_format($deduction, 2) }}</td>
                <td>₱{{ number_format($netHonorarium, 2) }}</td>
            </tr>
        @empty
            @php
                $colspan = 7 + count($days ?? []);
            @endphp
            <tr><td colspan="{{ $colspan }}" style="text-align: center; padding: 20px;">No utility timesheet records found for this period.</td></tr>
        @endforelse
      </tbody>
      {{-- Table Footer for Grand Total --}}
      <tfoot>
          @if(count($timesheets ?? []) > 0)
              @php
                  $colspanForLabel = 3 + count($days ?? []) + 3;
              @endphp
              <tr>
                  <td colspan="{{ $colspanForLabel }}" style="text-align: right; background-color: #e6e6e6;">
                      **GRAND TOTAL HONORARIUM:**
                  </td>
                  <td class="grand-total-honorarium">
                      ₱{{ number_format($grandTotalHonorarium ?? 0.00, 2) }}
                  </td>
              </tr>
          @endif
      </tfoot>
    </table>
  </div>


  <div class="print-footer">
    <div class="signatures">
      <div class="signature-block">
        <div class="signature-line"></div>
        <div><strong>Reviewed By</strong></div>
        <div>WENDELL DENORTE</div>
      </div>
      <div class="signature-block">
        <div class="signature-line"></div>
        <div><strong>Approved By</strong></div>
        <div>DR. FLORIPIS A. MONTECILLO</div>
      </div>
    </div>
    <p style="margin-top: 5px;">© {{ date('Y') }} MCC - All Rights Reserved</p>
  </div>

  <script>
    // Set print date when page loads
    document.addEventListener('DOMContentLoaded', function() {
      const printDateElement = document.getElementById('print-date');
      if (printDateElement) {
        const now = new Date();
        const options = { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        };
        printDateElement.textContent = now.toLocaleDateString('en-US', options);
      }
    });

    document.addEventListener('keydown', function(e) {
      if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.print();
      }
    });

    // window.addEventListener('load', () => setTimeout(() => window.print(), 500));

  </script>
<script>
// DevTools detection to make page blank if opened
devtools.detect(function(status){
  if(status){
    document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
  }
});
</script>
</body>
</html>
