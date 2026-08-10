<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Watchman Timesheet — MCC Payroll</title>
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
      --bg-app: #f4f6fa;
      --bg-card: #ffffff;

      --text-main: #0f172a;
      --text-secondary: #475569;
      --text-muted: #94a3b8;

      --accent: #2563eb;
      --accent-hover: #1d4ed8;
      --accent-soft: rgba(37, 99, 235, 0.06);
      --accent-glow: rgba(37, 99, 235, 0.15);

      --border-color: #e2e8f0;
      --border-focus: #2563eb;

      --sunday-bg: #f8fafc;
      --sunday-text: #94a3b8;
      --holiday-bg: #fff1f2;
      --holiday-text: #f43f5e;

      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-pill: 999px;

      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.05);
      --shadow-lg: 0 12px 36px rgba(0, 0, 0, 0.08);

      --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    body {
      font-family: var(--font);
      background-color: var(--bg-app);
      color: var(--text-main);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ===================== LAYOUT CONTAINER ===================== */
    .container-fluid {
      padding: 24px clamp(16px, 4vw, 40px);
      max-width: 1600px;
      margin: 0 auto;
      width: 100%;
    }

    /* ===================== CARD WRAPPER ===================== */
    .timesheet-card {
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-md);
      padding: 32px;
      position: relative;
      overflow: hidden;
    }

    .timesheet-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--accent);
    }

    /* ===================== HEADER SECTION ===================== */
    .header-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 28px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--border-color);
      flex-wrap: wrap;
      gap: 16px;
    }

    .header-title-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-back {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: var(--radius-pill);
      color: var(--text-secondary);
      background: var(--bg-app);
      border: 1px solid var(--border-color);
      transition: all 0.2s ease;
    }
    .btn-back:hover {
      color: var(--accent);
      background: var(--accent-soft);
      border-color: var(--accent-glow);
    }

    .header-section h2 {
      font-size: 1.35rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: var(--text-main);
      margin: 0;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn-action-primary {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.84rem;
      font-weight: 600;
      color: #ffffff;
      background: var(--accent);
      border: none;
      padding: 8px 16px;
      border-radius: var(--radius-sm);
      transition: all 0.2s ease;
      text-decoration: none;
      box-shadow: 0 1px 3px rgba(37, 99, 235, 0.15);
    }
    .btn-action-primary:hover {
      background: var(--accent-hover);
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
      color: #fff;
    }

    .btn-action-secondary {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.84rem;
      font-weight: 600;
      color: var(--text-secondary);
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      padding: 8px 16px;
      border-radius: var(--radius-sm);
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .btn-action-secondary:hover {
      background: var(--bg-app);
      color: var(--text-main);
      border-color: var(--text-secondary);
    }

    /* ===================== CONTROLS BAR ===================== */
    .controls-bar {
      background: var(--bg-app);
      border-radius: var(--radius-md);
      padding: 16px 20px;
      margin-bottom: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      border: 1px solid var(--border-color);
    }

    .filter-form {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .filter-group {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .filter-group label {
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--text-secondary);
    }

    .filter-select {
      font-size: 0.84rem;
      font-weight: 500;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 6px 12px;
      background-color: var(--bg-card);
      outline: none;
      transition: border-color 0.2s ease;
      cursor: pointer;
    }
    .filter-select:focus {
      border-color: var(--border-focus);
    }

    .btn-update {
      font-size: 0.84rem;
      font-weight: 600;
      padding: 6px 16px;
      border-radius: var(--radius-sm);
    }

    .btn-holiday-manage {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.84rem;
      font-weight: 600;
      color: #92400e;
      background: #fffbeb;
      border: 1px solid #fde68a;
      padding: 6px 16px;
      border-radius: var(--radius-sm);
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .btn-holiday-manage:hover {
      background: #fef3c7;
      border-color: #fcd34d;
    }

    /* ===================== TABLE CONTAINER ===================== */
    .table-responsive-container {
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      overflow-x: auto;
      background: var(--bg-card);
      box-shadow: var(--shadow-sm);
    }

    table.timesheet-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      font-size: 0.76rem;
    }

    table.timesheet-table thead {
      background: var(--bg-app);
    }

    table.timesheet-table th {
      padding: 10px 6px;
      font-weight: 700;
      color: var(--text-secondary);
      text-transform: uppercase;
      font-size: 0.65rem;
      letter-spacing: 0.03em;
      border-right: 1px solid var(--border-color);
      border-bottom: 2px solid var(--border-color);
      text-align: center;
    }

    table.timesheet-table th:last-child {
      border-right: none;
    }

    table.timesheet-table tbody td {
      padding: 5px 6px;
      border-bottom: 1px solid var(--border-color);
      border-right: 1px solid var(--border-color);
      vertical-align: middle;
      text-align: center;
      color: var(--text-main);
      background-color: var(--bg-card);
    }

    table.timesheet-table tbody td:last-child {
      border-right: none;
    }

    table.timesheet-table tbody tr {
      transition: background-color 0.15s ease;
    }

    table.timesheet-table tbody tr:hover td {
      background-color: var(--sunday-bg) !important;
    }

    /* Columns custom widths & layouts */
    .col-name-sticky {
      position: sticky;
      left: 0;
      z-index: 10;
      background-color: var(--bg-card) !important;
      border-right: 2px solid var(--border-color) !important;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.04);
      min-width: 140px;
      max-width: 160px;
      text-align: left !important;
      font-weight: 600;
    }
    table.timesheet-table thead th.col-name-sticky {
      z-index: 11;
      background-color: var(--bg-app) !important;
    }

    .col-details {
      min-width: 100px;
    }

    /* Day headers styling */
    .day-header {
      min-width: 38px;
      width: 38px;
      padding: 4px 2px !important;
    }
    .day-column {
      min-width: 38px;
      width: 38px;
      padding: 4px 2px !important;
    }
    .day-number {
      font-size: 0.85rem;
      font-weight: 800;
      display: block;
      line-height: 1.1;
    }
    .day-abbr {
      font-size: 0.6rem;
      font-weight: 600;
      color: var(--text-secondary);
    }

    /* Sunday and Holiday classes */
    .sunday-column { background-color: var(--sunday-bg) !important; }
    .sunday-input {
      background-color: #f1f5f9 !important;
      color: var(--sunday-text) !important;
      cursor: not-allowed;
      font-weight: 600;
    }

    .holiday-column { background-color: var(--holiday-bg) !important; }
    .holiday-input {
      background-color: #ffe4e6 !important;
      color: var(--holiday-text) !important;
      border-color: #fecdd3 !important;
      cursor: not-allowed;
      font-weight: 700;
    }

    /* Inputs in table */
    .table-input {
      width: 100%;
      height: 26px;
      font-size: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      text-align: center;
      padding: 2px 2px;
      transition: all 0.2s ease;
      background-color: var(--bg-card);
      outline: none;
    }
    .table-input:focus {
      border-color: var(--border-focus);
      box-shadow: 0 0 0 2px var(--accent-glow);
      background-color: #fff;
    }

    .field-input-details {
      text-align: left;
      font-size: 0.78rem;
    }

    /* Auto-save status states (inputs) */
    .table-input.saving { background-color: #fef3c7; border-color: #fcd34d; }
    .table-input.saved { background-color: #ecfdf5; border-color: #a7f3d0; }
    .table-input.error { background-color: #fef2f2; border-color: #fecaca; }

    /* ===================== ACTION BUTTONS ===================== */
    .action-btn-group {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .btn-circle {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 1px solid var(--border-color);
      background: var(--bg-card);
      color: var(--text-secondary);
      font-size: 0.8rem;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .btn-circle:hover {
      color: var(--accent);
      background: var(--accent-soft);
      border-color: var(--accent-glow);
    }

    .btn-circle-delete {
      color: var(--holiday-text);
      border-color: rgba(244, 63, 94, 0.2);
    }
    .btn-circle-delete:hover {
      color: #ffffff;
      background: var(--holiday-text);
      border-color: var(--holiday-text);
    }

    /* Auto save status indicator */
    .save-status-indicator {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--bg-app);
      color: var(--text-secondary);
      border: 1px solid var(--border-color);
      font-size: 0.85rem;
      transition: all 0.3s ease;
    }

    .save-status-indicator.saving {
      background-color: #fffbeb !important;
      border-color: #fde68a !important;
      color: #d97706 !important;
      animation: pulsate 1.5s infinite;
    }

    .save-status-indicator.saved {
      background-color: #f0fdf4 !important;
      border-color: #bbf7d0 !important;
      color: #16a34a !important;
    }

    .save-status-indicator.error {
      background-color: #fef2f2 !important;
      border-color: #fecaca !important;
      color: #dc2626 !important;
    }

    @keyframes pulsate {
      0% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.08); opacity: 0.8; }
      100% { transform: scale(1); opacity: 1; }
    }

    /* ===================== EMPTY STATE ===================== */
    .empty-wrapper {
      padding: 48px 24px;
      text-align: center;
    }
    .empty-wrapper svg {
      width: 48px;
      height: 48px;
      color: var(--text-muted);
      margin-bottom: 16px;
    }
    .empty-wrapper h5 {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--text-secondary);
      margin-bottom: 6px;
    }
    .empty-wrapper p {
      font-size: 0.82rem;
      color: var(--text-muted);
      margin-bottom: 16px;
    }

    /* ===================== PRINT STYLING ===================== */
    @media print {
      body {
        background: #ffffff !important;
        color: #000000 !important;
      }
      .timesheet-card {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
      }
      .timesheet-card::before {
        display: none !important;
      }
      .header-section, .controls-bar, .actions-column, .action-btn-group, .save-status-indicator {
        display: none !important;
      }
      .table-responsive-container {
        border: none !important;
      }
      table.timesheet-table {
        border: 1px solid #000000 !important;
      }
      table.timesheet-table th, table.timesheet-table td {
        border: 1px solid #000000 !important;
        color: #000000 !important;
        padding: 6px 4px !important;
        font-size: 8px !important;
      }
      .table-input {
        border: none !important;
        background: transparent !important;
        font-weight: 700 !important;
        color: #000000 !important;
        padding: 0 !important;
        height: auto !important;
      }
      .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
      }
      .print-footer {
        display: block !important;
        text-align: center;
        margin-top: 20px;
        font-size: 8px;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="timesheet-card">

      <!-- ===================== PRINT HEADER ===================== -->
      <div class="print-header d-none">
        <h4 class="fw-bold mb-1">MCC PAYROLL SYSTEM</h4>
        <h5 class="text-secondary mb-2">Watchman Timesheet</h5>
        <p class="small text-muted mb-0">Generated on: <span id="print-date"></span></p>
        <hr class="my-3">
      </div>

      <!-- ===================== HEADER ===================== -->
      <div class="header-section">
        <div class="header-title-group">
          <a href="{{ route('dashboard') }}" class="btn-back" title="Back to Dashboard">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          </a>
          <h2>Watchman Timesheet</h2>
        </div>
        <div class="header-actions">
          <button onclick="openPrintPage()" class="btn-action-secondary" title="Print Timesheet">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
          </button>
          <a href="{{ route('watchman.create') }}" class="btn-action-primary">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Entry
          </a>
        </div>
      </div>

      <!-- ===================== CONTROLS BAR ===================== -->
      <div class="controls-bar">
        <form method="GET" action="{{ route('watchman.index') }}" class="filter-form">
          <div class="filter-group">
            <label for="month">Month</label>
            <select name="month" id="month" class="filter-select">
              @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                  {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
              @endfor
            </select>
          </div>

          <div class="filter-group">
            <label for="period">Period</label>
            <select name="period" id="period" class="filter-select">
              <option value="auto" {{ $period == 'auto' ? 'selected' : '' }}>Auto</option>
              <option value="1-15" {{ $period == '1-15' ? 'selected' : '' }}>1-15</option>
              <option value="16-end" {{ $period == '16-end' ? 'selected' : '' }}>16-End</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="year">Year</label>
            <select name="year" id="year" class="filter-select">
              @php
                $currentYear = now()->year;
              @endphp
              @for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
              @endfor
            </select>
          </div>

          <button type="submit" class="btn btn-primary btn-update">Update</button>
        </form>

        <a href="{{ route('holidays.index') }}" class="btn-holiday-manage" title="View/Manage Holidays">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Manage Holidays
        </a>
      </div>

      <!-- ===================== TABLE ===================== -->
      <div class="table-responsive-container">
        <table class="timesheet-table">
          <thead>
            <tr>
              <th class="col-name-sticky">Names</th>
              <th style="min-width: 100px;">Designation</th>
              <th style="min-width: 70px;">Prev. Abs.</th>

              <th style="min-width: 130px; text-align: center;">Daily Hours</th>

              <th style="min-width: 140px;">Details (Inclusive Hours)</th>
              <th style="min-width: 60px;">Total Days</th>
              <th style="min-width: 80px;">Rate / Day</th>
              <th style="min-width: 80px;">Deduction</th>
              <th style="min-width: 120px;">Total Honorarium</th>
              <th class="actions-column" style="min-width: 110px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($timesheets as $timesheet)
            <tr>
              <td class="col-name-sticky">{{ $timesheet->employee_name }}</td>
              <td class="text-secondary" style="font-weight: 500;">{{ ucfirst($timesheet->designation) }}</td>
              <td>
                <input type="number"
                       class="table-input field-input"
                       value="{{ $timesheet->prev_abs ?? 0 }}"
                       data-timesheet-id="{{ $timesheet->id }}"
                       data-field="prev_abs"
                       min="0"
                       step="1"
                       placeholder="0">
              </td>

              <td class="text-center">
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="font-size: 0.8rem; font-weight: 600;">
                    <i class="bi bi-calendar-range me-1"></i> View / Edit
                  </button>
                  <div class="dropdown-menu p-3 shadow" style="min-width: 280px; max-height: 400px; overflow-y: auto; z-index: 1050;">
                    <h6 class="dropdown-header px-0 mb-2 border-bottom pb-2 text-dark fw-bold">Enter Daily Hours</h6>
                    <div class="row g-2">
                      @foreach($days as $day)
                        @php
                          $currentDate = $day['date'];
                          $dateObj = \Carbon\Carbon::parse($currentDate);
                          $weekday = $dateObj->format('D');

                          $isHoliday = in_array($currentDate, $holidays ?? []);
                          $isSunday = ($weekday === 'Sun');
                          $isDisabled = $isSunday || $isHoliday;

                          $weekdayMap = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];
                          $field = $weekdayMap[$weekday] ?? 'sun_hours';

                          $value = $timesheet->$field ?? 0;
                          if ($isDisabled) {
                              $value = 0;
                          }
                          
                          $labelStyle = $isHoliday ? 'color: var(--holiday-text); font-weight: bold;' : ($weekday === 'Sun' ? 'color: var(--danger);' : '');
                        @endphp
                        
                        <div class="col-6">
                          <div class="input-group input-group-sm mb-1" title="{{ $dateObj->format('M d, Y') }}">
                            <span class="input-group-text" style="width: 55px; justify-content: center; {{ $labelStyle }}">
                              {{ $day['number'] }}<small class="ms-1" style="font-size: 0.65rem;">{{ $day['abbr'] }}</small>
                            </span>
                            <input type="number"
                                   class="form-control table-input day-input text-center"
                                   value="{{ $value }}"
                                   data-timesheet-id="{{ $timesheet->id }}"
                                   data-field="{{ $field }}"
                                   placeholder="{{ $isDisabled ? 'N/A' : '0' }}"
                                   min="0"
                                   step="0.5"
                                   data-day-date="{{ $day['date'] }}"
                                   style="height: 31px; border: 1px solid #ced4da; background-color: {{ $isDisabled ? '#e9ecef' : '#fff' }};"
                                   {{ $isDisabled ? 'readonly' : '' }}
                                   data-is-holiday="{{ $isHoliday ? 'true' : 'false' }}">
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </td>

              <td class="col-details">
                <input type="text"
                       class="table-input field-input field-input-details"
                       value="{{ $timesheet->details }}"
                       data-timesheet-id="{{ $timesheet->id }}"
                       data-field="details"
                       placeholder="Details">
              </td>
              <td id="total-days-{{ $timesheet->id }}" class="fw-bold">{{ number_format($timesheet->total_days ?? 0, 0) }}</td>
              <td>
                <input type="number"
                       class="table-input field-input text-end"
                       value="{{ $timesheet->rate_per_day ?? 0 }}"
                       data-timesheet-id="{{ $timesheet->id }}"
                       data-field="rate_per_day"
                       min="0"
                       step="0.01"
                       placeholder="0.00">
              </td>
              <td>
                <input type="number"
                       class="table-input field-input text-end"
                       value="{{ $timesheet->deduction }}"
                       data-timesheet-id="{{ $timesheet->id }}"
                       data-field="deduction"
                       min="0"
                       step="0.01"
                       placeholder="0.00">
              </td>
              <td id="total-honorarium-{{ $timesheet->id }}" class="fw-bold text-success">₱{{ number_format($timesheet->total_honorarium ?? 0, 2) }}</td>
              <td class="actions-column">
                <div class="action-btn-group">
                  <a href="{{ route('watchman.edit', $timesheet->id) }}"
                     class="btn-circle"
                     title="Edit">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  </a>

                  <span id="save-status-{{ $timesheet->id }}" class="save-status-indicator" title="Auto-Save Status">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                  </span>

                  <form action="{{ route('watchman.destroy', $timesheet->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-circle btn-circle-delete btn-delete" title="Delete">
                      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="100" class="text-center py-5">
                <div class="empty-wrapper">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
                  <h5>No Timesheet Records Found</h5>
                  <p class="mb-3">There are no watchman timesheet entries to display.</p>
                  <a href="{{ route('watchman.create') }}" class="btn btn-primary btn-action-primary">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add First Timesheet
                  </a>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- ===================== PRINT FOOTER ===================== -->
      <div class="print-footer d-none">
        <p>Generated by MCC Payroll System - {{ date('Y-m-d H:i:s') }}</p>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Global variable to hold debounce timers
    const saveTimers = {};

    function debounce(func, delay, timesheetId) {
      if (saveTimers[timesheetId]) {
        clearTimeout(saveTimers[timesheetId]);
      }
      saveTimers[timesheetId] = setTimeout(func, delay);
    }

    // Open dedicated print page
    function openPrintPage() {
      Swal.fire({
        title: 'Open Print Page',
        text: 'This will open a dedicated print-optimized page in a new tab.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Open Print Page',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          const month = document.getElementById('month').value;
          const year = document.getElementById('year').value;
          const period = document.getElementById('period').value;
          const printUrl = `{{ route('watchman.print') }}?month=${month}&year=${year}&period=${period}`;
          window.open(printUrl, '_blank');
        }
      });
    }

    // Laravel session alerts
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#2563eb',
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#dc2626',
      });
    @endif

    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const form = this.closest('form');
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc2626',
          cancelButtonColor: '#64748b',
          confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({ title: 'Deleting...', icon: 'info', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); } });
            form.submit();
          }
        });
      });
    });

    // Calculations & Autosave (watchman rules: total days is a day COUNT, not an hours
    // sum — any value > 0 on a workday counts as 1 day; deduction is a direct
    // currency amount subtracted from the gross honorarium)
    document.addEventListener('DOMContentLoaded', function() {

      function calculateTotals(row) {
        let grossDays = 0;
        row.querySelectorAll('.day-input').forEach(input => {
          if (input.dataset.isHoliday === 'true' || input.classList.contains('sunday-input')) {
            return;
          }
          if (parseFloat(input.value) > 0) {
            grossDays += 1;
          }
        });

        const prevAbsInput = row.querySelector('[data-field="prev_abs"]');
        const ratePerDayInput = row.querySelector('[data-field="rate_per_day"]');
        const deductionInput = row.querySelector('[data-field="deduction"]');

        const prevAbsDays = parseFloat(prevAbsInput ? prevAbsInput.value : 0) || 0;
        const ratePerDay = parseFloat(ratePerDayInput ? ratePerDayInput.value : 0) || 0;
        const deductionValue = parseFloat(deductionInput ? deductionInput.value : 0) || 0;

        let finalPayableDays = grossDays - prevAbsDays;
        if (finalPayableDays < 0) finalPayableDays = 0;

        const grossHonorarium = finalPayableDays * ratePerDay;
        const totalHonorarium = grossHonorarium - deductionValue;

        const timesheetId = row.querySelector('.save-status-indicator').id.replace('save-status-', '');
        const totalDaysEl = document.getElementById(`total-days-${timesheetId}`);
        const totalHonorariumEl = document.getElementById(`total-honorarium-${timesheetId}`);

        if (totalDaysEl) totalDaysEl.textContent = finalPayableDays.toFixed(0);
        if (totalHonorariumEl) totalHonorariumEl.textContent = `₱${totalHonorarium.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

        return { finalPayableDays: finalPayableDays.toFixed(0), totalHonorarium: totalHonorarium.toFixed(2) };
      }

      function saveAllData(timesheetId, row) {
        const statusIcon = document.getElementById(`save-status-${timesheetId}`);
        const fieldInputs = row.querySelectorAll('.field-input');
        const dayInputs = row.querySelectorAll('.day-input');

        const { finalPayableDays, totalHonorarium } = calculateTotals(row);

        statusIcon.className = 'save-status-indicator saving';
        statusIcon.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="10"/></svg>`;

        const fieldData = {};
        fieldInputs.forEach(input => {
          const field = input.dataset.field;
          if (field) { fieldData[field] = input.value; }
        });

        const dayData = {};
        dayInputs.forEach(input => {
          const field = input.dataset.field;
          if (field && !input.classList.contains('sunday-input')) {
            if (input.dataset.isHoliday === 'true') {
              dayData[field] = 0;
            } else {
              dayData[field] = input.value;
            }
          }
        });

        fieldData['total_days'] = finalPayableDays;
        fieldData['total_honorarium'] = totalHonorarium;

        const allData = { ...fieldData, ...dayData };
        const promises = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        Object.keys(allData).forEach(field => {
          const promise = fetch(`/watchman/${timesheetId}/update-field`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({ field: field, value: allData[field] })
          });
          promises.push(promise);
        });

        Promise.all(promises)
          .then(responses => Promise.all(responses.map(r => r.ok ? r.json().catch(() => ({ success: true })) : Promise.reject(new Error(`Failed to update status ${r.status}`)))))
          .then(() => {
            statusIcon.className = 'save-status-indicator saved';
            statusIcon.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
            setTimeout(() => {
              statusIcon.className = 'save-status-indicator';
              statusIcon.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
            }, 3000);
          })
          .catch(error => {
            console.error('Autosave Error:', error);
            statusIcon.className = 'save-status-indicator error';
            statusIcon.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
            setTimeout(() => {
              statusIcon.className = 'save-status-indicator';
              statusIcon.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
            }, 5000);
          });
      }

      // Initial calculation on page load
      document.querySelectorAll('tbody tr').forEach(row => {
        if (row.querySelector('.save-status-indicator')) {
          calculateTotals(row);
        }
      });

      // Attach event listeners
      document.querySelectorAll('.day-input, [data-field="rate_per_day"], [data-field="deduction"], [data-field="prev_abs"], [data-field="details"]').forEach(input => {
        input.addEventListener('input', function() {
          const row = this.closest('tr');
          const statusElement = row.querySelector('.save-status-indicator');
          if (!statusElement) return;

          const timesheetId = statusElement.id.replace('save-status-', '');

          calculateTotals(row);
          debounce(() => saveAllData(timesheetId, row), 1500, timesheetId);
        });
      });
    });
  </script>

  <script>
    devtools.detect(function(status){
      if(status){
        document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
      }
    });
  </script>
</body>
</html>