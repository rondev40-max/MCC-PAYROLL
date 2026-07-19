<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .main-content {
            margin: 30px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 95%;
            max-width: 800px;
            border: 1px solid #e0e0e0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header img {
            max-height: 60px;
            width: auto;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 28px;
            color: #2d3748;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #003366;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .detail-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .detail-item label {
            color: #666;
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: 500;
        }
        .detail-item span {
            font-weight: 600;
            font-size: 15px;
            color: #333;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .summary-table th, .summary-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .summary-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            font-size: 15px;
        }
        .summary-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary-table td:last-child {
            text-align: right;
            font-weight: 700;
        }
        .total-row td {
            font-weight: 700;
            font-size: 18px;
            background-color: #e8f0f7;
            color: #003366;
            border-top: 2px solid #667eea;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 20px;
        }
        .attendance-table th, .attendance-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        .attendance-table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        .holiday-column {
            background-color: #f8d7da !important;
        }
        .sunday-column {
            background-color: #e9ecef !important;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="MCC Logo">
            @endif
            <h1>Payslip Summary</h1>
        </div>

        <div class="employee-details">
            <div class="section-title">Employee Information</div>
            <div class="details-grid">
                <div class="detail-item">
                    <label>Employee Name</label>
                    <span>{{ $employeeName ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Designation</label>
                    <span>{{ $designation ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Department</label>
                    <span>{{ $department ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Pay Period</label>
                    <span>{{ $payPeriod ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="payslip-summary">
            <div class="section-title">Honorarium Calculation</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Days/Hours Rendered</td>
                        <td>{{ $totalDaysOrHours ?? '0' }}</td>
                    </tr>
                    <tr>
                        <td>Rate</td>
                        <td>{{ $rate ?? '₱0.00' }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Honorarium</td>
                        <td>{{ $totalHonorarium ?? '₱0.00' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="attendance-breakdown">
            <div class="section-title">Payroll period</div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        @foreach($days as $day)
                            @php
                                $isHolidayHeader = in_array($day['date'], $holidays ?? []);
                            @endphp
                            <th class="{{ $isHolidayHeader ? 'holiday-column' : '' }}">
                                {{ $day['number'] }}<br>{{ $day['abbr'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($days as $day)
                            @php
                                $currentDate = $day['date'];
                                $isHoliday = in_array($currentDate, $holidays ?? []);
                                $weekday = \Carbon\Carbon::parse($currentDate)->format('D');
                                $isSunday = ($weekday === 'Sun');
                                
                                $columnClasses = '';
                                if ($isHoliday) {
                                    $columnClasses = 'holiday-column';
                                } elseif ($isSunday) {
                                    $columnClasses = 'sunday-column';
                                }

                                $hours = 0;
                                if (isset($timesheet)) {
                                    $field = strtolower($weekday) . '_hours'; // mon_hours, tue_hours etc.
                                    if (isset($timesheet->$field)) {
                                        $hours = $timesheet->$field;
                                    }
                                }
                            @endphp
                            <td class="{{ $columnClasses }}">
                                {{ $isHoliday ? 'H' : ($isSunday ? 'S' : ($hours > 0 ? $hours : '0')) }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply.</p>
            <p>&copy; {{ date('Y') }} Madridejos Community College. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
