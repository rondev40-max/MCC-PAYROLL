<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Instructor Fulltime Timesheet</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      position: relative;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="20" cy="80" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      pointer-events: none;
      z-index: 1;
    }

    .main-content {
      margin: 30px auto;
      padding: 30px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(255,255,255,0.2);
      width: 95%;
      max-width: 1400px;
      position: relative;
      z-index: 2;
      border: 1px solid rgba(255,255,255,0.3);
    }

    .main-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #667eea, #764ba2);
      border-radius: 20px 20px 0 0;
    }

    .main-content h2 {
      font-size: 28px;
      color: #2d3748;
      font-weight: 700;
      margin-bottom: 25px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Icon Buttons */
    .icon-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      border-radius: 15px;
      font-size: 18px;
      color: #fff;
      border: none;
      cursor: pointer;
      margin-right: 10px;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      position: relative;
      overflow: hidden;
    }

    .icon-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }

    .icon-btn:hover::before {
      left: 100%;
    }

    .btn-back {
      background: linear-gradient(135deg, #667eea, #764ba2);
    }
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .add-btn {
      background: linear-gradient(135deg, #11998e, #38ef7d);
    }
    .add-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
    }

    .print-btn {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border: 2px solid rgba(255,255,255,0.3);
    }
    .print-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    /* Action Buttons (Edit/Delete) and Auto-Save Status */
    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 6px;
      font-size: 16px;
      margin: 2px;
      color: #fff;
      border: none;
      text-decoration: none;
    }

    .btn-edit {
      background-color: #0d6efd;
    }
    .btn-edit:hover {
      background-color: #084298;
    }

    .btn-delete {
      background-color: #dc3545;
    }
    .btn-delete:hover {
      background-color: #a71d2a;
    }
    
    /* Auto-Save Status Icon Style */
    .btn-auto-status {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 6px;
      font-size: 16px;
      margin: 2px;
      color: #fff;
      transition: background-color 0.3s ease, transform 0.3s ease;
      background-color: #6c757d; /* Default Grey */
      cursor: default; 
    }

    .btn-auto-status.saving {
      background-color: #ffc107 !important; /* Yellow */
      animation: pulsate 1.5s infinite;
    }

    .btn-auto-status.saved {
      background-color: #198754 !important; /* Green */
    }
    
    .btn-auto-status.error {
      background-color: #dc3545 !important; /* Red */
    }

    /* Animation for saving state */
    @keyframes pulsate {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }


    /* Table Styling */
    .table-container {
      overflow-x: auto;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      background: white;
      border: 1px solid rgba(102, 126, 234, 0.1);
      margin-top: 20px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .main-content {
        margin: 15px;
        padding: 15px;
        width: calc(100% - 30px);
      }
      
      .d-flex.justify-content-between {
        flex-direction: column;
        gap: 15px;
      }
      
      .d-flex.align-items-center:first-child {
        justify-content: center;
      }
      
      .d-flex.align-items-center:last-child {
        justify-content: center;
      }
      
      th, td {
        padding: 6px 4px;
        font-size: 11px;
      }
      
      .day-column {
        width: 50px;
        min-width: 50px;
        max-width: 50px;
      }
      
      .day-input {
        height: 28px;
        font-size: 10px;
        padding: 1px 2px;
      }
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    table thead {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
    }

    th, td {
      padding: 8px 6px;
      text-align: center;
      border: 1px solid #ddd;
      font-size: 12px;
      white-space: nowrap;
    }
    
    /* Day columns styling */
    .day-column {
      width: 60px;
      min-width: 60px;
      max-width: 60px;
      padding: 4px 2px;
    }
    
    .day-input {
      width: 100%;
      height: 32px;
      padding: 2px 4px;
      border: 1px solid #ddd;
      border-radius: 4px;
      text-align: center;
      font-size: 11px;
      background: white;
      transition: all 0.2s ease;
    }

    /* Sunday styling */
    .sunday-column {
      background-color: #555555 !important;
    }
    
    .sunday-input {
      background-color: #666666 !important;
      border-color: #777777 !important;
      color: #ffffff !important;
      cursor: not-allowed;
      font-weight: bold;
    }
    
    .sunday-input:hover, .sunday-input:focus {
      background-color: #666666 !important;
      border-color: #777777 !important;
      box-shadow: none !important;
    }
    
    /* Holiday styling (NEW) */
    .holiday-column {
        background-color: #f8d7da !important; /* Light Red */
    }
    
    .holiday-input {
        background-color: #dc3545 !important; /* Red */
        border-color: #dc3545 !important;
        color: #ffffff !important;
        cursor: not-allowed;
        font-weight: bold;a
    }
    
    .day-input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
      outline: none;
    }
    
    .day-input:hover {
      border-color: #667eea;
    }
    
    .day-input.saving {
      background-color: #fff3cd;
      border-color: #ffc107;
    }
    
    .day-input.saved {
      background-color: #d1edff;
      border-color: #0dcaf0;
    }
    
    .day-input.error {
      background-color: #f8d7da;
      border-color: #dc3545;
    }
    
    /* Field input styling */
    .field-input {
      width: 100%;
      padding: 4px 6px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 12px;
      background: white;
      transition: all 0.2s ease;
    }
    
    .field-input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
      outline: none;
    }
    
    .field-input:hover {
      border-color: #667eea;
    }
    
    .field-input.saving {
      background-color: #fff3cd;
      border-color: #ffc107;
    }
    
    .field-input.saved {
      background-color: #d1edff;
      border-color: #0dcaf0;
    }
    
    .field-input.error {
      background-color: #f8d7da;
      border-color: #dc3545;
    }
    
    .day-header {
      width: 60px;
      min-width: 60px;
      max-width: 60px;
      font-size: 11px;
      line-height: 1.2;
    }
    
    .day-header small {
      font-size: 9px;
      color: #666;
    }

    /* Day header styles to mimic sample */
    .day-header { position: relative; }
    .day-number {
      display: inline-block;
      color: #000; /* black */
      font-size: 16px; /* bigger number */
      font-weight: 800;
      line-height: 1.1;
    }
    .day-abbr {
      display: inline-block;
      color: #000; /* black */
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .current-day { background-color: #f7c79a !important; }
    /* Tighten cell spacing similar to the image */
    .day-header, .day-column { padding: 6px 4px; }

    /* Highlight Monday to Saturday labels */
    .weekday-label {
      font-weight: 700;
      color: #000000; /* darker for contrast */
      background-color: #fff3cd; /* light highlight */
      padding: 2px 4px;
      border-radius: 4px;
      display: inline-block;
    }
    
    /* Empty state styling */
    .empty-state {
      padding: 2rem;
    }
    
    .empty-state i {
      display: block;
      margin: 0 auto 1rem;
    }

    th {
      font-weight: 600;
      font-size: 14px;
      color: white;
      text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    tr:hover td {
      background-color: rgba(102, 126, 234, 0.1) !important;
      transition: background-color 0.3s ease;
    }

    td.left {
      text-align: left;
    }

    tr:nth-child(even) td {
      background-color: #f9f9f9;
    }

    tr:hover td {
      background-color: #ffe6e6; /* light red hover */
    }

    /* Print-specific styles */
    @media print {
      body {
        background: white !important;
        color: black !important;
        font-size: 12px;
      }
      
      .main-content {
        margin: 0 !important;
        padding: 20px !important;
        background: white !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        width: 100% !important;
        max-width: none !important;
      }
      
      .icon-btn, .float-end, .action-btn, .btn-auto-status, .btn-delete, .btn-edit {
        display: none !important; /* Hide all action buttons and status icons */
      }
      
      .main-content h2 {
        color: black !important;
        text-align: center;
        margin-bottom: 30px;
        font-size: 18px;
        font-weight: bold;
      }
      
      table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 0 !important;
      }
      
      table thead {
        background-color: #f0f0f0 !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
      }
      
      th, td {
        border: 1px solid #000 !important;
        padding: 8px 6px !important;
        font-size: 10px !important;
        text-align: center !important;
      }
      
      th {
        font-weight: bold !important;
        background-color: #f0f0f0 !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
      }
      
      td.left {
        text-align: left !important;
      }
      
      tr:nth-child(even) td {
        background-color: #f9f9f9 !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
      }
      
      /* Hide actions column in print */
      .actions-column {
        display: none !important;
      }
      
      /* Hide input fields and show values in print */
      .day-input, .field-input {
        border: none !important;
        background: transparent !important;
        text-align: center !important;
        font-weight: bold !important;
        padding: 0 !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
      }
      
      .day-column {
        width: 35px !important;
        min-width: 35px !important;
        max-width: 35px !important;
      }
    }
    
    /* Custom SweetAlert2 styling to match theme */
    .swal-custom-popup {
      border-radius: 15px !important;
      border: 2px solid #dc3545 !important;
    }
    
    .swal-custom-title {
      color: #dc3545 !important;
      font-weight: 700 !important;
    }
    
    .swal-custom-content {
      color: #2c3e50 !important;
    }
    
    .swal-custom-button {
      background: linear-gradient(135deg, #dc3545, #c82333) !important;
      border: none !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
      padding: 12px 30px !important;
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
    }
    
    .swal-custom-button:hover {
      background: linear-gradient(135deg, #a71d2a, #b21e2f) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4) !important;
    }
    
    .swal-custom-cancel-button {
      background: linear-gradient(135deg, #6c757d, #5a6268) !important;
      border: none !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
      padding: 12px 30px !important;
      box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3) !important;
    }
    
    .swal-custom-cancel-button:hover {
      background: linear-gradient(135deg, #545b62, #4e555b) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4) !important;
    }

    /* Print button styling */
    .swal-print-button {
      background: linear-gradient(135deg, #0dcaf0, #0aa2c0) !important;
      border: none !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
      padding: 12px 30px !important;
      box-shadow: 0 4px 15px rgba(13, 202, 240, 0.3) !important;
    }
    
    .swal-print-button:hover {
      background: linear-gradient(135deg, #0aa2c0, #0891a5) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 20px rgba(13, 202, 240, 0.4) !important;
    }

    /* Print-specific header and footer styling */
    @media print {
      .print-header {
        display: block !important;
        page-break-inside: avoid;
      }
      
      .print-footer {
        display: block !important;
        page-break-inside: avoid;
        position: fixed;
        bottom: 0;
        width: 100%;
      }
      
      .print-header h1 {
        font-size: 20px;
        margin: 0;
        color: black;
      }
      
      .print-header h2 {
        font-size: 16px;
        margin: 5px 0;
        color: black;
      }
      
      .print-header p {
        font-size: 12px;
        margin: 5px 0;
        color: black;
      }
    }
  </style>
</head>
<body>

  <div class="main-content">
    <div class="print-header" style="display: none;">
      <h1>MCC PAYROLL SYSTEM</h1>
      <h2>Instructor Fulltime Timesheet</h2>
      <p>Generated on: <span id="print-date"></span></p>
      <hr>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center">
        <a href="{{ route('dashboard') }}" class="icon-btn btn-back me-3" title="Back to Dashboard">
          <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0">Instructor Fulltime Timesheet</h2>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button onclick="openPrintPage()" class="icon-btn print-btn" title="Print">
          <i class="bi bi-printer"></i>
        </button>
        
        <a href="{{ route('fulltime.create') }}" class="icon-btn add-btn" title="Add Entry">
          <i class="bi bi-plus-lg"></i>
        </a>
      </div>
    </div>

    <div class="d-flex justify-content-center mb-4">
      <form method="GET" action="{{ route('fulltime.index') }}" class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center">
          <label for="month" class="me-2 fw-bold">Month:</label>
          <select name="month" id="month" class="form-select" style="width: auto;">
            @for ($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
              </option>
            @endfor
          </select>
        </div>
        <div class="d-flex align-items-center">
          <label for="period" class="me-2 fw-bold">Period:</label>
          <select name="period" id="period" class="form-select" style="width: auto;">
            <option value="auto" {{ $period == 'auto' ? 'selected' : '' }}>Auto</option>
            <option value="1-15" {{ $period == '1-15' ? 'selected' : '' }}>1-15</option>
            <option value="16-end" {{ $period == '16-end' ? 'selected' : '' }}>16-End</option>
          </select>
        </div>
        <div class="d-flex align-items-center">
          <label for="year" class="me-2 fw-bold">Year:</label>
          <select name="year" id="year" class="form-select" style="width: auto;">
            @php
              $currentYear = now()->year;
            @endphp
            @for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
              <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Update</button>
        
        {{-- Holiday Button (Updated to link to a holiday management page) --}}
        <a href="{{ route('holidays.index') }}" class="btn btn-warning" title="View/Manage Holidays">
          <i class="bi bi-calendar-event me-1"></i> Holiday
        </a>
      </form>
    </div>

    <div class="table-container mt-3">
      <table>
        <thead>
          <tr>
            <th>NAMES</th>
            <th>DESIGNATION</th> 
            <th>Prev.Abs.</th>
            <th>DEPARTMENT</th>
            {{-- Dynamic day headers based on selected month --}}
            @foreach($days as $day)
              @php
                // Check if the current day is a holiday
                $isHolidayHeader = in_array($day['date'], $holidays ?? []);
              @endphp
              <th class="day-header {{ $isHolidayHeader ? 'holiday-column' : '' }}">
                <span class="day-number" style="color: {{ $isHolidayHeader ? '#dc3545' : '#000' }};">{{ $day['number'] }}</span>
                <br>
                <span class="day-abbr">{{ $day['abbr'] }}</span>
              </th>
            @endforeach
            <th>Details for<br>Inclusive Hours of Classes</th>
            <th>TOTAL<br>Hour</th>
            <th>Rate per<br>Hour</th>
            <th>Deduction<br>Previous Cut Off</th>
            <th>TOTAL HONORARIUM</th>
            <th class="actions-column">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($timesheets as $timesheet)
          <tr>
            <td class="left">{{ $timesheet->employee_name }}</td>
            <td>{{ ucfirst($timesheet->designation) }}</td>
            <td>
              <input type="number"
                     class="form-control field-input"
                     value="{{ $timesheet->prev_abs ?? 0.00 }}"
                     data-timesheet-id="{{ $timesheet->id }}"
                     data-field="prev_abs"
                     placeholder="0.00"
                     min="0"
                     step="0.25">
            </td>
            <td>{{ $timesheet->department }}</td>

            @foreach($days as $day)
              @php
                $currentDate = $day['date']; // E.g., '2025-10-28'
                
                // 1. Check if the current day is a holiday
                $isHoliday = in_array($currentDate, $holidays ?? []); 
                
                // 2. Determine weekday column name (mon_hours, tue_hours, ...)
                $weekday = \Carbon\Carbon::parse($currentDate)->format('D');
                $weekdayMap = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];
                $field = $weekdayMap[$weekday] ?? 'mon_hours';

                // 3. Determine Readonly/Disabled State
                $isDisabledOrHoliday = ($weekday === 'Sun') || $isHoliday;

                // 4. Set input value to 0 if it's a holiday, otherwise use existing value
                $inputValue = $isHoliday ? 0 : (is_numeric($timesheet->$field) && floor($timesheet->$field) == $timesheet->$field ? number_format($timesheet->$field, 0) : $timesheet->$field);
                
                // 5. Add CSS classes for coloring and input disabling
                $columnClasses = $isHoliday ? 'holiday-column' : ($weekday === 'Sun' ? 'sunday-column' : '');
                $inputClasses = $isHoliday ? 'holiday-input' : ($weekday === 'Sun' ? 'sunday-input' : '');
              @endphp
              
              <td class="day-column {{ $columnClasses }}">
                <input type="number"
                       class="form-control day-input {{ $inputClasses }}"
                       value="{{ $inputValue }}"
                       data-timesheet-id="{{ $timesheet->id }}"
                       data-field="{{ $field }}"
                       placeholder="0"
                       min="0"
                       step="0.5"
                       data-day-date="{{ $currentDate }}"
                       {{ $isDisabledOrHoliday ? 'readonly' : '' }}
                       data-is-holiday="{{ $isHoliday ? 'true' : 'false' }}"> {{-- Added for JS --}}
              </td>
            @endforeach
            <td>
              <input type="text"
                     class="form-control field-input"
                     value="{{ $timesheet->details }}"
                     data-timesheet-id="{{ $timesheet->id }}"
                     data-field="details"
                     placeholder="Details">
            </td>
            <td id="total-hour-{{ $timesheet->id }}">{{ is_numeric($timesheet->total_hour) && floor($timesheet->total_hour) == $timesheet->total_hour ? number_format($timesheet->total_hour, 0) : $timesheet->total_hour }}</td>
            
            <td>
              <input type="number" 
                     class="form-control field-input" 
                     value="{{ $timesheet->rate_per_hour ?? 0.00 }}" 
                     data-timesheet-id="{{ $timesheet->id }}" 
                     data-field="rate_per_hour"
                     min="0" 
                     step="0.01"
                     placeholder="0.00">
            </td>
            <td>
              <input type="number" 
                     class="form-control field-input" 
                     value="{{ $timesheet->deduction ?? 0.00 }}" 
                     data-timesheet-id="{{ $timesheet->id }}" 
                     data-field="deduction"
                     min="0" 
                     step="0.01"
                     placeholder="0.00">
            </td>
            <td id="total-honorarium-{{ $timesheet->id }}">₱{{ number_format($timesheet->total_honorarium ?? 0.00, 2) }}</td>
            
            <td class="actions-column">
              <a href="{{ route('fulltime.edit', $timesheet->id) }}" 
                 class="action-btn btn-edit me-2" 
                 title="Edit">
                <i class="bi bi-pencil-square"></i>
              </a>
              
              {{-- AUTO-SAVE STATUS ICON (Replaced the manual Save Button) --}}
              <span id="save-status-{{ $timesheet->id }}" 
                    class="action-btn btn-auto-status" 
                    title="Auto-Save Status">
                <i class="bi bi-grip-horizontal"></i>
              </span>
              
              <form action="{{ route('fulltime.destroy', $timesheet->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-btn btn-delete" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="23" class="text-center py-5">
              <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d; margin-bottom: 1rem;"></i>
                <h5 class="text-muted">No Timesheet Records Found</h5>
                <p class="text-muted mb-3">There are no fulltime timesheet entries to display.</p>
                <a href="{{ route('fulltime.create') }}" class="btn btn-primary">
                  <i class="bi bi-plus-lg me-2"></i>Add First Timesheet
                </a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    <div class="print-footer" style="display: none;">
      <p>Generated by MCC Payroll System - {{ date('Y-m-d H:i:s') }}</p>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Global variable to hold debounce timers
    const saveTimers = {};

    /**
     * Debounce function: Delays the execution of a function until after a certain time has passed
     * since the last time the function was called.
     */
    function debounce(func, delay, timesheetId) {
        // Clear the previous timer for this timesheet ID
        if (saveTimers[timesheetId]) {
            clearTimeout(saveTimers[timesheetId]);
        }
        
        // Set a new timer
        saveTimers[timesheetId] = setTimeout(func, delay);
    }

    // Open dedicated print page
function openPrintPage() {
    Swal.fire({
        title: 'Open Print Page',
        text: 'This will open a dedicated print-optimized page in a new tab.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#0dcaf0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-box-arrow-up-right"></i> Open Print Page',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            content: 'swal-custom-content',
            confirmButton: 'swal-print-button',
            cancelButton: 'swal-custom-cancel-button'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // 1. Kuhanin ang current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // 2. Kuhanin ang month, year, at period. Gumamit ng Blade fallback 
            //    sakaling walang parameters sa URL. Tiyakin na ang $month, $year, at $period 
            //    ay ipinasa sa index.blade.php mula sa controller.
            const month = urlParams.get('month') || {{ $month }}; 
            const year = urlParams.get('year') || {{ $year }};     
            const period = urlParams.get('period') || '{{ $period }}'; 
            
            // 3. I-build ang print URL kasama ang parameters
            // Dapat ito ay nasa isang linya o gumamit ng template literal (backticks ``)
            const printUrl = `{{ route('fulltime.print') }}?month=${month}&year=${year}&period=${period}`;
            
            // 4. Buksan ang print page
            window.open(printUrl, '_blank');
            
            Swal.fire({
                title: 'Print Page Opened!',
                text: 'The print-optimized page has been opened in a new tab.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                customClass: { popup: 'swal-custom-popup', title: 'swal-custom-title' }
            });
        }
    });
}

    // Check for success/error messages from Laravel session
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#198754',
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545',
      });
    @endif

    // Enhanced delete confirmation with SweetAlert
    document.querySelectorAll('.btn-delete').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const form = this.closest('form');
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({ title: 'Deleting...', icon: 'info', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); } });
            form.submit();
          }
        });
      });
    });

    // START OF AUTOSAVE AND CALCULATION LOGIC
    document.addEventListener('DOMContentLoaded', function() {
      
      // Function to perform the local calculation (UPDATED TO FIX DEDUCTION LOGIC)
      function calculateTotals(row) {
          let grossHours = 0;
          
          // 1. Calculate Gross Total Hours from day inputs (Mon-Sat only, EXCLUDING Holidays)
          const dayInputs = row.querySelectorAll('.day-input');
          dayInputs.forEach(input => {
            // SKIP Sunday AND Holiday inputs in calculation
            if (input.dataset.isHoliday === 'true' || input.classList.contains('sunday-input')) {
                return; 
            }
            const hours = parseFloat(input.value) || 0;
            grossHours += hours;
          });

          // 2. Get Prev.Abs., Rate, and Deduction
          const prevAbsInput = row.querySelector('[data-field="prev_abs"]');
          const ratePerHourInput = row.querySelector('[data-field="rate_per_hour"]');
          const deductionInput = row.querySelector('[data-field="deduction"]'); // This is the 'Deduction Previous Cut Off' field

          const prevAbsHours = parseFloat(prevAbsInput ? prevAbsInput.value : 0) || 0;
          const ratePerHour = parseFloat(ratePerHourInput ? ratePerHourInput.value : 0) || 0;
          // Ang value sa 'deduction' field ay tinuturing na oras na ibabawas sa honorarium
          const deductionValue = parseFloat(deductionInput ? deductionInput.value : 0) || 0; 

          // 3. Calculate Final Payable Hours and Total Honorarium
          // Gross Hours minus Previous Absences (Prov.Abs.)
          let finalPayableHours = grossHours - prevAbsHours;
          
          if (finalPayableHours < 0) {
              finalPayableHours = 0;
          }
          
          // Calculate Gross Honorarium based on Final Payable Hours
          const grossHonorarium = finalPayableHours * ratePerHour; 

          // Calculate the Total Honorarium: Gross Honorarium - Deduction Value
          const totalHonorarium = grossHonorarium - deductionValue;

          // 4. Update the display elements
          const timesheetId = row.querySelector('.btn-auto-status').id.replace('save-status-', ''); // Use the new status icon ID
          const totalHourElement = document.getElementById(`total-hour-${timesheetId}`);
          const totalHonorariumElement = document.getElementById(`total-honorarium-${timesheetId}`);

          if (totalHourElement) {
            // Display the FINAL PAYABLE HOURS, formatted
            totalHourElement.textContent = finalPayableHours.toFixed(1).replace(/\.0$/, '');
          }
          
          if (totalHonorariumElement) {
            // Format the total honorarium
            totalHonorariumElement.textContent = `₱${totalHonorarium.toFixed(2)}`;
          }
          
          return { finalPayableHours: finalPayableHours.toFixed(2), totalHonorarium: totalHonorarium.toFixed(2) };
      }
      
      // Main function to save data via AJAX
      function saveAllData(timesheetId, row) {
        const statusIcon = document.getElementById(`save-status-${timesheetId}`);
        const fieldInputs = row.querySelectorAll('.field-input');
        const dayInputs = row.querySelectorAll('.day-input');
        
        // 1. Calculate the final values first
        const { finalPayableHours, totalHonorarium } = calculateTotals(row);
        
        // 2. Set saving state
        statusIcon.classList.remove('saved', 'error');
        statusIcon.classList.add('saving');
        statusIcon.innerHTML = '<i class="bi bi-hourglass-split"></i>'; // Hourglass icon

        // 3. Collect all data
        const fieldData = {};
        fieldInputs.forEach(input => {
          const field = input.dataset.field;
          if (field) { fieldData[field] = input.value; }
        });

        const dayData = {};
        dayInputs.forEach(input => {
          const field = input.dataset.field;
          
          // CRITICAL: If it's a holiday, we explicitly save 0, even if the user somehow manages to change it.
          // This ensures data consistency on the backend.
          if (field && !input.classList.contains('sunday-input')) {
            if (input.dataset.isHoliday === 'true') {
                 dayData[field] = 0; 
            } else {
                 dayData[field] = input.value;
            }
          }
        });

        // Add calculated totals to fieldData to be saved
        fieldData['total_hour'] = finalPayableHours; 
        fieldData['total_honorarium'] = totalHonorarium;

        const allData = { ...fieldData, ...dayData };
        const promises = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // 4. Send multiple AJAX requests (one for each field is safer for partial updates)
        // We only send the fields that we are tracking via `allData`
        Object.keys(allData).forEach(field => {
            const promise = fetch(`/fulltime/${timesheetId}/update-field`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  field: field,
                  value: allData[field]
                })
              });
              promises.push(promise);
        });
        
        // 5. Handle responses
        Promise.all(promises)
          .then(responses => Promise.all(responses.map(r => r.ok ? r.json().catch(() => ({ success: true })) : Promise.reject(new Error(`Failed to update status ${r.status}`)) )))
          .then(() => {
            // Success state
            statusIcon.classList.remove('saving');
            statusIcon.classList.add('saved');
            statusIcon.innerHTML = '<i class="bi bi-check-lg"></i>'; // Check icon
            
            // Revert status icon after 3 seconds
            setTimeout(() => {
              statusIcon.classList.remove('saved');
              statusIcon.innerHTML = '<i class="bi bi-grip-horizontal"></i>'; // Default icon
            }, 3000);
          })
          .catch(error => {
            console.error('Autosave Error:', error);
            // Error state
            statusIcon.classList.remove('saving', 'saved');
            statusIcon.classList.add('error');
            statusIcon.innerHTML = '<i class="bi bi-x-lg"></i>'; // X icon
            
            // Revert status icon after 5 seconds
            setTimeout(() => {
              statusIcon.classList.remove('error');
              statusIcon.innerHTML = '<i class="bi bi-grip-horizontal"></i>'; // Default icon
            }, 5000);
          });
      }

      // Initial calculation for all rows on load
      document.querySelectorAll('tbody tr').forEach(row => {
          if (row.querySelector('.btn-auto-status')) {
              calculateTotals(row);
          }
      });
      
      // Attach event listeners for AUTOSAVE
      document.querySelectorAll('.day-input, [data-field="rate_per_hour"], [data-field="deduction"], [data-field="prev_abs"], [data-field="details"]').forEach(input => {
        input.addEventListener('input', function() {
          const row = this.closest('tr');
          const statusElement = row.querySelector('.btn-auto-status');
          // Check if the row has a status element (valid timesheet entry)
          if (!statusElement) return;
          
          const timesheetId = statusElement.id.replace('save-status-', '');
          
          // 1. Perform immediate calculation for live feedback
          calculateTotals(row);

          // 2. Debounce the save function (wait for 1.5 seconds after the last input)
          debounce(() => {
            saveAllData(timesheetId, row);
          }, 1500, timesheetId); 
        });
      });
      // END OF AUTOSAVE LOGIC
    });
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