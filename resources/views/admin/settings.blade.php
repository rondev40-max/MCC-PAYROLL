@extends('layouts.admin')

@section('title', 'System Settings — MCC Digital Payroll')
@section('header_title', 'System Settings')

@section('styles')
<style>
  .card-settings {
    background: var(--card);
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
  }
  .nav-tabs-custom {
    border-bottom: 2px solid var(--border);
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    padding: 0 1rem;
    flex-wrap: wrap;
  }
  .nav-tabs-custom .tab-link {
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--text-2);
    cursor: pointer;
    font-size: .9rem;
    font-weight: 700;
    padding: .75rem .25rem;
    transition: all .15s ease-in-out;
  }
  .nav-tabs-custom .tab-link:hover {
    color: var(--brand);
  }
  .nav-tabs-custom .tab-link.active {
    border-bottom-color: var(--brand);
    color: var(--brand);
  }
  .form-group-custom {
    margin-bottom: 1.25rem;
  }
  .form-group-custom label {
    display: block;
    font-size: .8rem;
    font-weight: 700;
    margin-bottom: .4rem;
    color: var(--text-2);
    text-transform: uppercase;
    letter-spacing: .3px;
  }
  .form-control-custom {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    color: var(--text);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .85rem;
    padding: .5rem .75rem;
    width: 100%;
    transition: border-color .15s ease-in-out;
  }
  .form-control-custom:focus {
    border-color: var(--brand);
    outline: none;
  }
  .form-check-custom {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: .5rem 0;
  }
  .form-check-custom input {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-10">

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: var(--r-sm);">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
      @csrf

      <div class="card-settings p-4">
        <!-- Custom Tabs Navigation -->
        <div class="nav-tabs-custom">
          <button type="button" class="tab-link active" onclick="switchTab(event, 'tab-profile')">
            <i class="bi bi-building me-1"></i>School Profile
          </button>
          <button type="button" class="tab-link" onclick="switchTab(event, 'tab-attendance')">
            <i class="bi bi-clock me-1"></i>Attendance Policies
          </button>
          <button type="button" class="tab-link" onclick="switchTab(event, 'tab-security')">
            <i class="bi bi-shield-lock me-1"></i>Security & Mail
          </button>
          <button type="button" class="tab-link" onclick="switchTab(event, 'tab-payroll')">
            <i class="bi bi-cash-coin me-1"></i>Payroll Defaults
          </button>
        </div>

        <!-- TAB: School Profile -->
        <div class="tab-content-item" id="tab-profile">
          <h5 class="mb-3" style="font-weight: 700; color: var(--text);">🏫 School Profile Settings</h5>
          <div class="row">
            <div class="col-md-6 form-group-custom">
              <label for="school_name">School Name</label>
              <input type="text" name="settings[school_name]" id="school_name" class="form-control-custom" value="{{ \App\Models\Setting::get('school_name', 'Madridejos Community College') }}">
            </div>
            <div class="col-md-6 form-group-custom">
              <label for="school_address">School Address</label>
              <input type="text" name="settings[school_address]" id="school_address" class="form-control-custom" value="{{ \App\Models\Setting::get('school_address', 'Poblacion, Madridejos, Cebu, Philippines') }}">
            </div>
            <div class="col-md-6 form-group-custom">
              <label for="signatory_name">HR / Authority Signatory</label>
              <input type="text" name="settings[signatory_name]" id="signatory_name" class="form-control-custom" value="{{ \App\Models\Setting::get('signatory_name', 'Dr. Jorex Sarraga') }}">
            </div>
            <div class="col-md-6 form-group-custom">
              <label for="signatory_title">Signatory Title</label>
              <input type="text" name="settings[signatory_title]" id="signatory_title" class="form-control-custom" value="{{ \App\Models\Setting::get('signatory_title', 'College President') }}">
            </div>
          </div>
        </div>

        <!-- TAB: Attendance Policies -->
        <div class="tab-content-item d-none" id="tab-attendance">
          <h5 class="mb-3" style="font-weight: 700; color: var(--text);">⏱️ Attendance & Timesheet Policies</h5>
          <div class="row">
            <div class="col-md-6 form-group-custom">
              <label for="grace_period_minutes">Late Grace Period (Minutes)</label>
              <input type="number" name="settings[grace_period_minutes]" id="grace_period_minutes" class="form-control-custom" value="{{ \App\Models\Setting::get('grace_period_minutes', 15) }}">
            </div>
            <div class="col-md-6 form-group-custom">
              <label for="overtime_threshold_hours">Overtime Threshold (Hours per Day)</label>
              <input type="number" name="settings[overtime_threshold_hours]" id="overtime_threshold_hours" class="form-control-custom" value="{{ \App\Models\Setting::get('overtime_threshold_hours', 8) }}">
            </div>
            <div class="col-12 form-group-custom">
              <div class="form-check-custom">
                <input type="checkbox" name="settings[restrict_by_ip]" id="restrict_by_ip" value="1" {{ \App\Models\Setting::get('restrict_by_ip', '0') == '1' ? 'checked' : '' }}>
                <div>
                  <label for="restrict_by_ip" class="m-0" style="cursor: pointer; text-transform:none; font-size:.85rem; letter-spacing:0; color:var(--text-2); font-weight:700;">Restrict Clock-In to School Wi-Fi IP Only</label>
                  <small class="text-muted d-block">When enabled, employees can only log in and time-in if their IP match the school network.</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB: Security & Mail -->
        <div class="tab-content-item d-none" id="tab-security">
          <h5 class="mb-3" style="font-weight: 700; color: var(--text);">🛡️ Security & Mail Policies</h5>
          <div class="row">
            <div class="col-md-6 form-group-custom">
              <div class="form-check-custom">
                <input type="checkbox" name="settings[enable_login_otp]" id="enable_login_otp" value="1" {{ \App\Models\Setting::get('enable_login_otp', '0') == '1' ? 'checked' : '' }}>
                <div>
                  <label for="enable_login_otp" class="m-0" style="cursor: pointer; text-transform:none; font-size:.85rem; letter-spacing:0; color:var(--text-2); font-weight:700;">Enable Login OTP Code Verification</label>
                  <small class="text-muted d-block">When enabled, users will receive a 6-digit verification code via email during login.</small>
                </div>
              </div>
            </div>
            <div class="col-md-6 form-group-custom">
              <div class="form-check-custom">
                <input type="checkbox" name="settings[bcc_admin_on_payslips]" id="bcc_admin_on_payslips" value="1" {{ \App\Models\Setting::get('bcc_admin_on_payslips', '0') == '1' ? 'checked' : '' }}>
                <div>
                  <label for="bcc_admin_on_payslips" class="m-0" style="cursor: pointer; text-transform:none; font-size:.85rem; letter-spacing:0; color:var(--text-2); font-weight:700;">BCC Administrator on all Sent Payslips</label>
                  <small class="text-muted d-block">Automatically sends a blind copy (BCC) of every dispatched payslip email to the admin account.</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB: Payroll Defaults -->
        <div class="tab-content-item d-none" id="tab-payroll">
          <h5 class="mb-3" style="font-weight: 700; color: var(--text);">💰 Payroll Default Settings</h5>
          <div class="row">
            <div class="col-md-6 form-group-custom">
              <label for="currency_symbol">Currency Symbol</label>
              <input type="text" name="settings[currency_symbol]" id="currency_symbol" class="form-control-custom" value="{{ \App\Models\Setting::get('currency_symbol', '₱') }}">
            </div>
            <div class="col-md-6 form-group-custom">
              <label for="default_cutoff_period">Default Cut-Off Period</label>
              <select name="settings[default_cutoff_period]" id="default_cutoff_period" class="form-control-custom">
                <option value="1-15" {{ \App\Models\Setting::get('default_cutoff_period', '1-15') == '1-15' ? 'selected' : '' }}>1-15 (First Half of Month)</option>
                <option value="16-30" {{ \App\Models\Setting::get('default_cutoff_period', '1-15') == '16-30' ? 'selected' : '' }}>16-30 (Second Half of Month)</option>
              </select>
            </div>
          </div>
        </div>

        <hr class="my-4" style="border-color: var(--border);">

        <!-- Save Button -->
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: var(--r-sm); font-size: .85rem; font-weight: 700;">
            <i class="bi bi-save me-1"></i>Save All Settings
          </button>
        </div>

      </div>
    </form>

  </div>
</div>
@endsection

@section('scripts')
<script>
  function switchTab(event, tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content-item').forEach(item => {
      item.classList.add('d-none');
    });

    // Remove active class from buttons
    document.querySelectorAll('.tab-link').forEach(btn => {
      btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabId).classList.remove('d-none');

    // Add active class to button
    event.currentTarget.classList.add('active');
  }
</script>
@endsection
