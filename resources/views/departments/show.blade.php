<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Department Details</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <!-- Bootstrap 5 CSS -->
   <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #dc3545;
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
    }

    .main-content {
      margin: 30px auto;
      padding: 30px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 95%;
      max-width: 1200px;
    }

    .main-content h2 {
      font-size: 24px;
      margin-bottom: 30px;
      color: #333;
      text-align: center;
    }

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

    .btn-back {
      background-color: #0d6efd;
    }
    .btn-back:hover {
      background-color: #084298;
    }

    .info-card {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 30px;
    }

    .info-item {
      margin-bottom: 15px;
    }

    .info-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 5px;
    }

    .info-value {
      color: #212529;
      font-size: 16px;
    }

    .badge {
      font-size: 0.875rem;
    }

    .stats-card {
      background: linear-gradient(135deg, #dc3545, #c82333);
      color: white;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
    }

    .stats-number {
      font-size: 2rem;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .stats-label {
      font-size: 0.9rem;
      opacity: 0.9;
    }

    .table-container {
      overflow-x: auto;
      margin-top: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    table thead {
      background-color: #f8d7da;
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

    td.left {
      text-align: left;
    }

    tr:nth-child(even) td {
      background-color: #f9f9f9;
    }

    tr:hover td {
      background-color: #ffe6e6;
    }
  </style>
</head>
<body>

  <div class="main-content">
    <!-- Back button -->
    <a href="{{ route('departments.index') }}" class="icon-btn btn-back" title="Back to Departments">
      <i class="bi bi-arrow-left"></i>
    </a>

    <h2>Department Details</h2>

    <!-- Department Information -->
    <div class="row">
      <div class="col-md-8">
        <div class="info-card">
          <div class="row">
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">Department Name</div>
                <div class="info-value">{{ $department->name }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">Department Code</div>
                <div class="info-value">
                  <span class="badge bg-primary">{{ $department->code }}</span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">
                  @if($department->is_active)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </div>
              </div>
              <div class="info-item">
                <div class="info-label">Created Date</div>
                <div class="info-value">{{ $department->created_at->format('F d, Y') }}</div>
              </div>
            </div>
          </div>
          @if($department->description)
          <div class="info-item">
            <div class="info-label">Description</div>
            <div class="info-value">{{ $department->description }}</div>
          </div>
          @endif
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <div class="stats-number">{{ $department->employees->count() }}</div>
          <div class="stats-label">Total Employees</div>
        </div>
      </div>
    </div>

    <!-- Present Today -->
    <h4 class="mb-3">Present on {{ $todayLabel }}</h4>
    @if(isset($presentToday) && $presentToday->count() > 0)
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Designation</th>
            <th>Type</th>
          </tr>
        </thead>
        <tbody>
          @foreach($presentToday as $p)
          <tr>
            <td class="left">{{ $p['name'] }}</td>
            <td>{{ $p['designation'] }}</td>
            <td>{{ $p['type'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
      <div class="alert alert-warning mt-2">
        <i class="bi bi-exclamation-circle me-2"></i>
        No present records for today yet.
      </div>
    @endif

    <!-- Employees List -->
    @if($department->employees->count() > 0)
    <h4 class="mb-3 mt-4">Employees in this Department</h4>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Position</th>
            <th>Hourly Salary</th>
            <th>Joined Date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($department->employees as $employee)
          <tr>
            <td class="left">{{ $employee->name }}</td>
            <td class="left">{{ $employee->email }}</td>
            <td>{{ $employee->position }}</td>
            <td>₱{{ number_format($employee->hourly_salary, 2) }}</td>
            <td>{{ $employee->created_at->format('M d, Y') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="alert alert-info mt-4">
      <i class="bi bi-info-circle me-2"></i>
      No employees assigned to this department yet.
    </div>
    @endif

    <!-- Timesheet Statistics -->
    <div class="row mt-4">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-primary">{{ $department->fulltimeTimesheets->count() }}</h5>
            <p class="card-text">Fulltime Timesheets</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-success">{{ $department->parttimeTimesheets->count() }}</h5>
            <p class="card-text">Parttime Timesheets</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-warning">{{ $department->staffTimesheets->count() }}</h5>
            <p class="card-text">Staff Timesheets</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-info">{{ $department->utilityTimesheets->count() }}</h5>
            <p class="card-text">Utility Timesheets</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mt-4">
      <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2"></i>Edit Department
      </a>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

