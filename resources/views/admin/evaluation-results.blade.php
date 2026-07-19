{{--
  ╔══════════════════════════════════════════════════════════════════════╗
  ║  MCC ADMIN PANEL — EVALUATION RESULTS                               ║
  ║  File: resources/views/admin/evaluation-results.blade.php           ║
  ╚══════════════════════════════════════════════════════════════════════╝
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Evaluation Results — MCC Digital Payroll V2</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <style>
  /* ── Variables ─────────────────────────────────────── */
  :root {
    --brand:        #2563eb;
    --brand-dark:   #1d4ed8;
    --brand-mid:    #3b82f6;
    --brand-light:  #eff6ff;
    --brand-glow:   rgba(37,99,235,0.15);
    --accent:       #10b981;
    --accent-light: #d1fae5;
    --warn:         #f59e0b;
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

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    overflow: hidden;
    height: 100vh;
  }

  /* ── App Shell ─────────────────────────────────────── */
  .app { display: flex; height: 100vh; overflow: hidden; }

  /* ── Sidebar ───────────────────────────────────────── */
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
  .sidebar-header { padding: 1.1rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); flex-shrink: 0; }
  .sidebar-logo { display: flex; align-items: center; gap: 10px; }
  .sidebar-logo img { width: 34px; height: 34px; border-radius: 8px; object-fit: contain; background: rgba(255,255,255,0.08); padding: 4px; }
  .brand-name { font-size: .82rem; font-weight: 800; color: #fff; letter-spacing: -.2px; }
  .brand-sub  { font-size: .65rem; color: rgba(255,255,255,0.38); font-weight: 400; letter-spacing: .3px; }
  .sidebar-nav { flex: 1; padding: .6rem .65rem 1rem; display: flex; flex-direction: column; gap: 1px; overflow-y: auto; overflow-x: hidden; }
  .nav-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.4px; color: rgba(255,255,255,0.22); padding: .9rem .55rem .25rem; }
  .sidebar .nav-link, .sidebar-btn {
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
  .sidebar .nav-link i, .sidebar-btn i { font-size: .95rem; width: 17px; flex-shrink: 0; opacity: .8; }
  .sidebar .nav-link:hover, .sidebar-btn:hover { background: var(--sidebar-hover); color: #fff; }
  .sidebar .nav-link:hover i, .sidebar-btn:hover i { opacity: 1; }
  .sidebar .nav-link.active { background: var(--sidebar-active); color: #fff; }
  .sidebar .nav-link.active i { opacity: 1; }
  .sidebar-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: .6rem; font-weight: 700; border-radius: 10px; padding: 1px 6px; }
  .sidebar .dropdown-menu { border-radius: var(--r-sm); border: 1px solid var(--border); box-shadow: var(--shadow-md); padding: .35rem; background: var(--card); }
  .sidebar .dropdown-item { border-radius: 7px; padding: .42rem .8rem; font-size: .8rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 500; color: var(--text); display: flex; align-items: center; gap: 7px; transition: background .13s; }
  .sidebar .dropdown-item:hover { background: var(--brand-light); color: var(--brand); }
  .sidebar-footer { padding: .65rem; border-top: 1px solid rgba(255,255,255,0.05); flex-shrink: 0; }
  .sidebar-logout { background: none; border: none; color: rgba(255,255,255,.45); width: 100%; text-align: left; padding: .5rem .65rem; border-radius: var(--r-sm); font-size: .82rem; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; gap: 9px; cursor: pointer; transition: all .18s; }
  .sidebar-logout:hover { background: rgba(239,68,68,.15); color: #fca5a5; }

  /* ── Content ───────────────────────────────────────── */
  .content { flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

  /* ── Topbar ────────────────────────────────────────── */
  .topbar {
    height: var(--topbar-h);
    background: var(--card);
    border-bottom: 1px solid var(--border);
    padding: 0 1.2rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: .75rem;
    box-shadow: var(--shadow-xs);
    z-index: 100;
  }
  .topbar-title { font-size: .95rem; font-weight: 800; color: var(--text); letter-spacing: -.3px; }
  .topbar-sub   { font-size: .7rem; color: var(--text-3); font-weight: 500; }
  .live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); display: inline-block; animation: pulse 2s infinite; }
  @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

  /* ── Btn Export ────────────────────────────────────── */
  .btn-export {
    display: inline-flex; align-items: center; gap: 5px;
    padding: .35rem .9rem; border-radius: var(--r-sm);
    background: var(--brand); color: #fff; border: none;
    font-size: .78rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;
    cursor: pointer; transition: all .2s; text-decoration: none;
  }
  .btn-export:hover { background: var(--brand-dark); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px var(--brand-glow); }
  .btn-export-outline {
    display: inline-flex; align-items: center; gap: 5px;
    padding: .35rem .9rem; border-radius: var(--r-sm);
    background: transparent; color: var(--brand); border: 1px solid var(--brand);
    font-size: .78rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;
    cursor: pointer; transition: all .2s; text-decoration: none;
  }
  .btn-export-outline:hover { background: var(--brand-light); color: var(--brand); }

  /* ── Page Body ─────────────────────────────────────── */
  .page-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: .55rem .85rem .5rem;
    min-height: 0;
    gap: .45rem;
  }

  /* ── KPI Strip ─────────────────────────────────────── */
  .kpi-strip {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .4rem;
    flex-shrink: 0;
  }
  .kpi-card {
    background: var(--card);
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-xs);
    padding: .6rem .75rem .55rem;
    position: relative;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
  }
  .kpi-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
  .kpi-card::after {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2.5px;
    background: var(--kc, var(--brand));
    border-radius: var(--r-md) var(--r-md) 0 0;
  }
  .kpi-icon { width: 28px; height: 28px; border-radius: 7px; display: grid; place-items: center; margin-bottom: .3rem; background: var(--ki, var(--brand-light)); }
  .kpi-icon i { font-size: .85rem; color: var(--kc, var(--brand)); }
  .kpi-val { font-size: 1.4rem; font-weight: 800; color: var(--text); letter-spacing: -1px; line-height: 1; }
  .kpi-label { font-size: .62rem; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: .4px; margin-top: 1px; }

  /* ── Main Grid ─────────────────────────────────────── */
  .main-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr 1.1fr;
    gap: .4rem;
    flex: 1;
    min-height: 0;
    overflow: hidden;
  }

  /* ── Chart Card ────────────────────────────────────── */
  .chart-card {
    background: var(--card);
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-xs);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
  }
  .chart-card-hd {
    padding: .6rem .85rem .5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
  }
  .chart-card-title { font-size: .78rem; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 6px; }
  .chart-card-sub { font-size: .65rem; color: var(--text-3); }
  .chart-area { flex: 1; min-height: 0; position: relative; padding: .5rem; }
  .chart-inner { position: absolute; inset: .5rem; }

  /* ── Donut legend ──────────────────────────────────── */
  .donut-legend { display: flex; flex-direction: column; gap: 4px; padding: 0 .85rem .65rem; flex-shrink: 0; }
  .legend-row { display: flex; align-items: center; gap: 7px; font-size: .72rem; font-weight: 600; color: var(--text-2); }
  .legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
  .legend-count { margin-left: auto; font-family: 'JetBrains Mono', monospace; font-size: .72rem; font-weight: 700; color: var(--text); }

  /* ── Interpretation scale ──────────────────────────── */
  .scale-wrap { padding: 0 .85rem .65rem; flex-shrink: 0; border-top: 1px solid var(--border); }
  .scale-title { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text-3); padding: .5rem 0 .3rem; }
  .scale-row { display: flex; align-items: center; gap: 7px; padding: .18rem 0; }
  .scale-pill { font-size: .62rem; font-weight: 700; border-radius: 20px; padding: .12rem .5rem; min-width: 70px; text-align: center; }
  .scale-bar-wrap { flex: 1; height: 5px; background: var(--border); border-radius: 3px; overflow: hidden; }
  .scale-bar { height: 100%; border-radius: 3px; }
  .scale-arrow { font-size: .7rem; flex-shrink: 0; opacity: 0; }
  .scale-arrow.active { opacity: 1; }

  /* ── Response Table ────────────────────────────────── */
  .resp-wrap { flex: 1; overflow-y: auto; min-height: 0; }
  .resp-table { width: 100%; border-collapse: collapse; font-size: .75rem; }
  .resp-table th { background: #f8fafc; padding: .45rem .7rem; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text-3); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 1; }
  .resp-table td { padding: .45rem .7rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
  .resp-table tr:last-child td { border-bottom: none; }
  .resp-table tr:hover td { background: #f8faff; }
  .role-pill { display: inline-flex; align-items: center; padding: .12rem .5rem; border-radius: 20px; font-size: .67rem; font-weight: 700; }
  .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-3); font-size: .82rem; gap: .5rem; }

  /* ── Verdict badge in topbar ───────────────────────── */
  .verdict-badge { display: inline-flex; align-items: center; gap: 5px; padding: .22rem .75rem; border-radius: 20px; font-size: .72rem; font-weight: 700; }

  /* ── Responsive sidebar ────────────────────────────── */
  @media (max-width: 991px) {
    .sidebar { position: fixed; transform: translateX(-100%); left: 0; top: 0; }
    .sidebar.open { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,.4); }
  }
  .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1025; }
  .sidebar-overlay.show { display: block; }

  /* Scrollbar */
  .resp-wrap::-webkit-scrollbar { width: 4px; }
  .resp-wrap::-webkit-scrollbar-track { background: transparent; }
  .resp-wrap::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 3px; }
  </style>
</head>
<body>

<div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

<div class="app">

  <!-- ══════════ SIDEBAR ══════════ -->
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
      <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i>Dashboard</a>

      <div class="nav-label">Management</div>

      <div class="dropdown">
        <button class="sidebar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
          @foreach(['130','150','170','190','210','220','250'] as $rate)
          <li><a class="dropdown-item" href="#" onclick="showInstructorRate('{{ $rate }}'); return false;"><i class="bi bi-currency-dollar"></i>₱{{ $rate }}</a></li>
          @endforeach
        </ul>
      </div>

      <div class="dropdown">
        <button class="sidebar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
        <button class="sidebar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-clipboard-data"></i><span>History Records</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('admin.history') }}"><i class="bi bi-calendar-check"></i>History Log</a></li>
          <li><a class="dropdown-item" href="{{ route('admin.payroll.history') }}"><i class="bi bi-scissors"></i>Payroll History</a></li>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item" href="{{ route('admin.employee.timesheets.submissions') }}"><i class="bi bi-clock-history"></i>Submitted Timesheets</a></li>
        </ul>
      </div>

      <a href="{{ route('master.list') }}" class="sidebar-btn text-decoration-none">
        <i class="bi bi-list-ul"></i><span>Master List</span>
      </a>

      <a href="{{ route('admin.salary.adjustment') }}" class="sidebar-btn text-decoration-none">
        <i class="bi bi-calculator"></i><span>Salary Adjustment</span>
      </a>

      <div class="nav-label">Evaluation</div>

      <a href="{{ route('admin.evaluation.results') }}" class="nav-link active">
        <i class="bi bi-bar-chart-fill"></i><span>Evaluation Results</span>
        @if(($responses ?? 0) > 0)
          <span class="sidebar-badge">{{ $responses }}</span>
        @endif
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
      <form action="{{ route('logout') }}" method="POST" id="logout-form-eval">@csrf</form>
      <button class="sidebar-logout" onclick="document.getElementById('logout-form-eval').submit()">
        <i class="bi bi-box-arrow-left"></i> Sign Out
      </button>
    </div>
  </aside>

  <!-- ══════════ MAIN CONTENT ══════════ -->
  <div class="content">

    <!-- TOPBAR -->
    <header class="topbar">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" id="mobileMenuBtn" style="border-radius:8px;padding:.3rem .5rem;">
        <i class="bi bi-list" style="font-size:1.2rem;"></i>
      </button>

      <div>
        <div class="topbar-title">Evaluation Results</div>
        <div class="topbar-sub">MCC Digital Payroll V2 · Usability Assessment</div>
      </div>

      @php
        $ov = $overallAvg ?? 0;
        $verdict     = $ov >= 4.20 ? 'Excellent'  : ($ov >= 3.40 ? 'Good' : ($ov >= 2.60 ? 'Moderate' : ($ov >= 1.80 ? 'Poor' : 'Very Poor')));
        $vColor      = $ov >= 4.20 ? '#16a34a'    : ($ov >= 3.40 ? '#2563eb' : ($ov >= 2.60 ? '#b45309' : ($ov >= 1.80 ? '#ea580c' : '#dc2626')));
        $vBg         = $ov >= 4.20 ? 'rgba(34,197,94,.1)' : ($ov >= 3.40 ? 'rgba(59,130,246,.1)' : ($ov >= 2.60 ? 'rgba(245,158,11,.1)' : ($ov >= 1.80 ? 'rgba(249,115,22,.1)' : 'rgba(239,68,68,.1)')));
      @endphp

      <span class="verdict-badge d-none d-md-inline-flex" style="background:{{ $vBg }};color:{{ $vColor }};">
        <i class="bi bi-patch-check-fill"></i>
        Overall: {{ number_format($ov, 2) }} · {{ $verdict }}
      </span>

      <span class="live-dot ms-1"></span>
      <span style="font-size:.72rem;color:var(--text-3);font-weight:600;">Live</span>

      <div class="ms-auto d-flex align-items-center gap-2">
        <a href="{{ route('admin.evaluation.results') }}?export=csv" class="btn-export">
          <i class="bi bi-download"></i> Export CSV
        </a>
        <a href="{{ route('employee.evaluation.form') }}" class="btn-export-outline">
          <i class="bi bi-pencil-square"></i> Fill Form
        </a>
        <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger d-none d-md-inline-flex align-items-center gap-1"
           style="border-radius:8px;font-size:.78rem;font-weight:600;"
           onclick="event.preventDefault();document.getElementById('logout-form-eval').submit();">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </header>

    <!-- PAGE BODY -->
    <div class="page-body">

      <!-- ══ KPI STRIP ══ -->
      <div class="kpi-strip">
        @php
          $kpis = [
            ['icon'=>'people-fill',       'val'=>$responses??0,                        'label'=>'Respondents',   'kc'=>'#2563eb','ki'=>'rgba(37,99,235,.1)'],
            ['icon'=>'hand-index-thumb',  'val'=>number_format($avgUsability??0,2),    'label'=>'Usability',     'kc'=>'#6366f1','ki'=>'rgba(99,102,241,.1)'],
            ['icon'=>'lightning-charge',  'val'=>number_format($avgEfficiency??0,2),   'label'=>'Efficiency',    'kc'=>'#10b981','ki'=>'rgba(16,185,129,.1)'],
            ['icon'=>'emoji-smile',       'val'=>number_format($avgSatisfaction??0,2), 'label'=>'Satisfaction',  'kc'=>'#f59e0b','ki'=>'rgba(245,158,11,.1)'],
            ['icon'=>'star-fill',         'val'=>number_format($overallAvg??0,2),      'label'=>'Overall Avg',   'kc'=>$vColor,  'ki'=>$vBg],
          ];
        @endphp
        @foreach($kpis as $k)
        <div class="kpi-card" style="--kc:{{ $k['kc'] }};--ki:{{ $k['ki'] }};">
          <div class="kpi-icon"><i class="bi bi-{{ $k['icon'] }}"></i></div>
          <div class="kpi-val">{{ $k['val'] }}</div>
          <div class="kpi-label">{{ $k['label'] }}</div>
        </div>
        @endforeach
      </div>

      <!-- ══ MAIN 3-COLUMN GRID ══ -->
      <div class="main-grid">

        <!-- ── COL 1: Role Donut + Interpretation Scale ── -->
        <div class="chart-card">
          <div class="chart-card-hd">
            <div class="chart-card-title"><i class="bi bi-pie-chart-fill" style="color:var(--brand);"></i>Respondents by Role</div>
          </div>
          <div class="chart-area" style="flex:0 0 auto;height:160px;">
            <div class="chart-inner"><canvas id="roleChart"></canvas></div>
          </div>
          <div class="donut-legend" id="roleLegend"></div>

          <!-- Interpretation Scale -->
          <div class="scale-wrap">
            <div class="scale-title">Score Interpretation</div>
            @php
              $scales = [
                ['4.20–5.00','Excellent','#16a34a','rgba(34,197,94,.12)'  ,4.20],
                ['3.40–4.19','Good',     '#2563eb','rgba(59,130,246,.12)' ,3.40],
                ['2.60–3.39','Moderate', '#b45309','rgba(245,158,11,.12)',2.60],
                ['1.80–2.59','Poor',     '#ea580c','rgba(249,115,22,.12)' ,1.80],
                ['1.00–1.79','Very Poor','#dc2626','rgba(239,68,68,.12)'  ,1.00],
              ];
            @endphp
            @foreach($scales as $i => [$range, $label, $color, $bg, $thresh])
            @php $isHere = ($ov >= $thresh) && ($i === 0 || $ov < $scales[$i-1][4]); @endphp
            <div class="scale-row">
              <span class="scale-pill" style="background:{{ $bg }};color:{{ $color }};">{{ $label }}</span>
              <span style="font-size:.62rem;color:var(--text-3);min-width:60px;">{{ $range }}</span>
              <i class="bi bi-arrow-left-circle-fill scale-arrow {{ $isHere ? 'active' : '' }}" style="color:{{ $color }};"></i>
            </div>
            @endforeach
          </div>
        </div>

        <!-- ── COL 2: Category Bar Chart ── -->
        <div class="chart-card">
          <div class="chart-card-hd">
            <div class="chart-card-title"><i class="bi bi-bar-chart-fill" style="color:#6366f1;"></i>Average Score by Category</div>
            <span class="chart-card-sub">out of 5.00</span>
          </div>
          <div class="chart-area">
            <div class="chart-inner"><canvas id="categoryChart"></canvas></div>
          </div>
        </div>

        <!-- ── COL 3: Recent Responses Table ── -->
        <div class="chart-card">
          <div class="chart-card-hd">
            <div class="chart-card-title"><i class="bi bi-table" style="color:var(--brand);"></i>Recent Responses</div>
            <span class="chart-card-sub">Latest {{ min(($recentResponses ?? collect())->count(), 10) }} entries</span>
          </div>
          <div class="resp-wrap">
            @if(($recentResponses ?? collect())->isEmpty())
              <div class="empty-state">
                <i class="bi bi-inbox" style="font-size:1.8rem;"></i>
                No responses yet.
              </div>
            @else
            <table class="resp-table">
              <thead>
                <tr>
                  <th>Role</th>
                  <th>U</th>
                  <th>E</th>
                  <th>S</th>
                  <th>Avg</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentResponses ?? [] as $r)
                @php
                  $rov = $r->overall_avg ?? 0;
                  $rv = $rov >= 4.20 ? ['#16a34a','rgba(34,197,94,.1)'] : ($rov >= 3.40 ? ['#2563eb','rgba(59,130,246,.1)'] : ($rov >= 2.60 ? ['#b45309','rgba(245,158,11,.1)'] : ($rov >= 1.80 ? ['#ea580c','rgba(249,115,22,.1)'] : ['#dc2626','rgba(239,68,68,.1)'])));
                  $rp = ['Administrator'=>['#2563eb','rgba(37,99,235,.1)'],'Faculty'=>['#7c3aed','rgba(124,58,237,.1)'],'Staff'=>['#10b981','rgba(16,185,129,.1)'],'Other'=>['#f59e0b','rgba(245,158,11,.1)']][$r->respondent_role ?? 'Other'] ?? ['#6b7a90','rgba(107,122,144,.1)'];
                @endphp
                <tr>
                  <td>
                    <span class="role-pill" style="background:{{ $rp[1] }};color:{{ $rp[0] }};">
                      {{ Str::limit($r->respondent_role ?? 'N/A', 8) }}
                    </span>
                  </td>
                  <td style="color:#6366f1;font-weight:700;">{{ number_format($r->avg_usability??0,1) }}</td>
                  <td style="color:#10b981;font-weight:700;">{{ number_format($r->avg_efficiency??0,1) }}</td>
                  <td style="color:#f59e0b;font-weight:700;">{{ number_format($r->avg_satisfaction??0,1) }}</td>
                  <td>
                    <span class="role-pill" style="background:{{ $rv[1] }};color:{{ $rv[0] }};">
                      {{ number_format($rov,2) }}
                    </span>
                  </td>
                  <td style="color:var(--text-3);font-size:.65rem;">{{ $r->created_at?->format('M d') ?? '—' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @endif
          </div>
        </div>

      </div><!-- /.main-grid -->
    </div><!-- /.page-body -->
  </div><!-- /.content -->
</div><!-- /.app -->

<script>
// ── Data ─────────────────────────────────────────────
const avgU = {{ number_format($avgUsability ?? 0, 2) }};
const avgE = {{ number_format($avgEfficiency ?? 0, 2) }};
const avgS = {{ number_format($avgSatisfaction ?? 0, 2) }};
const roleLabels = {!! json_encode(array_keys($roleData ?? [])) !!};
const roleValues = {!! json_encode(array_values($roleData ?? [])) !!};

// ── Role Donut ───────────────────────────────────────
const roleColors = ['#2563eb','#7c3aed','#10b981','#f59e0b','#ef4444'];
new Chart(document.getElementById('roleChart'), {
  type: 'doughnut',
  data: {
    labels: roleLabels,
    datasets: [{ data: roleValues, backgroundColor: roleColors, borderWidth: 3, borderColor:'#fff', hoverOffset:5 }]
  },
  options: {
    responsive: true, maintainAspectRatio: false, cutout: '68%',
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ` ${c.label}: ${c.raw}` } } }
  }
});

// Role legend
const legend = document.getElementById('roleLegend');
if (legend && roleLabels.length) {
  legend.innerHTML = roleLabels.map((l,i) => `
    <div class="legend-row">
      <div class="legend-dot" style="background:${roleColors[i]};"></div>
      <span>${l}</span>
      <span class="legend-count">${roleValues[i] || 0}</span>
    </div>
  `).join('');
} else if (legend) {
  legend.innerHTML = '<div style="font-size:.73rem;color:var(--text-3);padding:.25rem 0;">No data yet</div>';
}

// ── Category Horizontal Bar ──────────────────────────
new Chart(document.getElementById('categoryChart'), {
  type: 'bar',
  data: {
    labels: ['Usability','Efficiency','Satisfaction'],
    datasets: [{
      data: [avgU, avgE, avgS],
      backgroundColor: [
        'rgba(99,102,241,0.85)',
        'rgba(16,185,129,0.85)',
        'rgba(245,158,11,0.85)'
      ],
      borderRadius: { topRight: 7, bottomRight: 7 },
      borderSkipped: false,
      barThickness: 28,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { callbacks: { label: c => ` ${c.raw.toFixed(2)} / 5.00` } }
    },
    scales: {
      x: {
        beginAtZero: true, max: 5,
        ticks: { stepSize: 1, color: '#94a3b8', font: { size: 10 } },
        grid: { color: 'rgba(0,0,0,.04)' },
        border: { display: false }
      },
      y: {
        grid: { display: false },
        ticks: { color: '#475569', font: { size: 11, weight: '600' } },
        border: { display: false }
      }
    },
    animation: { duration: 900 }
  }
});

// ── Sidebar ──────────────────────────────────────────
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('mobileMenuBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  if (btn) btn.addEventListener('click', () => {
    const open = sidebar.classList.toggle('open');
    overlay.classList.toggle('show', open);
  });
});

// ── Instructor Rate ──────────────────────────────────
function showInstructorRate(rate) {
  fetch(`/api/instructors-by-rate?rate_range=${encodeURIComponent(rate)}`)
    .then(r => r.json())
    .then(data => {
      let html = `<!DOCTYPE html><html><head><title>Rate ₱${rate}</title>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
        <style>body{font-family:'Plus Jakarta Sans',sans-serif;padding:30px;color:#0f172a;}h2{color:#2563eb;}table{width:100%;border-collapse:collapse;margin-top:16px;}th{background:#2563eb;color:#fff;padding:10px;text-align:left;font-size:.8rem;}td{padding:9px 10px;border-bottom:1px solid #e2e8f0;font-size:.85rem;}tr:nth-child(even){background:#f8fafc;}</style>
        </head><body><h2>Instructor Rate ₱${rate}/hr</h2>
        <p style="color:#64748b;font-size:.82rem;">${data.count} instructor(s) · ${new Date().toLocaleString()}</p>`;
      if (data.instructors?.length) {
        html += `<table><thead><tr><th>#</th><th>Name</th><th>Designation</th><th>Rate/Hr</th><th>Type</th></tr></thead><tbody>`;
        data.instructors.forEach((ins,i) => { html += `<tr><td>${i+1}</td><td><b>${ins.name}</b></td><td>${ins.designation||'N/A'}</td><td>₱${ins.rate}</td><td>${ins.type}</td></tr>`; });
        html += '</tbody></table>';
      } else { html += `<p style="text-align:center;color:#94a3b8;padding:30px 0;">No instructors found.</p>`; }
      html += '</body></html>';
      const w = window.open('','_blank'); w.document.write(html); w.document.close();
      w.onload = () => { w.focus(); w.print(); setTimeout(()=>w.close(),1000); };
    }).catch(() => Swal.fire('Error','Could not fetch data.','error'));
}
</script>
</body>
</html>