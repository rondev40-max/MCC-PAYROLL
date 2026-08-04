<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>History Records</title>
   <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ═══════════════════════════════════════════
       DESIGN TOKENS & BASE
       ═══════════════════════════════════════════ */
    :root {
      --primary: #3b82f6;
      --primary-hover: #2563eb;
      --bg-light: #f8fafc;
      --card-bg: #ffffff;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --shadow-sm: 0 1px 3px rgba(0,0,0,.04);
      --shadow-md: 0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);
      --radius: 12px;
      --radius-sm: 8px;
    }
    body {
      background-color: var(--bg-light);
      font-family: 'Inter', system-ui, sans-serif;
      color: var(--text-main);
      -webkit-font-smoothing: antialiased;
    }

    /* ═══════════════════════════════════════════
       COMPONENTS
       ═══════════════════════════════════════════ */
    .card-modern {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      padding: 1.5rem;
      transition: box-shadow 0.2s ease;
    }
    .card-modern:hover {
      box-shadow: var(--shadow-md);
    }
    
    .page-title {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      margin-bottom: 0.25rem;
    }
    .page-subtitle {
      color: var(--text-muted);
      font-size: 0.95rem;
      font-weight: 400;
    }

    /* Buttons */
    .btn-modern {
      border-radius: var(--radius-sm);
      font-weight: 500;
      padding: 0.5rem 1rem;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn-icon-only {
      padding: 0.4rem 0.6rem;
      border-radius: 6px;
      line-height: 1;
    }
    
    /* Summary Cards */
    .stat-card {
      padding: 1.25rem;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      background: var(--card-bg);
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }
    .stat-blue { background: #eff6ff; color: #3b82f6; }
    .stat-green { background: #f0fdf4; color: #22c55e; }
    .stat-red { background: #fef2f2; color: #ef4444; }
    .stat-purple { background: #faf5ff; color: #a855f7; }
    
    .stat-value {
      font-size: 1.5rem;
      font-weight: 700;
      line-height: 1.2;
    }
    .stat-label {
      color: var(--text-muted);
      font-size: 0.85rem;
      font-weight: 500;
    }

    /* Toolbar */
    .toolbar-container {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1rem;
      margin-bottom: 1.5rem;
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: center;
      justify-content: space-between;
    }
    .search-wrapper {
      position: relative;
      flex: 1;
      min-width: 250px;
      max-width: 400px;
    }
    .search-wrapper i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
    }
    .search-wrapper .form-control {
      padding-left: 2.5rem;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
    }
    .search-wrapper .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    /* Table */
    .table-container {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
      background: var(--card-bg);
    }
    .table {
      margin-bottom: 0;
      font-size: 0.9rem;
    }
    .table thead th {
      background: #f8fafc;
      color: var(--text-muted);
      font-weight: 600;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 1rem;
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }
    .table tbody td {
      padding: 1rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--border);
    }
    .table-striped>tbody>tr:nth-of-type(odd)>* {
      background-color: #fbfcfd;
    }
    .table-hover>tbody>tr:hover>* {
      background-color: #f1f5f9;
    }
    
    .employee-cell {
      display: flex;
      flex-direction: column;
    }
    .employee-name {
      font-weight: 600;
      color: var(--text-main);
    }
    .employee-email {
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    /* Badges */
    .badge-soft-success { background: #dcfce7; color: #166534; }
    .badge-soft-danger { background: #fee2e2; color: #991b1b; }
    .badge-soft-warning { background: #fef9c3; color: #854d0e; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; }
    .badge-soft-primary { background: #dbeafe; color: #1e40af; }
    .badge-soft-info { background: #cffafe; color: #164e63; }
    .badge {
      font-weight: 500;
      padding: 0.4em 0.8em;
      border-radius: 6px;
    }

    /* Pagination Override */
    .pagination-container {
      padding: 1rem;
      border-top: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .pagination { margin: 0; }
    .page-link {
      border: 1px solid var(--border);
      color: var(--text-muted);
      padding: 0.4rem 0.75rem;
      font-size: 0.875rem;
    }
    .page-link:hover { background: #f1f5f9; color: var(--text-main); }
    .page-item.active .page-link {
      background-color: var(--primary);
      border-color: var(--primary);
      color: white;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 4rem 1rem;
    }
    .empty-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.8;
    }
  </style>
</head>
<body>
  <div class="container-fluid px-4 py-4 max-w-7xl mx-auto" style="max-width: 1400px;">
    
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h1 class="page-title">History Records</h1>
        <p class="page-subtitle mb-0">View and manage sent payroll records.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('admin.history.trash') }}" class="btn btn-modern btn-outline-danger">
          <i class="bi bi-trash"></i> Trash
        </a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-modern btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </div>

    @if(!isset($tableReady) || !$tableReady)
      <div class="alert alert-danger card-modern border-danger">
        <h4 class="alert-heading fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Database Error</h4>
        <p class="mb-1 text-danger">The payslip history table could not be loaded. Please ensure all migrations are run.</p>
        <p class="mb-0 small text-danger">{{ $errorMessage ?? 'Unknown error.' }}</p>
      </div>
    @else
      
      <!-- Summary Cards (Frontend Computed) -->
      @php
        // Purely frontend logic to compute counts without touching backend controllers
        try {
          $totalCount = \App\Models\PayslipHistory::count();
          $todayCount = \App\Models\PayslipHistory::whereDate('sent_at', today())->count();
          $failedCount = \App\Models\PayslipHistory::whereNotNull('error')->count();
          $monthCount = \App\Models\PayslipHistory::whereMonth('sent_at', now()->month)->whereYear('sent_at', now()->year)->count();
        } catch (\Exception $e) {
          $totalCount = $todayCount = $failedCount = $monthCount = 0;
        }
      @endphp
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon stat-blue"><i class="bi bi-files"></i></div>
            <div>
              <div class="stat-value">{{ number_format($totalCount) }}</div>
              <div class="stat-label">Total Records</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon stat-green"><i class="bi bi-send-check"></i></div>
            <div>
              <div class="stat-value">{{ number_format($todayCount) }}</div>
              <div class="stat-label">Sent Today</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon stat-red"><i class="bi bi-exclamation-octagon"></i></div>
            <div>
              <div class="stat-value">{{ number_format($failedCount) }}</div>
              <div class="stat-label">Failed Records</div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon stat-purple"><i class="bi bi-calendar-month"></i></div>
            <div>
              <div class="stat-value">{{ number_format($monthCount) }}</div>
              <div class="stat-label">This Month</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Compact Toolbar -->
      <form method="GET" action="{{ route('admin.history') }}" class="toolbar-container m-0 mb-4">
        <div class="search-wrapper">
          <i class="bi bi-search"></i>
          <input type="email" name="email" class="form-control form-control-sm" style="padding-left: 2rem;" placeholder="Search employee email..." value="{{ request('email') }}">
        </div>
        
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <select class="form-select form-select-sm" style="width: auto; min-width: 120px;">
            <option>All Status</option>
            <option>Sent</option>
            <option>Failed</option>
            <option>Pending</option>
          </select>
          
          <input type="date" class="form-control form-control-sm" style="width: auto;" title="Date Range Placeholder">
          
          <button type="submit" class="btn btn-modern btn-primary btn-sm">
            Filter
          </button>
          
          <a href="{{ route('admin.history') }}" class="btn btn-modern btn-outline-secondary btn-sm" title="Reset Filters">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
          
          <button type="button" class="btn btn-modern btn-outline-success btn-sm ms-auto ms-md-2" title="Export Placeholder">
            <i class="bi bi-download"></i> Export
          </button>
        </div>
      </form>

      <!-- History Table -->
      <div class="table-container shadow-sm mb-4">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle">
            <thead>
              <tr>
                <th style="width: 40px; padding-left: 1.5rem;"><input type="checkbox" id="selectAllCheckboxes" class="form-check-input"></th>
                <th>Employee <i class="bi bi-chevron-expand ms-1 text-muted"></i></th>
                <th>Payroll Type</th>
                <th>Amount <i class="bi bi-chevron-expand ms-1 text-muted"></i></th>
                <th>Days/Hours</th>
                <th>Sent Date <i class="bi bi-chevron-expand ms-1 text-muted"></i></th>
                <th>Status <i class="bi bi-chevron-expand ms-1 text-muted"></i></th>
                <th class="text-end" style="padding-right: 1.5rem;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($histories as $history)
              <tr>
                <td style="padding-left: 1.5rem;">
                  <input type="checkbox" class="record-checkbox form-check-input" value="{{ $history->id }}">
                </td>
                <td>
                  <div class="employee-cell">
                    <span class="employee-name">{{ $history->name }}</span>
                    <span class="employee-email">{{ $history->email }}</span>
                  </div>
                </td>
                <td>
                  <span class="badge badge-soft-{{ match($history->employee_type) { 'Fulltime' => 'success', 'Part-time' => 'info', 'Staff' => 'primary', 'Utility' => 'secondary', default => 'secondary' } }}">
                    {{ $history->employee_type }}
                  </span>
                </td>
                <td class="fw-medium text-dark">₱{{ number_format($history->total_honorarium ?? 0, 2) }}</td>
                <td class="text-muted">
                  {{ number_format($history->total_hours_or_days ?? 0, 2) }} {{ in_array(strtolower($history->employee_type), ['staff', 'utility']) ? 'days' : 'hrs' }}
                </td>
                <td>
                  <div class="text-dark">{{ \Carbon\Carbon::parse($history->sent_at)->format('M d, Y') }}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($history->sent_at)->format('h:i A') }}</div>
                </td>
                <td>
                  @if($history->error)
                    <span class="badge badge-soft-danger" data-bs-toggle="tooltip" title="{{ $history->error }}"><i class="bi bi-x-circle me-1"></i> Failed</span>
                  @else
                    <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i> Sent</span>
                  @endif
                </td>
                <td class="text-end" style="padding-right: 1.5rem;">
                  <div class="d-flex justify-content-end gap-1">
                    <button class="btn btn-outline-secondary btn-icon-only border-0" data-bs-toggle="tooltip" title="View Details">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-icon-only border-0" data-bs-toggle="tooltip" title="Download PDF">
                      <i class="bi bi-file-earmark-pdf"></i>
                    </button>
                    <button onclick="confirmSoftDelete({{ $history->id }})" class="btn btn-outline-danger btn-icon-only border-0" data-bs-toggle="tooltip" title="Move to Trash">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8">
                  <div class="empty-state">
                    <div class="empty-icon">📄</div>
                    <h5 class="fw-bold text-dark mb-1">No payroll history yet.</h5>
                    <p class="text-muted mb-0">Sent payroll records will appear here once employees receive their payslips.</p>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        
        <!-- Pagination & Bulk Actions -->
        <div class="pagination-container bg-white">
          <div class="d-flex align-items-center gap-2">
            <button id="deleteSelectedBtn" class="btn btn-modern btn-outline-danger btn-sm" disabled onclick="confirmMassSoftDelete()">
              <i class="bi bi-trash"></i> Delete Selected
            </button>
            @if(isset($histories) && $histories->total() > 0)
            <button id="deleteAllBtn" class="btn btn-modern btn-outline-danger btn-sm" onclick="confirmSoftDeleteAll()">
              Delete All
            </button>
            @endif
          </div>
          
          <div class="d-flex flex-wrap align-items-center justify-content-end gap-3 mt-3 mt-md-0">
            <div class="text-muted" style="font-size: 0.875rem;">
              Showing {{ $histories->firstItem() ?? 0 }}–{{ $histories->lastItem() ?? 0 }} of {{ $histories->total() ?? 0 }} records
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                @if ($histories->onFirstPage())
                  <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                  <li class="page-item"><a class="page-link" href="{{ $histories->previousPageUrl() }}">Previous</a></li>
                @endif

                @foreach ($histories->getUrlRange(max(1, $histories->currentPage() - 2), min($histories->lastPage(), $histories->currentPage() + 2)) as $page => $url)
                  @if ($page == $histories->currentPage())
                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                  @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                  @endif
                @endforeach

                @if ($histories->hasMorePages())
                  <li class="page-item"><a class="page-link" href="{{ $histories->nextPageUrl() }}">Next</a></li>
                @else
                  <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
              </ul>
            </nav>
          </div>
        </div>
      </div>
    @endif
  </div>

<script>
    // Utility function for fetch requests
    async function sendDelete(url, method = 'DELETE', body = null) {
      const options = {
        method: method,
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Content-Type': 'application/json'
        },
      };
      if (body) {
        options.body = JSON.stringify(body);
      }

      const response = await fetch(url, options);

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to complete action');
      }
      return response.json();
    }
    
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Checkbox logic
        const selectAll = document.getElementById('selectAllCheckboxes');
        const checkboxes = document.querySelectorAll('.record-checkbox');
        const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleDeleteButton();
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    selectAll.checked = false;
                } else if (document.querySelectorAll('.record-checkbox:checked').length === checkboxes.length) {
                    selectAll.checked = true;
                }
                toggleDeleteButton();
            });
        });

        function toggleDeleteButton() {
            const checkedCount = document.querySelectorAll('.record-checkbox:checked').length;
            deleteSelectedBtn.disabled = checkedCount === 0;
        }
    });

    function confirmSoftDelete(id) {
      Swal.fire({
        title: 'Move to Trash?',
        text: 'The record will be moved to trash and can be restored.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            await sendDelete(`{{ url('/admin/history') }}/${id}`);
            Swal.fire('Deleted!', 'Record moved to trash.', 'success').then(() => window.location.reload());
          } catch (err) {
            Swal.fire('Error', err.message, 'error');
          }
        }
      });
    }

    function confirmMassSoftDelete() {
        const selectedIds = Array.from(document.querySelectorAll('.record-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            Swal.fire('No records selected', 'Please select at least one record to delete.', 'info');
            return;
        }

        Swal.fire({
            title: 'Move selected to Trash?',
            text: `You are about to move ${selectedIds.length} record(s) to trash. They can be restored later.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, move to trash',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    await sendDelete(`{{ route('admin.history.mass.delete') }}`, 'DELETE', { ids: selectedIds });
                    Swal.fire('Deleted!', `${selectedIds.length} record(s) moved to trash.`, 'success').then(() => window.location.reload());
                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            }
        });
    }
    
    // ⭐️ NEW: Function to confirm and execute Soft Delete ALL ⭐️
    function confirmSoftDeleteAll() {
        const totalRecords = {{ $histories->total() }};
        if (totalRecords === 0) {
            Swal.fire('No records found', 'The history table is already empty.', 'info');
            return;
        }

        Swal.fire({
            title: 'Move ALL records to Trash?',
            text: `You are about to move all ${totalRecords} records to trash. They can be restored later. THIS ACTION WILL AFFECT ALL PAGES.`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Danger Red
            confirmButtonText: 'Yes, move ALL to trash',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    // Call the new route
                    const response = await sendDelete(`{{ route('admin.history.delete.all') }}`);
                    Swal.fire('Deleted All!', response.message, 'success').then(() => window.location.reload());
                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            }
        });
    }


    // Function to confirm permanent deletion (for trash page)
    function confirmForceDelete(id) {
      Swal.fire({
        title: 'Permanently delete?',
        text: 'This action cannot be undone.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete permanently',
        cancelButtonText: 'Cancel'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            // Note: If you have a separate route for trash, update the URL accordingly.
            // Assuming trash route uses /admin/history/{id}/force
            await sendDelete(`{{ url('/admin/history') }}/${id}/force`); 
            Swal.fire('Deleted!', 'Record permanently deleted.', 'success').then(() => window.location.reload());
          } catch (err) {
            Swal.fire('Error', err.message, 'error');
          }
        }
      });
    }
  </script>
<script>
// DevTools detection to make page blank if opened
devtools.detect(function(status){
  if(status){
    document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; font-family: Arial;"><h1>Access Denied</h1></div>';
    document.body.style.overflow = 'hidden';
  }
});
</script>
</body>
</html>