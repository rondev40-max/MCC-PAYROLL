<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Staff Timesheet</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
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
      background: #e0f7ff; /* Soft sky blue background */
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      position: relative;
    }

    /* Add subtle pattern overlay */
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.4) 2px, transparent 2px),
                  radial-gradient(circle at 80% 30%, rgba(255,255,255,0.3) 2px, transparent 2px),
                  radial-gradient(circle at 40% 80%, rgba(255,255,255,0.2) 2px, transparent 2px);
      background-size: 60px 60px;
      pointer-events: none;
    }

    .main-content {
      margin: 30px auto;
      padding: 35px;
      background: #fff; /* White content box */
      border-radius: 15px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.2), 0 2px 8px rgba(220, 53, 69, 0.1);
      width: 95%;
      max-width: 650px;
      position: relative;
      border: 1px solid rgba(220, 53, 69, 0.1);
    }

    /* Add subtle red accent border */
    .main-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #dc3545, #ff6b7a, #dc3545);
      border-radius: 15px 15px 0 0;
    }

    .main-content h2 {
      color: #dc3545;
      margin-bottom: 30px;
      text-align: center;
      font-weight: 700;
      font-size: 1.8rem;
      text-shadow: 0 1px 2px rgba(220, 53, 69, 0.1);
    }

    .form-label {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 0.95rem;
      display: block;
    }

    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 12px 15px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
      font-size: 0.95rem;
      background-color: #fafafa;
    }

    .form-control:focus {
      border-color: #dc3545;
      box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.15);
      background-color: #fff;
      transform: translateY(-1px);
    }

    .form-control:hover {
      border-color: #dc3545;
      background-color: #fff;
    }

    .form-control[readonly] {
      background-color: #f8f9fa;
      border-color: #dee2e6;
      color: #6c757d;
    }

    .btn-primary {
      background: linear-gradient(135deg, #dc3545, #c82333);
      border: none;
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #a71d2a, #b21e2f);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }

    .btn-secondary {
      background: linear-gradient(135deg, #6c757d, #5a6268);
      border: none;
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
      background: linear-gradient(135deg, #5a6268, #495057);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }

    .button-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .main-content {
        margin: 15px;
        padding: 25px;
        width: calc(100% - 30px);
      }
      
      .button-group {
        flex-direction: column;
        gap: 10px;
      }
      
      .btn-primary, .btn-secondary {
        width: 100%;
      }
      
      .main-content h2 {
        font-size: 1.5rem;
      }
    }

    /* Add focus indicators for accessibility */
    .form-control:focus,
    .btn:focus {
      outline: 2px solid #dc3545;
      outline-offset: 2px;
    }

    /* Enhanced textarea styling */
    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }

    /* Input group enhancements */
    .mb-3 {
      position: relative;
    }

    /* Add subtle hover effects to form groups */
    .mb-3:hover .form-label {
      color: #dc3545;
      transition: color 0.3s ease;
    }
  </style>
</head>
<body>
  <div class="main-content">
    <h2><i class="bi bi-pencil-square me-2"></i>Edit Staff Timesheet</h2>
    
    <form action="{{ route('staff.update', $timesheet->id) }}" method="POST">
      @csrf
      @method('PUT')
      {{-- Hidden fields para maipasa ang kasalukuyang month at year --}}
      <input type="hidden" name="month" value="{{ $timesheet->month }}">
      <input type="hidden" name="year" value="{{ $timesheet->year }}">
      <input type="hidden" name="period" value="{{ $timesheet->period }}">

      
      <div class="mb-3">
        <label for="employee_name" class="form-label">Employee Name</label>
        <input type="text" class="form-control" id="employee_name" name="employee_name" value="{{ $timesheet->employee_name }}" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ $timesheet->email }}" placeholder="name@gmail.com">
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="category" class="form-label">Category</label>
          <select class="form-control" id="category" required>
            <option value="staff" selected>Staff</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="designation" class="form-label">Specific Role</label>
          <select class="form-control @error('designation') is-invalid @enderror" id="designation" name="designation" required>
            <option value="" disabled {{ empty($timesheet->designation) ? 'selected' : '' }}>Select Role</option>
            <option value="BSIT Staff" {{ $timesheet->designation == 'BSIT Staff' ? 'selected' : '' }}>BSIT Staff</option>
            <option value="Registrar Staff" {{ $timesheet->designation == 'Registrar Staff' ? 'selected' : '' }}>Registrar Staff</option>
            <option value="Clinic Staff" {{ $timesheet->designation == 'Clinic Staff' ? 'selected' : '' }}>Clinic Staff</option>
            <option value="Library Staff" {{ $timesheet->designation == 'Library Staff' ? 'selected' : '' }}>Library Staff</option>
            <option value="Admin Staff" {{ $timesheet->designation == 'Admin Staff' ? 'selected' : '' }}>Admin Staff</option>
            <option value="VP Office Staff" {{ $timesheet->designation == 'VP Office Staff' ? 'selected' : '' }}>VP Office Staff</option>
            <option value="SAS Staff" {{ $timesheet->designation == 'SAS Staff' ? 'selected' : '' }}>SAS Staff</option>
            <option value="IT Encoder" {{ $timesheet->designation == 'IT Encoder' ? 'selected' : '' }}>IT Encoder</option>
            <option value="Guidance Staff" {{ $timesheet->designation == 'Guidance Staff' ? 'selected' : '' }}>Guidance Staff</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label for="prov_abr" class="form-label">Previous Absences</label>
        <input type="text" class="form-control" id="prov_abr" name="prov_abr" value="{{ $timesheet->prov_abr }}">
      </div>

        <div class="mb-3">
            <label for="days" class="form-label">Working Days (Hours per Day)</label>
            <div class="days-selector mb-2 p-3">
                <div class="row">
                    @php($days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])
                    @php($weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'])
                    @foreach($days as $index => $day)
                        @php($i = $index + 1)
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label class="form-label" for="day{{ $i }}">{{ $day }}</label>
                            <input type="number" class="form-control day-hours" id="day{{ $i }}" name="days[{{ $i }}]"
                                min="0" max="24" step="0.25"
                                value="{{ old('days.'.$i, $timesheet->{$weekdayMap[$i]} ?? 8) }}"
                                placeholder="8">
                        </div>
                    @endforeach
                </div>
            </div>
            <small class="form-text text-muted">Enter the number of hours worked for each day. The system will count days with hours > 0, excluding Sundays and holidays.</small>
        </div>

      <div class="mb-3">
        <label for="details" class="form-label">Details for Inclusive Hours of Classes</label>
        <textarea class="form-control" id="details" name="details" rows="3">{{ $timesheet->details }}</textarea>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="total_days" class="form-label">Total Days (Calculated)</label>
            <input type="number" step="1" class="form-control" id="total_days" name="total_days" value="{{ old('total_days', $timesheet->total_days ?? 0) }}" readonly>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="rate_per_day" class="form-label">Rate per Day</label>
            <input type="number" step="0.01" class="form-control" id="rate_per_day" name="rate_per_day" value="{{ $timesheet->rate_per_day }}">
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="deduction" class="form-label">Deduction Previous Cut Off</label>
        <input type="number" step="0.01" class="form-control" id="deduction" name="deduction" value="{{ $timesheet->deduction }}">
      </div>

      <div class="calculated-value">
          <div class="total-display">
              <p class="label">Calculated Total Honorarium</p>
              <p class="amount" id="calculated-total">₱{{ number_format($timesheet->total_honorarium, 2) }}</p>
          </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>Update Timesheet
        </button>
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">
          <i class="bi bi-x-lg me-1"></i>Cancel
        </a>
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
        confirmButtonColor: '#dc3545',
        customClass: {
          popup: 'swal-custom-popup',
          title: 'swal-custom-title',
          content: 'swal-custom-content',
          confirmButton: 'swal-custom-button'
        }
      });
    @endif

    // Form submission with loading state
    document.querySelector('form').addEventListener('submit', function(e) {
      const submitBtn = document.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      // Show loading state
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
      submitBtn.disabled = true;
      
      // Show loading alert
      Swal.fire({
        title: 'Updating Staff Timesheet...',
        text: 'Please wait while we update the timesheet.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        },
        customClass: {
          popup: 'swal-custom-popup',
          title: 'swal-custom-title',
          content: 'swal-custom-content'
        }
      });
      
      // Reset button after a delay (in case of validation errors)
      setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }, 5000);
    });

    // Auto-calculate total honorarium
    function calculateTotal() {
      let totalDays = 0;
      document.querySelectorAll('.day-hours').forEach(input => {
        if (parseFloat(input.value) > 0) {
          totalDays++;
        }
      });

      const prevAbs = parseFloat(document.getElementById('prov_abr').value) || 0;
      const ratePerDay = parseFloat(document.getElementById('rate_per_day').value) || 0;
      const deduction = parseFloat(document.getElementById('deduction').value) || 0;
      
      const finalDays = totalDays - prevAbs;
      document.getElementById('total_days').value = finalDays;

      const totalHonorarium = (finalDays * ratePerDay) - deduction;
      const calculatedValue = totalHonorarium < 0 ? 0 : totalHonorarium;
      
      // Update the display
      document.getElementById('calculated-total').textContent = '₱' + calculatedValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Add event listeners for auto-calculation
    document.querySelectorAll('.day-hours').forEach(input => {
      input.addEventListener('input', calculateTotal);
    });
    document.getElementById('prov_abr').addEventListener('input', calculateTotal);
    document.getElementById('rate_per_day').addEventListener('input', calculateTotal);
    document.getElementById('deduction').addEventListener('input', calculateTotal);

    // Calculate on page load
    document.addEventListener('DOMContentLoaded', calculateTotal);
  </script>

  <style>
    /* Custom SweetAlert2 styling to match theme */
    .swal-custom-popup {
      border-radius: 15px !important;
      border: 2px solid #dc3545 !important;
      box-shadow: 0 15px 50px rgba(220, 53, 69, 0.3) !important;
    }
    
    .swal-custom-title {
      color: #dc3545 !important;
      font-weight: 700 !important;
      font-size: 1.5rem !important;
    }
    
    .swal-custom-content {
      color: #2c3e50 !important;
      font-size: 1rem !important;
    }
    
    .swal-custom-button {
      background: linear-gradient(135deg, #dc3545, #c82333) !important;
      border: none !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
      padding: 12px 30px !important;
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
      transition: all 0.3s ease !important;
    }
    
    .swal-custom-button:hover {
      background: linear-gradient(135deg, #a71d2a, #b21e2f) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4) !important;
    }
  </style>
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
