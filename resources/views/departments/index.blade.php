<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Department Management</title>
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background: #f1f5f9; /* Slate 100 */
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      position: relative;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
      z-index: 0;
    }

    .main-content {
      margin: 40px auto;
      padding: 40px;
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.05);
      width: 90%;
      max-width: 90%;
      position: relative;
      border: 1px solid #e2e8f0;
      z-index: 1;
      animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .header-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .main-content h2 {
      font-size: 1.75rem;
      margin: 0;
      color: #1e293b;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .main-content h2 i {
      color: #3b82f6; /* Blue 500 */
    }

    /* Icon Buttons */
    .btn-back {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      font-size: 18px;
      background-color: #f1f5f9;
      color: #475569;
      border: 1px solid #cbd5e1;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .btn-back:hover {
      background-color: #e2e8f0;
      color: #1e293b;
      transform: translateY(-2px);
    }

    .add-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      border-radius: 10px;
      background: #3b82f6;
      color: white;
      font-weight: 600;
      text-decoration: none;
      border: none;
      transition: all 0.2s ease;
    }

    .add-btn:hover {
      background: #2563eb;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
      color: white;
    }

    /* Action Buttons */
    .action-group {
      display: flex;
      gap: 8px;
      justify-content: center;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      font-size: 14px;
      color: #fff;
      border: none;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .btn-view {
      background-color: #10b981; /* Emerald 500 */
    }
    .btn-view:hover {
      background-color: #059669;
      color: white;
    }

    .btn-edit {
      background-color: #3b82f6; /* Blue 500 */
    }
    .btn-edit:hover {
      background-color: #2563eb;
      color: white;
    }

    .btn-delete {
      background-color: #ef4444; /* Red 500 */
    }
    .btn-delete:hover {
      background-color: #dc2626;
      color: white;
    }

    /* Table Styling */
    .table-container {
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      overflow: hidden;
      background: white;
    }

    .table {
      margin-bottom: 0;
      vertical-align: middle;
    }

    .table thead th {
      background-color: #f8fafc;
      color: #475569;
      font-weight: 600;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e2e8f0;
      padding: 15px;
    }

    .table tbody td {
      padding: 15px;
      color: #334155;
      font-size: 0.95rem;
      border-bottom: 1px solid #e2e8f0;
    }

    .table tbody tr:hover {
      background-color: #f8fafc;
    }

    .table tbody tr:last-child td {
      border-bottom: none;
    }

    .badge-code {
      background-color: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      padding: 6px 10px;
      border-radius: 6px;
      font-weight: 600;
    }

    .badge-status-active {
      background-color: #f0fdf4;
      color: #166534;
      border: 1px solid #bbf7d0;
      padding: 6px 10px;
      border-radius: 6px;
      font-weight: 500;
    }

    .badge-status-inactive {
      background-color: #f8fafc;
      color: #475569;
      border: 1px solid #e2e8f0;
      padding: 6px 10px;
      border-radius: 6px;
      font-weight: 500;
    }

  </style>
</head>
<body>
  <div class="main-content">
    
    <div class="header-section">
      <div class="header-left">
        <a href="{{ route('dashboard') }}" class="btn-back" title="Back to Dashboard">
          <i class="bi bi-arrow-left"></i>
        </a>
        <h2><i class="bi bi-building"></i> Department Management</h2>
      </div>
      <a href="{{ route('departments.create') }}" class="add-btn" title="Add Department">
        <i class="bi bi-plus-lg"></i> Add Department
      </a>
    </div>

    <div class="table-container">
      <table class="table table-hover">
        <thead>
          <tr>
            <th class="text-center">ID</th>
            <th>Department Name</th>
            <th class="text-center">Code</th>
            <th>Description</th>
            <th class="text-center">Employees</th>
            <th class="text-center">Status</th>
            <th class="text-center">Created</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($departments as $department)
          <tr>
            <td class="text-center text-muted">#{{ $department->id }}</td>
            <td class="fw-medium">{{ $department->name }}</td>
            <td class="text-center">
              <span class="badge-code">{{ $department->code }}</span>
            </td>
            <td class="text-muted">{{ Str::limit($department->description ?? 'N/A', 30) }}</td>
            <td class="text-center fw-medium">{{ $department->employees->count() }}</td>
            <td class="text-center">
              @if($department->is_active)
                <span class="badge-status-active"><i class="bi bi-check-circle me-1"></i>Active</span>
              @else
                <span class="badge-status-inactive"><i class="bi bi-x-circle me-1"></i>Inactive</span>
              @endif
            </td>
            <td class="text-center text-muted">{{ $department->created_at->format('M d, Y') }}</td>
            <td>
              <div class="action-group">
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
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="bi bi-building-x fs-1 d-block mb-2"></i>
              No departments found in the system.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3b82f6',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: true,
        allowOutsideClick: false,
        customClass: {
          popup: 'rounded-4'
        }
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#ef4444',
        customClass: {
          popup: 'rounded-4'
        }
      });
    @endif
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
