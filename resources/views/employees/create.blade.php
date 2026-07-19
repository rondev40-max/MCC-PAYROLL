<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Employee</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <!-- Bootstrap 5 CSS -->
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
      max-width: 600px;
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

    .form-label {
      font-weight: 600;
      color: #333;
    }

    .form-control, .form-select {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
    }

    .form-control:focus, .form-select:focus {
      border-color: #dc3545;
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .btn-primary {
      background-color: #dc3545;
      border-color: #dc3545;
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 8px;
    }

    .btn-primary:hover {
      background-color: #c82333;
      border-color: #bd2130;
    }

    .btn-secondary {
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 8px;
    }

    .invalid-feedback {
      display: block;
    }
  </style>
</head>
<body>

  <div class="main-content">
    <!-- Back button -->
    <a href="{{ route('dashboard') }}" class="icon-btn btn-back" title="Back to Dashboard">
      <i class="bi bi-arrow-left"></i>
    </a>

    <h2>Add New Employee</h2>

    <form action="{{ route('employees.store') }}" method="POST">
      @csrf
      
      <div class="mb-3">
        <label for="name" class="form-label">Employee Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" 
               id="name" name="name" value="{{ old('name') }}" required>
        @error('name')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" 
               id="email" name="email" value="{{ old('email') }}" required>
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="type" class="form-label">Employment Type <span class="text-danger">*</span></label>
          <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
            <option value="">Select Employment Type</option>
            <option value="Full-time Instructor" {{ old('type') == 'Full-time Instructor' ? 'selected' : '' }}>Full-time Instructor</option>
            <option value="Part-time Instructor" {{ old('type') == 'Part-time Instructor' ? 'selected' : '' }}>Part-time Instructor</option>
            <option value="Staff" {{ old('type') == 'Staff' ? 'selected' : '' }}>Staff</option>
            <option value="Utility" {{ old('type') == 'Utility' ? 'selected' : '' }}>Utility</option>
          </select>
          @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="department_id" class="form-label">Department</label>
          <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
            <option value="">Select Department</option>
            @foreach($departments as $department)
              <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                {{ $department->name }}
              </option>
            @endforeach
          </select>
          @error('department_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="mb-4">
        <label for="hourly_salary" class="form-label">Hourly Salary <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text">₱</span>
          <input type="number" step="0.01" class="form-control @error('hourly_salary') is-invalid @enderror" 
                 id="hourly_salary" name="hourly_salary" value="{{ old('hourly_salary') }}" required>
        </div>
        @error('hourly_salary')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-2"></i>Add Employee
        </button>
      </div>
    </form>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
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
</body>
</html>

