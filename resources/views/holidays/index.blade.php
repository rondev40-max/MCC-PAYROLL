<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Manage Holidays</title>
  
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* Styling copied and adapted from your index.blade.php for consistency */
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      position: relative;
    }

    .main-content {
      margin: 30px auto;
      padding: 30px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(255,255,255,0.2);
      width: 95%;
      max-width: 800px; /* Mas maliit na max-width para sa management page */
      position: relative;
      z-index: 2;
      border: 1px solid rgba(255,255,255,0.3);
    }
    
    .main-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #667eea, #764ba2);
      border-radius: 20px 20px 0 0;
    }
    
    .main-content h2 {
      font-size: 28px;
      color: #2d3748;
      font-weight: 700;
      margin-bottom: 25px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .icon-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      border-radius: 15px;
      font-size: 18px;
      color: #fff;
      border: none;
      cursor: pointer;
      margin-right: 10px;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      position: relative;
      overflow: hidden;
    }
    
    .btn-back {
      background: linear-gradient(135deg, #667eea, #764ba2);
    }
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .table-container {
      overflow-x: auto;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      background: white;
      border: 1px solid rgba(102, 126, 234, 0.1);
      margin-top: 20px;
    }
    
    table thead {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
    }
    
    th, td {
      padding: 10px;
      text-align: left;
      border: 1px solid #ddd;
    }
  </style>
</head>
<body>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('fulltime.index') }}" class="icon-btn btn-back me-3" title="Back to Timesheet">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="mb-0">Manage Holidays</h2>
        </div>
    </div>

    {{-- Session Messages (Success/Error) --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-octagon-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Validation Error!</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-5 p-4 border rounded bg-light shadow-sm">
        <h5 class="mb-3 text-success"><i class="bi bi-plus-circle me-1"></i> Add New Holiday</h5>
        <form action="{{ route('holidays.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-5">
                <label for="date" class="form-label fw-bold">Date</label>
                <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date') }}" required>
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5">
                <label for="name" class="form-label fw-bold">Holiday Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., All Saints' Day">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100" style="height: 38px;">
                    <i class="bi bi-calendar-check me-1"></i> Save
                </button>
            </div>
        </form>
    </div>

    <h5 class="mt-4 mb-3 text-info"><i class="bi bi-list-stars me-1"></i> Existing Holidays</h5>
    <div class="table-container">
        <table class="table table-striped table-hover align-middle">
            <thead class="text-white">
                <tr>
                    <th style="width: 30%;">Date</th>
                    <th style="width: 50%;">Holiday Name</th>
                    <th style="width: 20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $holiday)
                <tr>
                    <td>
                        <strong>{{ \Carbon\Carbon::parse($holiday->date)->format('F d, Y') }}</strong>
                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($holiday->date)->format('(l)') }}</small>
                    </td>
                    <td>{{ $holiday->name }}</td>
                    <td>
                        <form id="delete-form-{{ $holiday->id }}" action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $holiday->id }}">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No holidays set yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- I-load ang Bootstrap JS at SweetAlert2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const holidayId = this.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this holiday? It will be immediately removed from all timesheets.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); } 
                    });
                    document.getElementById('delete-form-' + holidayId).submit();
                }
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
<script>
// DevTools detection copied from your index.blade.php
devtools.detect(function(status){
  if(status){
    document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
  }
});
</script>
</body>
</html>