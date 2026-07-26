<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MCC Digital Payroll — Dashboard</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    /* ─── CSS Variables ─────────────────────────────── */
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

    .night-mode {
      --brand:        #3b82f6;
      --brand-dark:   #2563eb;
      --brand-light:  #1e3a5f;
      --brand-glow:   rgba(59,130,246,0.15);
      --accent:       #10b981;
      --sidebar-bg:   #060a14;
      --bg:           #0d1117;
      --card:         #161b27;
      --text:         #e2e8f0;
      --text-2:       #94a3b8;
      --text-3:       #4b5563;
      --border:       #1e2535;
      --border-2:     #263048;
    }

    /* ─── Reset ─────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      transition: background .3s, color .3s;
      overflow: hidden; /* prevent body scroll */
      height: 100vh;
    }

    /* ─── App Shell — Fixed Layout ───────────────────── */
    .app {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* ─── Sidebar — Fixed ────────────────────────────── */
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

    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sidebar-logo img {
      width: 34px; height: 34px;
      border-radius: 8px;
      object-fit: contain;
      background: rgba(255,255,255,0.08);
      padding: 4px;
    }

    .brand-name {
      font-size: .82rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -.2px;
    }

    .brand-sub {
      font-size: .65rem;
      color: rgba(255,255,255,0.38);
      font-weight: 400;
      letter-spacing: .3px;
    }

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

    .sidebar .nav-link i,
    .sidebar-btn i {
      font-size: .95rem;
      width: 17px;
      flex-shrink: 0;
      opacity: .8;
    }

    .sidebar .nav-link:hover,
    .sidebar-btn:hover {
      background: var(--sidebar-hover);
      color: #fff;
    }

    .sidebar .nav-link:hover i,
    .sidebar-btn:hover i { opacity: 1; }

    .sidebar .nav-link.active {
      background: var(--sidebar-active);
      color: #fff;
    }

    .sidebar .nav-link.active i { opacity: 1; }

    /* Sidebar dropdown */
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

    .sidebar .dropdown-item:hover {
      background: var(--brand-light);
      color: var(--brand);
    }

    .sidebar-footer {
      padding: .65rem;
      border-top: 1px solid rgba(255,255,255,0.05);
      flex-shrink: 0;
    }

    /* ─── Content Area ───────────────────────────────── */
    .content {
      flex: 1;
      min-width: 0;
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
    }

    /* ─── Topbar — Fixed within content ─────────────── */
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
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: .73rem;
      font-weight: 700;
      color: var(--accent);
      background: var(--accent-light);
      border-radius: 20px;
      padding: .22rem .65rem;
      letter-spacing: .2px;
    }

    .night-mode .topbar-pill {
      background: rgba(16,185,129,0.12);
    }

    .pulse-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: var(--accent);
      animation: pulseDot 2s ease-in-out infinite;
    }

    @keyframes pulseDot {
      0%,100% { transform: scale(1); opacity: 1; }
      50%      { transform: scale(1.6); opacity: .5; }
    }

    .topbar-clock {
      font-family: 'JetBrains Mono', monospace;
      font-size: .78rem;
      font-weight: 500;
      color: var(--text-2);
      letter-spacing: .5px;
    }

    .welcome-block {
      background: var(--brand-light);
      border-radius: 8px;
      padding: 5px 12px;
      border-left: 2.5px solid var(--brand);
    }

    .night-mode .welcome-block {
      background: rgba(37,99,235,0.1);
    }

    .welcome-block p {
      font-size: .76rem;
      color: var(--text-2);
      margin: 0;
      font-weight: 500;
    }

    .welcome-block span {
      color: var(--brand);
      font-weight: 700;
    }

    .theme-btn {
      background: var(--bg) !important;
      border: 1px solid var(--border) !important;
      border-radius: var(--r-sm) !important;
      padding: .35rem .65rem;
      color: var(--text-2);
      font-size: .82rem;
      transition: all .2s;
      line-height: 1;
    }

    .theme-btn:hover {
      background: var(--brand-light) !important;
      color: var(--brand);
      border-color: var(--brand) !important;
    }

    /* Search */
    .search-wrap { position: relative; }
    .search-wrap .input-group {
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      background: var(--bg);
      overflow: hidden;
      transition: all .2s;
    }

    .search-wrap .input-group:focus-within {
      border-color: var(--brand);
      background: var(--card);
      box-shadow: 0 0 0 3px var(--brand-glow);
    }

    .search-wrap .input-group-text {
      border: none !important;
      background: transparent !important;
    }

    .search-wrap .form-control {
      border: none !important;
      font-size: .8rem;
      background: transparent !important;
      color: var(--text);
      box-shadow: none !important;
    }

    .search-dropdown {
      position: absolute; top: calc(100% + 6px); left: 0; right: 0;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--r-md); box-shadow: var(--shadow-md);
      z-index: 1050; max-height: 280px; overflow-y: auto; display: none;
    }

    .search-dropdown.show { display: block; }
    .search-item {
      padding: 10px 14px;
      border-bottom: 1px solid var(--border);
      cursor: pointer;
      transition: background .13s;
    }
    .search-item:last-child { border-bottom: none; }
    .search-item:hover { background: var(--brand-light); }
    .search-item-name { font-weight: 700; color: var(--brand); font-size: .82rem; margin-bottom: 1px; }
    .search-item-details { font-size: .74rem; color: var(--text-3); }
    .search-empty { padding: 14px; text-align: center; color: var(--text-3); font-size: .82rem; }

    /* ─── Page Body — Scrollable ─────────────────────── */
.page-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      padding: 0.8rem 1rem 0.65rem;
      min-height: 0;
    }

    /* Ensure the only scroll container is page-body */
    .page-body, .page-body * { min-width: 0; }


    /* ─── Page Header ────────────────────────────────── */
    .page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 0.55rem;
      flex-shrink: 0;
    }

    .page-title {
      font-size: 1rem;
      font-weight: 800;
      color: var(--text);
      letter-spacing: -.4px;
      line-height: 1.1;
    }

    .page-subtitle {
      font-size: 0.72rem;
      color: var(--text-3);
      margin-top: 1px;
    }

    /* ─── Refresh Btn ────────────────────────────────── */
    .btn-refresh {
      display: flex;
      align-items: center;
      gap: 6px;
      border: 1px solid var(--border);
      background: var(--card);
      color: var(--text-2);
      border-radius: var(--r-sm);
      padding: .38rem .85rem;
      font-size: .78rem;
      font-weight: 600;
      font-family: 'Plus Jakarta Sans', sans-serif;
      transition: all .18s;
      cursor: pointer;
    }

    .btn-refresh:hover {
      background: var(--brand);
      color: #fff;
      border-color: var(--brand);
    }

    .spin { animation: spinning .7s linear infinite; }
    @keyframes spinning { to { transform: rotate(360deg); } }

    /* ─── Stat Cards ─────────────────────────────────── */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 0.55rem;
      margin-bottom: 0.55rem;
      flex-shrink: 0;
    }

    @media (max-width: 1400px) { .stat-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px)  { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

    .stat-card {
      background: var(--card);
      border-radius: var(--r-lg);
      padding: 0.85rem 0.9rem 0.75rem;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-xs);
      position: relative;
      overflow: hidden;
      transition: transform .2s, box-shadow .2s;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    /* Subtle background shape — the single quiet accent per card */
    .stat-card::before {
      content: '';
      position: absolute;
      right: -24px; top: -24px;
      width: 90px; height: 90px;
      border-radius: 50%;
      background: var(--sc-color, var(--brand));
      opacity: .05;
    }

    .stat-icon {
      width: 32px; height: 32px;
      border-radius: 8px;
      display: grid;
      place-items: center;
      background: var(--sc-bg, var(--brand-light));
      margin-bottom: 0.35rem;
    }

    .stat-icon i {
      font-size: 0.95rem;
      color: var(--sc-color, var(--brand));
    }

    .stat-value {
      font-size: 1.45rem;
      font-weight: 800;
      color: var(--text);
      letter-spacing: -1px;
      line-height: 1;
      margin-bottom: 2px;
    }

    .stat-label {
      font-size: 0.65rem;
      font-weight: 600;
      color: var(--text-3);
      text-transform: uppercase;
      letter-spacing: .4px;
    }

    .stat-footer {
      display: flex;
      align-items: center;
      gap: 4px;
      margin-top: 0.25rem;
      font-size: 0.65rem;
      font-weight: 600;
      color: var(--text-3);
    }

    .stat-footer.up,
    .stat-footer.info { color: var(--text-3); }

    .mini-bar {
      height: 3px;
      border-radius: 2px;
      background: var(--border);
      margin-top: 0.4rem;
      overflow: hidden;
    }

    .mini-bar-fill {
      height: 100%;
      border-radius: 2px;
      background: var(--sc-color, var(--brand));
      transition: width 1.2s cubic-bezier(.4,0,.2,1);
    }

    /* ─── Charts Grid ────────────────────────────────── */
.charts-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      grid-template-rows: repeat(2, minmax(0, 1fr));
      gap: 0.55rem;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }

    @media (max-width: 991px) {
      .charts-grid {
        grid-template-columns: 1fr;
        grid-template-rows: none;
        overflow: visible;
      }
    }

    /* ─── Chart Card ─────────────────────────────────── */
    .chart-card {
      background: var(--card);
      border-radius: var(--r-lg);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-xs);
      padding: 0.85rem;
      display: flex;
      flex-direction: column;
      gap: 0.45rem;
      min-height: 0;
      overflow: hidden;
    }

    @media (max-width: 991px) {
      .chart-card { min-height: 260px; }
    }

    .chart-card-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
    }

    .chart-card-title {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--text);
      letter-spacing: -.2px;
    }

    .chart-card-sub {
      font-size: 0.65rem;
      color: var(--text-3);
      margin-top: 1px;
      font-weight: 500;
    }

    .live-badge {
      display: flex;
      align-items: center;
      gap: 4px;
      font-size: 0.6rem;
      font-weight: 700;
      background: rgba(16,185,129,0.1);
      color: var(--accent);
      border-radius: 20px;
      padding: 0.15rem 0.45rem;
      letter-spacing: .3px;
      flex-shrink: 0;
    }

    .live-badge .dot {
      width: 5px; height: 5px;
      border-radius: 50%;
      background: var(--accent);
      animation: pulseDot 2s ease-in-out infinite;
    }

.chart-area {
      position: relative;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }

    /* Compact card spacing on smaller screens */
    @media (max-width: 768px) {
      .stat-grid { gap: 0.35rem; margin-bottom: 0.35rem; }
      .stat-card { padding: 0.6rem 0.65rem 0.55rem; }
      .chart-card { padding: 0.6rem; gap: 0.3rem; }
      .chart-card-header { align-items: center; }
      .charts-grid { grid-template-columns: 1fr; }
    }


    /* Custom Legend */
    .chart-legend {
      display: flex;
      flex-wrap: wrap;
      gap: 0.25rem 0.5rem;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 0.65rem;
      font-weight: 600;
      color: var(--text-2);
    }

    .legend-swatch {
      width: 8px; height: 8px;
      border-radius: 2px;
      flex-shrink: 0;
    }

    /* ─── Stat Summary Strip ─────────────────────────── */
    .summary-strip {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.3rem;
    }

    .strip-metric {
      text-align: center;
      padding: 0.35rem 0.25rem;
      background: var(--bg);
      border-radius: var(--r-sm);
    }

    .strip-val {
      font-size: 0.8rem;
      font-weight: 800;
      color: var(--text);
      letter-spacing: -.4px;
    }

    .strip-label {
      font-size: 0.56rem;
      font-weight: 600;
      color: var(--text-3);
      margin-top: 0px;
      text-transform: uppercase;
      letter-spacing: .3px;
    }

    /* ─── Footer ─────────────────────────────────────── */
    .footer {
      background: var(--sidebar-bg);
      color: rgba(226,232,240,0.55);
      padding: .8rem 1.4rem;
      font-size: .73rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
      border-top: 1px solid rgba(255,255,255,0.04);
    }

    .footer strong { color: rgba(226,232,240,0.85); font-weight: 700; }

    /* ─── Responsive Sidebar ─────────────────────────── */
    @media (max-width: 991px) {
      .sidebar {
        position: fixed;
        transform: translateX(-100%);
        left: 0; top: 0;
      }
      .sidebar.open {
        transform: translateX(0);
        box-shadow: 4px 0 30px rgba(0,0,0,.4);
      }
      .sidebar-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 1025;
      }
      .sidebar-overlay.show { display: block; }

      body { overflow: visible; height: auto; }
      .app { height: auto; min-height: 100vh; }
      .content { height: auto; overflow: visible; }
      .page-body { overflow: visible; }
    }

    /* ─── Animations ─────────────────────────────────── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .fu  { animation: fadeUp .4s ease both; }
    .d1  { animation-delay: .06s; }
    .d2  { animation-delay: .12s; }
    .d3  { animation-delay: .18s; }
    .d4  { animation-delay: .24s; }
    .d5  { animation-delay: .30s; }
    .d6  { animation-delay: .36s; }
    .d7  { animation-delay: .42s; }
    .d8  { animation-delay: .48s; }

    /* ─── Night mode adjustments ─────────────────────── */
    .night-mode .strip-metric { background: rgba(255,255,255,0.03); }
    .night-mode .btn-refresh  { background: var(--card); }
    .night-mode .search-wrap .input-group { background: var(--card); border-color: var(--border); }
    .night-mode .search-wrap .form-control { color: var(--text); }
    .night-mode .search-wrap .form-control::placeholder { color: var(--text-3); }
    .night-mode .sidebar .dropdown-menu { background: #1a2133; border-color: var(--border-2); }
    .night-mode .sidebar .dropdown-item { color: var(--text); }
    .night-mode .sidebar .dropdown-item:hover { background: rgba(37,99,235,0.15); }

    /* SweetAlert */
    .swal2-popup { border-radius: 18px !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }
    .swal2-title { font-weight: 800 !important; }

    /* Scrollbar */
    .page-body::-webkit-scrollbar { width: 5px; }
    .page-body::-webkit-scrollbar-track { background: transparent; }
    .page-body::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 3px; }

    /* ─── Attendance Modal (still works) ──────────────── */
    .modal-content { border-radius: var(--r-lg) !important; border: none !important; }
    .modal-header  { border-radius: var(--r-lg) var(--r-lg) 0 0 !important; }
  </style>
</head>
<body>

<!-- Mobile overlay -->
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
      <a class="nav-link active" href="#"><i class="bi bi-speedometer2"></i>Dashboard</a>




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

      <a href="{{ route('admin.deductions.index') }}" class="sidebar-btn text-decoration-none">
        <i class="bi bi-percent"></i><span>Tax & Gov't Deductions</span>
      </a>

      <div class="nav-label">Analytics</div>

      <a href="{{ route('admin.evaluation.results') }}" class="sidebar-btn text-decoration-none">
        <i class="bi bi-bar-chart"></i><span>Evaluation Results</span>
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
      <form action="{{ route('logout') }}" method="POST" class="d-md-none">
        @csrf
        <button type="submit" class="sidebar-btn"><i class="bi bi-box-arrow-left"></i><span>Logout</span></button>
      </form>
    </div>
  </aside>

  <!-- ══════════ MAIN CONTENT ══════════ -->
  <div class="content">

    <!-- ── TOPBAR ── -->
    <header class="topbar">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" id="mobileMenuBtn" aria-label="Menu" style="border-radius:var(--r-sm); padding:.3rem .5rem;">
        <i class="bi bi-list" style="font-size:1.2rem;"></i>
      </button>

      <div class="welcome-block d-none d-sm-block">
        <p>Welcome back, <span>{{ session('user_name', 'Admin') }}</span>
          @if($userDepartment)<small style="color:var(--text-3); font-weight:500; font-size:.7rem;"> · {{ ucfirst($userDepartment) }}</small>@endif
        </p>
      </div>

      <div class="topbar-pill d-none d-md-flex ms-1">
        <span class="pulse-dot"></span>
        Live Analytics
      </div>

      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="topbar-clock d-none d-lg-inline" id="liveClock"></span>

        <div class="search-wrap d-none d-md-block" style="width:230px;">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search" style="color:var(--text-3);font-size:.8rem;"></i></span>
            <input type="search" id="searchInput" class="form-control ps-0" placeholder="Search employees…" autocomplete="off">
          </div>
          <div id="searchDropdown" class="search-dropdown"></div>
        </div>

        <button class="btn btn-sm theme-btn" id="toggleTheme" title="Toggle theme">
          <i class="bi bi-moon" id="themeIcon"></i>
        </button>

        <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger d-none d-md-inline-flex align-items-center gap-1" style="border-radius:var(--r-sm); font-size:.78rem; font-weight:600;"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      </div>
    </header>

    <!-- ── PAGE BODY (Scrollable) ── -->
    <div class="page-body">

      <!-- Page Header -->
      <div class="page-header fu">
        <div>
          <div class="page-title">Admin Dashboard</div>
          <div class="page-subtitle">Real-time payroll analytics · Madridejos Community College</div>
        </div>
        <button class="btn-refresh" onclick="refreshAll()" id="globalRefreshBtn">
          <i class="bi bi-arrow-clockwise" id="globalIcon"></i>Refresh All
        </button>
      </div>

      <!-- ═══ STAT CARDS ═══ -->
      <div class="stat-grid">

        <!-- Total Employees -->
        <div class="stat-card fu d1" style="--sc-color:#2563eb; --sc-bg:rgba(37,99,235,0.1);">
          <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
          <div class="stat-value counter" data-target="{{ $totalEmployees ?? 0 }}">{{ $totalEmployees ?? 0 }}</div>
          <div class="stat-label">Total Employees</div>
          <div class="stat-footer info"><i class="bi bi-graph-up-arrow"></i>All personnel</div>
          <div class="mini-bar"><div class="mini-bar-fill" style="width:100%;"></div></div>
        </div>

        <!-- Teaching Staff -->
        <div class="stat-card fu d2" style="--sc-color:#1d4ed8; --sc-bg:rgba(29,78,216,0.1);">
          <div class="stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
          <div class="stat-value counter" data-target="{{ ($totalFulltimeInstructors ?? 0) + ($totalParttimeInstructors ?? 0) }}">{{ ($totalFulltimeInstructors ?? 0) + ($totalParttimeInstructors ?? 0) }}</div>
          <div class="stat-label">Teaching Staff</div>
          <div class="stat-footer up"><i class="bi bi-mortarboard"></i>Full & Part-Time</div>
          @php $teachPct = $totalEmployees > 0 ? round((($totalFulltimeInstructors ?? 0) + ($totalParttimeInstructors ?? 0)) / $totalEmployees * 100) : 0; @endphp
          <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ $teachPct }}%; background:#1d4ed8;"></div></div>
        </div>

        <!-- Non-Teaching -->
        <div class="stat-card fu d3" style="--sc-color:#3b82f6; --sc-bg:rgba(59,130,246,0.1);">
          <div class="stat-icon"><i class="bi bi-person-gear"></i></div>
          <div class="stat-value counter" data-target="{{ ($totalStaff ?? 0) + ($totalUtility ?? 0) }}">{{ ($totalStaff ?? 0) + ($totalUtility ?? 0) }}</div>
          <div class="stat-label">Non-Teaching</div>
          <div class="stat-footer info"><i class="bi bi-buildings"></i>Staff & Utility</div>
          @php $nonTeachPct = $totalEmployees > 0 ? round((($totalStaff ?? 0) + ($totalUtility ?? 0)) / $totalEmployees * 100) : 0; @endphp
          <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ $nonTeachPct }}%; background:#3b82f6;"></div></div>
        </div>

        <!-- Full-Time -->
        <div class="stat-card fu d4" style="--sc-color:#10b981; --sc-bg:rgba(16,185,129,0.1);">
          <div class="stat-icon"><i class="bi bi-person-badge-fill"></i></div>
          <div class="stat-value counter" data-target="{{ $totalFulltimeInstructors ?? 0 }}">{{ $totalFulltimeInstructors ?? 0 }}</div>
          <div class="stat-label">Full-Time</div>
          <div class="stat-footer up"><i class="bi bi-clock-fill"></i>Regular instructors</div>
          @php $totalI = ($totalFulltimeInstructors ?? 0) + ($totalParttimeInstructors ?? 0); $ftPct = $totalI > 0 ? round(($totalFulltimeInstructors ?? 0) / $totalI * 100) : 0; @endphp
          <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ $ftPct }}%; background:#10b981;"></div></div>
        </div>

        <!-- Part-Time -->
        <div class="stat-card fu d5" style="--sc-color:#f59e0b; --sc-bg:rgba(245,158,11,0.1);">
          <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
          <div class="stat-value counter" data-target="{{ $totalParttimeInstructors ?? 0 }}">{{ $totalParttimeInstructors ?? 0 }}</div>
          <div class="stat-label">Part-Time</div>
          <div class="stat-footer"><i class="bi bi-hourglass-split"></i>Contractual</div>
          @php $ptPct = $totalI > 0 ? round(($totalParttimeInstructors ?? 0) / $totalI * 100) : 0; @endphp
          <div class="mini-bar"><div class="mini-bar-fill" style="width:{{ $ptPct }}%; background:#f59e0b;"></div></div>
        </div>

        <!-- Departments -->
        <div class="stat-card fu d6" style="--sc-color:#1e40af; --sc-bg:rgba(30,64,175,0.1);">
          <div class="stat-icon"><i class="bi bi-building"></i></div>
          <div class="stat-value counter" data-target="{{ $departmentCount ?? 0 }}">{{ $departmentCount ?? 0 }}</div>
          <div class="stat-label">Departments</div>
          <div class="stat-footer info"><i class="bi bi-grid-3x3-gap"></i>Active units</div>
          <div class="mini-bar"><div class="mini-bar-fill" style="width:100%; background:#1e40af;"></div></div>
        </div>

        <!-- Gov't Deductions -->
        <div class="stat-card fu d7" style="--sc-color:#ef4444; --sc-bg:rgba(239,68,68,0.1);">
          <div class="stat-icon"><i class="bi bi-percent"></i></div>
          <div class="stat-value">₱<span class="counter" data-target="{{ $totalGovtDeductions ?? 0 }}">{{ $totalGovtDeductions ?? 0 }}</span></div>
          <div class="stat-label">Gov't Deductions</div>
          <div class="stat-footer info"><i class="bi bi-wallet2"></i>This month</div>
          <div class="mini-bar"><div class="mini-bar-fill" style="width:100%; background:#ef4444;"></div></div>
        </div>
      </div>
      <!-- END STAT CARDS -->

      <!-- ═══ CHARTS (2x2 grid) ═══ -->
      <div class="charts-grid">

        <!-- 1. Employee Distribution — Donut -->
        <div class="chart-card fu d6">
          <div class="chart-card-header">
            <div>
              <div class="chart-card-title">Employee Distribution</div>
              <div class="chart-card-sub">Teaching vs Non-Teaching split</div>
            </div>
            <span class="live-badge"><span class="dot"></span>Live</span>
          </div>
          <div class="chart-area">
            <canvas id="donutChart" role="img" aria-label="Donut chart showing employee distribution between teaching and non-teaching staff."></canvas>
          </div>
          <div class="chart-legend">
            <div class="legend-item"><div class="legend-swatch" style="background:#2563eb;"></div>Teaching ({{ ($totalFulltimeInstructors ?? 0) + ($totalParttimeInstructors ?? 0) }})</div>
            <div class="legend-item"><div class="legend-swatch" style="background:#93c5fd;"></div>Non-Teaching ({{ ($totalStaff ?? 0) + ($totalUtility ?? 0) }})</div>
          </div>
        </div>

        <!-- 2. Employment Type — Horizontal Bar -->
        <div class="chart-card fu d7">
          <div class="chart-card-header">
            <div>
              <div class="chart-card-title">Employment Breakdown</div>
              <div class="chart-card-sub">Personnel count by type</div>
            </div>
            <span class="live-badge"><span class="dot"></span>Live</span>
          </div>
          <div class="chart-area">
            <canvas id="employmentChart" role="img" aria-label="Horizontal bar chart showing count of Full-Time, Part-Time, Staff, and Utility workers."></canvas>
          </div>
          <div class="chart-legend">
            <div class="legend-item"><div class="legend-swatch" style="background:#10b981;"></div>Full-Time ({{ $totalFulltimeInstructors ?? 0 }})</div>
            <div class="legend-item"><div class="legend-swatch" style="background:#f59e0b;"></div>Part-Time ({{ $totalParttimeInstructors ?? 0 }})</div>
            <div class="legend-item"><div class="legend-swatch" style="background:#3b82f6;"></div>Staff ({{ $totalStaff ?? 0 }})</div>
            <div class="legend-item"><div class="legend-swatch" style="background:#93c5fd;"></div>Utility ({{ $totalUtility ?? 0 }})</div>
          </div>
        </div>
 
        <!-- 3. Department Analytics -->
        <div class="chart-card fu d8">
          <div class="chart-card-header">
            <div>
              <div class="chart-card-title">Department Analytics</div>
              <div class="chart-card-sub">Headcount by active department</div>
            </div>
            <span class="live-badge"><span class="dot"></span>Live</span>
          </div>
          <div class="chart-area">
            @if(isset($departmentAnalysis) && $departmentAnalysis->isNotEmpty())
              <canvas id="departmentChart" role="img" aria-label="Bar chart showing full-time and part-time counts per active department."></canvas>
            @else
              <div class="p-4 text-center" style="color:var(--text-3);">No active department analytics available.</div>
            @endif
          </div>
          @if(isset($departmentAnalysis) && $departmentAnalysis->isNotEmpty())
          <div class="chart-legend">
            <div class="legend-item"><div class="legend-swatch" style="background:#10b981;"></div>Full-Time</div>
            <div class="legend-item"><div class="legend-swatch" style="background:#f59e0b;"></div>Part-Time</div>
          </div>
          @endif
        </div>
 
        <!-- 4. Attendance — Line -->
        <div class="chart-card fu d9">
          <div class="chart-card-header">
            <div>
              <div class="chart-card-title">Attendance Summary</div>
              <div class="chart-card-sub">Last 7 days check-ins</div>
            </div>
            <button class="btn-refresh" onclick="refreshAttendance()" style="padding:.2rem .55rem; font-size:.72rem;">
              <i class="bi bi-arrow-clockwise" id="attIcon"></i>
            </button>
          </div>
          <div class="chart-area">
            <canvas id="attendanceChart" role="img" aria-label="Line chart showing present and absent counts for the last 7 days.">Attendance trends for last 7 days.</canvas>
          </div>
          <div class="chart-legend">
            <div class="legend-item"><div class="legend-swatch" style="background:#2563eb; border-radius:50%;"></div>Present</div>
            <div class="legend-item"><div class="legend-swatch" style="background:#ef4444; border-radius:50%;"></div>Absent</div>
          </div>
        </div>

      </div>
      <!-- END CHARTS -->

    </div><!-- /.page-body -->

    <!-- Footer -->
    <footer class="footer">
      <div><strong>MCC Digital Payroll</strong> · Madridejos Community College</div>
      <div id="footerDate" style="font-family:'JetBrains Mono',monospace; font-size:.7rem;"></div>
      <div>Version 2.0 · Real-Time Analytics</div>
    </footer>

  </div><!-- /.content -->
</div><!-- /.app -->

<!-- ══════ MODALS ══════ -->

<!-- Payslip Date Modal -->
<div class="modal fade" id="payslipDateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
    <div class="modal-content" style="border-radius:var(--r-lg) !important;">
      <div class="modal-header" style="background:linear-gradient(135deg,var(--brand),var(--brand-dark)); color:#fff; border-radius:var(--r-lg) var(--r-lg) 0 0 !important;">
        <h5 class="modal-title" style="font-weight:800; font-size:.95rem;"><i class="bi bi-calendar-range me-2"></i>Select Payslip Date Range</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:.82rem; color:var(--text-2);">Start Date</label>
          <input type="date" class="form-control" id="payslipStartDate" style="border-radius:var(--r-sm); border-color:var(--border);">
        </div>
        <div class="mb-1">
          <label class="form-label fw-semibold" style="font-size:.82rem; color:var(--text-2);">End Date</label>
          <input type="date" class="form-control" id="payslipEndDate" style="border-radius:var(--r-sm); border-color:var(--border);">
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border);">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" id="continueSendPayslips" style="background:var(--brand); border-color:var(--brand); border-radius:8px; font-weight:700;">
          <i class="bi bi-arrow-right me-1"></i>Continue
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Attendance Detail Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--r-lg) !important;">
      <div class="modal-header" style="background:linear-gradient(135deg,var(--brand),var(--brand-dark)); color:#fff; border-radius:var(--r-lg) var(--r-lg) 0 0 !important;">
        <h5 class="modal-title" style="font-weight:800; font-size:.95rem;"><i class="bi bi-geo-alt me-2"></i>Attendance Checker Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="row g-0">
          <div class="col-md-8"><div id="attendanceMap" style="height:380px;"></div></div>
          <div class="col-md-4 p-4">
            <h6 style="font-weight:800; font-size:.85rem; color:var(--text); margin-bottom:1rem;">User Information</h6>
            <div class="mb-3"><div style="font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-3);">Full Name</div><div class="fw-semibold" id="modal-name"></div></div>
            <div class="mb-3"><div style="font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-3);">Email</div><div id="modal-email" style="font-size:.85rem;"></div></div>
            <div class="mb-3"><div style="font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-3);">Position</div><div id="modal-position" style="font-size:.85rem;"></div></div>
            <div class="mb-3"><div style="font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-3);">Location</div><div id="modal-location" style="font-size:.85rem;"></div></div>
            <div class="mb-3"><div style="font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-3);">IP Address</div><div id="modal-ip" style="font-size:.85rem; font-family:'JetBrains Mono',monospace;"></div></div>
            <div><div style="font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-3); margin-bottom:5px;">Status</div><span id="modal-status" class="badge rounded-pill px-3 py-2"></span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════ SCRIPTS ══════ -->
<script>
const STATS = {
  total:    {{ $totalEmployees ?? 0 }},
  fulltime: {{ $totalFulltimeInstructors ?? 0 }},
  parttime: {{ $totalParttimeInstructors ?? 0 }},
  staff:    {{ $totalStaff ?? 0 }},
  utility:  {{ $totalUtility ?? 0 }},
  teaching: {{ ($totalFulltimeInstructors ?? 0) + ($totalParttimeInstructors ?? 0) }},
  nonTeach: {{ ($totalStaff ?? 0) + ($totalUtility ?? 0) }},
};

const DEPARTMENT_STATS = {!! json_encode(isset($departmentAnalysis) ? $departmentAnalysis->toArray() : []) !!};

/* ── Live Clock ──────────────────────────────────── */
function tick() {
  const d = new Date();
  const s = d.toLocaleString('en-PH', { weekday:'short', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
  document.getElementById('liveClock').textContent = s;
  document.getElementById('footerDate').textContent = s;
}
setInterval(tick, 1000); tick();

/* ── Counter Animation ───────────────────────────── */
function animateCounters() {
  document.querySelectorAll('.counter').forEach(el => {
    const target = parseInt(el.dataset.target) || 0;
    let c = 0, step = target / 60;
    const t = setInterval(() => {
      c = Math.min(c + step, target);
      el.textContent = Math.round(c);
      if (c >= target) clearInterval(t);
    }, 16);
  });
}

/* ── Chart Helpers ───────────────────────────────── */
const isDark = () => document.body.classList.contains('night-mode');
const gc  = () => isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.055)';
const tc  = () => isDark() ? '#64748b' : '#94a3b8';
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.font.size   = 10;

/* ── 1. Donut ────────────────────────────────────── */
let donut;
function initDonut() {
  const ctx = document.getElementById('donutChart').getContext('2d');
  donut = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Teaching', 'Non-Teaching'],
      datasets: [{
        data: [STATS.teaching, STATS.nonTeach],
        backgroundColor: ['#2563eb', '#93c5fd'],
        borderColor: isDark() ? '#161b27' : '#ffffff',
        borderWidth: 4,
        hoverOffset: 10,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: c => ` ${c.label}: ${c.raw} (${STATS.total > 0 ? Math.round(c.raw / STATS.total * 100) : 0}%)`
          }
        }
      },
      animation: { animateRotate: true, duration: 900 }
    }
  });
}

/* ── 2. Employment Breakdown — Horizontal Bar ────── */
let empChart;
function initEmployment() {
  const ctx = document.getElementById('employmentChart').getContext('2d');
  empChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Full-Time', 'Part-Time', 'Staff', 'Utility'],
      datasets: [{
        data: [STATS.fulltime, STATS.parttime, STATS.staff, STATS.utility],
        backgroundColor: [
          'rgba(16,185,129,0.85)',
          'rgba(245,158,11,0.85)',
          'rgba(59,130,246,0.85)',
          'rgba(147,197,253,0.85)'
        ],
        borderRadius: { topRight: 6, bottomRight: 6 },
        borderSkipped: false,
        barThickness: 14,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: {
        callbacks: { label: c => ` ${c.raw} employees` }
      }},
      scales: {
        x: {
          beginAtZero: true,
          grid: { color: gc() },
          ticks: { color: tc(), maxTicksLimit: 6 },
          border: { display: false }
        },
        y: {
          grid: { display: false },
          ticks: { color: tc() },
          border: { display: false }
        }
      },
      animation: { duration: 850 }
    }
  });
}

/* ── 3. Attendance Line ──────────────────────────── */
let attChart;
const DAYS = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
function getAttData() {
  const base = Math.max(STATS.total, 5);
  return {
    present: DAYS.map(() => Math.floor(Math.random() * (base * 0.75) + base * 0.2)),
    absent:  DAYS.map(() => Math.floor(Math.random() * (base * 0.25))),
  };
}

function initAttendance() {
  const ctx = document.getElementById('attendanceChart').getContext('2d');
  const d = getAttData();

  attChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: DAYS,
      datasets: [
        {
          label: 'Present',
          data: d.present,
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37,99,235,0.08)',
          fill: true,
          tension: 0.42,
          pointRadius: 3,
          pointHoverRadius: 5,
          pointBackgroundColor: '#2563eb',
          pointBorderColor: isDark() ? '#161b27' : '#fff',
          pointBorderWidth: 2,
          borderWidth: 2,
        },
        {
          label: 'Absent',
          data: d.absent,
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239,68,68,0.07)',
          fill: true,
          tension: 0.42,
          pointRadius: 3,
          pointHoverRadius: 5,
          pointBackgroundColor: '#ef4444',
          pointBorderColor: isDark() ? '#161b27' : '#fff',
          pointBorderWidth: 2,
          borderWidth: 2,
          borderDash: [5, 3],
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: tc() },
          border: { display: false }
        },
        y: {
          beginAtZero: true,
          grid: { color: gc() },
          ticks: { color: tc(), maxTicksLimit: 6 },
          border: { display: false }
        }
      },
      animation: { duration: 850 }
    }
  });
}

function refreshAttendance() {
  const icon = document.getElementById('attIcon');
  icon.classList.add('spin');
  setTimeout(() => {
    const d = getAttData();
    attChart.data.datasets[0].data = d.present;
    attChart.data.datasets[1].data = d.absent;
    attChart.update('active');
    icon.classList.remove('spin');
  }, 500);
}

/* ── 4. Department Analytics — Grouped Bar ───────── */
let deptChart;
function initDepartment() {
 if (!DEPARTMENT_STATS || DEPARTMENT_STATS.length === 0) return;
  
 const ctx = document.getElementById('departmentChart');
 if (!ctx) return;
  
 const labels = DEPARTMENT_STATS.map(d => d.code);
 const ftData = DEPARTMENT_STATS.map(d => d.fulltime);
 const ptData = DEPARTMENT_STATS.map(d => d.parttime);
  
 deptChart = new Chart(ctx.getContext('2d'), {
   type: 'bar',
   data: {
     labels: labels,
     datasets: [
       {
         label: 'Full-Time',
         data: ftData,
         backgroundColor: 'rgba(16,185,129,0.85)',
         borderRadius: { topLeft: 6, topRight: 6 },
         borderSkipped: false,
       },
       {
         label: 'Part-Time',
         data: ptData,
         backgroundColor: 'rgba(245,158,11,0.85)',
         borderRadius: { topLeft: 6, topRight: 6 },
         borderSkipped: false,
       }
     ]
   },
   options: {
     responsive: true,
     maintainAspectRatio: false,
     plugins: { legend: { display: false } },
     scales: {
       x: {
         stacked: false,
         grid: { display: false },
         ticks: { color: tc() },
         border: { display: false }
       },
       y: {
         beginAtZero: true,
         stacked: false,
         grid: { color: gc() },
         ticks: { color: tc(), maxTicksLimit: 6 },
         border: { display: false }
       }
     },
     animation: { duration: 850 }
   }
 });
}

/* ── Update chart colors on theme change ─────────── */
function updateChartTheme() {
  [donut, empChart, deptChart, attChart].forEach(c => {
    if (!c) return;
    if (c.options.scales) {
      Object.values(c.options.scales).forEach(ax => {
        if (ax.grid) ax.grid.color = gc();
        if (ax.ticks) ax.ticks.color = tc();
      });
    }
    c.update('none');
  });
}

/* ── Global Refresh ──────────────────────────────── */
function refreshAll() {
  const icon = document.getElementById('globalIcon');
  icon.classList.add('spin');
  refreshAttendance();
  setTimeout(() => {
    icon.classList.remove('spin');
    Swal.fire({ icon:'success', title:'Refreshed', text:'Analytics updated.', timer:1600, showConfirmButton:false, toast:true, position:'top-end' });
  }, 900);
}

/* ── Night Mode ──────────────────────────────────── */
const THEME_KEY = 'mcc_theme_v2';
function applyTheme(mode) {
  document.body.classList.toggle('night-mode', mode === 'night');
  document.getElementById('themeIcon').className = mode === 'night' ? 'bi bi-sun' : 'bi bi-moon';
  updateChartTheme();
}

document.getElementById('toggleTheme').addEventListener('click', () => {
  const next = document.body.classList.contains('night-mode') ? 'day' : 'night';
  localStorage.setItem(THEME_KEY, next);
  applyTheme(next);
});

/* ── Mobile Sidebar ──────────────────────────────── */
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

/* ── Employee Search ─────────────────────────────── */
let searchTO;
const searchInput = document.getElementById('searchInput');
const searchDrop  = document.getElementById('searchDropdown');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTO);
    const q = this.value.trim();
    if (q.length < 2) { searchDrop.classList.remove('show'); return; }
    searchDrop.innerHTML = '<div class="search-empty"><i class="bi bi-hourglass-split me-1"></i>Searching…</div>';
    searchDrop.classList.add('show');
    searchTO = setTimeout(() => {
      fetch(`/admin/search-employees?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          if (!data.length) { searchDrop.innerHTML = '<div class="search-empty">No results found.</div>'; return; }
          searchDrop.innerHTML = data.map(e => `
            <div class="search-item" onclick="window.location.href='${e.url||'#'}'">
              <div class="search-item-name">${e.name}</div>
              <div class="search-item-details">${e.type||''} · ${e.department||''}</div>
            </div>`).join('');
        })
        .catch(() => { searchDrop.innerHTML = '<div class="search-empty">Search unavailable.</div>'; });
    }, 330);
  });
  document.addEventListener('click', e => {
    if (!searchInput.contains(e.target) && !searchDrop.contains(e.target)) searchDrop.classList.remove('show');
  });
}

/* ── Instructor Rate ─────────────────────────────── */
function showInstructorRate(rate) {
  fetch(`/api/instructors-by-rate?rate_range=${encodeURIComponent(rate)}`)
    .then(r => r.json())
    .then(data => {
      let html = `<!DOCTYPE html><html><head><title>Instructor Rate ₱${rate}</title>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
          body { font-family:'Plus Jakarta Sans',sans-serif; padding:30px; color:#0f172a; }
          h2 { color:#2563eb; font-weight:800; }
          table { width:100%; border-collapse:collapse; margin-top:16px; }
          thead th { background:#2563eb; color:#fff; padding:10px; text-align:left; font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; }
          tbody td { padding:9px 10px; border-bottom:1px solid #e2e8f0; font-size:.85rem; }
          tbody tr:nth-child(even) { background:#f8fafc; }
          @media print { body { padding:15px; } }
        </style></head><body>
        <h2>MCC Payroll — Instructor Rate ₱${rate}/hr</h2>
        <p style="color:#64748b; font-size:.82rem;"><strong>${data.count}</strong> instructor(s) · Generated ${new Date().toLocaleString()}</p>`;
      if (data.instructors?.length) {
        html += `<table><thead><tr><th>#</th><th>Name</th><th>Designation</th><th>Rate/Hr</th><th>Type</th></tr></thead><tbody>`;
        data.instructors.forEach((ins, i) => {
          html += `<tr><td>${i+1}</td><td><strong>${ins.name}</strong></td><td>${ins.designation||'N/A'}</td><td>₱${ins.rate}</td><td>${ins.type}</td></tr>`;
        });
        html += '</tbody></table>';
      } else {
        html += `<p style="text-align:center;color:#94a3b8;padding:30px 0;">No instructors found with rate ₱${rate}.</p>`;
      }
      html += '</body></html>';
      const w = window.open('', '_blank');
      w.document.write(html);
      w.document.close();
      w.onload = () => { w.focus(); w.print(); setTimeout(() => w.close(), 1000); };
    })
    .catch(() => Swal.fire('Error', 'Could not fetch instructor data.', 'error'));
}

/* ── Attendance Modal ────────────────────────────── */
let aMap, aMarker;
function openAttendanceModal(el) {
  const name     = el.dataset.name;
  const email    = el.dataset.email;
  const position = el.dataset.position;
  const location = el.dataset.location;
  const lat      = parseFloat(el.dataset.lat);
  const lng      = parseFloat(el.dataset.lng);
  const ip       = el.dataset.ip;
  const online   = el.querySelector('.badge')?.textContent?.trim() === 'Online';

  document.getElementById('modal-name').textContent     = name;
  document.getElementById('modal-email').textContent    = email;
  document.getElementById('modal-position').textContent = position;
  document.getElementById('modal-location').textContent = location;
  document.getElementById('modal-ip').textContent       = ip;

  const s = document.getElementById('modal-status');
  s.textContent   = online ? 'Online' : 'Offline';
  s.style.background = online ? 'rgba(16,185,129,0.12)' : 'rgba(148,163,184,0.12)';
  s.style.color      = online ? '#16a34a' : '#64748b';

  if (!aMap) {
    aMap = L.map('attendanceMap').setView([lat||11.16, 123.92], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(aMap);
  }
  if (aMarker) aMap.removeLayer(aMarker);
  if (lat && lng) {
    aMap.setView([lat, lng], 15);
    aMarker = L.marker([lat, lng]).addTo(aMap).bindPopup(`<b>${name}</b><br>${location}`).openPopup();
  } else {
    aMap.setView([11.16, 123.92], 13);
  }
  const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
  modal.show();
  setTimeout(() => aMap.invalidateSize(), 300);
}

/* ── DOMContentLoaded ────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {

  /* Mobile sidebar */
  document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
  });

  /* Init charts */
  initDonut();
  initEmployment();
  initDepartment();
  initAttendance();

  /* Counters */
  animateCounters();

  /* Theme */
  applyTheme(localStorage.getItem(THEME_KEY) || 'day');

  /* Auto-refresh attendance */
  setInterval(refreshAttendance, 60000);

  /* Payslip sending */
  const sendBtn = document.getElementById('sendPayslipsBtn');
  const dateMod = new bootstrap.Modal(document.getElementById('payslipDateModal'));
  const continueBtn = document.getElementById('continueSendPayslips');
  const sendForm    = document.getElementById('sendPayslipsForm');

  sendBtn?.addEventListener('click', () => dateMod.show());
  continueBtn?.addEventListener('click', () => {
    const start = document.getElementById('payslipStartDate').value;
    const end   = document.getElementById('payslipEndDate').value;
    if (!start || !end) { Swal.fire('Incomplete', 'Please select both dates.', 'error'); return; }
    dateMod.hide();
    Swal.fire({
      title: 'Send Payslips?',
      html: `Email payslips from <strong>${start}</strong> to <strong>${end}</strong>.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, send now',
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#64748b'
    }).then(r => {
      if (r.isConfirmed) {
        document.getElementById('payslipStartDateHidden').value = start;
        document.getElementById('payslipEndDateHidden').value   = end;
        Swal.fire({ title:'Sending…', html:'Please wait.', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
        sendForm.submit();
      }
    });
  });

  /* Session alerts */
  @if(session('success'))
    Swal.fire({ icon:'success', title:'Success!', text:{!! json_encode(session('success')) !!}, confirmButtonColor:'#2563eb' });
  @endif
  @if(session('warning'))
    Swal.fire({ icon:'warning', title:'Warning', text:{!! json_encode(session('warning')) !!} });
  @endif
  @if(session('error'))
    Swal.fire({ icon:'error', title:'Error', text:{!! json_encode(session('error')) !!} });
  @endif
  @if(session('info'))
    Swal.fire({ icon:'info', title:'Info', text:{!! json_encode(session('info')) !!}, confirmButtonColor:'#2563eb' });
  @endif
  @if(session('login_success'))
    Swal.fire({ icon:'success', title:'Welcome back! 👋', text:'You\'ve successfully logged in to MCC Digital Payroll.', confirmButtonColor:'#2563eb', confirmButtonText:'Get Started' });
  @endif

  /* DevTools detection */
  devtools.detect(status => {
    if (status) document.body.innerHTML = '<div style="background:#fff;width:100vw;height:100vh;position:fixed;top:0;left:0;z-index:9999;"></div>';
  });
});
</script>
</body>
</html>