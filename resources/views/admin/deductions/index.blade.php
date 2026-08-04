<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MCC Digital Payroll — Tax & Gov't Deductions</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand:        #2563eb;
      --brand-dark:   #1d4ed8;
      --brand-light:  #eff6ff;
      --brand-glow:   rgba(37,99,235,0.15);
      --accent:       #16a34a;
      --danger:       #dc2626;
      --sidebar-w:    220px;
      --topbar-h:     60px;
      --sidebar-bg:   #0f172a;
      --sidebar-text: rgba(226,232,240,0.75);
      --sidebar-hover:rgba(255,255,255,0.06);
      --sidebar-active:rgba(37,99,235,0.85);
      --bg:           #f1f5f9;
      --card:         #ffffff;
      --text:         #0f172a;
      --text-2:       #475569;
      --text-3:       #94a3b8;
      --border:       #e2e8f0;
      --border-2:     #cbd5e1;
      --shadow-xs:    0 1px 3px rgba(15,23,42,0.06);
      --shadow-sm:    0 2px 8px rgba(15,23,42,0.08);
      --shadow-md:    0 8px 24px rgba(15,23,42,0.10);
      --r-sm:         10px;
      --r-md:         14px;
      --r-lg:         18px;
    }

    .night-mode {
      --brand-light:  #1e3a5f;
      --sidebar-bg:   #060a14;
      --bg:           #0d1117;
      --card:         #161b27;
      --text:         #e2e8f0;
      --text-2:       #94a3b8;
      --text-3:       #4b5563;
      --border:       #1e2535;
      --border-2:     #263048;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      transition: background .3s, color .3s;
      height: 100vh;
      overflow: hidden;
    }

    .app { display: flex; height: 100vh; overflow: hidden; }

    /* ─── Sidebar ───────────────────────────── */
    .sidebar {
      width: var(--sidebar-w); flex-shrink: 0; background: var(--sidebar-bg);
      height: 100vh; display: flex; flex-direction: column;
      overflow-y: auto; overflow-x: hidden;
      scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.07) transparent;
      z-index: 1030; position: relative;
    }
    .sidebar-header { padding: 1.1rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); flex-shrink: 0; }
    .sidebar-logo { display: flex; align-items: center; gap: 10px; }
    .sidebar-logo img { width: 34px; height: 34px; border-radius: 8px; object-fit: contain; background: rgba(255,255,255,0.08); padding: 4px; }
    .brand-name { font-size: .82rem; font-weight: 800; color: #fff; letter-spacing: -.2px; }
    .brand-sub  { font-size: .65rem; color: rgba(255,255,255,0.38); font-weight: 400; letter-spacing: .3px; }
    .sidebar-nav { flex: 1; padding: .6rem .65rem 1rem; display: flex; flex-direction: column; gap: 1px; overflow-y: auto; overflow-x: hidden; }
    .nav-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.4px; color: rgba(255,255,255,0.22); padding: .9rem .55rem .25rem; }
    .sidebar .nav-link, .sidebar-btn {
      color: var(--sidebar-text); border-radius: var(--r-sm); padding: .5rem .65rem;
      font-size: .82rem; font-weight: 500; display: flex; align-items: center; gap: 9px;
      transition: background .15s, color .15s; white-space: nowrap;
      text-decoration: none; background: transparent; border: none; width: 100%;
      text-align: left; font-family: 'Plus Jakarta Sans', sans-serif; cursor: pointer;
    }
    .sidebar .nav-link i, .sidebar-btn i { font-size: .95rem; width: 17px; flex-shrink: 0; opacity: .8; }
    .sidebar .nav-link:hover, .sidebar-btn:hover { background: var(--sidebar-hover); color: #fff; }
    .sidebar .nav-link:hover i, .sidebar-btn:hover i { opacity: 1; }
    .sidebar .nav-link.active { background: var(--sidebar-active); color: #fff; }
    .sidebar .nav-link.active i { opacity: 1; }
    .sidebar .dropdown-menu { border-radius: var(--r-sm); border: 1px solid var(--border); box-shadow: var(--shadow-md); padding: .35rem; background: var(--card); }
    .sidebar .dropdown-item { border-radius: 7px; padding: .42rem .8rem; font-size: .8rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 500; color: var(--text); display: flex; align-items: center; gap: 7px; transition: background .13s; }
    .sidebar .dropdown-item:hover { background: var(--brand-light); color: var(--brand); }
    .sidebar-footer { padding: .65rem; border-top: 1px solid rgba(255,255,255,0.05); flex-shrink: 0; }
    .night-mode .sidebar .dropdown-menu { background: #1a2133; border-color: var(--border-2); }
    .night-mode .sidebar .dropdown-item { color: var(--text); }
    .night-mode .sidebar .dropdown-item:hover { background: rgba(37,99,235,0.15); }
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1025; }
    .sidebar-overlay.show { display: block; }

    @media (max-width: 991px) {
      .sidebar { position: fixed; transform: translateX(-100%); left: 0; top: 0; transition: transform .25s ease-in-out; }
      .sidebar.open { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,.4); }
      body { overflow: visible; height: auto; }
      .app { height: auto; min-height: 100vh; }
      .content { height: auto !important; overflow: visible !important; }
    }

    /* ─── Content ───────────────────────────── */
    .content { flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

    .topbar {
      height: var(--topbar-h); background: var(--card); border-bottom: 1px solid var(--border);
      padding: 0 1.4rem; flex-shrink: 0; display: flex; align-items: center;
      justify-content: space-between; gap: .9rem; box-shadow: var(--shadow-xs); z-index: 100;
    }
    .topbar-left { display: flex; align-items: center; gap: .8rem; }
    .topbar h5 { font-weight: 700; color: var(--text); margin: 0; }

    .theme-btn {
      background: var(--bg) !important; border: 1px solid var(--border) !important;
      border-radius: var(--r-sm) !important; width: 34px; height: 34px;
      display: flex; align-items: center; justify-content: center; color: var(--text-2); transition: all .2s;
    }
    .theme-btn:hover { background: var(--brand-light) !important; color: var(--brand); border-color: var(--brand) !important; }

    .page-body { flex: 1; overflow-y: auto; padding: 1.1rem 1.4rem 1.6rem; }
    .page-header { margin-bottom: 1rem; }
    .page-title { font-size: 1.1rem; font-weight: 800; color: var(--text); letter-spacing: -.4px; }
    .page-subtitle { font-size: .78rem; color: var(--text-3); margin-top: 2px; }

    /* ─── Deduction Cards ───────────────────── */
    .deduction-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: .8rem; }

    .deduction-card {
      background: var(--card); border: 1px solid var(--border); border-radius: var(--r-lg);
      box-shadow: var(--shadow-xs); padding: 1.1rem; display: flex; flex-direction: column;
      gap: .6rem; transition: box-shadow .2s;
    }
    .deduction-card:hover { box-shadow: var(--shadow-md); }

    .dc-header { display: flex; align-items: flex-start; justify-content: space-between; gap: .6rem; }
    .dc-title { font-size: .92rem; font-weight: 800; color: var(--text); }
    .dc-desc  { font-size: .72rem; color: var(--text-3); margin-top: 2px; line-height: 1.4; }
    .dc-icon  { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: var(--brand-light); color: var(--brand); font-size: 1rem; flex-shrink: 0; }

    .status-pill {
      font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
      border-radius: 20px; padding: .2rem .6rem; display: inline-flex; align-items: center; gap: 4px;
    }
    .status-pill.active   { background: rgba(22,163,74,0.1); color: var(--accent); }
    .status-pill.inactive { background: rgba(148,163,184,0.15); color: var(--text-3); }
    .status-pill .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    .dc-figures { display: grid; grid-template-columns: repeat(3, 1fr); gap: .4rem; padding-top: .3rem; border-top: 1px solid var(--border); }
    .dc-figure { text-align: center; }
    .dc-figure-val   { font-size: .86rem; font-weight: 800; color: var(--text); letter-spacing: -.3px; }
    .dc-figure-label { font-size: .58rem; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: .3px; margin-top: 1px; }

    .dc-footer { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding-top: .4rem; }
    .form-check-input:checked { background-color: var(--accent); border-color: var(--accent); }

    .btn-edit {
      background: var(--brand-light); color: var(--brand); border: none; border-radius: var(--r-sm);
      padding: .4rem .85rem; font-size: .76rem; font-weight: 700; display: flex; align-items: center;
      gap: 6px; transition: background .15s; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-edit:hover { background: var(--brand); color: #fff; }

    .bracket-note {
      background: var(--bg); border: 1px dashed var(--border-2); border-radius: var(--r-sm);
      padding: .5rem .65rem; font-size: .68rem; color: var(--text-3);
      display: flex; gap: 6px; align-items: flex-start;
    }
    .bracket-note i { color: var(--brand); flex-shrink: 0; margin-top: 1px; }

    /* ─── Modal ─────────────────────────────── */
    .modal-content { border-radius: var(--r-lg) !important; border: none !important; background: var(--card); }
    .modal-header  { border-radius: var(--r-lg) var(--r-lg) 0 0 !important; border-bottom: 1px solid var(--border) !important; padding: 1rem 1.25rem; }
    .modal-footer  { border-top: 1px solid var(--border) !important; padding: .85rem 1.25rem; }
    .modal-body    { padding: 1.25rem; }

    /* Modal header layout */
    .modal-icon-header { display: flex; align-items: center; gap: .75rem; }
    .modal-dc-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--brand-light); color: var(--brand); font-size: 1.1rem; flex-shrink: 0; }
    .modal-ded-name { font-size: .95rem; font-weight: 800; color: var(--text); margin: 0; line-height: 1.2; }
    .modal-ded-sub  { font-size: .7rem; color: var(--text-3); margin: 2px 0 0; }

    /* Segmented rate-type control */
    .rate-seg { display: flex; border: 1px solid var(--border); border-radius: var(--r-sm); overflow: hidden; }
    .rate-seg-opt {
      flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px;
      padding: .44rem .8rem; font-size: .78rem; font-weight: 600; cursor: pointer;
      color: var(--text-2); background: transparent; transition: background .15s, color .15s;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .rate-seg-opt + .rate-seg-opt { border-left: 1px solid var(--border); }
    .rate-seg-opt input[type=radio] { display: none; }
    .rate-seg-opt.seg-on { background: var(--brand); color: #fff; }

    /* Active toggle row */
    .active-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: .75rem 1rem; background: var(--bg);
      border: 1px solid var(--border); border-radius: var(--r-sm);
    }
    .active-row-title { font-size: .82rem; font-weight: 700; color: var(--text); }
    .active-row-sub   { font-size: .69rem; color: var(--text-3); margin-top: 1px; }

    /* Save button */
    .btn-save {
      background: var(--brand); color: #fff; border: none; border-radius: var(--r-sm);
      padding: .45rem 1.4rem; font-size: .82rem; font-weight: 700;
      font-family: 'Plus Jakarta Sans', sans-serif; cursor: pointer; transition: background .15s;
    }
    .btn-save:hover { background: var(--brand-dark); }

    /* Form controls */
    .form-label { font-size: .78rem; font-weight: 700; color: var(--text-2); margin-bottom: .3rem; }
    .form-control, .form-select {
      border-radius: var(--r-sm); border: 1px solid var(--border);
      font-size: .84rem; padding: .5rem .7rem; background: var(--card); color: var(--text);
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-glow); outline: none; }
    .input-group-text {
      background: var(--bg); border: 1px solid var(--border); color: var(--text-2);
      font-size: .8rem; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .input-group > .form-control { border-left: none; }
    .input-group-text { border-radius: var(--r-sm) 0 0 var(--r-sm) !important; }
    .input-group > .form-control { border-radius: 0 var(--r-sm) var(--r-sm) 0 !important; }

    /* Night-mode modal overrides */
    .night-mode .modal-content { background: var(--card); }
    .night-mode .modal-header,
    .night-mode .modal-footer  { border-color: var(--border) !important; }
    .night-mode .form-control,
    .night-mode .form-select   { background: #1a2133; color: var(--text); border-color: var(--border); }
    .night-mode .input-group-text { background: var(--bg); border-color: var(--border); color: var(--text-2); }
    .night-mode .rate-seg { border-color: var(--border); }
    .night-mode .rate-seg-opt { color: var(--text-3); }
    .night-mode .rate-seg-opt + .rate-seg-opt { border-color: var(--border); }
    .night-mode .active-row { background: rgba(255,255,255,0.03); border-color: var(--border); }
    .night-mode .bracket-note { background: rgba(255,255,255,0.03); }
  </style>
</head>
<body>

<div class="app">
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

  {{-- ══════ SIDEBAR ══════ --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <img src="{{ asset('images/logo.png') }}" alt="MCC">
        <div>
          <div class="brand-name">MCC Digital</div>
          <div class="brand-sub">Payroll System v2</div>
        </div>
      </div>
    </div>

    <div class="sidebar-nav">
      <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2"></i>Dashboard
      </a>

      <div class="nav-label">Management</div>

      <div class="dropdown">
        <button class="sidebar-btn dropdown-toggle {{ request()->routeIs('fulltime.*', 'parttime.*', 'utility.*', 'staff.*') ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-people"></i><span>Employees</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('fulltime.index') }}"><i class="bi bi-person-badge"></i>Full-Time Instructors</a></li>
          <li><a class="dropdown-item" href="{{ route('parttime.index') }}"><i class="bi bi-person-check"></i>Part-Time Instructors</a></li>
          <li><a class="dropdown-item" href="{{ route('utility.index') }}"><i class="bi bi-tools"></i>Utility Workers</a></li>
          <li><a class="dropdown-item" href="{{ route('staff.index') }}"><i class="bi bi-person-workspace"></i>Staff</a></li>
        </ul>
      </div>

      <div class="dropdown">
        <button class="sidebar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-cash-coin"></i><span>Instructor Rate</span>
        </button>
        <ul class="dropdown-menu">
          @foreach(['130', '150', '170', '190', '210', '220', '250'] as $rate)
            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}?rate={{ $rate }}"><i class="bi bi-currency-dollar"></i>₱{{ $rate }}</a></li>
          @endforeach
        </ul>
      </div>

      <div class="dropdown">
        <button class="sidebar-btn dropdown-toggle {{ request()->routeIs('departments.*', 'bsit.*', 'bsba.*', 'bshm.*', 'education.*') ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-building"></i><span>Department</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('departments.index') }}"><i class="bi bi-gear"></i>Manage Departments</a></li>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item" href="{{ route('bsit.index') }}"><i class="bi bi-laptop"></i>BSIT</a></li>
          <li><a class="dropdown-item" href="{{ route('bsba.index') }}"><i class="bi bi-briefcase"></i>BSBA</a></li>
          <li><a class="dropdown-item" href="{{ route('bshm.index') }}"><i class="bi bi-cup-hot"></i>BSHM</a></li>
          <li><a class="dropdown-item" href="{{ route('education.index') }}"><i class="bi bi-book"></i>Education</a></li>
        </ul>
      </div>

      <div class="nav-label">Records</div>

      <div class="dropdown">
        <button class="sidebar-btn dropdown-toggle {{ request()->routeIs('admin.history', 'admin.payroll.history', 'admin.employee.timesheets.submissions') ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-clipboard-data"></i><span>History Records</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('admin.history') }}"><i class="bi bi-calendar-check"></i>History Log</a></li>
          <li><a class="dropdown-item" href="{{ route('admin.payroll.history') }}"><i class="bi bi-scissors"></i>Payroll History</a></li>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item" href="{{ route('admin.employee.timesheets.submissions') }}"><i class="bi bi-clock-history"></i>Submitted Timesheets</a></li>
        </ul>
      </div>

      <a href="{{ route('master.list') }}" class="sidebar-btn text-decoration-none {{ request()->routeIs('master.list*') ? 'active' : '' }}">
        <i class="bi bi-list-ul"></i><span>Master List</span>
      </a>

      <a href="{{ route('admin.salary.adjustment') }}" class="sidebar-btn text-decoration-none {{ request()->routeIs('admin.salary.adjustment*') ? 'active' : '' }}">
        <i class="bi bi-calculator"></i><span>Salary Adjustment</span>
      </a>

      <a href="{{ route('admin.deductions.index') }}" class="sidebar-btn text-decoration-none {{ request()->routeIs('admin.deductions.*') ? 'active' : '' }}">
        <i class="bi bi-percent"></i><span>Tax & Gov't Deductions</span>
      </a>

      <div class="nav-label">Analytics</div>

      <a href="{{ route('admin.evaluation.results') }}" class="sidebar-btn text-decoration-none {{ request()->routeIs('admin.evaluation.results') ? 'active' : '' }}">
        <i class="bi bi-bar-chart"></i><span>Evaluation Results</span>
      </a>

      <div class="nav-label">Configuration</div>

      <a href="{{ route('admin.settings.index') }}" class="sidebar-btn text-decoration-none {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <i class="bi bi-gear"></i><span>System Settings</span>
      </a>

      <div class="nav-label">Payslips</div>

      <form action="{{ route('admin.send.payslips') }}" method="POST" id="sendPayslipsForm">
        @csrf
        <input type="hidden" name="start_date" id="payslipStartDateHidden">
        <input type="hidden" name="end_date" id="payslipEndDateHidden">
        <button type="button" class="sidebar-btn" id="sendPayslipsBtn">
          <i class="bi bi-send-check"></i><span>Send Payslips (All)</span>
        </button>
      </form>
    </div>

    <div class="sidebar-footer">
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="sidebar-btn"><i class="bi bi-box-arrow-left"></i><span>Logout</span></button>
      </form>
    </div>
  </aside>

  {{-- ══════ MAIN CONTENT ══════ --}}
  <div class="content">
    <header class="topbar">
      <div class="topbar-left">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" id="mobileMenuBtn" style="border-radius:var(--r-sm); padding:.3rem .5rem;">
          <i class="bi bi-list" style="font-size:1.2rem;"></i>
        </button>
        <h5 class="d-none d-md-block">Tax & Gov't Deductions</h5>
      </div>
      <button class="theme-btn" id="nightModeToggle"><i class="bi bi-moon-stars" id="nightModeIcon"></i></button>
    </header>

    <main class="page-body">
      <div class="page-header">
        <div class="page-title">Government Deduction Settings</div>
        <div class="page-subtitle">Rates applied automatically when payroll is computed. Toggle a deduction off to exclude it entirely.</div>
      </div>

      @php
        $icons = [
          'withholding_tax' => 'bi-receipt-cutoff',
          'gsis'            => 'bi-bank',
          'philhealth'      => 'bi-heart-pulse',
          'pag_ibig'        => 'bi-house-door',
          'sss'             => 'bi-shield-check',
        ];
        $labels = [
          'withholding_tax' => 'Withholding Tax',
          'gsis'            => 'GSIS',
          'philhealth'      => 'PhilHealth',
          'pag_ibig'        => 'Pag-IBIG',
          'sss'             => 'SSS',
        ];
      @endphp

      @if($settings->isEmpty())
        <div class="deduction-card">No deduction settings found. Run the <code>deduction_settings</code> seeder/migration first.</div>
      @else
        <div class="deduction-grid">
          @foreach($settings as $s)
            <div class="deduction-card">
              <div class="dc-header">
                <div class="d-flex gap-2">
                  <div class="dc-icon"><i class="bi {{ $icons[$s->deduction_type] ?? 'bi-percent' }}"></i></div>
                  <div>
                    <div class="dc-title">{{ $labels[$s->deduction_type] ?? ucfirst(str_replace('_',' ',$s->deduction_type)) }}</div>
                    @if($s->description)
                      <div class="dc-desc">{{ $s->description }}</div>
                    @endif
                  </div>
                </div>
                <span class="status-pill {{ $s->is_active ? 'active' : 'inactive' }}">
                  <span class="dot"></span>{{ $s->is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <div class="dc-figures">
                <div class="dc-figure">
                  <div class="dc-figure-val">{{ $s->rate_type === 'percentage' ? number_format($s->rate_value,3).'%' : '₱'.number_format($s->rate_value,2) }}</div>
                  <div class="dc-figure-label">{{ $s->rate_type === 'percentage' ? 'Rate' : 'Fixed Amt' }}</div>
                </div>
                <div class="dc-figure">
                  <div class="dc-figure-val">{{ $s->min_amount !== null ? '₱'.number_format($s->min_amount,2) : '—' }}</div>
                  <div class="dc-figure-label">Min Cap</div>
                </div>
                <div class="dc-figure">
                  <div class="dc-figure-val">{{ $s->max_amount !== null ? '₱'.number_format($s->max_amount,2) : '—' }}</div>
                  <div class="dc-figure-label">Max Cap</div>
                </div>
              </div>

              @if($s->deduction_type === 'withholding_tax')
                <div class="bracket-note">
                  <i class="bi bi-info-circle"></i>
                  <span>Computed from the BIR graduated bracket table — not this rate value. Only the Active toggle applies.</span>
                </div>
              @endif

              <div class="dc-footer">
                <form method="POST" action="{{ route('admin.deductions.toggle', $s->id) }}" style="display:inline;">
                  @csrf @method('PATCH')
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" role="switch"
                      onchange="this.closest('form').submit()"
                      {{ $s->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" style="font-size:.72rem; color:var(--text-3);">Enabled</label>
                  </div>
                </form>
                <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                  onclick='openEditModal(@json($s))'>
                  <i class="bi bi-pencil"></i>Edit
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </main>
  </div>
</div>

{{-- ══════ EDIT MODAL ══════ --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <form class="modal-content" id="editForm" method="POST">
      @csrf
      @method('PUT')

      {{-- Header --}}
      <div class="modal-header">
        <div class="modal-icon-header">
          <div class="modal-dc-icon"><i class="bi bi-percent" id="modalIcon"></i></div>
          <div>
            <p class="modal-ded-name" id="editModalTitle">Edit Deduction</p>
            <p class="modal-ded-sub" id="editModalSub">Adjust rate, caps, and status</p>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Body --}}
      <div class="modal-body d-flex flex-column gap-3">

        {{-- BIR info banner — only shown for withholding_tax --}}
        <div class="bracket-note" id="wtaxBanner" style="display:none;">
          <i class="bi bi-info-circle"></i>
          <div>
            <strong style="font-size:.72rem;display:block;margin-bottom:2px;">BIR Graduated Brackets apply</strong>
            <span style="font-size:.67rem;line-height:1.5;">The actual amount withheld is computed from the BIR bracket table in code — not from the rate value below. Only the Active toggle has effect here.</span>
          </div>
        </div>

        {{-- Rate Type --}}
        <div>
          <label class="form-label">Rate Type</label>
          <div class="rate-seg">
            <label class="rate-seg-opt" id="segPercent">
              <input type="radio" name="rate_type" value="percentage" id="rtPercent" onchange="syncRateType()">
              <i class="bi bi-percent"></i> Percentage
            </label>
            <label class="rate-seg-opt" id="segFixed">
              <input type="radio" name="rate_type" value="fixed" id="rtFixed" onchange="syncRateType()">
              <i class="bi bi-currency-exchange"></i> Fixed Amount
            </label>
          </div>
        </div>

        {{-- Rate Value --}}
        <div>
          <label class="form-label" id="editRateValueLabel">Rate Value (%)</label>
          <div class="input-group">
            <span class="input-group-text" id="ratePrefix">%</span>
            <input type="number" step="0.001" min="0" name="rate_value" id="editRateValue"
              class="form-control" required placeholder="e.g. 3.000">
          </div>
        </div>

        {{-- Min / Max --}}
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Min Amount <span style="font-weight:400;color:var(--text-3);">(optional)</span></label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" step="0.01" min="0" name="min_amount" id="editMinAmount"
                class="form-control" placeholder="None">
            </div>
          </div>
          <div class="col-6">
            <label class="form-label">Max Amount <span style="font-weight:400;color:var(--text-3);">(optional)</span></label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" step="0.01" min="0" name="max_amount" id="editMaxAmount"
                class="form-control" placeholder="None">
            </div>
          </div>
        </div>

        {{-- Description --}}
        <div>
          <label class="form-label">Description <span style="font-weight:400;color:var(--text-3);">(optional)</span></label>
          <textarea name="description" id="editDescription" class="form-control" rows="2"
            placeholder="Short note about this deduction..."></textarea>
        </div>

        {{-- Active Toggle --}}
        <div class="active-row">
          <div>
            <div class="active-row-title">Enable deduction</div>
            <div class="active-row-sub">When off, this is skipped during payroll computation</div>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" role="switch"
              name="is_active" id="editIsActive" value="1">
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const LABELS = {
    withholding_tax: 'Withholding Tax',
    gsis: 'GSIS',
    philhealth: 'PhilHealth',
    pag_ibig: 'Pag-IBIG',
    sss: 'SSS',
  };
  const ICONS = {
    withholding_tax: 'bi-receipt-cutoff',
    gsis: 'bi-bank',
    philhealth: 'bi-heart-pulse',
    pag_ibig: 'bi-house-door',
    sss: 'bi-shield-check',
  };

  // ── Night mode ──────────────────────────────────
  if (localStorage.getItem('night-mode') === 'true') {
    document.body.classList.add('night-mode');
    document.getElementById('nightModeIcon').className = 'bi bi-sun';
  }
  document.getElementById('nightModeToggle').addEventListener('click', () => {
    const isNight = document.body.classList.toggle('night-mode');
    localStorage.setItem('night-mode', isNight);
    document.getElementById('nightModeIcon').className = isNight ? 'bi bi-sun' : 'bi bi-moon-stars';
  });

  // ── Mobile sidebar ──────────────────────────────
  document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
  });
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
  }

  // ── Segmented rate-type control ──────────────────
  function syncRateType() {
    const isP = document.getElementById('rtPercent').checked;
    document.getElementById('editRateValueLabel').textContent = isP ? 'Rate Value (%)' : 'Fixed Amount (₱)';
    document.getElementById('ratePrefix').textContent = isP ? '%' : '₱';
    document.getElementById('segPercent').classList.toggle('seg-on', isP);
    document.getElementById('segFixed').classList.toggle('seg-on', !isP);
  }

  // ── Open edit modal ──────────────────────────────
  function openEditModal(data) {
    const isWT = data.deduction_type === 'withholding_tax';

    // Form action
    document.getElementById('editForm').action = `/admin/deductions/${data.id}`;

    // Header
    document.getElementById('editModalTitle').textContent =
      'Edit ' + (LABELS[data.deduction_type] || data.deduction_type);
    document.getElementById('editModalSub').textContent =
      isWT ? 'BIR Graduated Tax — toggle only' : 'Adjust rate, caps, and status';
    document.getElementById('modalIcon').className =
      'bi ' + (ICONS[data.deduction_type] || 'bi-percent');

    // BIR banner visibility
    document.getElementById('wtaxBanner').style.display = isWT ? 'flex' : 'none';

    // Rate type
    document.getElementById('rtPercent').checked = (data.rate_type === 'percentage');
    document.getElementById('rtFixed').checked   = (data.rate_type === 'fixed');
    syncRateType();

    // Values
    document.getElementById('editRateValue').value   = data.rate_value  ?? '';
    document.getElementById('editMinAmount').value   = data.min_amount  ?? '';
    document.getElementById('editMaxAmount').value   = data.max_amount  ?? '';
    document.getElementById('editDescription').value = data.description ?? '';
    document.getElementById('editIsActive').checked  = !!data.is_active;
  }

  // ── Flash messages ───────────────────────────────
  @if(session('success'))
    Swal.fire({
      icon: 'success', title: 'Done!',
      text: {!! json_encode(session('success')) !!},
      confirmButtonColor: '#2563eb',
      timer: 2200, showConfirmButton: false,
      toast: true, position: 'top-end',
    });
  @endif
  @if($errors->any())
    Swal.fire({
      icon: 'error', title: 'Please check the form',
      text: {!! json_encode($errors->first()) !!},
    });
  @endif
</script>
</body>
</html>