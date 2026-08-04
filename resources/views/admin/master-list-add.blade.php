<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Employee - Master List</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    :root {
      --primary: #2c3e50;
      --primary-light: #34495e;
      --accent: #3498db;
      --success: #27ae60;
      --warning: #f39c12;
      --danger: #e74c3c;
      --info: #16a085;
      --light: #ecf0f1;
      --dark: #1a1a1a;
      --muted: #f8f9fa;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
      background-attachment: fixed;
      color: var(--dark);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Header */
    .page-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: white;
      padding: 1.5rem 2rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .header-content {
      max-width: 1000px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.2rem;
      font-weight: 700;
    }

    .btn {
      border-radius: 8px;
      padding: 0.6rem 1.2rem;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--accent);
      color: white;
    }

    .btn-primary:hover {
      background: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
      color: white;
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 2px solid white;
    }

    .btn-secondary:hover {
      background: white;
      color: var(--primary);
    }

    .btn-sm {
      padding: 0.4rem 0.8rem;
      font-size: 0.85rem;
    }

    /* Container */
    .container-main {
      flex: 1;
      max-width: 1000px;
      margin: 2rem auto;
      padding: 0 1rem;
      width: 100%;
    }

    /* Form Card */
    .form-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 2rem;
    }

    .form-title {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 2rem;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--dark);
      padding-bottom: 1rem;
      border-bottom: 2px solid var(--light);
    }

    /* Form Section */
    .form-section {
      margin-bottom: 2rem;
    }

    .section-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .section-title::before {
      content: '';
      width: 4px;
      height: 20px;
      background: var(--accent);
      border-radius: 2px;
    }

    /* Form Group */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: var(--dark);
      font-size: 0.95rem;
    }

    .form-control,
    .form-select {
      border-radius: 8px;
      border: 1px solid #ddd;
      padding: 0.75rem;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    /* Grid Layout */
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .form-col {
      display: flex;
      flex-direction: column;
    }

    /* Days Selector */
    .days-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
      background: var(--muted);
      padding: 1.5rem;
      border-radius: 8px;
      margin-top: 0.5rem;
    }

    .day-input-group {
      display: flex;
      flex-direction: column;
    }

    .day-input-group label {
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    .day-input-group input {
      padding: 0.6rem;
      border-radius: 6px;
      border: 1px solid #ddd;
      font-size: 0.9rem;
    }

    /* Summary Box */
    .summary-box {
      background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.05) 100%);
      border-left: 4px solid var(--accent);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
    }

    .summary-label {
      font-weight: 600;
      color: var(--dark);
    }

    .summary-value {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--accent);
    }

    /* Textarea */
    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }

    /* Form Actions */
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      padding-top: 1.5rem;
      border-top: 2px solid var(--light);
      margin-top: 2rem;
    }

    /* Footer */
    .page-footer {
      text-align: center;
      padding: 1.5rem;
      color: #7f8c8d;
      font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .form-card {
        padding: 1.5rem;
      }

      .form-title {
        font-size: 1.2rem;
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      .days-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .form-actions {
        flex-direction: column-reverse;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }
    }

    /* Alert Styles */
    .alert {
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .required-note {
      font-size: 0.85rem;
      color: #7f8c8d;
      margin-top: 0.5rem;
      font-style: italic;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="header-content">
      <div class="header-title">
        <i class="bi bi-person-plus-fill"></i>
        <span>Add New Employee</span>
      </div>
      <a href="{{ route('master.list') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to List
      </a>
    </div>
  </div>

  <!-- Main Container -->
  <div class="container-main">
    <div class="form-card">
      <div class="form-title">
        <i class="bi bi-file-earmark-plus" style="color: var(--accent);"></i>
        <span>Employee Information Form</span>
      </div>

      <form method="POST" action="{{ route('master.list.add.store') }}" id="employeeForm">
        @csrf

        <!-- Basic Information -->
        <div class="form-section">
          <div class="section-title">
            <i class="bi bi-person"></i> Basic Information
          </div>

          <div class="form-row">
            <div class="form-col">
              <label class="form-label">Employee Name <span style="color: var(--danger);">*</span></label>
              <input type="text" name="employee_name" class="form-control" placeholder="Full Name" 
                     value="{{ old('employee_name') }}" required>
              <div class="required-note">e.g., John Doe</div>
            </div>

            <div class="form-col">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="employee@mcc.edu.ph" 
                     value="{{ old('email') }}">
              <div class="required-note">Optional</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-col">
              <label class="form-label">Employee Type <span style="color: var(--danger);">*</span></label>
              <select name="employee_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="fulltime" {{ old('employee_type') == 'fulltime' ? 'selected' : '' }}>Fulltime</option>
                <option value="parttime" {{ old('employee_type') == 'parttime' ? 'selected' : '' }}>Parttime</option>
                <option value="contract" {{ old('employee_type') == 'contract' ? 'selected' : '' }}>Contract</option>
              </select>
            </div>

            <div class="form-col">
              <label class="form-label">Designation <span style="color: var(--danger);">*</span></label>
              <select name="designation" class="form-select" required>
                <option value="">Select Designation</option>
                <option value="instructor" {{ old('designation') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                <option value="staff" {{ old('designation') == 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="utility" {{ old('designation') == 'utility' ? 'selected' : '' }}>Utility</option>
                <option value="admin" {{ old('designation') == 'admin' ? 'selected' : '' }}>Administrative</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Department & Location -->
        <div class="form-section">
          <div class="section-title">
            <i class="bi bi-building"></i> Department & Location
          </div>

          <div class="form-row">
            <div class="form-col">
              <label class="form-label">Department <span style="color: var(--danger);">*</span></label>
              <select name="department" class="form-select" required>
                <option value="">Select Department</option>
                <option value="BSIT" {{ old('department') == 'BSIT' ? 'selected' : '' }}>BSIT - Information Technology</option>
                <option value="BSBA" {{ old('department') == 'BSBA' ? 'selected' : '' }}>BSBA - Business Administration</option>
                <option value="BSHM" {{ old('department') == 'BSHM' ? 'selected' : '' }}>BSHM - Hotel Management</option>
                <option value="BSED" {{ old('department') == 'BSED' ? 'selected' : '' }}>BSED - Education</option>
                <option value="BEED" {{ old('department') == 'BEED' ? 'selected' : '' }}>BEED - Early Childhood Education</option>
                @isset($departments)
                  @foreach($departments as $dept)
                    <option value="{{ $dept->code }}" {{ old('department') == $dept->code ? 'selected' : '' }}>
                      {{ $dept->name }}
                    </option>
                  @endforeach
                @endisset
              </select>
            </div>

            <div class="form-col">
              <label class="form-label">Province Abbreviation</label>
              <input type="text" name="prov_abr" class="form-control" placeholder="e.g., CEU, NB" 
                     value="{{ old('prov_abr') }}">
              <div class="required-note">Optional</div>
            </div>
          </div>
        </div>

        <!-- Working Hours -->
        <div class="form-section">
          <div class="section-title">
            <i class="bi bi-calendar-check"></i> Working Schedule
          </div>

          <label class="form-label">Hours per Day (Monday to Saturday)</label>
          <div class="days-grid">
            @php($days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])
            @foreach($days as $index => $day)
              <div class="day-input-group">
                <label>{{ $day }}</label>
                <input type="number" class="day-hours" name="days[{{ $index + 1 }}]" 
                       min="0" max="24" step="0.25" placeholder="0" 
                       value="{{ old('days.' . ($index + 1)) }}">
              </div>
            @endforeach
          </div>
          <div class="required-note" style="margin-top: 1rem;">Enter hours worked per day. Leave blank for 0 hours.</div>
        </div>

        <!-- Compensation -->
        <div class="form-section">
          <div class="section-title">
            <i class="bi bi-cash-coin"></i> Compensation Details
          </div>

          <div class="form-row">
            <div class="form-col">
              <label class="form-label">Total Hours <span style="color: var(--danger);">*</span></label>
              <input type="number" step="0.01" name="total_hour" id="total_hour" class="form-control" 
                     placeholder="0.00" value="{{ old('total_hour') }}" required>
              <div class="required-note">Weekly or contract hours</div>
            </div>

            <div class="form-col">
              <label class="form-label">Rate per Hour <span style="color: var(--danger);">*</span></label>
              <input type="number" step="0.01" name="rate_per_hour" id="rate_per_hour" class="form-control" 
                     placeholder="0.00" value="{{ old('rate_per_hour') }}" required>
              <div class="required-note">₱ per hour</div>
            </div>

            <div class="form-col">
              <label class="form-label">Previous Cutoff Deduction</label>
              <input type="number" step="0.01" name="deduction" id="deduction" class="form-control" 
                     placeholder="0.00" value="{{ old('deduction', 0) }}">
              <div class="required-note">If any</div>
            </div>
          </div>

          <!-- Summary Box -->
          <div class="summary-box">
            <div class="summary-row">
              <span class="summary-label">Calculated Honorarium:</span>
              <span class="summary-value" id="calculated-total">₱0.00</span>
            </div>
            <div style="font-size: 0.85rem; color: #7f8c8d; margin-top: 0.5rem;">
              Formula: (Total Hours × Rate per Hour) - Deduction
            </div>
          </div>
        </div>

        <!-- Details -->
        <div class="form-section">
          <div class="section-title">
            <i class="bi bi-chat-left-text"></i> Additional Details
          </div>

          <label class="form-label">Class Schedule & Details</label>
          <textarea name="details" class="form-control" placeholder="Enter any relevant details about work schedule, class rooms, or special notes...">{{ old('details') }}</textarea>
          <div class="required-note">Optional</div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
          <a href="{{ route('master.list') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="bi bi-check-lg"></i> Add Employee
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <div class="page-footer">
    <p>Madridejos Community College | Employee Management System | © 2025</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Calculate total from day hours
    function getTotalHoursFromDaysInputs() {
      const inputs = document.querySelectorAll('.day-hours');
      let total = 0;
      inputs.forEach(input => {
        const value = parseFloat(input.value);
        if (!isNaN(value) && value > 0) {
          total += value;
        }
      });
      return total;
    }

    // Auto-calculate honorarium
    function calculateTotal() {
      const totalHour = parseFloat(document.getElementById('total_hour').value) || 0;
      const ratePerHour = parseFloat(document.getElementById('rate_per_hour').value) || 0;
      const deduction = parseFloat(document.getElementById('deduction').value) || 0;

      const totalHonorarium = (totalHour * ratePerHour) - deduction;
      const finalAmount = totalHonorarium < 0 ? 0 : totalHonorarium;

      const displayEl = document.getElementById('calculated-total');
      displayEl.textContent = '₱' + finalAmount.toFixed(2);
      displayEl.style.color = finalAmount > 0 ? '#27ae60' : '#e74c3c';
    }

    // Day hours change event
    document.querySelectorAll('.day-hours').forEach(input => {
      input.addEventListener('input', () => {
        const total = getTotalHoursFromDaysInputs();
        document.getElementById('total_hour').value = total.toFixed(2);
        calculateTotal();
      });
    });

    // Compensation inputs change
    document.getElementById('total_hour').addEventListener('input', calculateTotal);
    document.getElementById('rate_per_hour').addEventListener('input', calculateTotal);
    document.getElementById('deduction').addEventListener('input', calculateTotal);

    // Form submission
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('submitBtn');
      const originalText = submitBtn.innerHTML;

      submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
      submitBtn.disabled = true;

      Swal.fire({
        title: 'Adding Employee...',
        text: 'Please wait while we add the new employee.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      setTimeout(() => {
        if (submitBtn.disabled) {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }
      }, 5000);
    });

    // Error handling
    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session("error") }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3498db'
      });
    @endif

    // Initial calculation
    calculateTotal();

    // DevTools detection
    devtools.detect(function(status) {
      if (status) {
        document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
      }
    });
  </script>
</body>
</html>