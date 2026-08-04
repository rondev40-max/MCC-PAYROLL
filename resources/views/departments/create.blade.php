<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Department</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
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
      max-width: 800px;
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
    <a href="{{ route('departments.index') }}" class="icon-btn btn-back" title="Back to Departments">
      <i class="bi bi-arrow-left"></i>
    </a>

    <h2>Add New Department</h2>

    <form action="{{ route('departments.store') }}" method="POST">
      @csrf
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" 
                 id="name" name="name" value="{{ old('name') }}" required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="code" class="form-label">Department Code <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('code') is-invalid @enderror" 
                 id="code" name="code" value="{{ old('code') }}" maxlength="10" required>
          @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="form-text text-muted">Max 10 characters (e.g., BSIT, BSBA)</small>
        </div>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" 
                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-4">
        <label for="is_active" class="form-label">Status</label>
        <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
          <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
          <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('is_active')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-2"></i>Create Department
        </button>
      </div>
    </form>
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

