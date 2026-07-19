<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Utility Timesheet</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
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
      background: linear-gradient(135deg, #545b62, #4e555b);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }

    .button-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 35px;
    }

    .calculated-value {
      background: linear-gradient(135deg, #e8f5e8, #d4edda);
      border: 2px solid #28a745;
      border-radius: 8px;
      padding: 15px;
      margin-top: 20px;
      text-align: center;
      font-weight: 600;
      color: #155724;
      box-shadow: 0 2px 10px rgba(40, 167, 69, 0.1);
    }

    .calculated-value .amount {
      font-size: 1.5rem;
      color: #28a745;
      text-shadow: 0 1px 2px rgba(40, 167, 69, 0.1);
    }

    .row .col-md-6 {
      padding-left: 10px;
      padding-right: 10px;
    }

    /* Enhanced form styling */
    .form-floating {
      position: relative;
      margin-bottom: 20px;
    }

    .form-floating > .form-control {
      height: calc(3.5rem + 2px);
      padding: 1rem 0.75rem;
    }

    .form-floating > label {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      padding: 1rem 0.75rem;
      pointer-events: none;
      border: 1px solid transparent;
      transform-origin: 0 0;
      transition: opacity .1s ease-in-out,transform .1s ease-in-out;
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
    }
  </style>
</head>
<body>
  <div class="main-content">
    <h2><i class="bi bi-pencil-square me-2"></i>Edit Utility Timesheet</h2>
    
    <form action="{{ route('utility.update', $timesheet->id) }}" method="POST">
      @csrf
      @method('PUT')

      <!-- Hidden fields for month, year, and period -->
      <input type="hidden" name="month" value="{{ $timesheet->month }}">
      <input type="hidden" name="year" value="{{ $timesheet->year }}">
      <input type="hidden" name="period" value="{{ $timesheet->period }}">
      
      <div class="mb-3">
        <label for="employee_name" class="form-label">Employee Name</label>
        <input type="text" class="form-control" id="employee_name" name="employee_name" value="{{ $timesheet->employee_name }}" required>
      </div>

      <div class="mb-3">
        <label for="designation" class="form-label">Designation</label>
        <select class="form-control" id="designation" name="designation" required>
          <option value="">Select Designation</option>
          <option value="instructor" {{ $timesheet->designation == 'instructor' ? 'selected' : '' }}>Instructor</option>
          <option value="utility" {{ $timesheet->designation == 'utility' ? 'selected' : '' }}>Utility</option>
          <option value="staff" {{ $timesheet->designation == 'staff' ? 'selected' : '' }}>Staff</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="prov_abr" class="form-label">Previous Absences</label>
        <input type="text" class="form-control" id="prov_abr" name="prov_abr" value="{{ $timesheet->prov_abr }}">
      </div>

      <div class="mb-3">
        <label for="days" class="form-label">Working Days (Hours per Day)</label>
        <div class="days-selector mb-2">
          <div class="row">
            @php
              $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
              $dayColumns = [
                  1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours',
                  4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'
              ];
            @endphp
            @foreach($days as $index => $day)
              @php($i = $index + 1)
              <div class="col-md-4 col-sm-6 col-12 mb-3">
                <label class="form-label" for="day{{ $i }}">{{ $day }} Hours</label>
                <input
                  type="number"
                  class="form-control day-hours"
                  id="day{{ $i }}"
                  name="days[{{ $i }}]"
                  min="0"
                  max="24"
                  step="0.25"
                  value="{{ $timesheet->{$dayColumns[$i]} ?? '' }}"
                  placeholder="0"
                >
              </div>
            @endforeach
          </div>
        </div>
        <small class="form-text text-muted">Enter the number of hours worked for each day (Monday–Saturday). Leave blank for 0.</small>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="total_days" class="form-label">Total Days</label>
            <input type="number" step="0.01" class="form-control" id="total_days" name="total_days" value="{{ $timesheet->total_days ?? 0 }}" min="0" readonly>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="rate_per_day" class="form-label">Rate per Day</label>
            <input type="number" step="0.01" class="form-control" id="rate_per_day" name="rate_per_day" value="{{ $timesheet->rate_per_day }}">
          </div>
        </div>
        <div class="col-md-6">
        <label for="deduction" class="form-label">Deduction Previous Cut Off</label>
        <input type="number" step="0.01" class="form-control" id="deduction" name="deduction" value="{{ $timesheet->deduction }}">
      </div>

      <!-- Total Honorarium Display -->
      <div class="calculated-value">
        <div class="mb-2">
          <i class="bi bi-calculator me-2"></i>Calculated Total Honorarium
        </div>
        <div class="amount" id="calculatedAmount">₱{{ number_format($timesheet->total_honorarium ?? 0, 2) }}</div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>Update Timesheet
        </button>
        <a href="{{ route('utility.index') }}" class="btn btn-secondary">
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
        title: 'Updating Utility Timesheet...',
        text: 'Please wait while we save your changes.',
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
      const totalDays = parseFloat(document.getElementById('total_days').value) || 0;
      const ratePerDay = parseFloat(document.getElementById('rate_per_day').value) || 0;
      const deduction = parseFloat(document.getElementById('deduction').value) || 0;
      
      const grossHonorarium = totalDays * ratePerDay;
      const totalHonorarium = grossHonorarium - deduction;
      const calculatedValue = totalHonorarium < 0 ? 0 : totalHonorarium;
      
      // Update calculated total display
      const calculatedAmount = document.getElementById('calculatedAmount');
      calculatedAmount.textContent = '₱' + calculatedValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Update total days based on inputs
    function updateTotalDays() {
        const dayInputs = document.querySelectorAll('.day-hours');
        const presentDays = Array.from(dayInputs).filter(input => (parseFloat(input.value) || 0) > 0).length;
        document.getElementById('total_days').value = presentDays;
    }

    // Handle days selection
    function updateDaysField() {
      const checkboxes = document.querySelectorAll('.day-checkbox:checked');
      const selectedDays = Array.from(checkboxes).map(cb => cb.value);
      document.getElementById('days').value = selectedDays.join(',');
    }

    // Add event listeners for auto-calculation
    document.querySelectorAll('.day-hours, #rate_per_day, #deduction').forEach(el => {
        el.addEventListener('input', () => {
            if (el.classList.contains('day-hours')) {
                updateTotalDays();
            }
            calculateTotal();
        });
    });

    // Calculate on page load
    document.addEventListener('DOMContentLoaded', function() {
      calculateTotal();
    });
  </script>

  <style>
    /* Custom SweetAlert2 styling to match theme */
    .swal-custom-popup {
      border-radius: 15px !important;
      border: 2px solid #dc3545 !important;
    }
    
    .swal-custom-title {
      color: #dc3545 !important;
      font-weight: 700 !important;
    }
    
    .swal-custom-content {
      color: #2c3e50 !important;
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
