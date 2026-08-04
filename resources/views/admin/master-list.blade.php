<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Master List - Madridejos Community College</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      /* Shared app tokens — same family used on the timesheet pages */
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --primary-tint: #eff6ff;

      --slate-50:  #f8fafc;
      --slate-100: #f1f5f9;
      --slate-200: #e2e8f0;
      --slate-300: #cbd5e1;
      --slate-400: #94a3b8;
      --slate-500: #64748b;
      --slate-600: #475569;
      --slate-700: #334155;
      --slate-900: #0f172a;

      --success: #16a34a;
      --success-tint: #f0fdf4;
      --warning: #d97706;
      --warning-tint: #fffbeb;
      --danger: #dc2626;
      --danger-tint: #fef2f2;
      --info: #0891b2;
      --info-tint: #ecfeff;

      --radius: 10px;
      --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.06);
      --shadow-md: 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body { height: 100%; }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--slate-50);
      color: var(--slate-900);
      padding-bottom: 3rem;
    }

    a { text-decoration: none; }

    :focus-visible {
      outline: 2px solid var(--primary);
      outline-offset: 2px;
    }

    /* ---------- Header ---------- */
    .page-header {
      background: white;
      border-bottom: 1px solid var(--slate-200);
      padding: 1.5rem 0;
      margin-bottom: 1.75rem;
    }

    .header-content {
      max-width: 1320px;
      margin: 0 auto;
      padding: 0 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .header-title {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .header-title .icon-chip {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      background: var(--primary-tint);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }

    .header-title h1 {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--slate-900);
      letter-spacing: -0.01em;
    }

    .header-title p {
      font-size: 0.85rem;
      color: var(--slate-500);
      font-weight: 500;
    }

    .header-actions { display: flex; gap: 0.6rem; }

    .btn {
      border-radius: 8px;
      padding: 0.55rem 1rem;
      font-weight: 600;
      font-size: 0.9rem;
      border: 1px solid transparent;
      cursor: pointer;
      transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-dark); color: white; }

    .btn-outline {
      background: white;
      color: var(--slate-700);
      border-color: var(--slate-200);
    }
    .btn-outline:hover { background: var(--slate-100); color: var(--slate-900); }

    /* ---------- Container ---------- */
    .container-main {
      max-width: 1320px;
      margin: 0 auto;
      padding: 0 1.5rem;
    }

    /* ---------- Stats ---------- */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .stat-card {
      background: white;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius);
      padding: 1.1rem 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.9rem;
      box-shadow: var(--shadow-sm);
    }

    .stat-card .stat-icon {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      flex-shrink: 0;
    }

    .stat-card.instructors .stat-icon { background: var(--primary-tint); color: var(--primary); }
    .stat-card.staff .stat-icon       { background: var(--success-tint); color: var(--success); }
    .stat-card.utility .stat-icon     { background: var(--warning-tint); color: var(--warning); }
    .stat-card.total .stat-icon       { background: var(--info-tint); color: var(--info); }

    .stat-value {
      font-size: 1.5rem;
      font-weight: 800;
      line-height: 1.1;
      color: var(--slate-900);
    }

    .stat-label {
      font-size: 0.78rem;
      color: var(--slate-500);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    /* ---------- Department legend ---------- */
    .legend-bar {
      background: white;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius);
      padding: 0.85rem 1.25rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1.1rem;
      flex-wrap: wrap;
      font-size: 0.85rem;
    }

    .legend-bar .legend-title {
      font-weight: 700;
      color: var(--slate-600);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .legend-dot {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: 600;
      color: var(--slate-700);
    }

    .legend-dot .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
    }

    /* ---------- Filter ---------- */
    .filter-section {
      background: white;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius);
      padding: 1.1rem 1.25rem;
      margin-bottom: 1.5rem;
    }

    .filter-row {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr auto;
      gap: 0.75rem;
    }

    .form-control, .form-select {
      border-radius: 999px;
      border: 1px solid var(--slate-200);
      padding: 0.6rem 1rem;
      font-size: 0.9rem;
      background: var(--slate-50);
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
      background: white;
    }

    .filter-row .btn-primary { border-radius: 999px; padding: 0.6rem 1.25rem; }

    /* ---------- Table ---------- */
    .table-section {
      background: white;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius);
      overflow: hidden;
      margin-bottom: 1.5rem;
    }

    .table-header {
      padding: 1rem 1.25rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--slate-200);
    }

    .table-header h2 {
      font-size: 1rem;
      font-weight: 700;
      color: var(--slate-900);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .table-header h2 i { color: var(--primary); }

    .badge-count {
      background: var(--primary-tint);
      color: var(--primary);
      padding: 0.3rem 0.7rem;
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 700;
    }

    .table-wrapper { overflow-x: auto; }

    .table { margin: 0; border-collapse: collapse; width: 100%; min-width: 720px; }

    .table thead th {
      background: var(--slate-50);
      border-bottom: 1px solid var(--slate-200);
      padding: 0.75rem 1rem;
      font-weight: 700;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: var(--slate-500);
      white-space: nowrap;
    }

    .table tbody td {
      padding: 0.85rem 1rem;
      border-bottom: 1px solid var(--slate-100);
      vertical-align: middle;
      font-size: 0.9rem;
    }

    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr:hover { background-color: var(--slate-50); }

    .employee-name {
      font-weight: 700;
      color: var(--slate-900);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--primary-tint);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.8rem;
      flex-shrink: 0;
    }

    .badge {
      padding: 0.3rem 0.65rem;
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 700;
      display: inline-block;
    }

    .badge-department { background: var(--slate-100); color: var(--slate-600); }
    .badge-dept-bsit { background: var(--danger-tint); color: var(--danger); }
    .badge-dept-bsba { background: var(--success-tint); color: var(--success); }
    .badge-dept-bshm { background: var(--warning-tint); color: var(--warning); }
    .badge-dept-education { background: var(--primary-tint); color: var(--primary); }

    .badge-fulltime { background: var(--success-tint); color: var(--success); }
    .badge-parttime { background: var(--warning-tint); color: var(--warning); }
    .badge-staff { background: var(--success-tint); color: var(--success); }
    .badge-utility { background: var(--warning-tint); color: var(--warning); }

    .amount { font-weight: 700; color: var(--slate-900); }

    .action-buttons { display: flex; gap: 0.4rem; }

    .btn-icon {
      width: 32px;
      height: 32px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: background 0.15s ease, color 0.15s ease;
      font-size: 0.85rem;
    }

    .btn-edit { background: var(--primary-tint); color: var(--primary); }
    .btn-edit:hover { background: var(--primary); color: white; }

    .btn-delete { background: var(--danger-tint); color: var(--danger); }
    .btn-delete:hover { background: var(--danger); color: white; }

    .empty-state { text-align: center; padding: 3.5rem 2rem; color: var(--slate-400); }
    .empty-state i { font-size: 3rem; margin-bottom: 0.75rem; display: block; }
    .empty-state p { margin: 0.25rem 0 0 0; }
    .empty-state p:first-of-type { font-size: 1rem; font-weight: 600; color: var(--slate-600); }

    .page-footer {
      text-align: center;
      padding: 1.5rem;
      color: var(--slate-400);
      font-size: 0.82rem;
    }

    @media (max-width: 768px) {
      .header-content { flex-direction: column; align-items: flex-start; }
      .filter-row { grid-template-columns: 1fr; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media print {
      body { background: white; }
      .page-header, .filter-section, .header-actions, .action-buttons { display: none !important; }
      .table-section { border: none; box-shadow: none; }
      .page-footer { display: none; }
    }
  </style>
</head>
<body>

  <div class="page-header">
    <div class="header-content">
      <div class="header-title">
        <div class="icon-chip"><i class="bi bi-building"></i></div>
        <div>
          <h1>Master List</h1>
          <p>All employees — Madridejos Community College</p>
        </div>
      </div>
      <div class="header-actions">
        <a href="{{ route('dashboard') }}" class="btn btn-outline">
          <i class="bi bi-arrow-left"></i> Dashboard
        </a>
        <button class="btn btn-outline" onclick="window.print()">
          <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ route('master.list.add') }}" class="btn btn-primary">
          <i class="bi bi-plus-lg"></i> Add Employee
        </a>
      </div>
    </div>
  </div>

  <div class="container-main">

    <div class="stats-grid">
      <div class="stat-card instructors">
        <div class="stat-icon"><i class="bi bi-briefcase-fill"></i></div>
        <div>
          <div class="stat-value">{{ $employees->filter(fn($e) => str_contains(strtolower($e->type ?? ''), 'instructor') || str_contains(strtolower($e->designation ?? ''), 'instructor'))->count() }}</div>
          <div class="stat-label">Instructors</div>
        </div>
      </div>

      <div class="stat-card staff">
        <div class="stat-icon"><i class="bi bi-person-lines-fill"></i></div>
        <div>
          <div class="stat-value">{{ $employees->where('type', '=', 'Staff')->count() }}</div>
          <div class="stat-label">Staff</div>
        </div>
      </div>

      <div class="stat-card utility">
        <div class="stat-icon"><i class="bi bi-tools"></i></div>
        <div>
          <div class="stat-value">{{ $employees->where('type', '=', 'Utility')->count() }}</div>
          <div class="stat-label">Utility</div>
        </div>
      </div>

      <div class="stat-card total">
        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
        <div>
          <div class="stat-value">{{ $employees->count() }}</div>
          <div class="stat-label">Total Employees</div>
        </div>
      </div>
    </div>

    <div class="legend-bar">
      <span class="legend-title"><i class="bi bi-palette-fill"></i> Departments</span>
      <span class="legend-dot"><span class="dot" style="background: var(--danger);"></span> BSIT</span>
      <span class="legend-dot"><span class="dot" style="background: var(--success);"></span> BSBA</span>
      <span class="legend-dot"><span class="dot" style="background: var(--warning);"></span> BSHM</span>
      <span class="legend-dot"><span class="dot" style="background: var(--primary);"></span> Education</span>
    </div>

    <div class="filter-section">
      <form method="GET" action="{{ route('master.list') }}" id="filterForm">
        <div class="filter-row">
          <input type="text" class="form-control" placeholder="Search by name or email..." id="searchInput" name="search" value="{{ request('search', '') }}">
          <select class="form-select" id="designationFilter" name="employee_type" onchange="document.getElementById('filterForm').submit()">
            <option value="all">All Types</option>
            <option value="fulltime" {{ $selectedEmployeeType === 'fulltime' ? 'selected' : '' }}>Full-time</option>
            <option value="parttime" {{ $selectedEmployeeType === 'parttime' ? 'selected' : '' }}>Part-time</option>
            <option value="staff" {{ $selectedEmployeeType === 'staff' ? 'selected' : '' }}>Staff</option>
            <option value="utility" {{ $selectedEmployeeType === 'utility' ? 'selected' : '' }}>Utility</option>
          </select>
          <select class="form-select" id="departmentFilter" name="department" onchange="document.getElementById('filterForm').submit()">
            <option value="all">All Departments</option>
            @foreach($departments as $dept)
              <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
        </div>
      </form>
    </div>

    <div class="table-section">
      <div class="table-header">
        <h2><i class="bi bi-list-check"></i> All Employees</h2>
        <span class="badge-count">{{ $employees->count() }} total</span>
      </div>

      @if($employees->isEmpty())
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <p>No employees found</p>
          <p style="font-size: 0.85rem;">Try adjusting your search or filter criteria</p>
        </div>
      @else
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Employee Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Type</th>
                <th>Rate/Hour</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($employees as $idx => $employee)
                @php
                  $dept = strtoupper(trim($employee->department ?? ''));
                  $deptBadgeClass = match(true) {
                    str_contains($dept, 'BSIT') => 'badge-dept-bsit',
                    str_contains($dept, 'BSBA') => 'badge-dept-bsba',
                    str_contains($dept, 'BSHM') => 'badge-dept-bshm',
                    str_contains($dept, 'EDUC') || str_contains($dept, 'EDUCATION') => 'badge-dept-education',
                    default => 'badge-department'
                  };
                  $isInstructor = str_contains(strtolower($employee->type ?? ''), 'instructor')
                               || str_contains(strtolower($employee->designation ?? ''), 'instructor');

                  $nameParts = preg_split('/\s+/', trim($employee->employee_name ?? 'Unknown'), -1, PREG_SPLIT_NO_EMPTY);
                  $initials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                @endphp
                <tr>
                  <td>{{ $idx + 1 }}</td>
                  <td>
                    <div class="employee-name">
                      <div class="avatar">{{ $initials }}</div>
                      <div>
                        <span>{{ $employee->employee_name }}</span>
                        @if($isInstructor && $employee->email)
                          <br><small><a href="mailto:{{ $employee->email }}" style="color: var(--primary); font-size: 0.78rem;"><i class="bi bi-envelope-fill" style="font-size: 0.72rem;"></i> {{ $employee->email }}</a></small>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($employee->email)
                      <a href="mailto:{{ $employee->email }}" style="color: var(--slate-600);">{{ $employee->email }}</a>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($employee->department)
                      <span class="badge {{ $deptBadgeClass }}">{{ $employee->department }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $type = $employee->type ?? 'Unknown';
                      $badgeClass = match($type) {
                        'Full-time Instructor' => 'badge-fulltime',
                        'Part-time Instructor' => 'badge-parttime',
                        'Staff' => 'badge-staff',
                        'Utility' => 'badge-utility',
                        default => str_contains(strtolower($type), 'instructor') ? 'badge-fulltime' : 'badge-department'
                      };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $type }}</span>
                  </td>
                  <td>
                    @if($employee->rate)
                      <span class="amount">₱{{ number_format($employee->rate, 2) }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <div class="action-buttons">
                      <a href="{{ route('master.list.edit', ['id' => $employee->id, 'type' => strtolower(str_replace(['Full-time Instructor', 'Part-time Instructor'], ['fulltime', 'parttime'], $employee->type))]) }}" class="btn-icon btn-edit" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <button class="btn-icon btn-delete" onclick="deleteEmployee({{ $employee->id }}, '{{ strtolower(str_replace(['Full-time Instructor', 'Part-time Instructor'], ['fulltime', 'parttime'], $employee->type)) }}')" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  <div class="page-footer">
    <p>Madridejos Community College · Employee Master List · © 2025</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // ── Mobile Sidebar Toggling ──────────────────────
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('open');
      document.getElementById('overlay').classList.toggle('show');
    }
    function closeSidebar() {
      document.getElementById('sidebar').classList.remove('open');
      document.getElementById('overlay').classList.remove('show');
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function deleteEmployee(id, type) {
      Swal.fire({
        title: 'Delete Employee?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, Delete!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('{{ route("master.list.delete") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
              ids: [id],
              type: type || 'fulltime'
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire('Deleted!', 'Employee deleted successfully.', 'success')
                .then(() => window.location.reload());
            } else {
              Swal.fire('Error!', data.message || 'Failed to delete employee.', 'error');
            }
          })
          .catch(error => {
            Swal.fire('Error!', 'An error occurred while deleting the employee.', 'error');
            console.error('Error:', error);
          });
        }
      });
    }

    devtools.detect(function(status) {
      if (status) {
        document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
      }
    });

    let searchTimer;
    document.getElementById('searchInput').addEventListener('input', function () {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () {
        document.getElementById('filterForm').submit();
      }, 400);
    });
  </script>
</body>
</html>