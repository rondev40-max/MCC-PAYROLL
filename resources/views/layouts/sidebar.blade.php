{{-- resources/views/layouts/sidebar.blade.php --}}
{{-- This is a PARTIAL: only the fixed <aside> nav markup. --}}
{{-- Do NOT put <!DOCTYPE>, <head>, <body>, or @yield('content') in this file --}}
{{-- it gets @include()'d inside other full pages, and a second full <html> --}}
{{-- document nested inside another one is what was breaking every page. --}}

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
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
          href="{{ route('admin.dashboard') }}">
          <i class="bi bi-speedometer2"></i>Dashboard
        </a>

        <div class="nav-label">Management</div>

        @include('partials.employees-menu')

        <div class="dropdown">
          <button class="sidebar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-cash-coin"></i><span>Instructor Rate</span>
          </button>
          <ul class="dropdown-menu">
            @foreach(['130', '150', '170', '190', '210', '220', '250'] as $rate)
              <li><a class="dropdown-item" href="#"
                  onclick="if(typeof showInstructorRate === 'function') { showInstructorRate('{{ $rate }}'); } else { window.location.href='{{ route('admin.dashboard') }}?rate={{ $rate }}'; } return false;"><i
                    class="bi bi-currency-dollar"></i>₱{{ $rate }}</a></li>
            @endforeach
          </ul>
        </div>

        <div class="dropdown">
          <button
            class="sidebar-btn dropdown-toggle {{ request()->routeIs('departments.*', 'bsit.*', 'bsba.*', 'bshm.*', 'education.*') ? 'active' : '' }}"
            type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-building"></i><span>Department</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('departments.index') }}"><i class="bi bi-gear"></i>Manage
                Departments</a></li>
            <li>
              <hr class="dropdown-divider my-1">
            </li>
            <li><a class="dropdown-item" href="{{ route('bsit.index') }}"><i class="bi bi-laptop"></i>BSIT</a></li>
            <li><a class="dropdown-item" href="{{ route('bsba.index') }}"><i class="bi bi-briefcase"></i>BSBA</a></li>
            <li><a class="dropdown-item" href="{{ route('bshm.index') }}"><i class="bi bi-cup-hot"></i>BSHM</a></li>
            <li><a class="dropdown-item" href="{{ route('education.index') }}"><i class="bi bi-book"></i>Education</a>
            </li>
          </ul>
        </div>

        <div class="nav-label">Records</div>

        <div class="dropdown">
          <button
            class="sidebar-btn dropdown-toggle {{ request()->routeIs('admin.history', 'admin.payroll.history', 'admin.attendance-payroll.*') ? 'active' : '' }}"
            type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-clipboard-data"></i><span>History Records</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('admin.history') }}"><i class="bi bi-calendar-check"></i>History
                Log</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.payroll.history') }}"><i
                  class="bi bi-scissors"></i>Payroll History</a></li>
            <li>
              <hr class="dropdown-divider my-1">
            </li>
            <li><a class="dropdown-item d-flex align-items-center justify-content-between gap-2" href="{{ route('admin.attendance-payroll.index') }}">
              <span><i class="bi bi-clock-history"></i> Attendance Payroll</span>
              @php $_pendingRev = \App\Models\AttendanceSession::whereIn('status', ['open','needs_review'])->count(); @endphp
              @if($_pendingRev > 0)
                <span class="badge bg-warning text-dark" style="font-size:.65rem;">{{ $_pendingRev }}</span>
              @endif
            </a></li>
          </ul>
        </div>

        <a href="{{ route('master.list') }}"
          class="sidebar-btn text-decoration-none {{ request()->routeIs('master.list*') ? 'active' : '' }}">
          <i class="bi bi-list-ul"></i><span>Master List</span>
        </a>

        <a href="{{ route('admin.salary.adjustment') }}"
          class="sidebar-btn text-decoration-none {{ request()->routeIs('admin.salary.adjustment*') ? 'active' : '' }}">
          <i class="bi bi-calculator"></i><span>Salary Adjustment</span>
        </a>

        <a href="{{ route('admin.deductions.index') }}"
          class="sidebar-btn text-decoration-none {{ request()->routeIs('admin.deductions.*') ? 'active' : '' }}">
          <i class="bi bi-percent"></i><span>Tax & Gov't Deductions</span>
        </a>

        <div class="nav-label">Analytics</div>

        <a href="{{ route('admin.evaluation.results') }}"
          class="sidebar-btn text-decoration-none {{ request()->routeIs('admin.evaluation.results') ? 'active' : '' }}">
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

@once
  <script>
    // "Send Payslips (All)" for pages that don't ship the dashboard's #payslipDateModal.
    document.addEventListener('DOMContentLoaded', function () {
      const btn = document.getElementById('sendPayslipsBtn');
      if (!btn || document.getElementById('payslipDateModal') || typeof Swal === 'undefined') return;

      btn.addEventListener('click', function () {
        Swal.fire({
          title: 'Select Payslip Date Range',
          html:
            '<label for="swalPayslipStart" class="form-label d-block text-start mt-2 mb-1">Start Date</label>' +
            '<input type="date" id="swalPayslipStart" class="form-control">' +
            '<label for="swalPayslipEnd" class="form-label d-block text-start mt-3 mb-1">End Date</label>' +
            '<input type="date" id="swalPayslipEnd" class="form-control">',
          showCancelButton: true,
          confirmButtonText: 'Yes, send now',
          confirmButtonColor: '#2563eb',
          cancelButtonColor: '#64748b',
          preConfirm: function () {
            const start = document.getElementById('swalPayslipStart').value;
            const end = document.getElementById('swalPayslipEnd').value;
            if (!start || !end) {
              Swal.showValidationMessage('Please select both dates.');
              return false;
            }
            return { start: start, end: end };
          }
        }).then(function (r) {
          if (!r.isConfirmed) return;
          document.getElementById('payslipStartDateHidden').value = r.value.start;
          document.getElementById('payslipEndDateHidden').value = r.value.end;
          Swal.fire({ title: 'Sending…', html: 'Please wait.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
          document.getElementById('sendPayslipsForm').submit();
        });
      });
    });
  </script>
@endonce
