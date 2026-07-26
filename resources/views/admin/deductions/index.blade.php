{{-- ============================================================
     MCC Digital Payroll — Tax & Gov't Deductions
     FIXES:
       1. Checkbox is_active bug → hidden input trick (0 default)
       2. Unified form field naming: deductions[key][field]
       3. page-body overflow-y: auto (content was unreachable)
       4. Dark mode form control styles
       5. SweetAlert confirmations on destructive actions
       6. sendPayslipsBtn JS handler
       7. Employee search JS handler placeholder
       8. Redesigned settings rows (cleaner, scannable)
       9. Improved employees table with scroll & color badges
     CONTROLLER NOTE:
       Update your controller to read $request->deductions as:
         foreach ($request->deductions as $key => $data) {
             DeductionSetting::updateOrCreate(
                 ['deduction_type' => $key],
                 [
                     'rate_type'  => $data['rate_type'],
                     'rate_value' => $data['rate_value'],
                     'min_amount' => $data['min_amount'] ?? null,
                     'max_amount' => $data['max_amount'] ?? null,
                     'is_active'  => $data['is_active'] ?? 0,
                 ]
             );
         }
================================================================ --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tax & Gov't Deductions — MCC Digital Payroll</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <style>
    /* ─── CSS Variables ──────────────────────────────────── */
    :root {
      --brand:         #2563eb;
      --brand-dark:    #1d4ed8;
      --brand-mid:     #3b82f6;
      --brand-light:   #eff6ff;
      --brand-glow:    rgba(37,99,235,0.15);
      --accent:        #10b981;
      --accent-light:  #d1fae5;
      --warn:          #f59e0b;
      --danger:        #ef4444;
      --sidebar-w:     220px;
      --topbar-h:      60px;
      --sidebar-bg:    #0f172a;
      --sidebar-text:  rgba(226,232,240,0.75);
      --sidebar-hover: rgba(255,255,255,0.06);
      --sidebar-active:rgba(37,99,235,0.85);
      --bg:            #f1f5f9;
      --card:          #ffffff;
      --text:          #0f172a;
      --text-2:        #475569;
      --text-3:        #94a3b8;
      --border:        #e2e8f0;
      --border-2:      #cbd5e1;
      --shadow-xs:     0 1px 3px rgba(15,23,42,0.06);
      --shadow-sm:     0 2px 8px rgba(15,23,42,0.08);
      --shadow-md:     0 8px 24px rgba(15,23,42,0.10);
      --r-sm:          10px;
      --r-md:          14px;
      --r-lg:          18px;
    }

    .night-mode {
      --brand:         #3b82f6;
      --brand-dark:    #2563eb;
      --brand-light:   #1e3a5f;
      --brand-glow:    rgba(59,130,246,0.15);
      --sidebar-bg:    #060a14;
      --bg:            #0d1117;
      --card:          #161b27;
      --text:          #e2e8f0;
      --text-2:        #94a3b8;
      --text-3:        #4b5563;
      --border:        #1e2535;
      --border-2:      #263048;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      transition: background .3s, color .3s;
      overflow: hidden;
      height: 100vh;
    }

    /* ─── App Shell ──────────────────────────────────────── */
    .app { display: flex; height: 100vh; overflow: hidden; }

    /* ─── Sidebar ────────────────────────────────────────── */
    .sidebar {
      width: var(--sidebar-w);
      flex-shrink: 0;
      background: var(--sidebar-bg);
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
      overflow-x: hidden;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,0.07) transparent;
      transition: transform .3s;
      z-index: 1030;
      position: relative;
    }

    .sidebar-header {
      padding: 1.1rem 1rem;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      flex-shrink: 0;
    }

    .sidebar-logo { display: flex; align-items: center; gap: 10px; }

    .sidebar-logo img {
      width: 34px; height: 34px;
      border-radius: 8px;
      object-fit: contain;
      background: rgba(255,255,255,0.08);
      padding: 4px;
    }

    .brand-name  { font-size: .82rem; font-weight: 800; color: #fff; letter-spacing: -.2px; }
    .brand-sub   { font-size: .65rem; color: rgba(255,255,255,0.38); font-weight: 400; letter-spacing: .3px; }

    .sidebar-nav {
      flex: 1;
      padding: .6rem .65rem 1rem;
      display: flex;
      flex-direction: column;
      gap: 1px;
      overflow-y: auto;
      overflow-x: hidden;
    }

    .nav-label {
      font-size: .6rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.4px;
      color: rgba(255,255,255,0.22);
      padding: .9rem .55rem .25rem;
    }

    .sidebar .nav-link,
    .sidebar-btn {
      color: var(--sidebar-text);
      border-radius: var(--r-sm);
      padding: .5rem .65rem;
      font-size: .82rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 9px;
      transition: background .15s, color .15s;
      white-space: nowrap;
      text-decoration: none;
      background: transparent;
      border: none;
      width: 100%;
      text-align: left;
      font-family: 'Plus Jakarta Sans', sans-serif;
      cursor: pointer;
    }

    .sidebar .nav-link i, .sidebar-btn i {
      font-size: .95rem; width: 17px; flex-shrink: 0; opacity: .8;
    }

    .sidebar .nav-link:hover, .sidebar-btn:hover {
      background: var(--sidebar-hover); color: #fff;
    }

    .sidebar .nav-link.active { background: var(--sidebar-active); color: #fff; }
    .sidebar .nav-link.active i, .sidebar .nav-link:hover i, .sidebar-btn:hover i { opacity: 1; }

    .sidebar .dropdown-menu {
      border-radius: var(--r-sm);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-md);
      padding: .35rem;
      background: var(--card);
    }

    .sidebar .dropdown-item {
      border-radius: 7px;
      padding: .42rem .8rem;
      font-size: .8rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 500;
      color: var(--text);
      display: flex;
      align-items: center;
      gap: 7px;
      transition: background .13s;
    }

    .sidebar .dropdown-item:hover { background: var(--brand-light); color: var(--brand); }

    .sidebar-footer {
      padding: .65rem;
      border-top: 1px solid rgba(255,255,255,0.05);
      flex-shrink: 0;
    }

    /* ─── Content + Topbar ───────────────────────────────── */
    .content { flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

    .topbar {
      height: var(--topbar-h);
      background: var(--card);
      border-bottom: 1px solid var(--border);
      padding: 0 1.4rem;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      gap: .9rem;
      box-shadow: var(--shadow-xs);
      transition: background .3s, border-color .3s;
      z-index: 100;
    }

    .topbar-pill {
      display: flex; align-items: center; gap: 5px;
      font-size: .73rem; font-weight: 700; color: var(--accent);
      background: var(--accent-light); border-radius: 20px; padding: .22rem .65rem;
    }

    .night-mode .topbar-pill { background: rgba(16,185,129,0.12); }

    .pulse-dot {
      width: 6px; height: 6px; border-radius: 50%; background: var(--accent);
      animation: pulseDot 2s ease-in-out infinite;
    }

    @keyframes pulseDot {
      0%,100% { transform: scale(1); opacity: 1; }
      50%      { transform: scale(1.6); opacity: .5; }
    }

    .topbar-clock {
      font-family: 'JetBrains Mono', monospace;
      font-size: .78rem; font-weight: 500; color: var(--text-2); letter-spacing: .5px;
    }

    .welcome-block {
      background: var(--brand-light); border-radius: 8px;
      padding: 5px 12px; border-left: 2.5px solid var(--brand);
    }

    .night-mode .welcome-block { background: rgba(37,99,235,0.1); }

    .welcome-block p { font-size: .76rem; color: var(--text-2); margin: 0; font-weight: 500; }
    .welcome-block span { color: var(--brand); font-weight: 700; }

    .theme-btn {
      background: var(--bg) !important; border: 1px solid var(--border) !important;
      border-radius: var(--r-sm) !important; padding: .35rem .65rem;
      color: var(--text-2); font-size: .82rem; transition: all .2s; line-height: 1;
    }

    .theme-btn:hover { background: var(--brand-light) !important; color: var(--brand); border-color: var(--brand) !important; }

    /* ─── Page Body — FIXED: overflow-y: auto (was: hidden) ── */
    .page-body {
      flex: 1;
      overflow-y: auto;          /* FIX: was overflow:hidden — content below fold was unreachable */
      overflow-x: hidden;
      padding: 0.85rem 1rem 1.2rem;
      min-height: 0;
    }

    .page-body::-webkit-scrollbar { width: 5px; }
    .page-body::-webkit-scrollbar-track { background: transparent; }
    .page-body::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 3px; }

    /* ─── Page Header ────────────────────────────────────── */
    .page-header {
      display: flex; align-items: flex-start;
      justify-content: space-between; margin-bottom: 1rem;
    }

    .page-title    { font-size: 1rem; font-weight: 800; color: var(--text); letter-spacing: -.4px; line-height: 1.1; }
    .page-subtitle { font-size: 0.72rem; color: var(--text-3); margin-top: 2px; }

    /* ─── Section Card ───────────────────────────────────── */
    .section-card {
      background: var(--card);
      border-radius: var(--r-lg);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-xs);
      margin-bottom: 1rem;
      overflow: hidden;
    }

    .section-card-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 0.85rem 1rem; border-bottom: 1px solid var(--border);
      background: var(--card); flex-wrap: wrap; gap: 0.5rem;
    }

    .section-card-title {
      font-size: 0.82rem; font-weight: 700; color: var(--text);
      display: flex; align-items: center; gap: 7px;
    }

    .section-card-title i { color: var(--brand); font-size: 0.92rem; }

    .section-card-body { padding: 1rem; }

    /* ─── Deduction Setting Rows ─────────────────────────── */
    .ded-rows { display: flex; flex-direction: column; gap: 0.55rem; }

    .ded-row {
      display: grid;
      grid-template-columns: 220px 120px 150px 100px 110px 110px;
      align-items: center;
      gap: 0.65rem;
      padding: 0.75rem 0.85rem;
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      background: var(--bg);
      transition: border-color .2s, box-shadow .2s;
    }

    .ded-row:hover { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-glow); }

    @media (max-width: 1100px) {
      .ded-row { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 600px) {
      .ded-row { grid-template-columns: 1fr; }
    }

    .ded-info { display: flex; align-items: center; gap: 10px; }

    .ded-icon {
      width: 34px; height: 34px; border-radius: 9px;
      display: grid; place-items: center; flex-shrink: 0;
      font-size: 1rem;
    }

    .ded-name  { font-size: 0.82rem; font-weight: 700; color: var(--text); }
    .ded-key   { font-size: 0.62rem; color: var(--text-3); margin-top: 1px; font-family: 'JetBrains Mono', monospace; }

    .ded-field-label {
      font-size: 0.6rem; font-weight: 700; color: var(--text-3);
      text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; display: block;
    }

    .ded-active { display: flex; align-items: center; }

    /* Toggle switch style */
    .form-switch .form-check-input {
      width: 2.2em; height: 1.2em; cursor: pointer;
      background-color: var(--border-2); border-color: var(--border-2);
    }

    .form-switch .form-check-input:checked {
      background-color: var(--accent); border-color: var(--accent);
    }

    .form-switch .form-check-label {
      font-size: 0.72rem; font-weight: 600;
      color: var(--text-2); cursor: pointer;
    }

    /* ─── Form controls dark mode FIX ───────────────────── */
    .form-control, .form-select {
      background-color: var(--card);
      color: var(--text);
      border-color: var(--border);
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.8rem;
      transition: border-color .2s, box-shadow .2s, background .3s;
    }

    .form-control:focus, .form-select:focus {
      background-color: var(--card);
      color: var(--text);
      border-color: var(--brand);
      box-shadow: 0 0 0 3px var(--brand-glow);
    }

    .form-control::placeholder { color: var(--text-3); }

    /* Night mode form controls */
    .night-mode .form-control,
    .night-mode .form-select {
      background-color: #1a2133;
      color: var(--text);
      border-color: var(--border-2);
    }

    .night-mode .form-control:focus,
    .night-mode .form-select:focus {
      background-color: #1a2133;
      color: var(--text);
    }

    .night-mode .form-control::placeholder { color: var(--text-3); }

    .night-mode .ded-row { background: rgba(255,255,255,0.02); }

    /* ─── Period Filter Strip ────────────────────────────── */
    .filter-strip {
      display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;
    }

    .filter-strip .filter-label {
      font-size: 0.72rem; font-weight: 700; color: var(--text-2); white-space: nowrap;
    }

    .filter-strip select { max-width: 130px; }

    /* ─── Action Row ─────────────────────────────────────── */
    .action-row {
      display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;
    }

    .btn-apply {
      background: var(--accent); color: #fff; border: none;
      border-radius: var(--r-sm); padding: .38rem .9rem;
      font-size: .78rem; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;
      cursor: pointer; transition: opacity .15s, transform .15s;
      display: inline-flex; align-items: center; gap: 5px;
    }

    .btn-apply:hover { opacity: .88; transform: translateY(-1px); }

    .btn-report {
      background: transparent; color: var(--brand);
      border: 1.5px solid var(--brand); border-radius: var(--r-sm);
      padding: .38rem .9rem; font-size: .78rem; font-weight: 700;
      font-family: 'Plus Jakarta Sans', sans-serif; cursor: pointer;
      transition: all .15s; display: inline-flex; align-items: center; gap: 5px;
      text-decoration: none;
    }

    .btn-report:hover { background: var(--brand); color: #fff; }

    /* ─── Employees Table ────────────────────────────────── */
    .table-wrap {
      border-radius: var(--r-md);
      overflow: hidden;
      border: 1px solid var(--border);
    }

    .ded-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.76rem;
    }

    .ded-table thead th {
      background: var(--brand);
      color: #fff;
      padding: 0.6rem 0.75rem;
      font-weight: 700;
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: .4px;
      white-space: nowrap;
      position: sticky; top: 0; z-index: 2;
    }

    .ded-table thead th.text-end { text-align: right; }

    .ded-table tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    .ded-table tbody tr:last-child { border-bottom: none; }
    .ded-table tbody tr:hover { background: var(--brand-light); }
    .night-mode .ded-table tbody tr:hover { background: rgba(37,99,235,0.08); }

    .ded-table td {
      padding: 0.55rem 0.75rem;
      vertical-align: middle;
      color: var(--text);
    }

    .ded-table td.text-end { text-align: right; }

    .ded-table tfoot td {
      padding: 0.6rem 0.75rem;
      font-weight: 800;
      font-size: 0.76rem;
      background: var(--brand-light);
      color: var(--text);
      border-top: 2px solid var(--brand);
    }

    .night-mode .ded-table tfoot td { background: rgba(37,99,235,0.12); }

    .ded-table tfoot td.text-end { text-align: right; }

    /* Amount badge */
    .amt-badge {
      display: inline-block;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.72rem;
      font-weight: 500;
      padding: 2px 7px;
      border-radius: 6px;
    }

    .amt-gross  { background: rgba(16,185,129,0.1); color: #059669; }
    .amt-wtax   { background: rgba(220,38,38,0.08); color: #dc2626; }
    .amt-gsis   { background: rgba(37,99,235,0.08); color: #2563eb; }
    .amt-ph     { background: rgba(5,150,105,0.08); color: #059669; }
    .amt-pagibig{ background: rgba(180,83,9,0.08);  color: #b45309; }
    .amt-sss    { background: rgba(109,40,217,0.08);color: #6d28d9; }
    .amt-total  { background: rgba(220,38,38,0.1);  color: #dc2626; font-weight: 700; }
    .amt-net    { background: rgba(37,99,235,0.1);  color: var(--brand); font-weight: 700; }

    /* ─── Empty State ────────────────────────────────────── */
    .empty-state {
      text-align: center; padding: 2.5rem 1rem;
      color: var(--text-3);
    }

    .empty-state i { font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: .4; }
    .empty-state p { font-size: 0.8rem; margin: 0; }

    /* ─── Totals Summary Cards ───────────────────────────── */
    .totals-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 0.55rem;
      margin-bottom: 0.85rem;
    }

    .total-pill {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: 0.6rem 0.75rem;
      box-shadow: var(--shadow-xs);
    }

    .total-pill-label {
      font-size: 0.62rem; font-weight: 700; color: var(--text-3);
      text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px;
    }

    .total-pill-val {
      font-size: 0.9rem; font-weight: 800; color: var(--text);
      font-family: 'JetBrains Mono', monospace; letter-spacing: -.5px;
    }

    /* Alert */
    .alert-success {
      background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.3);
      color: #065f46; border-radius: var(--r-sm);
      font-size: 0.8rem; padding: 0.6rem 0.9rem;
    }

    .night-mode .alert-success { color: #6ee7b7; background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.2); }

    /* ─── Responsive Sidebar ─────────────────────────────── */
    @media (max-width: 991px) {
      .sidebar { position: fixed; transform: translateX(-100%); left: 0; top: 0; }
      .sidebar.open { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,.4); }
      .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1025; }
      .sidebar-overlay.show { display: block; }
      body { overflow: visible; height: auto; }
      .app  { height: auto; min-height: 100vh; }
      .content { height: auto; overflow: visible; }
      .page-body { overflow: visible; }
    }

    /* ─── Animations ─────────────────────────────────────── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .fu  { animation: fadeUp .35s ease both; }
    .d1  { animation-delay: .05s; }
    .d2  { animation-delay: .10s; }
    .d3  { animation-delay: .15s; }

    /* Night mode misc */
    .night-mode .sidebar .dropdown-menu { background: #1a2133; border-color: var(--border-2); }
    .night-mode .sidebar .dropdown-item { color: var(--text); }
    .night-mode .sidebar .dropdown-item:hover { background: rgba(37,99,235,0.15); }
    .night-mode .table-wrap { border-color: var(--border); }

    /* SweetAlert */
    .swal2-popup  { border-radius: 18px !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }
    .swal2-title  { font-weight: 800 !important; }
  </style>
</head>
<body>

<!-- Mobile overlay -->
@include('layouts.sidebar')

<div class="app">

  <!-- ══════════ MAIN CONTENT ══════════ -->
  <div class="content">

    <!-- ── TOPBAR ── -->
    <header class="topbar">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" id="mobileMenuBtn" aria-label="Menu"
              style="border-radius:var(--r-sm); padding:.3rem .5rem;">
        <i class="bi bi-list" style="font-size:1.2rem;"></i>
      </button>

      <div class="welcome-block d-none d-sm-block">
        <p>Welcome back, <span>{{ session('user_name', 'Admin') }}</span>
          @if(!empty($userDepartment))
          <small style="color:var(--text-3); font-weight:500; font-size:.7rem;"> · {{ ucfirst($userDepartment) }}</small>
          @endif
        </p>
      </div>

      <div class="topbar-pill d-none d-md-flex ms-1">
        <span class="pulse-dot"></span>
        Live Analytics
      </div>

      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="topbar-clock d-none d-lg-inline" id="liveClock"></span>

        <button class="btn btn-sm theme-btn" id="toggleTheme" title="Toggle theme">
          <i class="bi bi-moon" id="themeIcon"></i>
        </button>

        <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger d-none d-md-inline-flex align-items-center gap-1"
           style="border-radius:var(--r-sm); font-size:.78rem; font-weight:600;"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      </div>
    </header>

    <!-- ── PAGE BODY ── -->
    <div class="page-body">

      <!-- Page Header -->
      <div class="page-header fu">
        <div>
          <div class="page-title"><i class="bi bi-percent" style="color:var(--brand);margin-right:6px;"></i>Tax & Gov't Deductions</div>
          <div class="page-subtitle">Configure rates and apply government deductions per payroll period</div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert-success mb-3 fu">
        <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
      </div>
      @endif

      @if($errors->any())
      <div class="alert alert-danger mb-3 fu" style="font-size:0.8rem;border-radius:var(--r-sm);">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      <!-- ═══ SETTINGS CARD ═══ -->
      <div class="section-card fu d1">
        <div class="section-card-header">
          <div class="section-card-title">
            <i class="bi bi-sliders"></i>
            Deduction Settings
          </div>
          <small style="font-size:0.68rem; color:var(--text-3);">Changes apply to all future payroll computations</small>
        </div>

        <div class="section-card-body">

          {{--
            FIX 1: Unified naming → deductions[key][field]
            FIX 2: Hidden input before each checkbox ensures is_active=0 is always submitted.
                   When checked: checkbox value (1) wins. When unchecked: hidden (0) wins.
            Controller must process $request->deductions as associative array.
          --}}
          <form action="{{ route('admin.deductions.update-settings') }}" method="POST" id="settingsForm">
            @csrf

            @php
              $dedConfig = [
                'withholding_tax' => [
                  'label' => 'Withholding Tax',
                  'desc'  => 'Income tax withheld at source',
                  'icon'  => 'bi-receipt-cutoff',
                  'color' => '#dc2626',
                  'bg'    => '#fee2e2',
                ],
                'gsis' => [
                  'label' => 'GSIS',
                  'desc'  => 'Gov\'t Service Insurance System',
                  'icon'  => 'bi-shield-check',
                  'color' => '#2563eb',
                  'bg'    => '#dbeafe',
                ],
                'philhealth' => [
                  'label' => 'PhilHealth',
                  'desc'  => 'National health insurance',
                  'icon'  => 'bi-heart-pulse',
                  'color' => '#059669',
                  'bg'    => '#d1fae5',
                ],
                'pag_ibig' => [
                  'label' => 'Pag-IBIG',
                  'desc'  => 'Home Development Mutual Fund',
                  'icon'  => 'bi-house-heart',
                  'color' => '#b45309',
                  'bg'    => '#fef3c7',
                ],
                'sss' => [
                  'label' => 'SSS',
                  'desc'  => 'Social Security System',
                  'icon'  => 'bi-umbrella',
                  'color' => '#6d28d9',
                  'bg'    => '#ede9fe',
                ],
              ];
            @endphp

            <!-- Column headers (hidden on small screens) -->
            <div class="ded-rows mb-1 d-none d-xl-block" style="padding: 0 0.85rem;">
              <div style="display:grid; grid-template-columns:220px 120px 150px 100px 110px 110px; gap:0.65rem; font-size:0.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-3);">
                <span>Deduction</span>
                <span>Active</span>
                <span>Rate Type</span>
                <span>Value</span>
                <span>Min (₱)</span>
                <span>Max (₱)</span>
              </div>
            </div>

            <div class="ded-rows">
              @foreach($dedConfig as $key => $cfg)
                @php $s = $settings[$key] ?? null; @endphp
                <div class="ded-row">

                  <!-- Name + icon -->
                  <div class="ded-info">
                    <div class="ded-icon" style="background:{{ $cfg['bg'] }}; color:{{ $cfg['color'] }};">
                      <i class="{{ $cfg['icon'] }}"></i>
                    </div>
                    <div>
                      <div class="ded-name">{{ $cfg['label'] }}</div>
                      <div class="ded-key">{{ $cfg['desc'] }}</div>
                    </div>
                  </div>

                  <!-- Active toggle — FIX: hidden input ensures 0 submitted when unchecked -->
                  <div class="ded-active">
                    <div>
                      <div class="ded-field-label d-xl-none">Active</div>
                      <div class="form-check form-switch mb-0">
                        {{-- Hidden input: default value when checkbox is unchecked --}}
                        <input type="hidden" name="deductions[{{ $key }}][is_active]" value="0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               name="deductions[{{ $key }}][is_active]"
                               value="1"
                               id="active_{{ $key }}"
                               {{ ($s->is_active ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active_{{ $key }}">
                          {{ ($s->is_active ?? 1) ? 'Enabled' : 'Disabled' }}
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Rate Type -->
                  <div>
                    <label class="ded-field-label">Rate Type</label>
                    <select name="deductions[{{ $key }}][rate_type]" class="form-select form-select-sm">
                      <option value="percentage" {{ ($s->rate_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                      <option value="fixed"      {{ ($s->rate_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount (₱)</option>
                    </select>
                  </div>

                  <!-- Rate Value -->
                  <div>
                    <label class="ded-field-label">Value</label>
                    <input type="number" step="0.0001" min="0"
                           name="deductions[{{ $key }}][rate_value]"
                           class="form-control form-control-sm text-end"
                           value="{{ $s->rate_value ?? 0 }}"
                           placeholder="0.00">
                  </div>

                  <!-- Min Amount -->
                  <div>
                    <label class="ded-field-label">Min (₱)</label>
                    <input type="number" step="0.01" min="0"
                           name="deductions[{{ $key }}][min_amount]"
                           class="form-control form-control-sm text-end"
                           value="{{ $s->min_amount ?? '' }}"
                           placeholder="None">
                  </div>

                  <!-- Max Amount -->
                  <div>
                    <label class="ded-field-label">Max (₱)</label>
                    <input type="number" step="0.01" min="0"
                           name="deductions[{{ $key }}][max_amount]"
                           class="form-control form-control-sm text-end"
                           value="{{ $s->max_amount ?? '' }}"
                           placeholder="None">
                  </div>

                </div><!-- /ded-row -->
              @endforeach
            </div><!-- /ded-rows -->

            <div class="mt-3 d-flex align-items-center gap-2">
              <button type="submit" class="btn btn-sm btn-primary" style="background:var(--brand); border:none; border-radius:var(--r-sm); font-weight:700; font-size:.78rem;">
                <i class="bi bi-save me-1"></i>Save Settings
              </button>
              <small style="color:var(--text-3); font-size:0.68rem;">Min/Max cap the computed deduction amount. Leave blank for no cap.</small>
            </div>
          </form>
        </div>
      </div><!-- /settings card -->

      <!-- ═══ COMPUTE DEDUCTIONS CARD ═══ -->
      <div class="section-card fu d2">
        <div class="section-card-header">
          <div class="section-card-title">
            <i class="bi bi-people"></i>
            Compute Deductions for Period
          </div>
        </div>

        <div class="section-card-body">

          <!-- Filter + Actions Row -->
          <div class="d-flex flex-wrap gap-2 align-items-center mb-3">

            <!-- Period Filter -->
            <form method="GET" action="{{ route('admin.deductions.index') }}" class="filter-strip" id="filterForm">
              <span class="filter-label"><i class="bi bi-calendar3 me-1"></i>Period:</span>

              <select name="month" class="form-select form-select-sm" style="width:130px;" onchange="this.form.submit()">
                @foreach($months as $val => $label)
                  <option value="{{ $val }}" {{ (int)$month === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>

              <select name="year" class="form-select form-select-sm" style="width:100px;" onchange="this.form.submit()">
                @foreach($years as $y)
                  <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
              </select>

              <select name="period" class="form-select form-select-sm" style="width:130px;" onchange="this.form.submit()">
                <option value="auto"   {{ $period == 'auto'   ? 'selected' : '' }}>Auto Period</option>
                <option value="1-15"   {{ $period == '1-15'   ? 'selected' : '' }}>1st Half (1–15)</option>
                <option value="16-end" {{ $period == '16-end' ? 'selected' : '' }}>2nd Half (16–end)</option>
                <option value="all"    {{ $period == 'all'    ? 'selected' : '' }}>All</option>
              </select>
            </form>

            <!-- Actions -->
            <div class="action-row ms-auto">
              {{-- Apply Deductions — confirmation required (irreversible) --}}
              <form method="POST" action="{{ route('admin.deductions.apply') }}" id="applyForm">
                @csrf
                <input type="hidden" name="month"  value="{{ $month }}">
                <input type="hidden" name="year"   value="{{ $year }}">
                <input type="hidden" name="period" value="{{ $period }}">
                <button type="button" class="btn-apply" id="applyBtn"
                        data-month="{{ $month }}" data-year="{{ $year }}" data-period="{{ $period }}">
                  <i class="bi bi-check2-circle"></i>
                  Apply Deductions
                </button>
              </form>

              <a href="{{ route('admin.deductions.summary', ['month' => $month, 'year' => $year]) }}"
                 class="btn-report">
                <i class="bi bi-printer"></i>
                Summary Report
              </a>
            </div>
          </div>

          <!-- Summary Totals (visible when data exists) -->
          @if(count($employees) > 0)
          <div class="totals-row fu d3">
            <div class="total-pill">
              <div class="total-pill-label">Gross Pay</div>
              <div class="total-pill-val" style="color:#059669;">₱{{ number_format($stats['total_gross'], 2) }}</div>
            </div>
            <div class="total-pill">
              <div class="total-pill-label">W/Tax</div>
              <div class="total-pill-val" style="color:#dc2626;">₱{{ number_format($stats['total_wtax'], 2) }}</div>
            </div>
            <div class="total-pill">
              <div class="total-pill-label">GSIS</div>
              <div class="total-pill-val" style="color:#2563eb;">₱{{ number_format($stats['total_gsis'], 2) }}</div>
            </div>
            <div class="total-pill">
              <div class="total-pill-label">PhilHealth</div>
              <div class="total-pill-val" style="color:#059669;">₱{{ number_format($stats['total_philhealth'], 2) }}</div>
            </div>
            <div class="total-pill">
              <div class="total-pill-label">Pag-IBIG</div>
              <div class="total-pill-val" style="color:#b45309;">₱{{ number_format($stats['total_pagibig'], 2) }}</div>
            </div>
            <div class="total-pill">
              <div class="total-pill-label">SSS</div>
              <div class="total-pill-val" style="color:#6d28d9;">₱{{ number_format($stats['total_sss'], 2) }}</div>
            </div>
            <div class="total-pill">
              <div class="total-pill-label">Total Deductions</div>
              <div class="total-pill-val" style="color:#dc2626;">₱{{ number_format($stats['total_govt_ded'], 2) }}</div>
            </div>
            <div class="total-pill">
              <div class="total-pill-label">Net Pay</div>
              <div class="total-pill-val" style="color:var(--brand);">₱{{ number_format($stats['total_net_pay'], 2) }}</div>
            </div>
          </div>
          @endif

          <!-- Table -->
          <div style="overflow-x: auto;">
            <div class="table-wrap">
              <table class="ded-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Designation</th>
                    <th class="text-end">Gross Pay</th>
                    <th class="text-end">W/Tax</th>
                    <th class="text-end">GSIS</th>
                    <th class="text-end">PhilHealth</th>
                    <th class="text-end">Pag-IBIG</th>
                    <th class="text-end">SSS</th>
                    <th class="text-end">Total Ded.</th>
                    <th class="text-end">Net Pay</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($employees as $i => $emp)
                  <tr>
                    <td style="color:var(--text-3); width:32px;">{{ $i + 1 }}</td>
                    <td style="font-weight:700; white-space:nowrap;">{{ $emp->employee_name }}</td>
                    <td style="color:var(--text-2); white-space:nowrap;">{{ $emp->designation ?? '—' }}</td>
                    <td class="text-end">
                      <span class="amt-badge amt-gross">₱{{ number_format($emp->gross_pay, 2) }}</span>
                    </td>
                    <td class="text-end">
                      <span class="amt-badge amt-wtax">₱{{ number_format($emp->withholding_tax_val, 2) }}</span>
                    </td>
                    <td class="text-end">
                      <span class="amt-badge amt-gsis">₱{{ number_format($emp->gsis_val, 2) }}</span>
                    </td>
                    <td class="text-end">
                      <span class="amt-badge amt-ph">₱{{ number_format($emp->philhealth_val, 2) }}</span>
                    </td>
                    <td class="text-end">
                      <span class="amt-badge amt-pagibig">₱{{ number_format($emp->pag_ibig_val, 2) }}</span>
                    </td>
                    <td class="text-end">
                      <span class="amt-badge amt-sss">₱{{ number_format($emp->sss_val, 2) }}</span>
                    </td>
                    <td class="text-end">
                      <span class="amt-badge amt-total">₱{{ number_format($emp->total_govt_ded, 2) }}</span>
                    </td>
                    <td class="text-end">
                      <span class="amt-badge amt-net">₱{{ number_format($emp->net_pay, 2) }}</span>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="11">
                      <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No employees found for this period.<br>
                           <small style="font-size:0.72rem;">Try selecting a different month, year, or period.</small>
                        </p>
                      </div>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
                @if(count($employees) > 0)
                <tfoot>
                  <tr>
                    <td colspan="3" class="text-end" style="color:var(--text-3); font-size:0.68rem; text-transform:uppercase; letter-spacing:.4px;">Totals</td>
                    <td class="text-end" style="color:#059669;">₱{{ number_format($stats['total_gross'], 2) }}</td>
                    <td class="text-end" style="color:#dc2626;">₱{{ number_format($stats['total_wtax'], 2) }}</td>
                    <td class="text-end" style="color:#2563eb;">₱{{ number_format($stats['total_gsis'], 2) }}</td>
                    <td class="text-end" style="color:#059669;">₱{{ number_format($stats['total_philhealth'], 2) }}</td>
                    <td class="text-end" style="color:#b45309;">₱{{ number_format($stats['total_pagibig'], 2) }}</td>
                    <td class="text-end" style="color:#6d28d9;">₱{{ number_format($stats['total_sss'], 2) }}</td>
                    <td class="text-end" style="color:#dc2626;">₱{{ number_format($stats['total_govt_ded'], 2) }}</td>
                    <td class="text-end" style="color:var(--brand);">₱{{ number_format($stats['total_net_pay'], 2) }}</td>
                  </tr>
                </tfoot>
                @endif
              </table>
            </div><!-- /table-wrap -->
          </div><!-- /overflow-x -->

        </div><!-- /section-card-body -->
      </div><!-- /compute card -->

    </div><!-- /page-body -->
  </div><!-- /content -->
</div><!-- /app -->

<script>
// ─── Sidebar ────────────────────────────────────────────────
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}
document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);

// ─── Clock ─────────────────────────────────────────────────
(function tickClock() {
  const el = document.getElementById('liveClock');
  if (el) el.textContent = new Date().toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit', second:'2-digit' });
  setTimeout(tickClock, 1000);
})();

// ─── Theme Toggle ───────────────────────────────────────────
const themeIcon = document.getElementById('themeIcon');
if (localStorage.getItem('theme') === 'dark') {
  document.body.classList.add('night-mode');
  themeIcon?.classList.replace('bi-moon', 'bi-sun');
}
document.getElementById('toggleTheme')?.addEventListener('click', () => {
  const dark = document.body.classList.toggle('night-mode');
  localStorage.setItem('theme', dark ? 'dark' : 'light');
  themeIcon?.classList.replace(dark ? 'bi-moon' : 'bi-sun', dark ? 'bi-sun' : 'bi-moon');
  // Update toggle labels in deduction rows
  document.querySelectorAll('.form-check-input[role="switch"]').forEach(cb => {
    const lbl = cb.nextElementSibling;
    if (lbl) lbl.textContent = cb.checked ? 'Enabled' : 'Disabled';
  });
});

// ─── Active toggle label update ─────────────────────────────
document.querySelectorAll('.form-check-input[role="switch"]').forEach(cb => {
  cb.addEventListener('change', () => {
    const lbl = cb.nextElementSibling;
    if (lbl) lbl.textContent = cb.checked ? 'Enabled' : 'Disabled';
  });
});

// ─── Apply Deductions — SweetAlert confirmation ─────────────
document.getElementById('applyBtn')?.addEventListener('click', function () {
  const btn = this;
  const month  = btn.dataset.month;
  const year   = btn.dataset.year;
  const period = btn.dataset.period;

  // Map numeric month to name
  const monthNames = ['','January','February','March','April','May','June',
                      'July','August','September','October','November','December'];

  Swal.fire({
    icon: 'warning',
    title: 'Apply Deductions?',
    html: `This will compute and save government deductions for all employees in <strong>${monthNames[month] || month} ${year} (${period})</strong>.<br><br>Existing deduction records for this period will be overwritten.`,
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i>Yes, Apply',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#64748b',
    reverseButtons: true,
    focusCancel: true,
    customClass: { popup: 'swal2-popup' },
  }).then(result => {
    if (result.isConfirmed) {
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Applying…';
      btn.disabled = true;
      document.getElementById('applyForm').submit();
    }
  });
});

// ─── Send Payslips — SweetAlert confirmation ─────────────────
document.getElementById('sendPayslipsBtn')?.addEventListener('click', function () {
  Swal.fire({
    icon: 'question',
    title: 'Send Payslips to All?',
    text: 'This will email payslips to every employee on record. This action cannot be undone.',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-send-check me-1"></i>Send Now',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#64748b',
    reverseButtons: true,
  }).then(result => {
    if (result.isConfirmed) {
      // Optionally prompt for date range first
      document.getElementById('sendPayslipsForm').submit();
    }
  });
});

// ─── Settings form — prevent accidental submit on Enter ──────
document.getElementById('settingsForm')?.addEventListener('keydown', function (e) {
  if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
    e.preventDefault();
  }
});

// ─── Auto-submit period filter on change (already on selects via onchange)
// Fallback for environments where inline onchange is stripped:
document.querySelectorAll('#filterForm select').forEach(sel => {
  sel.addEventListener('change', () => document.getElementById('filterForm').submit());
});
</script>

</body>
</html>