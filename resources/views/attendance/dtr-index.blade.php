<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daily Time Records — MCC Attendance</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  @include('attendance.partials.dtr-styles')
  <style>
    .roster { width: 100%; border-collapse: collapse; }
    .roster thead th {
      text-align: left; padding: 11px 20px;
      font-size: .66rem; font-weight: 700; letter-spacing: .09em;
      text-transform: uppercase; color: var(--text-3);
      border-bottom: 1px solid var(--border);
      background: #f8fafc;
    }
    .roster tbody td {
      padding: 14px 20px; border-bottom: 1px solid var(--border-2);
      font-size: .87rem; vertical-align: middle;
    }
    .roster tbody tr:last-child td { border-bottom: none; }
    .roster tbody tr:hover td { background: #f9fbff; }
    .roster .num { font-variant-numeric: tabular-nums; }

    .emp-name { font-weight: 600; }
    .emp-meta { font-size: .74rem; color: var(--text-3); margin-top: 1px; }

    .pill {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 3px 10px; border-radius: 999px;
      font-size: .7rem; font-weight: 600;
      background: rgba(37,99,235,.09); color: var(--brand-dark);
    }
    .row-actions { display: flex; gap: 7px; justify-content: flex-end; }
    @media (max-width: 720px) {
      .roster thead { display: none; }
      .roster tbody td { display: block; border: none; padding: 4px 18px; }
      .roster tbody tr { display: block; border-bottom: 1px solid var(--border-2); padding: 12px 0; }
      .row-actions { justify-content: flex-start; padding-top: 8px; }
    }
  </style>
</head>
<body>

  <header class="topbar no-print">
    <div class="topbar-inner">
      <a href="{{ route('attendance.dashboard') }}" class="topbar-brand">
        <i class="bi bi-calendar2-check"></i>
        <span>MCC Attendance<small>Daily Time Records</small></span>
      </a>
      <div class="topbar-actions">
        <a href="{{ route('attendance.dashboard') }}" class="btn btn-ghost btn-sm">
          <i class="bi bi-grid"></i> Dashboard
        </a>
        <form method="POST" action="{{ route('attendance.logout') }}">
          @csrf
          <button type="submit" class="btn btn-ghost btn-sm"><i class="bi bi-box-arrow-right"></i> Sign out</button>
        </form>
      </div>
    </div>
  </header>

  <div class="wrap">

    @if(session('success'))
      <div class="flash"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
    @endif

    <div class="page-head">
      <div class="eyebrow">Civil Service Form No. 48</div>
      <h1 class="page-title">Daily Time Records</h1>
      <p class="page-sub">
        Pick a period to review, edit or print an employee's DTR.
      </p>
    </div>

    <form method="GET" action="{{ route('attendance.dtr.index') }}" class="toolbar no-print">
      <div class="field">
        <label for="course">Department</label>
        <input type="text" id="course" name="course" value="{{ $course }}" placeholder="e.g. BSIT">
      </div>
      <div class="field">
        <label for="month">Period</label>
        <select id="month" name="month">
          @foreach($monthOptions as $value => $label)
            <option value="{{ $value }}" @selected($month->format('Y-m') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Show records</button>
    </form>

    <div class="card">
      <div class="card-head">
        <div class="card-title">
          {{ $course ?: 'No department selected' }} &middot; {{ $month->format('F Y') }}
        </div>
        @if($employees->isNotEmpty())
          <span class="pill">{{ $employees->count() }} employee{{ $employees->count() === 1 ? '' : 's' }}</span>
        @endif
      </div>

      @if($employees->isEmpty())
        <div class="empty">
          <i class="bi bi-calendar-x" style="font-size:2rem;opacity:.4;"></i>
          <div class="empty-title">No attendance recorded</div>
          <p class="empty-sub">
            Nothing has been logged for {{ $course ?: 'this department' }} in {{ $month->format('F Y') }}.
            Record attendance on the dashboard first, then come back to produce the DTR.
          </p>
        </div>
      @else
        <table class="roster">
          <thead>
            <tr>
              <th>Employee</th>
              <th class="num">Days recorded</th>
              <th class="num">Hours</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($employees as $employee)
              @php
                $params = [
                  'course'     => $course,
                  'employeeId' => $employee->employee_id,
                  'month'      => $month->format('Y-m'),
                  'type'       => $employee->employee_type,
                ];
              @endphp
              <tr>
                <td>
                  <div class="emp-name">{{ $employee->employee_name ?: 'Employee #'.$employee->employee_id }}</div>
                  <div class="emp-meta">{{ $employee->employee_type ?: 'Unspecified type' }}</div>
                </td>
                <td class="num">{{ $employee->days_recorded }}</td>
                <td class="num">{{ number_format((float) $employee->hours, 2) }}</td>
                <td>
                  <div class="row-actions">
                    <a href="{{ route('attendance.dtr.show', $params) }}" class="btn btn-outline btn-sm">
                      <i class="bi bi-pencil-square"></i> Open
                    </a>
                    <a href="{{ route('attendance.dtr.print', $params) }}" target="_blank" class="btn btn-outline btn-sm">
                      <i class="bi bi-printer"></i> Print
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>

</body>
</html>
