@php 
    // I-initialize ang grand total
    $grandTotalHonorarium = 0; 
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - Admin Personnel Timesheet Detailed Compact</title>
    
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

        .print-header h1 { font-size: 14px; margin-bottom: 1px; }
        .print-header h2 { font-size: 12px; margin-bottom: 2px; }
        .print-header .meta-info { font-size: 8px; margin-top: 2px; }
        .print-header .meta-info span { margin: 0 8px; }

        /* General Table styles - COMPACT */
        .table-container { width: 100%; overflow-x: hidden; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
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
        tfoot td {
            background-color: #e6e6e6; 
            font-weight: bold;
            font-size: 9px;
            padding: 4px 2px;
        }
        .day-header { background-color: #b0c4de !important; }
        .timesheet-table td { font-weight: 600; }
        
        /* Column Sizing */
        .timesheet-table td:nth-child(1), .timesheet-table th:nth-child(1) { width: 12%; max-width: 120px; white-space: normal; text-align: left; padding-left: 3px; }
        .timesheet-table td:nth-child(2), .timesheet-table th:nth-child(2) { width: 8%; max-width: 80px; white-space: normal; }
        .timesheet-table td:nth-child(3), .timesheet-table th:nth-child(3) { width: 4%; max-width: 40px; }
        
        /* Daily columns */
        .timesheet-table th:nth-child(n+4):nth-child(-n+18),
        .timesheet-table td:nth-child(n+4):nth-child(-n+18) { width: 2.5%; padding: 2px 1px; font-size: 7px; }
        
        /* Details and Totals Columns */
        .timesheet-table th:nth-child(19) { width: 10%; white-space: normal; } /* Details */
        .timesheet-table td:nth-child(19) { white-space: normal; text-align: left; padding-left: 3px; }
        .timesheet-table th:nth-child(n+20), .timesheet-table td:nth-child(n+20) { font-size: 7.5px; width: 6%; padding: 2px 1px; }

        .timesheet-table td:nth-child(20) { font-weight: bold; color: #1a75ff; } /* Total Days */
        .timesheet-table td:nth-child(23) { font-weight: bold; color: #d63384; } /* TOTAL HONORARIUM */
        .grand-total-honorarium { color: #880044; font-size: 11px !important; background-color: #ffe6f0 !important; }

        .absent-day { background-color: #555 !important; color: white !important; font-weight: bold; }
        
        .day-number { font-size: 10px; font-weight: bold; line-height: 1; }
        .day-abbr { font-size: 7px; line-height: 1; }
        .day-header > span { display: block; }

        /* Footer/Signatures Styles */
        .print-footer { margin-top: 15px; text-align: center; padding-top: 5px; font-size: 8px; }
        .print-footer .signatures { display: flex; justify-content: space-around; margin-top: 10px; padding: 0 30px; }
        .signature-block { text-align: center; width: 180px; }
        .signature-line { border-bottom: 1px solid #000; margin-bottom: 3px; height: 20px; }
        .signature-block div:last-child { font-size: 8px; font-weight: bold; }

        /* Control buttons (non-print) */
        .print-controls { position: fixed; top: 10px; right: 10px; z-index: 1000; background: #fff; padding: 10px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #ddd; }
        .btn { padding: 6px 12px; font-size: 10px; text-decoration: none; border: 1px solid #ccc; background-color: #f0f0f0; color: #333; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Document</button>
        <a href="{{ route('admin-personnel.index') }}" class="btn btn-back">← Back to List</a>
    </div>

    <div class="print-header">
        <h1>MCC PAYROLL SYSTEM</h1>
        <h2>Admin Personnel Timesheet - Detailed</h2>
        <div class="meta-info">
            <span><strong>Generated:</strong> {{ date('F j, Y \a\t g:i A') }}</span>
            <span><strong>Period:</strong> {{ strtoupper($period_display ?? 'N/A') }}</span>
        </div>
    </div>

    <div class="table-container">
        <table class="timesheet-table">
            <thead>
                <tr>
                    <th rowspan="2">NAMES</th>
                    <th rowspan="2">DESIGNATION</th>
                    <th rowspan="2">PREV.<br>ABS.</th>
                    <th colspan="{{ count($days ?? []) }}" class="day-header">DAILY PRESENCE (1 = Present)</th>
                    <th rowspan="2">Details</th>
                    <th rowspan="2">TOTAL<br>Days</th>
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
                            <span class="day-number">{{ $day['number'] }}</span><br>
                            <span class="day-abbr">{{ $day['abbr'] }}</span>
                        </th>
                    @empty
                        <th colspan="15">No days configured</th> 
                    @endforelse
                </tr>
            </thead>
            <tbody>
                @forelse($timesheets as $timesheet)
                    @php
                        $calculatedTotalDays = 0;
                        $ratePerDay = (float)($timesheet->rate_per_day ?? 0);
                        $deduction = (float)($timesheet->deduction ?? 0);
                        $prevAbs = (float)($timesheet->prev_abs ?? 0);
                        $weekdayMap = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];
                    @endphp
                    <tr>
                        <td>{{ $timesheet->employee_name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($timesheet->designation ?? 'Admin Personnel') }}</td>
                        <td>{{ number_format($prevAbs, 0) }}</td>
                        
                        {{-- Daily Presence Loop --}}
                        @foreach($days as $day)
                            @php
                                $currentDate = $day['date'];
                                $dateObj = \Carbon\Carbon::parse($currentDate); 
                                $isSunday = ($dateObj->dayOfWeekIso == 7);
                                $isHoliday = in_array($currentDate, $holidays ?? []);
                                $columnClass = $isSunday || $isHoliday ? 'absent-day' : '';
                                
                                $weekday = $dateObj->format('D');
                                $field = $weekdayMap[$weekday] ?? 'sun_hours';
                                
                                $dailyValue = (float)($timesheet->$field ?? 0);
                                $isPresent = ($dailyValue > 0 && !$isSunday && !$isHoliday);

                                if ($isPresent) {
                                    $calculatedTotalDays++;
                                }
                                
                                $displayValue = $isPresent ? '1' : '0';
                            @endphp
                            <td class="{{ $columnClass }}">{{ $displayValue }}</td>
                        @endforeach

                        @php
                            // Final Calculations
                            $finalDays = max(0, $calculatedTotalDays - $prevAbs);
                            $grossHonorarium = $finalDays * $ratePerDay;
                            $netHonorarium = max(0, $grossHonorarium - $deduction);
                            $grandTotalHonorarium += $netHonorarium;
                        @endphp

                        {{-- Details and Totals --}}
                        <td>{{ $timesheet->details }}</td>
                        <td>{{ number_format($calculatedTotalDays, 0) }}</td>
                        <td>{{ number_format($ratePerDay, 2) }}</td>
                        <td>{{ number_format($deduction, 2) }}</td>
                        <td>₱{{ number_format($netHonorarium, 2) }}</td>
                    </tr>
                @empty
                    @php $colspan = 7 + count($days ?? []); @endphp
                    <tr><td colspan="{{ $colspan }}" style="text-align: center; padding: 10px;">No admin-personnel timesheet records found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                @if(count($timesheets ?? []) > 0)
                    @php
                        $colspanForLabel = 3 + count($days ?? []) + 4;
                    @endphp
                    <tr>
                        <td colspan="{{ $colspanForLabel }}" style="text-align: right; background-color: #e6e6e6;">
                            **GRAND TOTAL HONORARIUM:**
                        </td>
                        <td class="grand-total-honorarium">
                            ₱{{ number_format($grandTotalHonorarium, 2) }}
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
</body>
</html>