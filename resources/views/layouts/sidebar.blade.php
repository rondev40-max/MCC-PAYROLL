<!-- Mobile overlay -->
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

<!-- Mobile Toggle Floating Button (Visible only on screen size < 992px) -->
<button class="btn btn-primary d-lg-none" id="sidebarMobileBtn" style="position:fixed; bottom:20px; right:20px; z-index:1000; border-radius:50%; width:50px; height:50px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.25);" onclick="toggleSidebar()">
  <i class="bi bi-list" style="font-size:1.5rem;"></i>
</button>

<!-- Shared JS helper to allow overlay close and toggle functions -->
<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
  }

  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
  }
</script>
