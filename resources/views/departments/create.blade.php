<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Department</title>
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
      max-width: 800px;
      position: relative;
      border: 1px solid #e2e8f0;
      z-index: 1;
      animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .main-content h2 {
      color: #1e293b;
      margin-bottom: 30px;
      font-weight: 700;
      font-size: 1.75rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .main-content h2 i {
      color: #3b82f6; /* Blue 500 */
    }

    /* Floating Labels Styling */
    .form-floating > .form-control,
    .form-floating > .form-select {
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      background-color: #f8fafc;
      transition: all 0.2s ease;
    }

    .form-floating > .form-control:focus,
    .form-floating > .form-select:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
      background-color: #ffffff;
    }

    .form-floating > .form-control.is-invalid,
    .form-floating > .form-select.is-invalid {
      border-color: #ef4444;
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .form-floating > label {
      color: #64748b;
      font-weight: 500;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label,
    .form-floating > .form-select ~ label {
      color: #3b82f6;
      font-weight: 600;
    }

    /* Buttons */
    .btn-primary {
      background: #3b82f6;
      border: none;
      padding: 12px 28px;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.2s ease;
    }

    .btn-primary:hover {
      background: #2563eb;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .btn-secondary {
      background: transparent;
      border: 1px solid #cbd5e1;
      color: #64748b;
      padding: 12px 28px;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.2s ease;
    }

    .btn-secondary:hover {
      background: #f1f5f9;
      color: #334155;
      border-color: #94a3b8;
    }

    .button-group {
      display: flex;
      gap: 15px;
      margin-top: 35px;
      justify-content: flex-end;
    }

    @media (max-width: 768px) {
      .button-group {
        flex-direction: column-reverse;
      }
      .btn-primary, .btn-secondary {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <div class="main-content">
    <h2><i class="bi bi-building-add"></i> Add New Department</h2>

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
            <h6 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please check your inputs</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('departments.store') }}" method="POST">
      @csrf
      
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="form-floating">
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name') }}" placeholder="Department of Computer Science" required>
            <label for="name">Department Name <span class="text-danger">*</span></label>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-floating">
            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                   id="code" name="code" value="{{ old('code') }}" placeholder="BSIT" maxlength="10" required>
            <label for="code">Department Code <span class="text-danger">*</span></label>
          </div>
          <small class="text-muted ms-1 mt-1 d-block" style="font-size: 0.8rem;">Max 10 characters (e.g., BSIT, BSBA)</small>
        </div>
      </div>

      <div class="mb-4">
        <div class="form-floating">
          <textarea class="form-control @error('description') is-invalid @enderror" 
                    id="description" name="description" placeholder="Description..." style="height: 100px">{{ old('description') }}</textarea>
          <label for="description">Description</label>
        </div>
      </div>

      <div class="mb-4">
        <div class="form-floating">
          <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
          </select>
          <label for="is_active">Status</label>
        </div>
      </div>

      <div class="button-group">
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-2"></i>Create Department
        </button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  devtools.detect(function(status){
    if(status){
      document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
    }
  });
  </script>
</body>
</html>
