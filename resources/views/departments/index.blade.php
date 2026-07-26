<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Department Management</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
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
      background: #dc3545; /* Red background */
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
    }

    .main-content {
      margin: 30px auto;
      padding: 20px;
      background: #fff; /* White content box */
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

    .btn-back {
      background-color: #0d6efd;
    }
    .btn-back:hover {
      background-color: #084298;
    }

    .add-btn {
      background-color: #198754;
    }
    .add-btn:hover {
      background-color: #146c43;
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

    .btn-view {
      background-color: #198754;
    }
    .btn-view:hover {
      background-color: #146c43;
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
      background-color: #f8d7da; /* Light red header */
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
      background-color: #ffe6e6; /* light red hover */
    }

    .badge {
      font-size: 0.75rem;
    }
    .sidebar, #sidebarMobileBtn, .sidebar-overlay { display: none !important; }
    .sidebar-shift { margin-left: 0 !important; }
  </style>
  @include('layouts.sidebar-styles')
</head>
<body>
  @include('layouts.sidebar')
  <div class="sidebar-shift">
  <div class="main-content">
    <!-- Back button with icon -->
    <a href="{{ route('dashboard') }}" class="icon-btn btn-back" title="Back to Dashboard">
      <i class="bi bi-arrow-left"></i>
    </a>

    <h2>Department Management</h2>

    <!-- Add button with icon -->
    <div class="float-end">
      <a href="{{ route('departments.create') }}" class="icon-btn add-btn" title="Add Department">
        <i class="bi bi-plus-lg"></i>
      </a>
    </div>

    <div class="table-container mt-3">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Department Name</th>
            <th>Code</th>
            <th>Description</th>
            <th>Employees</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($departments as $department)
          <tr>
            <td>{{ $department->id }}</td>
            <td class="left">{{ $department->name }}</td>
            <td><span class="badge bg-primary">{{ $department->code }}</span></td>
            <td class="left">{{ $department->description ?? 'N/A' }}</td>
            <td>{{ $department->employees->count() }}</td>
            <td>
              @if($department->is_active)
                <span class="badge bg-success">Active</span>
              @else
                <span class="badge bg-secondary">Inactive</span>
              @endif
            </td>
            <td>{{ $department->created_at->format('M d, Y') }}</td>
            <td>
              <a href="{{ route('departments.show', $department->id) }}" 
                 class="action-btn btn-view" title="View">
                <i class="bi bi-eye"></i>
              </a>
              <a href="{{ route('departments.edit', $department->id) }}" 
                 class="action-btn btn-edit" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('departments.destroy', $department->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-btn btn-delete" onclick="return confirm('Are you sure? This will affect all related employees.')" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center">No departments found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    // Check for success message from Laravel session
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: true,
        allowOutsideClick: false
      });
    @endif

    // Check for error message from Laravel session
    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545'
      });
    @endif
  </script>

<script>
// DevTools detection to make page blank if opened
devtools.detect(function(status){
  if(status){
    document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
  }
});
</script>
</div>
</body>
</html>

