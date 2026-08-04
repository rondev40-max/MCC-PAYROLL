<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Part-time Timesheet</title><script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  	 <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <h2><i class="bi bi-plus-circle me-2"></i>Add Part-time Timesheet</h2>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> May mga problema sa iyong input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('parttime.store') }}" method="POST">
      @csrf
      
      <div class="mb-3">
        <label for="employee_name" class="form-label">Employee Name</label>
        <input type="text" class="form-control" id="employee_name" name="employee_name" value="{{ old('employee_name') }}" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="name@gmail.com">
      </div>

      <div class="mb-3">
        <label for="designation" class="form-label">Designation</label>
        <select class="form-control" id="designation" name="designation" required>
          <option value="">Select Designation</option>
          <option value="instructor" {{ old('designation') == 'instructor' ? 'selected' : '' }}>Instructor</option>
          <option value="utility" {{ old('designation') == 'utility' ? 'selected' : '' }}>Utility</option>
          <option value="staff" {{ old('designation') == 'staff' ? 'selected' : '' }}>Staff</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="prov_abr" class="form-label">Province Abbreviation</label>
        <input type="text" class="form-control" id="prov_abr" name="prov_abr" value="{{ old('prov_abr') }}">
      </div>

      <div class="mb-3">
        <label for="department" class="form-label">Department</label>
        <select class="form-control" id="department" name="department" required>
          <option value="" disabled selected>Select Department</option>
          {{-- Fixed options to match enum --}}
          <option value="BSIT" {{ old('department') == 'BSIT' ? 'selected' : '' }}>BSIT</option>
          <option value="BSBA" {{ old('department') == 'BSBA' ? 'selected' : '' }}>BSBA</option>
          <option value="BSHM" {{ old('department') == 'BSHM' ? 'selected' : '' }}>BSHM</option>
          <option value="BSED" {{ old('department') == 'BSED' ? 'selected' : '' }}>BSED</option>
          <option value="BEED" {{ old('department') == 'BEED' ? 'selected' : '' }}>BEED</option>
          
          {{-- Dynamic options, kung available ang $departments --}}
          @if(isset($departments))
              @foreach($departments as $department)
                <option value="{{ $department->code }}" {{ old('department') == $department->code ? 'selected' : '' }}>{{ $department->name }}</option>
              @endforeach
          @endif
        </select>
      </div>

      <div class="mb-3">
        <label for="days" class="form-label">Working Days (Hours per Day)</label>
        <div class="days-selector mb-2 p-3">
            <div class="row">
                @php($days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])
                @foreach($days as $index => $day)
                    @php($i = $index + 1)
                    <div class="col-md-4 col-sm-6 mb-3">
                        <label class="form-label" for="day{{ $i }}">{{ $day }}</label>
                        <input
                            type="number"
                            class="form-control day-hours"
                            id="day{{ $i }}"
                            name="days[{{ $i }}]"
                            min="0"
                            max="24"
                            step="0.25"
                            value="{{ old("days.{$i}") }}"
                            placeholder="0"
                        >
                    </div>
                @endforeach
            </div>
        </div>
        <small class="form-text text-muted">Enter the number of hours worked for each day (Monday–Saturday). The system will calculate the total based on the selected period, excluding Sundays and holidays.</small>
      </div>

      <div class="mb-3">
        <label for="details" class="form-label">Details for Inclusive Hours of Classes</label>
        <textarea class="form-control" id="details" name="details" rows="3">{{ old('details') }}</textarea>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="total_hour" class="form-label">Total Hours</label>
            {{-- Ang value nito ay kakalkulahin ng JavaScript --}}
            <input type="number" step="0.01" class="form-control" id="total_hour" name="total_hour" value="{{ old('total_hour', 0) }}" readonly>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="rate_per_hour" class="form-label">Rate per Hour</label>
            <input type="number" step="0.01" class="form-control" id="rate_per_hour" name="rate_per_hour" value="{{ old('rate_per_hour', '120.00') }}">
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="deduction" class="form-label">Deduction Previous Cut Off</label>
        <input type="number" step="0.01" class="form-control" id="deduction" name="deduction" value="{{ old('deduction', '0') }}">
      </div>

      <div class="total-display">
        <p class="label">Calculated Total Honorarium</p>
        <p class="amount" id="calculated-total">₱0.00</p>
      </div>

      <div class="button-group">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>Add Timesheet
        </button>
        <a href="{{ route('parttime.index') }}" class="btn btn-secondary">
          <i class="bi bi-x-lg me-1"></i>Cancel
        </a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    // Handle per-day hours inputs -> compute total hours and update display
    function getTotalHoursFromDaysInputs() {
      const inputs = document.querySelectorAll('.day-hours');
      let total = 0;
      inputs.forEach(inp => {
        // Gumamit ng default na 0 kapag walang laman (null)
        const v = parseFloat(inp.value) || 0; 
        if (v > 0) total += v;
      });
      return total;
    }

    // Recalculate total hours when day inputs change
    document.querySelectorAll('.day-hours').forEach(input => {
      input.addEventListener('input', () => {
        // Update total_hour input with the sum of day hours. This is just for display.
        const total = getTotalHoursFromDaysInputs();
        const totalHourEl = document.getElementById('total_hour');
        if (totalHourEl) totalHourEl.value = total.toFixed(2);
        calculateTotal();
      });
    });

    // Auto-calculate total honorarium
    function calculateTotal() {
      // Note: total_hour is readonly. Ang kalkulasyon ay para sa display purposes lang
      // at hindi isasama sa form submission. Ang final total ay kakalkulahin sa backend.
      const totalHour = parseFloat(document.getElementById('total_hour').value) || 0;
      const ratePerHour = parseFloat(document.getElementById('rate_per_hour').value) || 0;
      const deduction = parseFloat(document.getElementById('deduction').value) || 0;
      
      const totalHonorarium = (totalHour * ratePerHour) - deduction;
      const calculatedValue = totalHonorarium < 0 ? 0 : totalHonorarium;
      
      // Update the display
      document.getElementById('calculated-total').textContent = '₱' + calculatedValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      
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
    document.getElementById('rate_per_hour').addEventListener('input', calculateTotal);
    document.getElementById('deduction').addEventListener('input', calculateTotal);

    // Initial calculation (need to call this initially to set the total)
    document.addEventListener('DOMContentLoaded', () => {
        const initialTotal = getTotalHoursFromDaysInputs();
        document.getElementById('total_hour').value = initialTotal.toFixed(2);
        calculateTotal();
    });
    
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
        // NOTE: The validation errors from Laravel will automatically dismiss the Swal.fire and display the errors above the form.
      }, 5000);
    });
  </script>

  <style>
    /* ... (Iyong SweetAlert styles dito) ... */
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