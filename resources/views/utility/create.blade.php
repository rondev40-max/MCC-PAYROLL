<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Utility Timesheet</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
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
    }

    .main-content {
      margin: 30px auto;
      padding: 30px;
      background: #fff; /* White content box */
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 95%;
      max-width: 600px;
    }

    .main-content h2 {
      color: #dc3545;
      margin-bottom: 25px;
      text-align: center;
      font-weight: 600;
    }

    .form-label {
      color: #333;
      font-weight: 500;
      margin-bottom: 5px;
    }

    .form-control,
    .form-select {
      border: 2px solid #e9ecef;
      border-radius: 6px;
      padding: 10px 12px;
      margin-bottom: 15px;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #dc3545;
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .btn-primary {
      background-color: #dc3545;
      border-color: #dc3545;
      padding: 10px 25px;
      font-weight: 500;
    }

    .btn-primary:hover {
      background-color: #a71d2a;
      border-color: #a71d2a;
    }

    .btn-secondary {
      background-color: #6c757d;
      border-color: #6c757d;
      padding: 10px 25px;
      font-weight: 500;
    }

    .btn-secondary:hover {
      background-color: #545b62;
      border-color: #545b62;
    }

    .button-group {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 25px;
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

    /* Enhanced textarea styling */
    textarea.form-control {
      resize: vertical;
      min-height: 80px;
      padding: 8px 12px;
    }

    /* FIXED: Error message styling */
    .invalid-feedback {
      display: block;
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: -10px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="main-content">
    <h2><i class="bi bi-plus-circle me-2"></i>Add Utility Timesheet</h2>
    
    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('utility.store') }}" method="POST">
      @csrf

      <!-- Hidden fields for month, year, and period -->
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="year" value="{{ $year }}">
      <input type="hidden" name="period" value="{{ $period }}">
      
      <!-- FIXED: Changed from text input to dropdown -->
      <div class="mb-3">
        <label for="employee_name" class="form-label">Select Utility Worker *</label>
        <select class="form-select @error('employee_name') is-invalid @enderror" 
                id="employee_name" 
                name="employee_name" 
                required>
          <option value="">-- Choose an Employee --</option>
          @forelse($utilityEmployees as $id => $name)
            <option value="{{ $id }}" {{ old('employee_name') == $id ? 'selected' : '' }}>
              {{ $name }}
            </option>
          @empty
            <option value="" disabled>No utility workers found in the system</option>
          @endforelse

        </select>
        @error('employee_name')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">
          If the employee is not listed, please add them to the Master List first.
        </small>
      </div>

      <div class="mb-3">
        <label for="designation" class="form-label">Designation *</label>
        <select class="form-control @error('designation') is-invalid @enderror" 
                id="designation" 
                name="designation" 
                required>
          <option value="">Select Designation</option>
          <option value="instructor" {{ old('designation') == 'instructor' ? 'selected' : '' }}>Instructor</option>
          <option value="utility" {{ old('designation') == 'utility' ? 'selected' : '' }}>Utility</option>
          <option value="staff" {{ old('designation') == 'staff' ? 'selected' : '' }}>Staff</option>
        </select>
        @error('designation')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-3">
        <label for="prov_abr" class="form-label">Previous Absences (Days)</label>
        <input type="number" 
               step="0.01" 
               class="form-control @error('prov_abr') is-invalid @enderror" 
               id="prov_abr" 
               name="prov_abr"
               value="{{ old('prov_abr', 0) }}"
               min="0">
        @error('prov_abr')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
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
                  value="{{ old('days.' . $i, 0) }}"
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
            <input type="number" step="0.01" class="form-control" id="total_days" name="total_days" value="0" min="0" readonly>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="rate_per_day" class="form-label">Rate per Day (₱)</label>
            <input type="number" 
                   step="0.01" 
                   class="form-control @error('rate_per_day') is-invalid @enderror" 
                   id="rate_per_day" 
                   name="rate_per_day" 
                   value="{{ old('rate_per_day', '0.00') }}"
                   min="0">
            @error('rate_per_day')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="deduction" class="form-label">Deduction Previous Cut Off (₱)</label>
        <input type="number" 
               step="0.01" 
               class="form-control @error('deduction') is-invalid @enderror" 
               id="deduction" 
               name="deduction" 
               value="{{ old('deduction', '0.00') }}"
               min="0">
        @error('deduction')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
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
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Creating...';
      submitBtn.disabled = true;
      
      // Show loading alert
      Swal.fire({
        title: 'Creating Utility Timesheet...',
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

    // Handle per-day hours inputs -> compute total days and update display
    function getTotalDaysFromDaysInputs() {
      const inputs = document.querySelectorAll('.day-hours');
      let total = 0;
      inputs.forEach(inp => {
        const v = parseFloat(inp.value);
        if (!isNaN(v) && v > 0) total += 1;
      });
      return total;
    }

    // Recalculate total days when day inputs change
    document.querySelectorAll('.day-hours').forEach(input => {
      input.addEventListener('input', () => {
        // Update total_days input with the count of days with hours > 0
        const total = getTotalDaysFromDaysInputs();
        const totalDaysEl = document.getElementById('total_days');
        if (totalDaysEl) totalDaysEl.value = total;
        calculateTotal();
      });
    });

    // Auto-calculate total honorarium
    function calculateTotal() {
      const totalDays = parseFloat(document.getElementById('total_days').value) || 0;
      const ratePerDay = parseFloat(document.getElementById('rate_per_day').value) || 0;
      const deduction = parseFloat(document.getElementById('deduction').value) || 0;

      const totalHonorarium = (totalDays * ratePerDay) - deduction;
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
    document.getElementById('total_days').addEventListener('input', calculateTotal);
    document.getElementById('rate_per_day').addEventListener('input', calculateTotal);
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