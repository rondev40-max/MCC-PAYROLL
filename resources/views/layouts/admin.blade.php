<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin Dashboard - Madridejos Community College')</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --brand:        #2563eb;
      --brand-dark:   #1d4ed8;
      --brand-light:  #eff6ff;
      --brand-glow:   rgba(37,99,235,0.15);
      --accent:       #16a34a;
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

    /* ─── App Shell ─────────────────────────────────── */
    .app {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* ─── Sidebar ───────────────────────────────────── */
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
      overflow: hidden; /* layout wrapper */
    }

    /* ─── Topbar ─────────────────────────────────────── */
    .topbar {
      height: var(--topbar-h);
      background: var(--card);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1.25rem;
      flex-shrink: 0;
      z-index: 10;
      box-shadow: var(--shadow-xs);
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .main-body {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem;
    }

    /* ─── Responsive Sidebar ─────────────────────────── */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1025;
    }

    @media (max-width: 991.98px) {
      .sidebar {
        position: fixed;
        left: 0; top: 0; bottom: 0;
        transform: translateX(-100%);
        transition: transform .25s ease-in-out;
      }
      .sidebar.open {
        transform: translateX(0);
      }
      .sidebar-overlay.show {
        display: block;
      }
    }

    .night-mode .sidebar .dropdown-menu { background: #1a2133; border-color: var(--border-2); }
    .night-mode .sidebar .dropdown-item { color: var(--text); }
    .night-mode .sidebar .dropdown-item:hover { background: rgba(37,99,235,0.15); }

    @yield('styles')
  </style>
</head>
<body>

<div class="app">
  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

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
          @foreach(['130','150','170','190','210','220','250'] as $rate)
          <li><a class="dropdown-item" href="#" onclick="if(typeof showInstructorRate === 'function') { showInstructorRate('{{ $rate }}'); } else { window.location.href='{{ route('admin.dashboard') }}?rate={{ $rate }}'; } return false;"><i class="bi bi-currency-dollar"></i>₱{{ $rate }}</a></li>
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

  <!-- ══════════ MAIN CONTENT ══════════ -->
  <div class="content">

    <!-- ── TOPBAR ── -->
    <header class="topbar">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" id="mobileMenuBtn" aria-label="Menu" style="border-radius:var(--r-sm); padding:.3rem .5rem;" onclick="toggleSidebar()">
        <i class="bi bi-list" style="font-size:1.2rem;"></i>
      </button>

      <h5 class="m-0 d-none d-md-block" style="font-weight: 700; color: var(--text);">
        @yield('header_title', 'Payroll System Dashboard')
      </h5>

      <div class="topbar-actions">
        <!-- Night Mode Toggle -->
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center" id="nightModeToggle" onclick="toggleNightMode()" style="width: 34px; height: 34px; border-radius: var(--r-sm);">
          <i class="bi bi-moon-stars" id="nightModeIcon"></i>
        </button>
        <span class="d-none d-sm-inline" style="font-size: .85rem; font-weight:600; color: var(--text-2)">
          {{ Auth::user()->name ?? 'Administrator' }}
        </span>
      </div>
    </header>

    <!-- ── MAIN BODY CONTENT ── -->
    <main class="main-body">
      @yield('content')
    </main>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ── Night Mode Toggle ────────────────────────────
  if (localStorage.getItem('night-mode') === 'true') {
    document.body.classList.add('night-mode');
    document.getElementById('nightModeIcon').className = 'bi bi-sun';
  }

  function toggleNightMode() {
    const isNight = document.body.classList.toggle('night-mode');
    localStorage.setItem('night-mode', isNight);
    document.getElementById('nightModeIcon').className = isNight ? 'bi bi-sun' : 'bi bi-moon-stars';
  }

  // ── Mobile Sidebar Toggling ──────────────────────
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
  }

  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
  }
</script>
@yield('scripts')
</body>
</html>
