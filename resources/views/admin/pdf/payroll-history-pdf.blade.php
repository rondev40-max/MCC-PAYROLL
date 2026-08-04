<!DOCTYPE html>
<html>
<head>
    <title>Payroll History - {{ $monthName }} {{ $selectedYear }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.1;
            color: #000;
            background: #fff;
            padding: 5px;
        }

        @page {
            size: legal landscape;
            margin: 0.3in;
        }
        
        @media print {
            body {
                padding: 0;
                font-size: 8px;
            }
            
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            
            th, td {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        /* Header */
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

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
        }

        th {
            background-color: #d8e0ff;
            font-weight: bold;
            font-size: 7px;
            padding: 4px 1px;
            line-height: 1;
        }

        /* Column Widths */
        .col-name { width: 8%; text-align: left; padding-left: 3px; }
        .col-designation { width: 7%; }
        .col-prev-abs { width: 3%; }
        .col-dept { width: 5%; }
        .col-timesheet { width: 50%; }
        .col-total { width: 5%; }
        .col-rate { width: 7%; }
        .col-deduction { width: 6%; }
        .col-honorarium { width: 8%; }

        /* Timesheet Inner Table */
        .timesheet-table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            font-size: 7pt;
            border: none;
        }
        
        .timesheet-table th,
        .timesheet-table td {
            border: 1px solid #ccc;
            padding: 1px;
            text-align: center;
            height: 10pt;
        }
        
        .timesheet-table th {
            font-weight: bold;
            background-color: #b0c4de;
        }

        .day-number {
            font-size: 10px;
            font-weight: bold;
            line-height: 1;
        }
        
        .day-abbr {
            font-size: 7px;
            line-height: 1;
        }
        
        .day-value {
            font-weight: 600;
        }
        
        .day-sunday,
        .day-holiday {
            background-color: #555 !important;
            color: white !important;
            font-weight: bold;
        }
        
        .day-absent {
            color: #dc3545;
        }

        /* Footer */
        tfoot td {
            background-color: #e6e6e6;
            font-weight: bold;
            font-size: 9px;
            padding: 4px 2px;
        }

        .grand-total-honorarium {
            color: #880044;
            font-size: 11px !important;
            background-color: #ffe6f0 !important;
        }

        /* Signatures */
        .print-footer {
            margin-top: 15px;
            text-align: center;
            padding-top: 5px;
            font-size: 8px;
        }

        .print-footer .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding: 0 40px;
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
    </style>
</head>
<body>

    <div class="print-header">
        <h1>MCC PAYROLL SYSTEM</h1>
        <h2>Payroll History - Detailed</h2>
        <div class="meta-info">
            <span><strong>Generated:</strong> {{ date('F j, Y \a\t g:i A') }}</span>
            <span><strong>Period:</strong> {{ $monthName }} {{ $selectedYear }}
                @if(isset($selectedPeriod) && $selectedPeriod !== 'all')
                    ({{ $selectedPeriod == '16-end' ? '16 - End of Month' : $selectedPeriod }})
                @endif
            </span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="col-name">NAMES</th>
                <th rowspan="2" class="col-designation">DESIGNATION</th>
                <th rowspan="2" class="col-prev-abs">PREV.<br>ABS.</th>
                <th rowspan="2" class="col-dept">DEPARTMENT</th>
                <th colspan="1" class="col-timesheet">DAILY HOURS/ATTENDANCE FOR CUT-OFF</th>
                <th rowspan="2" class="col-total">TOTAL Hour/Days</th>
                <th rowspan="2" class="col-rate">Rate per Hour/Day</th>
                <th rowspan="2" class="col-deduction">Deduc. Prev. Cut Off</th>
                <th rowspan="2" class="col-honorarium">TOTAL HONORARIUM</th>
            </tr>
            <tr>
                <th class="col-timesheet">
                    {{ $monthName }} {{ $selectedYear }}
                    @if(isset($selectedPeriod) && $selectedPeriod !== 'all')
                        ({{ $selectedPeriod == '16-end' ? '16-End' : $selectedPeriod }})
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
                    <td class="col-timesheet" style="padding: 0.5px !important;">
                        @if($record->timesheet && !empty($record->days_in_period))
                            @php
                                $dayCount = count($record->days_in_period);
                                $colWidth = $dayCount > 0 ? (100 / $dayCount) : 0;
                            @endphp
                            
                            <table class="timesheet-table">
                                <thead>
                                    {{-- Day Numbers --}}
                                    <tr>
                                        @foreach($record->days_in_period as $day)
                                            <th style="width: {{ $colWidth }}%;">{{ $day['number'] }}</th>
                                        @endforeach
                                    </tr>
                                    {{-- Day Abbreviations --}}
                                    <tr>
                                        @foreach($record->days_in_period as $day)
                                            @php
                                                $isSunday = $day['is_sunday'];
                                                $isHoliday = in_array($day['date'], $holidays ?? []);
                                                $cellClass = ($isSunday || $isHoliday) ? 'day-sunday' : '';
                                            @endphp
                                            <th style="width: {{ $colWidth }}%;" class="{{ $cellClass }}">{{ $day['abbr'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Values --}}
                                    <tr>
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
                                                    if ($value == 0) {
                                                        $displayValue = '0.00';
                                                        $cellClass = 'day-absent';
                                                    } else {
                                                        $displayValue = number_format($value, 2, '.', '');
                                                        $cellClass = 'day-value';
                                                    }
                                                }
                                                
                                                if ($isSunday || $isHoliday) {
                                                    $cellClass = 'day-sunday';
                                                }
                                            @endphp
                                            <td style="width: {{ $colWidth }}%; border-bottom: none;" class="{{ $cellClass }}">
                                                {{ $displayValue }}
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div style="font-size: 7pt; margin-top: 3px; padding-left: 2px;">
                                Period: {{ $record->pay_period ?? 'N/A' }}
                                @if($isStaffUtility)
                                    (1=Present, 0=Absent)
                                @else
                                    (Hours)
                                @endif
                            </div>
                        @else
                            <div style="padding: 2px;">
                                {{ $record->pay_period ?? 'N/A' }} (No Timesheet Data)
                            </div>
                        @endif
                    </td>
                    
                    <td class="col-total">
                        {{ number_format($record->total_hours_or_days ?? 0, 2) }}
                        {{ $isStaffUtility ? 'days' : 'hrs' }}
                    </td>
                    <td class="col-rate">PHP {{ number_format($record->rate ?? 0, 2) }}</td>
                    <td class="col-deduction">PHP 0.00</td>
                    <td class="col-honorarium">PHP {{ number_format($record->total_honorarium ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">
                        No payroll records found for {{ $monthName }} {{ $selectedYear }}
                        @if(isset($selectedPeriod) && $selectedPeriod !== 'all')
                            for period {{ $selectedPeriod == '16-end' ? '16 - End of Month' : $selectedPeriod }}.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
        
        {{-- Footer with Grand Total --}}
        @if(count($records) > 0)
            <tfoot>
                <tr>
                    <td colspan="8" style="text-align: right; background-color: #e6e6e6;">
                        **GRAND TOTAL HONORARIUM:**
                    </td>
                    <td class="grand-total-honorarium">
                        PHP {{ number_format($grandTotal, 2) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="print-footer">
        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div><strong>Approved By</strong></div>
                <div>DR. FLORIPIS A. MONTECILLO</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div><strong>Reviewed By</strong></div>
                <div>WENDELL DENORTE</div>
            </div>
        </div>
        <p style="margin-top: 5px;">© {{ date('Y') }} MCC - All Rights Reserved</p>
    </div>

</body>
</html>