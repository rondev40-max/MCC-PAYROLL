<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Utility Timesheet</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  @php
    // Ensure variables are defined with defaults if not passed from controller
    $month = $month ?? now()->month;
    $year = $year ?? now()->year;
    $period = $period ?? 'auto';
    $days = $days ?? [];
    $timesheets = $timesheets ?? collect();
    $startDay = $startDay ?? 16;
    $endDay = $endDay ?? now()->daysInMonth;
  @endphp
   <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* Auto-save Input Styling */
    input.form-control {
      transition: all 0.3s ease;
    }

    input.form-control:hover {
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }

    input.form-control.saving {
      background: rgba(255, 193, 7, 0.1) !important;
      border-color: #ffc107 !important;
      animation: pulse 1s infinite;
      box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2) !important;
    }

    input.form-control.saved {
      background: rgba(40, 167, 69, 0.1) !important;
      border-color: #28a745 !important;
      box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2) !important;
    }

    input.form-control.error {
      background: rgba(220, 53, 69, 0.1) !important;
      border-color: #dc3545 !important;
      box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
    }

    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }

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
      background: linear-gradient(135deg, #4caf50, #45a049);
    }
    .add-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }

    .print-btn {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border: 2px solid rgba(255,255,255,0.3);
    }
    .print-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(240, 147, 251, 0.4);
    }

    /* Action Buttons (Edit/Delete) */
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

    /* Ayos para sa Actions Column */
    .actions-container {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem; /* Nagdagdag ng space sa pagitan ng mga button */
    }

    /* Auto-Save Status Icon Style (from fulltime) */
    .btn-auto-status {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 6px;
      font-size: 16px;
      /* Inalis ang margin: 2px; para gamitin ang gap */
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

    th {
      font-weight: 600;
      font-size: 13px;
      color: white;
      text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    td.left {
      text-align: left;
    }

    tr:nth-child(even) td {
      background-color: #f9f9f9;
    }

    tr:hover td {
      background-color: rgba(102, 126, 234, 0.1) !important;
      transition: background-color 0.3s ease;
    }

    /* Column widths matching parttime */
    td.left {
      width: 120px;
      min-width: 120px;
    }
    td:nth-child(2) {
      width: 100px;
      min-width: 100px;
    }
    td:nth-child(3) {
      width: 80px;
      min-width: 80px;
    }
    .day-column {
      width: 60px;
      min-width: 60px;
      max-width: 60px;
      padding: 4px 2px;
    }
    td:nth-child(19) {
      width: 60px;
      min-width: 60px;
    }
    td:nth-child(20) {
      width: 80px;
      min-width: 80px;
    }
    td:nth-child(21) {
      width: 80px;
      min-width: 80px;
    }
    td:nth-child(22) {
      width: 100px;
      min-width: 100px;
    }
    td:nth-child(23) {
      width: 80px;
      min-width: 80px;
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
    .day-input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      outline: none;
    }
    .day-input:hover {
      border-color: #667eea;
    }

    /* Field inputs for rate_per_day and deduction */
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
      background: rgba(255, 193, 7, 0.1);
      border-color: #ffc107;
      animation: pulse 1s infinite;
    }
    .field-input.saved {
      background: rgba(40, 167, 69, 0.1);
      border-color: #28a745;
    }
    .field-input.error {
      background: rgba(220, 53, 69, 0.1);
      border-color: #dc3545;
    }

    /* Save button styling */
    .save-btn {
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
      background: linear-gradient(135deg, #28a745, #218838);
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }
    .save-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    }
    .save-btn.saving {
      background: linear-gradient(135deg, #ffc107, #e0a800);
      animation: pulse 1s infinite;
    }
    .save-btn.saved {
      background: linear-gradient(135deg, #28a745, #218838);
    }
    .save-btn.error {
      background: linear-gradient(135deg, #dc3545, #c82333);
    }

    /* Pulse animation */
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }

    /* Empty state styling */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 15px;
      margin-top: 20px;
      border: 2px dashed #e0e0e0;
    }

    .empty-state-content i {
      font-size: 64px;
      color: #6c757d;
      margin-bottom: 20px;
      opacity: 0.5;
    }

    .empty-state-content h4 {
      color: #495057;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .empty-state-content p {
      color: #6c757d;
      font-size: 16px;
      margin: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .main-content {
        margin: 15px;
        padding: 15px;
        width: calc(100% - 30px);
      }

      .top-actions {
        flex-direction: column;
        gap: 15px;
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
      
      .icon-btn, .float-end, .action-btn {
        display: none !important;
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
      
      .print-header {
        text-align: center;
        margin-bottom: 20px;
      }
      
      .print-footer {
        margin-top: 30px;
        text-align: center;
        font-size: 10px;
        color: #666;
      }
      
      /* Hide actions column in print */
      .actions-column {
        display: none !important;
      }

      /* Sunday/Holiday Highlighting for Print */
      .absent-day {
        background-color: #e9ecef !important; /* Light gray for print */
        color: #333 !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
      }

      .absent-day input {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
        pointer-events: none;
      }
    }

    /* Sunday/Holiday Styling (para maging dark gray) */
    .absent-day {
      background-color: #555555 !important;
    }
    
    .disabled-day-input {
      background-color: #666666 !important;
      border-color: #777777 !important;
      color: #ffffff !important;
      cursor: not-allowed;
      font-weight: bold;
    }
    
    .disabled-day-input:hover, .disabled-day-input:focus {
      background-color: #666666 !important;
      border-color: #777777 !important;
      box-shadow: none !important;
    }
  </style>
</head>
<body>

  <div class="main-content">
    <!-- Print Header (hidden on screen, visible on print) -->
    <div class="print-header" style="display: none;">
      <h1>MCC PAYROLL SYSTEM</h1>
      <h2>Utility Timesheet</h2>
      <p>Generated on: <span id="print-date"></span></p>
      <hr>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center">
        <a href="{{ route('dashboard') }}" class="icon-btn btn-back me-3" title="Back to Dashboard">
          <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0">Utility Timesheet</h2>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button onclick="openPrintPage()" class="icon-btn print-btn" title="Print">
          <i class="bi bi-printer"></i>
        </button>
        
        <a href="{{ route('utility.create') }}" class="icon-btn add-btn" title="Add Entry">
          <i class="bi bi-plus-lg"></i>
        </a>
      </div>
    </div>
        <!-- Month/Year/Period Selector -->
    <div class="d-flex justify-content-center mb-4">
      <form method="GET" action="{{ route('utility.index') }}" class="d-flex align-items-center gap-3">
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
      </form>
      <form action="{{ route('holidays.index') }}" method="GET" class="d-flex align-items-center ms-3">
        <button type="submit" class="btn btn-warning" title="Manage Holidays">
            <i class="bi bi-calendar-event me-1"></i> Holiday</button>
      </form>
    </div>


    <div class="table-container mt-3">
      <table>
        <thead>
          <tr>
            <th>NAMES</th>
            <th>DESIGNATION</th>
            <th>Prev.Abs.</th>
            <!-- <th>DEPARTMENT</th> -->
            @foreach($days as $day)
              @php
                $isSunday = (\Carbon\Carbon::parse($day['date'])->dayOfWeekIso == 7);
                $isHoliday = in_array($day['date'], $holidays ?? []);
                $headerClass = $isSunday || $isHoliday ? 'absent-day' : 'day-header';
              @endphp
              <th class="{{ $headerClass }}">
                <span class="day-number">{{ $day['number'] }}</span><br>
                <span class="day-abbr">{{ $day['abbr'] }}</span>
              </th>
            @endforeach
            <th>Details</th>
            <th>TOTAL<br>Day</th>
            <th>Rate per<br>Day</th>
            <th>Deduction<br>Previous Cut Off</th>
            <th>TOTAL HONORARIUM</th>
            <th class="actions-column">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($timesheets as $timesheet)
          <tr id="timesheet-row-{{ $timesheet->id }}">
            <td class="left">{{ $timesheet->employee_name }}</td>
            <td>{{ ucfirst($timesheet->designation) }}</td>
            <td>
              <input type="number" {{-- BINAGO: Ginawang field-input para sa auto-save --}}
                     class="form-control field-input"
                     value="{{ $timesheet->prov_abr ?? 0.00 }}"
                     data-timesheet-id="{{ $timesheet->id }}"
                     data-field="prov_abr"
                     placeholder="0.00"
                     min="0"
                     step="0.01">
            </td>
            <!-- <td>{{ $timesheet->department ?? '' }}</td> -->
            @foreach($days as $day)
              @php
                  $dayDate = \Carbon\Carbon::parse($day['date']);
                  $weekday = $dayDate->format('D'); // Mon, Tue, etc.
                  $weekdayMap = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];
                  $columnField = $weekdayMap[$weekday] ?? null;
                  
                  // Kunin ang value mula sa tamang column (e.g., $timesheet->mon_hours)
                  $dailyValue = $columnField ? ($timesheet->$columnField ?? 0) : 0;

                  $isSunday = ($dayDate->dayOfWeekIso == 7);
                  $isHoliday = in_array($day['date'], $holidays ?? []);
                  $isDisabled = $isSunday || $isHoliday;
                  $cellClass = 'day-column ' . ($isDisabled ? 'absent-day' : ''); // AYOS: Tiniyak na laging may 'day-column' class
              @endphp
              <td class="{{ $cellClass }}">
                <input type="number"
                       class="form-control field-input day-input {{ $isDisabled ? 'disabled-day-input' : '' }}"
                       value="{{ $isDisabled ? 0 : $dailyValue }}"
                       data-timesheet-id="{{ $timesheet->id }}"
                       data-field="{{ $columnField }}" {{-- Ang field ay mon_hours, tue_hours, etc. --}}
                       placeholder="0" {{-- TINANGGAL: data-weekday at data-date --}}
                       min="0"
                       step="1"
                       {{ $isDisabled ? 'readonly' : '' }}>
              </td>
            @endforeach
            <td>
              <input type="text"
                     class="form-control field-input"
                     value="{{ $timesheet->details ?? '' }}"
                     data-timesheet-id="{{ $timesheet->id }}"
                     data-field="details"
                     placeholder="Details">
            </td>
            <td id="total-days-{{ $timesheet->id }}">{{ $timesheet->total_days ?? 0 }}</td>
            <td>
              <input type="number" 
                     class="form-control field-input" 
                     value="{{ $timesheet->rate_per_day ?? 0.00 }}" 
                     data-timesheet-id="{{ $timesheet->id }}" 
                     data-field="rate_per_day"
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
              <div class="actions-container">
                <a href="{{ route('utility.edit', $timesheet->id) }}" 
                   class="action-btn btn-edit" 
                   title="Edit">
                  <i class="bi bi-pencil-square"></i>
                </a>
                {{-- AUTO-SAVE STATUS ICON --}}
                <span id="save-status-{{ $timesheet->id }}" 
                      class="action-btn btn-auto-status" 
                      title="Auto-Save Status">
                  <i class="bi bi-grip-horizontal"></i>
                </span>
                <form action="{{ route('utility.destroy', $timesheet->id) }}" method="POST" class="d-inline delete-form">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="action-btn btn-delete" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="23" class="text-center py-5">
              <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d; margin-bottom: 1rem;"></i>
                <h5 class="text-muted">No Timesheet Records Found</h5>
                <p class="text-muted mb-3">There are no utility timesheet entries to display.</p>
                <a href="{{ route('utility.create') }}" class="btn btn-primary">
                  <i class="bi bi-plus-lg me-2"></i>Add First Timesheet
                </a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>

      @if($timesheets->isEmpty())
      <div class="empty-state">
        <div class="empty-state-content">
          <i class="bi bi-calendar-x"></i>
          <h4>No Timesheets Found</h4>
          <p>There are no utility timesheets to display. Click the <strong>+</strong> button to add a new entry.</p>
        </div>
      </div>
      @endif
    </div>
  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    // Global variable to hold debounce timers
    const saveTimers = {};

    /**
     * Debounce function: Delays the execution of a function.
     */
    function debounce(func, delay, timesheetId) {
        if (saveTimers[timesheetId]) {
            clearTimeout(saveTimers[timesheetId]);
        }
        saveTimers[timesheetId] = setTimeout(func, delay);
    }

    document.addEventListener('DOMContentLoaded', () => {
      // Function to save all data for a row
      function saveAllData(timesheetId, row) {
        const statusIcon = document.getElementById(`save-status-${timesheetId}`);
        if (!statusIcon) return;

        // Set saving state
        statusIcon.classList.remove('saved', 'error');
        statusIcon.classList.add('saving');
        statusIcon.innerHTML = '<i class="bi bi-hourglass-split"></i>';

        // Collect all data from the row
        const allData = {};
        row.querySelectorAll('.field-input').forEach(input => {
            const field = input.dataset.field;
            if (field) {
                // For utility, a day input is either 1 (present) or 0 (absent)
                // We ensure it's a valid number.
                allData[field] = (input.value > 0) ? 1 : 0;
            }
        });

        const promises = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Send multiple AJAX requests (one for each field)
        Object.keys(allData).forEach(field => {
            const promise = fetch(`/utility/${timesheetId}/update-field`, {
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

        // Handle responses
        Promise.all(promises)
            .then(responses => {
                // Check if all responses are OK
                const allSuccessful = responses.every(r => r.ok);
                if (!allSuccessful) {
                    // Find the first failed response to get an error message
                    const failedResponse = responses.find(r => !r.ok);
                    return failedResponse.json().then(err => Promise.reject(err));
                }
                // Get the JSON from the last successful response to update totals
                return responses[responses.length - 1].json();
            })
            .then(finalResult => {
                // Success state
                statusIcon.classList.remove('saving');
                statusIcon.classList.add('saved');
                statusIcon.innerHTML = '<i class="bi bi-check-lg"></i>';

                // Update totals from the server's response
                if (finalResult.success) {
                    const totalDaysCell = document.getElementById(`total-days-${timesheetId}`);
                    const totalHonorariumCell = document.getElementById(`total-honorarium-${timesheetId}`);
                    if (totalDaysCell && finalResult.total_days !== undefined) {
                        totalDaysCell.textContent = finalResult.total_days;
                    }
                    if (totalHonorariumCell && finalResult.total_honorarium !== undefined) {
                        totalHonorariumCell.textContent = `₱${parseFloat(finalResult.total_honorarium).toFixed(2)}`;
                    }
                }

                // Revert status icon after 3 seconds
                setTimeout(() => {
                    statusIcon.classList.remove('saved');
                    statusIcon.innerHTML = '<i class="bi bi-grip-horizontal"></i>';
                }, 3000);
            })
            .catch(error => {
                console.error('Autosave Error:', error);
                // Error state
                statusIcon.classList.remove('saving', 'saved');
                statusIcon.classList.add('error');
                statusIcon.innerHTML = '<i class="bi bi-x-lg"></i>';

                // Revert status icon after 5 seconds
                setTimeout(() => {
                    statusIcon.classList.remove('error');
                    statusIcon.innerHTML = '<i class="bi bi-grip-horizontal"></i>';
                }, 5000);
            });
      }

      // Attach event listeners for AUTOSAVE
      document.querySelectorAll('.field-input').forEach(input => {
        input.addEventListener('input', function() {
          const row = this.closest('tr');
          const statusElement = row.querySelector('.btn-auto-status');
          if (!statusElement) return;
          
          const timesheetId = statusElement.id.replace('save-status-', '');
          
          // Debounce the save function (wait for 1.5 seconds after last input)
          debounce(() => {
            saveAllData(timesheetId, row);
          }, 1500, timesheetId); 
        });
      });

      // SweetAlert for Delete Confirmation
      document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
          event.preventDefault(); // Ito ang naghi-hide sa default function ng delete

          Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            customClass: {
              popup: 'swal-custom-popup',
              title: 'swal-custom-title',
              content: 'swal-custom-content',
              confirmButton: 'swal-custom-button',
              cancelButton: 'swal-custom-cancel-button'
            }
          }).then((result) => {
            if (result.isConfirmed) {
              // Kung kinumpirma, isusumite na ang form
              this.submit();
            }
          });
        });
      });
    });

    function saveAllData_old(timesheetId, fieldData, dayData, buttonElement) {
      const token = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = token ? token.getAttribute('content') : '';

      const promises = [];

      // Create promises for each field update
      Object.keys(fieldData).forEach(field => {
        const promise = fetch(`/utility/${timesheetId}/update-field`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            field: field,
            value: fieldData[field]
          })
        });
        promises.push(promise);
      });

      // Create promises for each day update
      Object.keys(dayData).forEach(day => {
        const promise = fetch(`/utility/${timesheetId}/update-day`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            day: day,
            hours: dayData[day] // For utility, it's days (0/1), but using hours key for consistency
          })
        });
        promises.push(promise);
      });

      // Wait for all updates to complete
      Promise.all(promises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(results => {
          // Check if all updates were successful
          const allSuccessful = results.every(result => result.success);

          if (allSuccessful) {
            // Update totals from results
            results.forEach(result => {
              // Update total honorarium if it's from a field update
              if (result.total_honorarium) {
                const totalHonorariumElement = document.getElementById(`total-honorarium-${timesheetId}`);
                if (totalHonorariumElement) {
                  totalHonorariumElement.textContent = '₱' + parseFloat(result.total_honorarium).toFixed(2);
                }
              }
            });

            // Reset button to saved state
            buttonElement.classList.remove('saving');
            buttonElement.classList.add('saved');
            buttonElement.innerHTML = '<i class="bi bi-check-circle"></i>';

            // Reset to normal after 2 seconds
            setTimeout(() => {
              buttonElement.classList.remove('saved');
              buttonElement.innerHTML = '<i class="bi bi-check-lg"></i>';
            }, 2000);

            // Show success message
            Swal.fire({
              icon: 'success',
              title: 'Saved!',
              text: 'Timesheet updated successfully.',
              timer: 2000,
              showConfirmButton: false,
              customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                content: 'swal-custom-content'
              }
            });
          } else {
            // Handle errors
            buttonElement.classList.remove('saving');
            buttonElement.classList.add('error');
            buttonElement.innerHTML = '<i class="bi bi-x-circle"></i>';

            setTimeout(() => {
              buttonElement.classList.remove('error');
              buttonElement.innerHTML = '<i class="bi bi-check-lg"></i>';
            }, 2000);

            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Some updates failed. Please try again.',
              customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                content: 'swal-custom-content',
                confirmButton: 'swal-custom-button'
              }
            });
          }
        })
        .catch(error => {
          console.error('Save error:', error);
          buttonElement.classList.remove('saving');
          buttonElement.classList.add('error');
          buttonElement.innerHTML = '<i class="bi bi-x-circle"></i>';

          setTimeout(() => {
            buttonElement.classList.remove('error');
            buttonElement.innerHTML = '<i class="bi bi-check-lg"></i>';
          }, 2000);

          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while saving.',
            customClass: {
              popup: 'swal-custom-popup',
              title: 'swal-custom-title',
              content: 'swal-custom-content',
              confirmButton: 'swal-custom-button'
            }
          });
        });
    }

    // Check for success message from Laravel session
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#667eea', // Match theme
        timer: 3000,
        timerProgressBar: true,
        customClass: {
          popup: 'swal-custom-popup',
          title: 'swal-custom-title',
          content: 'swal-custom-content',
          confirmButton: 'swal-custom-button'
        }
      });
    @endif

    // Check for error messages
    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545',
        customClass: {
          popup: 'swal-custom-popup',
          title: 'swal-custom-title',
          content: 'swal-custom-content',
          confirmButton: 'swal-custom-button'
        }
      });
    @endif

    // Function to open the print page with SweetAlert confirmation
    function openPrintPage() {
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        const period = document.getElementById('period').value;

        // Construct the URL for the print route with query parameters
        const printUrl = `{{ route('utility.print') }}?month=${month}&year=${year}&period=${period}`; // Use backticks for template literals

        Swal.fire({
            title: 'Confirm Print',
            text: "Are you sure you want to print the timesheet for the selected period?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3498db', // Blue, adjust to match your theme if different
            cancelButtonColor: '#6c757d', // Grey
            confirmButtonText: 'Yes, Print!',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                content: 'swal-custom-content',
                confirmButton: 'swal-custom-button',
                cancelButton: 'swal-custom-cancel-button'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // User confirmed, open print page in a new tab
                window.open(printUrl, '_blank');
            }
        });
    }
  </script>

  <style>
    /* Custom SweetAlert2 styling */
    .swal-custom-popup {
      border-radius: 15px !important;
      border: 2px solid #667eea !important;
      box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3) !important;
    }
    
    .swal-custom-title {
      color: #667eea !important;
      font-weight: 700 !important;
      font-size: 1.5rem !important;
    }
    
    .swal-custom-content {
      color: #2c3e50 !important;
      font-size: 1rem !important;
      font-weight: 500 !important;
    }
    
    .swal-custom-button {
      background: linear-gradient(135deg, #667eea, #764ba2) !important;
      border: none !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
      padding: 12px 30px !important;
      font-size: 0.95rem !important;
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3) !important;
      transition: all 0.3s ease !important;
    }
    
    .swal-custom-button:hover {
      background: linear-gradient(135deg, #5a67d8, #6b4fa2) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4) !important;
    }

    .swal-print-button {
      background: linear-gradient(135deg, #0dcaf0, #0aa2c0) !important;
    }
    
    .swal-print-button:hover {
      background: linear-gradient(135deg, #0aa2c0, #088395) !important;
    }

    .swal-custom-cancel-button {
      background: #6c757d !important;
    }
    
    .swal-custom-cancel-button:hover {
      background: #5a6268 !important;
    }
  </style>

  </div>
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
