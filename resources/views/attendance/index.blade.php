<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $selectedDepartment }} Attendance Checker</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  {{-- FIX #1: Removed devtools.detect — causes false positives (browser extensions,
       responsive design mode) and provides zero actual security. Protect data server-side. --}}

  @php
  $deptColors = [
      'BSIT'      => ['primary' => '#dc3545', 'light' => '#f8d7da'],
      'BSBA'      => ['primary' => '#0d6efd', 'light' => '#cfe2ff'],
      'BSHM'      => ['primary' => '#198754', 'light' => '#d1e7dd'],
      'EDUCATION' => ['primary' => '#fd7e14', 'light' => '#ffe5cc'],
  ];
  $colors = $deptColors[$selectedDepartment] ?? ['primary' => '#6c757d', 'light' => '#e2e3e5'];
  @endphp

  <style>
    :root {
      --dept:       {{ $colors['primary'] }};
      --dept-light: {{ $colors['light'] }};
    }

    body {
      font-family: "Segoe UI", Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: var(--dept);
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
    }

    .main-content {
      margin: 30px auto;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 95%;
      max-width: 1400px;
    }

    .main-content h2 {
      font-size: 22px;
      margin-bottom: 20px;
      color: #333;
      display: inline-block;
    }

    /* Icon Buttons */
    .icon-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 18px;
      color: #fff;
      border: none;
      cursor: pointer;
      margin-right: 8px;
      text-decoration: none;
    }

    .btn-back       { background-color: var(--dept); }
    .btn-back:hover { opacity: 0.8; }

    .print-btn {
      background-color: #ffffff;
      color: var(--dept) !important;
      border: 2px solid var(--dept);
    }

    .print-btn:hover {
      background-color: var(--dept);
      color: #ffffff !important;
    }

    /* Week Navigation */
    .week-nav {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      gap: 15px;
    }

    .week-nav button {
      background: none;
      border: 1px solid #ddd;
      padding: 8px 12px;
      border-radius: 5px;
      cursor: pointer;
    }

    .week-nav button:hover {
      background-color: var(--dept);
      color: white;
    }

    .week-nav button:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    /* Table */
    .table-container { overflow-x: auto; }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    table thead {
      background-color: var(--dept-light);
    }

    th, td {
      padding: 10px 12px;
      text-align: center;
      border: 1px solid #ddd;
      font-size: 13px;
    }

    th {
      font-weight: 600;
      font-size: 14px;
      color: #333;
    }

    td.left { text-align: left; }

    tr:nth-child(even) td { background-color: #f9f9f9; }

    tr:hover td {
      background-color: var(--dept-light);
    }

    /* Attendance cells */
    .attendance-cell {
      font-size: 18px;
      padding: 8px !important;
      cursor: pointer;
    }

    .attendance-cell:hover { background-color: #f0f0f0 !important; }

    .text-success { color: #198754 !important; }
    .text-danger  { color: #dc3545 !important; }

    /* Day header small text */
    th small {
      font-weight: normal;
      color: #666;
      font-size: 10px;
    }

    /* Summary cards */
    .summary-cards {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .summary-card {
      flex: 1;
      min-width: 200px;
      padding: 15px;
      border-radius: 8px;
      color: white;
      text-align: center;
    }

    .summary-card h4 { margin: 0 0 5px 0; font-size: 24px; }
    .summary-card p  { margin: 0; font-size: 14px; }

    .card-total   { background-color: var(--dept); }
    .card-present { background-color: #198754; }
    .card-absent  { background-color: #dc3545; }
    .card-partial { background-color: #ffc107; color: #000; }

    /* Print styles */
    @media print {
      body { background: white !important; color: black !important; font-size: 12px; }

      .main-content {
        margin: 0 !important; padding: 20px !important;
        background: white !important; box-shadow: none !important;
        border-radius: 0 !important; width: 100% !important; max-width: none !important;
      }

      .icon-btn, .float-end, .week-nav, .summary-cards { display: none !important; }

      .main-content h2 {
        color: black !important; text-align: center;
        margin-bottom: 30px; font-size: 18px; font-weight: bold;
      }

      table { width: 100% !important; border-collapse: collapse !important; margin: 0 !important; }

      table thead {
        background-color: #f0f0f0 !important;
        -webkit-print-color-adjust: exact; color-adjust: exact;
      }

      th, td { border: 1px solid #000 !important; padding: 8px 6px !important; font-size: 10px !important; text-align: center !important; }

      th { font-weight: bold !important; background-color: #f0f0f0 !important; -webkit-print-color-adjust: exact; color-adjust: exact; }

      td.left { text-align: left !important; }

      tr:nth-child(even) td { background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact; color-adjust: exact; }
    }
  </style>
</head>
<body>

  <div class="main-content">
    <!-- Back button -->
    <a href="{{ route('dashboard') }}" class="icon-btn btn-back" title="Back to Dashboard">
      <i class="bi bi-arrow-left"></i>
    </a>

    <h2>{{ $selectedDepartment }} Attendance Checker</h2>

    <!-- Print button -->
    <div class="float-end">
      <button onclick="printAttendance()" class="icon-btn print-btn me-2" title="Print Attendance">
        <i class="bi bi-printer"></i>
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="summary-card card-total">
        <h4 id="total-employees">{{ count($attendanceData) }}</h4>
        <p>Total Employees</p>
      </div>
      <div class="summary-card card-present">
        <h4 id="present-count">0</h4>
        <p>Present Today</p>
      </div>
      <div class="summary-card card-absent">
        <h4 id="absent-count">0</h4>
        <p>Absent Today</p>
      </div>
      <div class="summary-card card-partial">
        <h4 id="partial-count">0</h4>
        <p>Partial Week</p>
      </div>
    </div>

    <!-- Week Navigation -->
    <div class="week-nav">
      <button id="prev-week-btn" onclick="previousWeek()">
        <i class="bi bi-chevron-left"></i> Previous Week
      </button>
      <span id="current-week-display">
        Week of {{ $currentWeek['monday']->format('M d') }} – {{ $currentWeek['saturday']->format('M d, Y') }}
      </span>
      <button id="next-week-btn" onclick="nextWeek()">
        Next Week <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <div class="table-container mt-3">
      <table>
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Mon<br><small>{{ $currentWeek['monday']->format('M d') }}</small></th>
            <th>Tue<br><small>{{ $currentWeek['tuesday']->format('M d') }}</small></th>
            <th>Wed<br><small>{{ $currentWeek['wednesday']->format('M d') }}</small></th>
            <th>Thu<br><small>{{ $currentWeek['thursday']->format('M d') }}</small></th>
            <th>Fri<br><small>{{ $currentWeek['friday']->format('M d') }}</small></th>
            <th>Sat<br><small>{{ $currentWeek['saturday']->format('M d') }}</small></th>
            <th>Total Days</th>
            <th>Total Hours</th>
          </tr>
        </thead>
        <tbody>
          @forelse($attendanceData as $record)
          <tr data-employee-id="{{ $record['employee_id'] }}">
            <td>{{ $record['employee_id'] }}</td>
            <td class="left">{{ $record['employee_name'] }}</td>
            <td>{{ $record['designation'] ?? 'N/A' }}</td>
            <td>{{ $record['department'] }}</td>
            <td class="attendance-cell" onclick="toggleAttendance({{ $record['employee_id'] }}, 'monday')">
              @if($record['monday'])
                <i class="bi bi-check-circle-fill text-success" title="{{ $record['monday_hours'] }} hours"></i>
              @else
                <i class="bi bi-x-circle-fill text-danger"></i>
              @endif
            </td>
            <td class="attendance-cell" onclick="toggleAttendance({{ $record['employee_id'] }}, 'tuesday')">
              @if($record['tuesday'])
                <i class="bi bi-check-circle-fill text-success" title="{{ $record['tuesday_hours'] }} hours"></i>
              @else
                <i class="bi bi-x-circle-fill text-danger"></i>
              @endif
            </td>
            <td class="attendance-cell" onclick="toggleAttendance({{ $record['employee_id'] }}, 'wednesday')">
              @if($record['wednesday'])
                <i class="bi bi-check-circle-fill text-success" title="{{ $record['wednesday_hours'] }} hours"></i>
              @else
                <i class="bi bi-x-circle-fill text-danger"></i>
              @endif
            </td>
            <td class="attendance-cell" onclick="toggleAttendance({{ $record['employee_id'] }}, 'thursday')">
              @if($record['thursday'])
                <i class="bi bi-check-circle-fill text-success" title="{{ $record['thursday_hours'] }} hours"></i>
              @else
                <i class="bi bi-x-circle-fill text-danger"></i>
              @endif
            </td>
            <td class="attendance-cell" onclick="toggleAttendance({{ $record['employee_id'] }}, 'friday')">
              @if($record['friday'])
                <i class="bi bi-check-circle-fill text-success" title="{{ $record['friday_hours'] }} hours"></i>
              @else
                <i class="bi bi-x-circle-fill text-danger"></i>
              @endif
            </td>
            <td class="attendance-cell" onclick="toggleAttendance({{ $record['employee_id'] }}, 'saturday')">
              @if($record['saturday'])
                <i class="bi bi-check-circle-fill text-success" title="{{ $record['saturday_hours'] }} hours"></i>
              @else
                <i class="bi bi-x-circle-fill text-danger"></i>
              @endif
            </td>
            <td><strong>{{ $record['total_days'] }}</strong></td>
            <td><strong>{{ number_format($record['total_hours'], 1) }}</strong></td>
          </tr>
          @empty
          <tr>
            <td colspan="12" class="text-center text-muted py-4">
              <i class="bi bi-inbox me-2"></i>No employees found in {{ $selectedDepartment }} department
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ── Server-side values exposed to JS ──────────────────────────────────────
    const csrfToken          = '{{ csrf_token() }}';
    const selectedDepartment = '{{ $selectedDepartment }}';
    const currentWeekStart   = '{{ $currentWeek['monday']->format('Y-m-d') }}';

    // Read the computed CSS variable so all Swal dialogs use the correct
    // department colour without needing a separate @if chain.
    const deptColor = getComputedStyle(document.documentElement)
                        .getPropertyValue('--dept').trim();

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
      updateSummaryStats();

      // Disable "Next Week" if we're already on the current week.
      // Construct dates in local time (append 'T00:00:00') so the comparison
      // is not shifted by UTC offset.
      const today   = new Date(); today.setHours(0, 0, 0, 0);
      const weekMon = new Date(currentWeekStart + 'T00:00:00');
      const nextMon = new Date(weekMon); nextMon.setDate(nextMon.getDate() + 7);
      if (nextMon > today) {
        document.getElementById('next-week-btn').disabled = true;
      }
    });

    // ── Summary stats ─────────────────────────────────────────────────────────
    function updateSummaryStats() {
      const rows = document.querySelectorAll('tbody tr[data-employee-id]');
      let presentCount = 0, absentCount = 0, partialCount = 0;

      // getDay(): 0=Sun 1=Mon 2=Tue 3=Wed 4=Thu 5=Fri 6=Sat
      // Columns:  EmpID(1) Name(2) Desig(3) Dept(4) Mon(5)…Sat(10) Days(11) Hrs(12)
      const todayJS     = new Date().getDay();
      const dayToColIdx = { 1: 5, 2: 6, 3: 7, 4: 8, 5: 9, 6: 10 };
      const todayColIdx = dayToColIdx[todayJS] || null;

      rows.forEach(row => {
        if (todayColIdx) {
          const todayIcon = row.querySelector(`.attendance-cell:nth-child(${todayColIdx}) i`);
          todayIcon && todayIcon.classList.contains('text-success') ? presentCount++ : absentCount++;
        }
        const daysPresent = row.querySelectorAll('.attendance-cell i.text-success').length;
        if (daysPresent > 0 && daysPresent < 6) partialCount++;
      });

      document.getElementById('present-count').textContent = presentCount;
      document.getElementById('absent-count').textContent  = absentCount;
      document.getElementById('partial-count').textContent = partialCount;
    }

    // ── Toggle attendance (modal) ─────────────────────────────────────────────
    function toggleAttendance(employeeId, day) {
      const row         = document.querySelector(`tr[data-employee-id="${employeeId}"]`);
      const cell        = row.querySelector(`.attendance-cell:nth-child(${getDayColumnIndex(day)})`);
      const currentIcon = cell ? cell.querySelector('i') : null;
      const isPresent   = currentIcon && currentIcon.classList.contains('text-success');
      const existingHrs = isPresent ? (parseFloat(currentIcon.title) || 8) : 8;

      Swal.fire({
        title: 'Update Attendance',
        // FIX #4: confirmButtonColor added so modal matches the department theme.
        confirmButtonColor: deptColor,
        cancelButtonColor:  '#6c757d',
        html: `
          <div class="mb-3" style="text-align:left">
            <label class="form-label fw-bold">Mark as:</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="attendance" id="present" value="present" ${isPresent ? 'checked' : ''}>
              <label class="form-check-label" for="present">✅ Present</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="attendance" id="absent" value="absent" ${!isPresent ? 'checked' : ''}>
              <label class="form-check-label" for="absent">❌ Absent</label>
            </div>
          </div>
          <div class="mb-3" style="text-align:left">
            <label for="hours" class="form-label fw-bold">Hours worked:</label>
            <input type="number" class="form-control" id="hours" min="0" max="24" step="0.5" value="${existingHrs}">
          </div>`,
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText:  'Cancel',
        preConfirm: () => {
          const attendance = document.querySelector('input[name="attendance"]:checked');
          if (!attendance) {
            Swal.showValidationMessage('Please select attendance status');
            return false;
          }

          // FIX #3: Validate hours range so the server never receives out-of-range values.
          const hoursVal = parseFloat(document.getElementById('hours').value);
          if (isNaN(hoursVal) || hoursVal < 0 || hoursVal > 24) {
            Swal.showValidationMessage('Hours must be a number between 0 and 24');
            return false;
          }

          return { status: attendance.value, hours: hoursVal };
        }
      }).then(result => {
        if (result.isConfirmed) {
          updateAttendanceRecord(employeeId, day, result.value.status === 'present', result.value.hours);
        }
      });
    }

    // ── AJAX save ─────────────────────────────────────────────────────────────
    function updateAttendanceRecord(employeeId, day, isPresent, hours) {
      const data = {
        employee_id:      employeeId,
        department:       selectedDepartment,
        [day]:            isPresent,
        [day + '_hours']: isPresent ? hours : 0,
        _token:           csrfToken
      };

      fetch('/attendance/api/save-attendance', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN':  csrfToken
        },
        body: JSON.stringify(data)
      })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
      })
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Update failed');

        // Update UI icon
        const row  = document.querySelector(`tr[data-employee-id="${employeeId}"]`);
        const cell = row.querySelector(`.attendance-cell:nth-child(${getDayColumnIndex(day)})`);
        const icon = cell.querySelector('i');

        icon.className = isPresent
          ? 'bi bi-check-circle-fill text-success'
          : 'bi bi-x-circle-fill text-danger';
        icon.title = isPresent ? `${hours} hours` : '';

        updateSummaryStats();

        Swal.fire({
          icon: 'success',
          title: 'Updated!',
          text: 'Attendance updated successfully.',
          timer: 1500,
          showConfirmButton: false
        });
      })
      .catch(error => {
        // FIX #4: confirmButtonColor added for consistency.
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Failed to update attendance: ' + error.message,
          confirmButtonColor: deptColor
        });
      });
    }

    // ── Column index map ──────────────────────────────────────────────────────
    // Columns: EmpID(1) Name(2) Designation(3) Dept(4) Mon(5)…Sat(10) Days(11) Hrs(12)
    function getDayColumnIndex(day) {
      return { monday: 5, tuesday: 6, wednesday: 7, thursday: 8, friday: 9, saturday: 10 }[day];
    }

    // ── FIX #2: Safe local-date formatter ─────────────────────────────────────
    // d.toISOString() converts to UTC before formatting. In UTC+ timezones
    // (e.g. Philippines = UTC+8) midnight local time is still the previous
    // calendar day in UTC, producing a date that is one day too early.
    // This helper formats using local year/month/day instead.
    function formatLocalDate(d) {
      const yyyy = d.getFullYear();
      const mm   = String(d.getMonth() + 1).padStart(2, '0');
      const dd   = String(d.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    }

    // ── Print ─────────────────────────────────────────────────────────────────
    function printAttendance() {
      Swal.fire({
        title: 'Print Attendance',
        text:  'This will open the print dialog for the attendance sheet.',
        icon:  'info',
        showCancelButton:    true,
        confirmButtonColor:  deptColor,
        cancelButtonColor:   '#6c757d',
        confirmButtonText:   '<i class="bi bi-printer"></i> Print',
        cancelButtonText:    'Cancel'
      }).then(result => { if (result.isConfirmed) window.print(); });
    }

    // ── Week navigation ───────────────────────────────────────────────────────
    function previousWeek() {
      // Construct in local time to avoid UTC-offset shifting the date.
      const d = new Date(currentWeekStart + 'T00:00:00');
      d.setDate(d.getDate() - 7);
      // FIX #2: Use formatLocalDate() instead of d.toISOString().split('T')[0].
      window.location.href = `/${selectedDepartment.toLowerCase()}?week=${formatLocalDate(d)}`;
    }

    function nextWeek() {
      const today = new Date(); today.setHours(0, 0, 0, 0);
      const d     = new Date(currentWeekStart + 'T00:00:00');
      d.setDate(d.getDate() + 7);
      if (d > today) {
        // FIX #4: confirmButtonColor added for consistency.
        Swal.fire({
          icon: 'warning',
          title: 'Future Week',
          text: 'Cannot view attendance for future weeks.',
          confirmButtonColor: deptColor
        });
        return;
      }
      // FIX #2: Use formatLocalDate() instead of d.toISOString().split('T')[0].
      window.location.href = `/${selectedDepartment.toLowerCase()}?week=${formatLocalDate(d)}`;
    }

    // ── Flash messages ────────────────────────────────────────────────────────
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Welcome!',
        text: @json(session('success')),
        timer: 3000,
        showConfirmButton: false
      });
    @endif
  </script>

</body>
</html>