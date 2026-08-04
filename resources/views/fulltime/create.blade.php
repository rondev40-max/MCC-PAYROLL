<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Fulltime Timesheet</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
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
      background: #add8e6; /* Light blue background */
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
      background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 2px, transparent 2px);
      background-size: 50px 50px;
      pointer-events: none;
    }

    .main-content {
      margin: 30px auto;
      padding: 35px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.2), 0 2px 8px rgba(220, 53, 69, 0.1);
      width: 95%;
      max-width: 700px;
      position: relative;
      border: 1px solid rgba(220, 53, 69, 0.1);
      animation: slideInUp 0.6s ease-out;
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

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
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
      margin-bottom: 6px;
      font-size: 0.9rem;
      display: block;
    }

    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 6px;
      padding: 8px 12px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      background-color: #fafafa;
      height: auto;
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

    .btn-primary {
      background: linear-gradient(135deg, #dc3545, #c82333);
      border: none;
      padding: 10px 25px;
      font-weight: 600;
      border-radius: 6px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 0.85rem;
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #a71d2a, #b21e2f);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }

    .btn-primary:active {
      transform: translateY(0);
    }

    .btn-secondary {
      background: linear-gradient(135deg, #6c757d, #5a6268);
      border: none;
      padding: 10px 25px;
      font-weight: 600;
      border-radius: 6px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 0.85rem;
      box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
      background: linear-gradient(135deg, #545b62, #4e555b);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }

    .btn-secondary:active {
      transform: translateY(0);
    }

    .button-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 35px;
      flex-wrap: wrap;
    }

    /* Enhanced row styling */
    .row .col-md-6 {
      padding-left: 10px;
      padding-right: 10px;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
      .main-content {
        margin: 15px;
        padding: 25px;
        width: calc(100% - 30px);
      }
      
      .button-group {
        flex-direction: column;
        align-items: center;
      }
      
      .btn-primary, .btn-secondary {
        width: 100%;
        max-width: 200px;
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
      min-height: 80px;
      padding: 8px 12px;
    }

    /* Input group enhancements */
    .mb-3 {
      position: relative;
      margin-bottom: 1rem;
    }

    /* Add subtle hover effects to form groups */
    .mb-3:hover .form-label {
      color: #dc3545;
      transition: color 0.3s ease;
    }

    /* Days selector styling */
    .days-selector {
      background: #f8f9fa;
      border-radius: 6px;
      padding: 12px;
      border: 2px solid #e9ecef;
      transition: border-color 0.3s ease;
    }

    .days-selector:hover {
      border-color: #dc3545;
    }

    .form-check {
      margin-bottom: 0;
    }

    .form-check-input {
      margin-top: 0.25rem;
    }

    .form-check-label {
      font-size: 0.9rem;
      color: #495057;
      cursor: pointer;
    }

    .form-check-input:checked + .form-check-label {
      color: #dc3545;
      font-weight: 600;
    }

    .form-check-input:checked {
      background-color: #dc3545;
      border-color: #dc3545;
    }

    /* Total honorarium display */
    .total-display {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      border: 2px solid #dc3545;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
      margin-top: 10px;
    }

    .total-display .amount {
      font-size: 1.5rem;
      font-weight: 700;
      color: #dc3545;
      margin: 0;
    }

    .total-display .label {
      font-size: 0.9rem;
      color: #6c757d;
      margin: 0;
    }
  </style>
</head>
<body>
  <div class="main-content">
    <h2><i class="bi bi-plus-circle me-2"></i>Add Fulltime Timesheet</h2>
    
    <form action="{{ route('fulltime.store') }}" method="POST">
      @csrf
      
      <div class="mb-3">
        <label for="employee_name" class="form-label">Employee Name</label>
        <input type="text" class="form-control" id="employee_name" name="employee_name" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="name@gmail.com">
      </div>

      <div class="mb-3">
        <label for="designation" class="form-label">Designation</label>
        <select class="form-control" id="designation" name="designation" required>
          <option value="">Select Designation</option>
          <option value="instructor">Instructor</option>
          <option value="utility">Utility</option>
          <option value="staff">Staff</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="prev_abs" class="form-label">Previous Absences (Hours)</label>
        <input type="number" class="form-control" id="prev_abs" name="prev_abs" value="0" step="0.25">
      </div>

      <div class="mb-3">
        <label for="department" class="form-label">Department</label>
        <select class="form-control" id="department" name="department" required>
            <option value="" disabled selected>Select Department</option>
            @php
                // Combine preset options with dynamic ones, ensuring no duplicates
                $presetCodes = ['BSIT', 'BSBA', 'BSHM', 'BSED', 'BEED'];
                $existingCodes = array_map('strtoupper', $departments->pluck('code')->all());
                $allCodes = array_unique(array_merge($presetCodes, $existingCodes));
            @endphp

            {{-- Loop through all unique codes to create options --}}
            @foreach($allCodes as $code)
                <option value="{{ $code }}" {{ old('department') == $code ? 'selected' : '' }}>
                    {{ $code }}
                </option>
            @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label for="days" class="form-label">Working Days (Hours per Day)</label>
        <div class="days-selector mb-2">
          <div class="row">
            @php($days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])
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
                  value=""
                  placeholder="0"
                  oninput="this.value = this.value && Math.floor(this.value) == this.value ? parseInt(this.value) : this.value"
                >
              </div>
            @endforeach
          </div>
        </div>
        <small class="form-text text-muted">Enter the number of hours worked for each day (Monday–Saturday). Leave blank for 0.</small>
      </div>

      <div class="mb-3">
        <label for="details" class="form-label">Details for Inclusive Hours of Classes</label>
        <textarea class="form-control" id="details" name="details" rows="3"></textarea>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="total_hour" class="form-label">Total Hours</label>
            <input type="number" step="0.01" class="form-control" id="total_hour" name="total_hour" 
                   oninput="this.value = this.value && Math.floor(this.value) == this.value ? parseInt(this.value) : this.value">
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="rate_per_hour" class="form-label">Rate per Hour</label>
            <input type="number" step="0.01" class="form-control" id="rate_per_hour" name="rate_per_hour">
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="deduction" class="form-label">Deduction Previous Cut Off</label>
        <input type="number" step="0.01" class="form-control" id="deduction" name="deduction" value="0">
      </div>

      <!-- Total Honorarium Display -->
      <div class="total-display">
        <p class="label">Calculated Total Honorarium</p>
        <p class="amount" id="calculated-total">₱0.00</p>
      </div>

      <div class="button-group">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>Add Timesheet
        </button>
        <a href="{{ route('fulltime.index') }}" class="btn btn-secondary">
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
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Creating...';
      submitBtn.disabled = true;
      
      // Show loading alert
      Swal.fire({
        title: 'Creating Part-time Timesheet...',
        text: 'Please wait while we add the new timesheet.',
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

    // Handle per-day hours inputs -> compute total hours and update display
    function getTotalHoursFromDaysInputs() {
      const inputs = document.querySelectorAll('.day-hours');
      let total = 0;
      inputs.forEach(inp => {
        const v = parseFloat(inp.value);
        if (!isNaN(v) && v > 0) total += v;
      });
      return total;
    }

    // Recalculate total hours when day inputs change
    document.querySelectorAll('.day-hours').forEach(input => {
      input.addEventListener('input', () => {
        // Update total_hour input with the sum of day hours
        const total = getTotalHoursFromDaysInputs();
        const totalHourEl = document.getElementById('total_hour');
        if (totalHourEl) totalHourEl.value = total.toFixed(2);
        calculateTotal();
      });
    });

    // Auto-calculate total honorarium
    function calculateTotal() {
      const totalHour = parseFloat(document.getElementById('total_hour').value) || 0;
      const ratePerHour = parseFloat(document.getElementById('rate_per_hour').value) || 0;
      const deduction = parseFloat(document.getElementById('deduction').value) || 0;
      
      const totalHonorarium = (totalHour * ratePerHour) - deduction;
      const calculatedValue = totalHonorarium < 0 ? 0 : totalHonorarium;
      
      // Update the display
      document.getElementById('calculated-total').textContent = '₱' + calculatedValue.toFixed(2);
      
      // Add visual feedback
      const display = document.querySelector('.total-display');
      if (calculatedValue > 0) {
        display.style.borderColor = '#198754';
        display.querySelector('.amount').style.color = '#198754';
      } else {
        display.style.borderColor = '#dc3545';
        display.querySelector('.amount').style.color = '#dc3545';
      }
    }

    // Add event listeners for auto-calculation
    document.getElementById('total_hour').addEventListener('input', calculateTotal);
    document.getElementById('rate_per_hour').addEventListener('input', calculateTotal);
    document.getElementById('deduction').addEventListener('input', calculateTotal);

    // Initial calculation
    calculateTotal();


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
