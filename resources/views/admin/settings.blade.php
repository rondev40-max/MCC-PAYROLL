@extends('layouts.admin')

@section('title', 'System Settings — MCC Digital Payroll')
@section('header_title', 'System Settings')

@section('styles')
<style>
  /* ─── Modern Settings UI Token Adjustments ─── */
  .settings-container {
    animation: fadeUp .4s ease both;
  }
  
  .settings-layout {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 2rem;
  }

  /* Settings Sidebar Nav */
  .settings-sidebar {
    background: var(--card);
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    padding: .85rem;
    height: fit-content;
    box-shadow: var(--shadow-sm);
  }

  .settings-tab-btn {
    width: 100%;
    background: transparent;
    border: none;
    border-radius: var(--r-sm);
    padding: .75rem 1rem;
    font-size: .85rem;
    font-weight: 700;
    color: var(--text-2);
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
    transition: all .15s ease;
    cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
  }

  .settings-tab-btn i {
    font-size: 1.1rem;
    color: var(--text-3);
    transition: color .15s;
  }

  .settings-tab-btn:hover {
    background: var(--bg);
    color: var(--brand);
  }

  .settings-tab-btn:hover i {
    color: var(--brand);
  }

  .settings-tab-btn.active {
    background: var(--brand-light);
    color: var(--brand);
  }

  .settings-tab-btn.active i {
    color: var(--brand);
  }

  /* Settings Content Card */
  .settings-content-card {
    background: var(--card);
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    padding: 2rem;
    min-height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .settings-section-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: .5rem;
  }

  .settings-section-subtitle {
    font-size: .82rem;
    color: var(--text-3);
    margin-bottom: 2rem;
  }

  /* Form Elements */
  .form-group-modern {
    margin-bottom: 1.5rem;
  }

  .form-group-modern label {
    display: block;
    font-size: .75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-2);
    margin-bottom: .5rem;
  }

  /* Input Groups & Focus States */
  .input-group-modern {
    display: flex;
    align-items: center;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    transition: border-color .15s, box-shadow .15s;
  }

  .input-group-modern:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px var(--brand-glow);
  }

  .input-group-modern .input-addon {
    padding: 0 .9rem;
    color: var(--text-3);
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-right: 1px solid var(--border);
  }

  .input-group-modern .form-input {
    flex: 1;
    background: transparent;
    border: none;
    color: var(--text);
    font-size: .85rem;
    font-weight: 600;
    padding: .65rem .85rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    outline: none;
    width: 100%;
  }

  /* Modern Toggles (iOS style) */
  .switch-card {
    background: var(--bg);
    border-radius: var(--r-sm);
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    border: 1px solid var(--border);
    transition: all .15s ease;
  }

  .switch-card:hover {
    border-color: var(--border-2);
  }

  .switch-card-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .switch-card-title {
    font-size: .85rem;
    font-weight: 700;
    color: var(--text);
  }

  .switch-card-desc {
    font-size: .78rem;
    color: var(--text-3);
  }

  /* Custom Switch Slider */
  .form-switch .form-check-input {
    width: 2.6em;
    height: 1.4em;
    background-color: var(--border-2);
    border-color: transparent;
    cursor: pointer;
    transition: background-color .15s ease, background-position .15s ease;
  }

  .form-switch .form-check-input:focus {
    box-shadow: 0 0 0 3px var(--brand-glow);
    border-color: transparent;
  }

  .form-switch .form-check-input:checked {
    background-color: var(--brand);
  }

  /* ─── Responsive Media Queries ─── */
  @media (max-width: 768px) {
    .settings-layout {
      grid-template-columns: 1fr;
      gap: 1.25rem;
    }
    .settings-sidebar {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      padding: .5rem;
    }
    .settings-tab-btn {
      width: auto;
      flex: 1;
      justify-content: center;
      padding: .5rem .75rem;
      font-size: .8rem;
      white-space: nowrap;
    }
  }
</style>
@endsection

@section('content')
<div class="settings-container container-fluid py-2">
  
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 d-flex align-items-center mb-4" role="alert" style="border-radius: var(--r-sm); background: var(--brand-light); color: var(--brand); box-shadow: var(--shadow-xs);">
      <i class="bi bi-check-circle-fill me-2 fs-5"></i>
      <div class="small fw-bold">{{ session('success') }}</div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(37%) sepia(93%) saturate(1478%) hue-rotate(205deg) brightness(98%) contrast(105%);"></button>
    </div>
  @endif

  <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
    @csrf

    <div class="settings-layout">
      <!-- Left aligned Settings tabs -->
      <nav class="settings-sidebar">
        <button type="button" class="settings-tab-btn active" id="btn-profile" onclick="switchSettingsTab('profile')">
          <i class="bi bi-building"></i>School Profile
        </button>
        <button type="button" class="settings-tab-btn" id="btn-attendance" onclick="switchSettingsTab('attendance')">
          <i class="bi bi-clock-history"></i>Attendance Policies
        </button>
        <button type="button" class="settings-tab-btn" id="btn-security" onclick="switchSettingsTab('security')">
          <i class="bi bi-shield-lock"></i>Security & Mail
        </button>
        <button type="button" class="settings-tab-btn" id="btn-payroll" onclick="switchSettingsTab('payroll')">
          <i class="bi bi-wallet2"></i>Payroll Defaults
        </button>
        <button type="button" class="settings-tab-btn" id="btn-logs" onclick="switchSettingsTab('logs')">
          <i class="bi bi-journal-text"></i>Activity Logs
        </button>
      </nav>

      <!-- Right aligned Settings Content card -->
      <div class="settings-content-card">
        
        <!-- SECTION: School Profile -->
        <div class="settings-section" id="sec-profile">
          <div class="settings-section-title">
            <i class="bi bi-building-fill text-primary"></i>
            <span>🏫 School Profile Settings</span>
          </div>
          <p class="settings-section-subtitle">Configure administrative details displayed on printed timesheets, payroll summaries, and official payslip headers.</p>
          
          <div class="row g-3">
            <div class="col-md-6 form-group-modern">
              <label for="school_name">Official School Name</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-bank"></i></span>
                <input type="text" name="settings[school_name]" id="school_name" class="form-input" value="{{ \App\Models\Setting::get('school_name', 'Madridejos Community College') }}" placeholder="e.g. Madridejos Community College">
              </div>
            </div>

            <div class="col-md-6 form-group-modern">
              <label for="school_address">Campus Address</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-geo-alt"></i></span>
                <input type="text" name="settings[school_address]" id="school_address" class="form-input" value="{{ \App\Models\Setting::get('school_address', 'Poblacion, Madridejos, Cebu, Philippines') }}" placeholder="e.g. Poblacion, Madridejos, Cebu">
              </div>
            </div>

            <div class="col-md-6 form-group-modern">
              <label for="signatory_name">HR Director / Certified Signatory</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-person-badge"></i></span>
                <input type="text" name="settings[signatory_name]" id="signatory_name" class="form-input" value="{{ \App\Models\Setting::get('signatory_name', 'Dr. Jorex Sarraga') }}" placeholder="e.g. Dr. Jorex Sarraga">
              </div>
            </div>

            <div class="col-md-6 form-group-modern">
              <label for="signatory_title">Signatory Title</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-award"></i></span>
                <input type="text" name="settings[signatory_title]" id="signatory_title" class="form-input" value="{{ \App\Models\Setting::get('signatory_title', 'College President') }}" placeholder="e.g. College President">
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION: Attendance & Timesheet -->
        <div class="settings-section d-none" id="sec-attendance">
          <div class="settings-section-title">
            <i class="bi bi-clock-fill text-warning"></i>
            <span>⏱️ Attendance & Timesheet Policies</span>
          </div>
          <p class="settings-section-subtitle">Define grace allowances, daily overtime thresholds, and network boundary conditions for clocking in.</p>

          <div class="row g-3">
            <div class="col-md-6 form-group-modern">
              <label for="grace_period_minutes">Late Grace Period Allowance</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-hourglass-split"></i></span>
                <input type="number" name="settings[grace_period_minutes]" id="grace_period_minutes" class="form-input" value="{{ \App\Models\Setting::get('grace_period_minutes', 15) }}" min="0" placeholder="e.g. 15">
                <span class="px-3 text-muted small border-start font-weight-bold">MINS</span>
              </div>
            </div>

            <div class="col-md-6 form-group-modern">
              <label for="overtime_threshold_hours">Daily Overtime Threshold</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-stopwatch"></i></span>
                <input type="number" name="settings[overtime_threshold_hours]" id="overtime_threshold_hours" class="form-input" value="{{ \App\Models\Setting::get('overtime_threshold_hours', 8) }}" min="1" placeholder="e.g. 8">
                <span class="px-3 text-muted small border-start font-weight-bold">HRS</span>
              </div>
            </div>

            <div class="col-12 mt-2">
              <div class="switch-card">
                <div class="switch-card-info">
                  <div class="switch-card-title">Restrict Clock-In to Campus Network Only</div>
                  <div class="switch-card-desc">When toggled, instructors and staff can only clock in or out if their device is connected to the physical school network IP.</div>
                </div>
                <div class="form-check form-switch m-0 p-0">
                  <input class="form-check-input" type="checkbox" name="settings[restrict_by_ip]" id="restrict_by_ip" value="1" {{ \App\Models\Setting::get('restrict_by_ip', '0') == '1' ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION: Security & Mail -->
        <div class="settings-section d-none" id="sec-security">
          <div class="settings-section-title">
            <i class="bi bi-shield-lock-fill text-success"></i>
            <span>🛡️ Security & Mail Policies</span>
          </div>
          <p class="settings-section-subtitle">Manage administrative login verification layers and configure email copy policies for dispatched pay slips.</p>

          <div class="row g-3">
            <div class="col-md-12">
              <div class="switch-card mb-3">
                <div class="switch-card-info">
                  <div class="switch-card-title">Require 6-Digit Email OTP on Login</div>
                  <div class="switch-card-desc">Strengthen dashboard security. Users must authenticate with a one-time verification code dispatched to their school email.</div>
                </div>
                <div class="form-check form-switch m-0 p-0">
                  <input class="form-check-input" type="checkbox" name="settings[enable_login_otp]" id="enable_login_otp" value="1" {{ \App\Models\Setting::get('enable_login_otp', '0') == '1' ? 'checked' : '' }}>
                </div>
              </div>

              <div class="switch-card">
                <div class="switch-card-info">
                  <div class="switch-card-title">BCC Administrator on Dispatched Payslips</div>
                  <div class="switch-card-desc">Enforce audit logs. Whenever an employee's payslip is generated and emailed, the admin account automatically receives a blind copy.</div>
                </div>
                <div class="form-check form-switch m-0 p-0">
                  <input class="form-check-input" type="checkbox" name="settings[bcc_admin_on_payslips]" id="bcc_admin_on_payslips" value="1" {{ \App\Models\Setting::get('bcc_admin_on_payslips', '0') == '1' ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION: Payroll Defaults -->
        <div class="settings-section d-none" id="sec-payroll">
          <div class="settings-section-title">
            <i class="bi bi-wallet2 text-danger"></i>
            <span>💰 Payroll Default Settings</span>
          </div>
          <p class="settings-section-subtitle">Set global currency symbols and default cutoff parameters for newly initialized payment records.</p>

          <div class="row g-3">
            <div class="col-md-6 form-group-modern">
              <label for="currency_symbol">Global Currency Symbol</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-cash"></i></span>
                <input type="text" name="settings[currency_symbol]" id="currency_symbol" class="form-input" value="{{ \App\Models\Setting::get('currency_symbol', '₱') }}" placeholder="e.g. ₱">
              </div>
            </div>

            <div class="col-md-6 form-group-modern">
              <label for="default_cutoff_period">Default Cut-Off Period</label>
              <div class="input-group-modern">
                <span class="input-addon"><i class="bi bi-calendar-range"></i></span>
                <select name="settings[default_cutoff_period]" id="default_cutoff_period" class="form-input" style="height: auto; padding: .65rem .85rem;">
                  <option value="1-15" {{ \App\Models\Setting::get('default_cutoff_period', '1-15') == '1-15' ? 'selected' : '' }}>1-15 (First Half of Month)</option>
                  <option value="16-30" {{ \App\Models\Setting::get('default_cutoff_period', '1-15') == '16-30' ? 'selected' : '' }}>16-30 (Second Half of Month)</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION: Activity Logs -->
        <div class="settings-section d-none" id="sec-logs">
          <div class="settings-section-title">
            <i class="bi bi-journal-text text-info"></i>
            <span>📋 Recent System Activity Logs</span>
          </div>
          <p class="settings-section-subtitle">Audit history of recent actions executed on the payroll system.</p>

          <div class="table-responsive">
            <table class="table table-hover align-middle" style="font-size: .8rem;">
              <thead class="table-light">
                <tr>
                  <th>User</th>
                  <th>Action</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($logs as $log)
                  <tr>
                    <td>
                      <div class="fw-bold">{{ $log->causer->name ?? 'System' }}</div>
                      <small class="text-muted">{{ $log->causer->email ?? '' }}</small>
                    </td>
                    <td>
                      <span class="badge bg-{{ $log->event === 'updated' ? 'primary' : ($log->event === 'deleted' ? 'danger' : 'success') }}" style="font-size:.65rem; padding: 2px 6px;">{{ ucfirst($log->event ?? 'Log') }}</span>
                      <span class="ms-1 fw-semibold text-secondary" style="color: var(--text-2) !important;">{{ $log->description }}</span>
                    </td>
                    <td class="text-nowrap text-muted">{{ $log->created_at->diffForHumans() }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="text-center py-4 text-muted">No activities logged yet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer / Save Actions -->
        <div class="d-flex justify-content-end pt-3 border-top mt-4" style="border-color: var(--border) !important;">
          <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2" style="border-radius: var(--r-sm); font-size: .85rem; font-weight: 800; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
            <i class="bi bi-cloud-arrow-up-fill fs-6"></i>
            <span>Save All Configuration</span>
          </button>
        </div>

      </div>
    </div>
  </form>

</div>
@endsection

@section('scripts')
<script>
  function switchSettingsTab(group) {
    // Hide all sections
    document.querySelectorAll('.settings-section').forEach(sec => {
      sec.classList.add('d-none');
    });

    // Remove active class from buttons
    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
      btn.classList.remove('active');
    });

    // Show current section & active button
    document.getElementById(`sec-${group}`).classList.remove('d-none');
    document.getElementById(`btn-${group}`).classList.add('active');
  }
</script>
@endsection
