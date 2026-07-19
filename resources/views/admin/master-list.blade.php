<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Master List - Madridejos Community College</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --primary: #2c3e50;
      --primary-light: #34495e;
      --accent: #3498db;
      --success: #27ae60;
      --warning: #f39c12;
      --danger: #e74c3c;
      --info: #16a085;
      --light: #ecf0f1;
      --dark: #1a1a1a;
      --muted: #f8f9fa;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
      background-attachment: fixed;
      color: var(--dark);
      padding-bottom: 2rem;
    }

    /* Header */
    .page-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: white;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .header-content {
      max-width: 1400px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
    }

    .header-title {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .header-title h1 {
      margin: 0;
      font-size: 2rem;
      font-weight: 700;
    }

    .header-title p {
      margin: 0;
      font-size: 0.95rem;
      opacity: 0.9;
    }

    .header-actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      border-radius: 8px;
      padding: 0.6rem 1.2rem;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-primary {
      background: var(--accent);
      color: white;
    }

    .btn-primary:hover {
      background: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
      color: white;
      text-decoration: none;
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 2px solid white;
    }

    .btn-secondary:hover {
      background: white;
      color: var(--primary);
      text-decoration: none;
    }

    .btn-sm {
      padding: 0.4rem 0.8rem;
      font-size: 0.85rem;
    }

    /* Container */
    .container-main {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    /* Stats Section */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      border-left: 4px solid var(--accent);
      transition: all 0.3s ease;
      text-align: center;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card.instructors {
      border-left-color: var(--accent);
    }

    .stat-card.staff {
      border-left-color: var(--success);
    }

    .stat-card.utility {
      border-left-color: var(--warning);
    }

    .stat-icon {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .stat-value {
      font-size: 2.2rem;
      font-weight: 700;
      margin: 0.5rem 0;
    }

    .stat-label {
      font-size: 0.9rem;
      color: #7f8c8d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }

    /* Table Section */
    .table-section {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 2rem;
    }

    .table-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: white;
      padding: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .table-header h2 {
      margin: 0;
      font-size: 1.3rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .badge-count {
      background: rgba(255, 255, 255, 0.25);
      color: white;
      padding: 0.35rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .table-wrapper {
      overflow-x: auto;
    }

    .table {
      margin: 0;
      border-collapse: collapse;
    }

    .table thead th {
      background: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
      padding: 1rem 0.75rem;
      font-weight: 700;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--dark);
    }

    .table tbody td {
      padding: 1rem 0.75rem;
      border-bottom: 1px solid #e9ecef;
      vertical-align: middle;
    }

    .table tbody tr:hover {
      background-color: rgba(52, 152, 219, 0.05);
      transition: background-color 0.2s ease;
    }

    /* Employee Name */
    .employee-name {
      font-weight: 700;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), #2980b9);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1rem;
    }

    /* Badge Styles */
    .badge {
      padding: 0.4rem 0.8rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 700;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .badge-department {
      background: rgba(52, 152, 219, 0.15);
      color: var(--accent);
    }

    /* Department color coding */
    .badge-dept-bsit {
      background: rgba(231, 76, 60, 0.15);
      color: #e74c3c;
      border: 1px solid rgba(231, 76, 60, 0.3);
    }

    .badge-dept-bsba {
      background: rgba(39, 174, 96, 0.15);
      color: #27ae60;
      border: 1px solid rgba(39, 174, 96, 0.3);
    }

    .badge-dept-bshm {
      background: rgba(243, 156, 18, 0.15);
      color: #d68910;
      border: 1px solid rgba(243, 156, 18, 0.3);
    }

    .badge-dept-education {
      background: rgba(52, 152, 219, 0.15);
      color: #2980b9;
      border: 1px solid rgba(52, 152, 219, 0.3);
    }

    .badge-instructor {
      background: rgba(52, 152, 219, 0.15);
      color: var(--accent);
    }

    .badge-staff {
      background: rgba(39, 174, 96, 0.15);
      color: var(--success);
    }

    .badge-utility {
      background: rgba(243, 156, 18, 0.15);
      color: var(--warning);
    }

    .badge-fulltime {
      background: rgba(39, 174, 96, 0.15);
      color: var(--success);
    }

    .badge-parttime {
      background: rgba(243, 156, 18, 0.15);
      color: var(--warning);
    }

    /* Amount */
    .amount {
      font-weight: 700;
      color: var(--accent);
      font-size: 1.05rem;
    }

    /* Actions */
    .action-buttons {
      display: flex;
      gap: 0.5rem;
    }

    .btn-icon {
      width: 36px;
      height: 36px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.9rem;
    }

    .btn-edit {
      background: rgba(52, 152, 219, 0.15);
      color: var(--accent);
    }

    .btn-edit:hover {
      background: var(--accent);
      color: white;
    }

    .btn-delete {
      background: rgba(231, 76, 60, 0.15);
      color: var(--danger);
    }

    .btn-delete:hover {
      background: var(--danger);
      color: white;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #95a5a6;
    }

    .empty-state i {
      font-size: 5rem;
      margin-bottom: 1rem;
      opacity: 0.2;
    }

    /* Filter Section */
    .filter-section {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .filter-title {
      font-weight: 700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .filter-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }

    .form-control,
    .form-select {
      border-radius: 8px;
      border: 1px solid #ddd;
      padding: 0.75rem;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    /* Footer */
    .page-footer {
      text-align: center;
      padding: 2rem;
      color: #7f8c8d;
      font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        text-align: center;
      }

      .header-title h1 {
        font-size: 1.5rem;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }

      .table {
        font-size: 0.85rem;
      }

      .table thead th,
      .table tbody td {
        padding: 0.75rem 0.5rem;
      }

      .filter-row {
        grid-template-columns: 1fr;
      }
    }

    /* Print Styles */
    @media print {
      body {
        background: white;
      }

      .page-header,
      .filter-section,
      .header-actions,
      .action-buttons {
        display: none !important;
      }

      .table {
        box-shadow: none;
      }

      .table thead th {
        background: #333 !important;
        color: white !important;
      }

      .page-footer {
        display: none;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="header-content">
      <div class="header-title">
        <div>
          <i class="bi bi-building" style="font-size: 2.5rem;"></i>
        </div>
        <div>
          <h1>Master List - All Employees</h1>
          <p>Madridejos Community College </p>
        </div>
      </div>
      <div class="header-actions">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
          <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
        </a>
        <a href="{{ route('master.list.add') }}" class="btn btn-primary">
          <i class="bi bi-plus-lg"></i> Add Employee
        </a>
        <button class="btn btn-secondary" onclick="window.print()">
          <i class="bi bi-printer"></i> Print
        </button>
      </div>
    </div>
  </div>

  <!-- Main Container -->
  <div class="container-main">
    <!-- Statistics -->
    <div class="stats-grid">
      <div class="stat-card instructors">
        <div class="stat-icon" style="color: var(--accent);">
          <i class="bi bi-briefcase-fill"></i>
        </div>
        <div class="stat-value">{{ $employees->filter(fn($e) => str_contains(strtolower($e->type ?? ''), 'instructor') || str_contains(strtolower($e->designation ?? ''), 'instructor'))->count() }}</div>
        <div class="stat-label">Instructors</div>
      </div>

      <div class="stat-card staff">
        <div class="stat-icon" style="color: var(--success);">
          <i class="bi bi-person-lines-fill"></i>
        </div>
        <div class="stat-value">{{ $employees->where('type', '=', 'Staff')->count() }}</div>
        <div class="stat-label">Staff Members</div>
      </div>

      <div class="stat-card utility">
        <div class="stat-icon" style="color: var(--warning);">
          <i class="bi bi-tools"></i>
        </div>
        <div class="stat-value">{{ $employees->where('type', '=', 'Utility')->count() }}</div>
        <div class="stat-label">Utility Workers</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="color: var(--info);">
          <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-value">{{ $employees->count() }}</div>
        <div class="stat-label">Total Employees</div>
      </div>
    </div>

    <!-- Department Color Legend -->
    <div style="background: white; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
      <span style="font-weight: 700; font-size: 0.9rem; color: #555;"><i class="bi bi-palette-fill"></i> Department Colors:</span>
      <span class="badge badge-dept-bsit" style="padding: 0.45rem 1rem;">● BSIT</span>
      <span class="badge badge-dept-bsba" style="padding: 0.45rem 1rem;">● BSBA</span>
      <span class="badge badge-dept-bshm" style="padding: 0.45rem 1rem;">● BSHM</span>
      <span class="badge badge-dept-education" style="padding: 0.45rem 1rem;">● Education</span>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
      <div class="filter-title">
        <i class="bi bi-funnel-fill"></i> Search & Filter
      </div>
      <form method="GET" action="{{ route('master.list') }}" id="filterForm">
        <div class="filter-row">
          <input type="text" class="form-control" placeholder="🔍 Search by name or email..." id="searchInput" name="search" value="{{ request('search', '') }}">
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
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Search
          </button>
        </div>
      </form>
    </div>

    <!-- Employees Table -->
    <div class="table-section">
      <div class="table-header">
        <h2>
          <i class="bi bi-list-check"></i> All Employees
        </h2>
        <span class="badge-count">{{ $employees->count() }} Total</span>
      </div>
      @if($employees->isEmpty())
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <p style="margin: 0; font-size: 1.1rem;">No employees found</p>
          <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">Try adjusting your search or filter criteria</p>
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
                @endphp
                <tr>
                  <td>{{ $idx + 1 }}</td>
                  <td>
                    <div class="employee-name">
                      <div class="avatar" style="background: linear-gradient(135deg, {{ ['#3498db', '#e74c3c', '#27ae60', '#f39c12'][($idx % 4)] }}, {{ ['#2980b9', '#c0392b', '#229954', '#d68910'][($idx % 4)] }});">
                        {{ substr($employee->employee_name ?? 'UN', 0, 1) }}{{ substr(explode(' ', $employee->employee_name ?? 'Unknown')[1] ?? '', 0, 1) }}
                      </div>
                      <div>
                        <span>{{ $employee->employee_name }}</span>
                        @if($isInstructor && $employee->email)
                          <br><small><a href="mailto:{{ $employee->email }}" style="color: var(--accent); text-decoration: none; font-size: 0.8rem;"><i class="bi bi-envelope-fill" style="font-size: 0.75rem;"></i> {{ $employee->email }}</a></small>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($employee->email)
                      <a href="mailto:{{ $employee->email }}" style="color: var(--accent); text-decoration: none;">{{ $employee->email }}</a>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    @if($employee->department)
                      <span class="badge {{ $deptBadgeClass }}">{{ $employee->department }}</span>
                    @else
                      <span class="text-muted">-</span>
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
                      <span class="text-muted">-</span>
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

  <!-- Footer -->
  <div class="page-footer">
    <p>Madridejos Community College | Employee Master List | © 2025</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function deleteEmployee(id, type) {
      Swal.fire({
        title: 'Delete Employee?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#95a5a6',
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

    // DevTools detection
    devtools.detect(function(status) {
      if (status) {
        document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
      }
    });

    // Auto-submit search input after user stops typing (400ms debounce)
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