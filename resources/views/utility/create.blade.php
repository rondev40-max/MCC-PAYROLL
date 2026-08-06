<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Utility Timesheet</title>
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

    /* Days selector section */
    .days-section {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .days-section-title {
      font-size: 1rem;
      font-weight: 600;
      color: #334155;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Total display card */
    .total-display {
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      border: 1px solid #bfdbfe;
      border-radius: 12px;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 25px;
      transition: all 0.3s ease;
    }

    .total-display .label {
      font-size: 1rem;
      color: #1e40af;
      font-weight: 600;
      margin: 0;
    }

    .total-display .amount {
      font-size: 1.75rem;
      font-weight: 700;
      color: #1d4ed8;
      margin: 0;
    }

    /* Total state transitions */
    .total-display.positive {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border-color: #bbf7d0;
    }
    .total-display.positive .label, .total-display.positive .amount {
      color: #166534;
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
    <h2><i class="bi bi-tools"></i> Add Utility Timesheet</h2>
    
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
    
    <form action="{{ route('utility.store') }}" method="POST">
      @csrf
      
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="form-floating">
            <select class="form-select @error('employee_name') is-invalid @enderror" id="employee_name" name="employee_name" required>
              <option value="" disabled selected>Select Utility Employee</option>
              @forelse($utilityEmployees as $id => $name)
                <option value="{{ $id }}" {{ old('employee_name') == $id ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @empty
                <option value="" disabled>No utility workers found in the system</option>
              @endforelse
            </select>
            <label for="employee_name">Employee Name *</label>
          </div>
          <small class="text-muted ms-1 mt-1 d-block" style="font-size: 0.8rem;">If not listed, add them to Master List first.</small>
        </div>
        <div class="col-md-6">
          <div class="form-floating">
            <select class="form-select @error('designation') is-invalid @enderror" id="designation" name="designation" required>
              <option value="" disabled selected>Select Designation</option>
              <option value="instructor" {{ old('designation') == 'instructor' ? 'selected' : '' }}>Instructor</option>
              <option value="utility" {{ old('designation') == 'utility' ? 'selected' : '' }}>Utility</option>
              <option value="staff" {{ old('designation') == 'staff' ? 'selected' : '' }}>Staff</option>
            </select>
            <label for="designation">Designation *</label>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-12">
          <div class="form-floating">
            <input type="number" step="0.01" class="form-control @error('prov_abr') is-invalid @enderror" id="prov_abr" name="prov_abr" value="{{ old('prov_abr', 0) }}" min="0">
            <label for="prov_abr">Previous Absences (Days)</label>
          </div>
        </div>
      </div>

      <div class="days-section">
        <div class="days-section-title">
          <i class="bi bi-calendar3"></i> Working Days (Hours per Day)
        </div>
        <div class="row g-3">
            @php($days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])
            @foreach($days as $index => $day)
                @php($i = $index + 1)
                <div class="col-md-4 col-sm-6">
                    <div class="form-floating">
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
                        <label for="day{{ $i }}">{{ $day }}</label>
                    </div>
                </div>
            @endforeach
        </div>
        <small class="text-muted d-block mt-3"><i class="bi bi-info-circle me-1"></i>Enter the number of hours worked for each day (Monday–Saturday).</small>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <div class="form-floating">
            <input type="number" step="0.01" class="form-control bg-light" id="total_days" name="total_days" value="0" min="0" readonly>
            <label for="total_days">Total Days</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating">
            <input type="number" step="0.01" class="form-control @error('rate_per_day') is-invalid @enderror" id="rate_per_day" name="rate_per_day" value="{{ old('rate_per_day', '0.00') }}" min="0">
            <label for="rate_per_day">Rate per Day (₱)</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating">
            <input type="number" step="0.01" class="form-control @error('deduction') is-invalid @enderror" id="deduction" name="deduction" value="{{ old('deduction', '0.00') }}" min="0">
            <label for="deduction">Deductions (₱)</label>
          </div>
        </div>
      </div>

      <div class="total-display" id="total-display-card">
        <p class="label">Calculated Honorarium</p>
        <p class="amount" id="calculated-total">₱0.00</p>
      </div>

      <div class="button-group">
        <a href="{{ route('utility.index') }}" class="btn btn-secondary">
          Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i> Save Timesheet
        </button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    function getTotalDaysFromDaysInputs() {
      const inputs = document.querySelectorAll('.day-hours');
      let total = 0;
      inputs.forEach(inp => {
        const v = parseFloat(inp.value);
        if (!isNaN(v) && v > 0) total += 1;
      });
      return total;
    }

    document.querySelectorAll('.day-hours').forEach(input => {
      input.addEventListener('input', () => {
        const total = getTotalDaysFromDaysInputs();
        const totalDaysEl = document.getElementById('total_days');
        if (totalDaysEl) totalDaysEl.value = total;
        calculateTotal();
      });
    });

    function calculateTotal() {
      const totalDays = parseFloat(document.getElementById('total_days').value) || 0;
      const ratePerDay = parseFloat(document.getElementById('rate_per_day').value) || 0;
      const deduction = parseFloat(document.getElementById('deduction').value) || 0;
      
      const totalHonorarium = (totalDays * ratePerDay) - deduction;
      const calculatedValue = totalHonorarium < 0 ? 0 : totalHonorarium;
      
      document.getElementById('calculated-total').textContent = '₱' + calculatedValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      
      const displayCard = document.getElementById('total-display-card');
      if (calculatedValue > 0) {
        displayCard.classList.add('positive');
      } else {
        displayCard.classList.remove('positive');
      }
    }

    document.getElementById('total_days').addEventListener('input', calculateTotal);
    document.getElementById('rate_per_day').addEventListener('input', calculateTotal);
    document.getElementById('deduction').addEventListener('input', calculateTotal);

    document.addEventListener('DOMContentLoaded', () => {
        calculateTotal();
    });
    
    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3b82f6',
        customClass: {
          popup: 'rounded-4'
        }
      });
    @endif

    document.querySelector('form').addEventListener('submit', function(e) {
      const submitBtn = document.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
      submitBtn.disabled = true;
      
      Swal.fire({
        title: 'Saving Timesheet...',
        text: 'Please wait...',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); },
        customClass: { popup: 'rounded-4' }
      });
      
      setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }, 5000);
    });
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