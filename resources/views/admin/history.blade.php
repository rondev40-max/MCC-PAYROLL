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
  
  <style>
    /* ⭐️ FIX: Custom CSS para ma-override ang masyadong malaking buttons/pagination */
    .pagination-sm .page-link {
        padding: 0.25rem 0.5rem; /* Bawasan ang padding */
        font-size: 0.875rem; /* Bawasan ang font size */
        line-height: 1.5;
    }
    
    .card-soft {
      background:#ffffff;
      border:1px solid #eef1f4;
      border-radius:1rem;
      box-shadow:0 8px 24px rgba(0,0,0,.04);
    }
    
    .table thead th {
        background-color: #3498db;
        color: white;
    }
    
    .table-sm th, .table-sm td {
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
        font-size: 0.9rem;
    }

    .sidebar, #sidebarMobileBtn, .sidebar-overlay { display: none !important; }
    .sidebar-shift { margin-left: 0 !important; }
  </style>
  @include('layouts.sidebar-styles')
</head>
<body class="bg-light">
  @include('layouts.sidebar')
  <div class="sidebar-shift">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>History Records</h3>
      <div>
        <a href="{{ route('admin.history.trash') }}" class="btn btn-outline-warning me-2"><i class="bi bi-trash"></i> Trash</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
      </div>
    </div>

    @if(!isset($tableReady) || !$tableReady)
      <div class="alert alert-danger">
        <h4 class="alert-heading">Database Error!</h4>
        <p>The payslip history table could not be loaded. Please ensure all migrations are run.</p>
        <p class="mb-0 small">{{ $errorMessage ?? 'Unknown error.' }}</p>
      </div>
    @else
      <div class="card-soft p-4 mb-4">
        <h5 class="card-title"><i class="bi bi-filter me-2"></i>Filter Records</h5>
        <form method="GET" action="{{ route('admin.history') }}" class="row g-3 align-items-end">
          <div class="col-md-6">
            <label for="email" class="form-label">Search by Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="employee@example.com" value="{{ request('email') }}">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-search me-1"></i> Search
            </button>
          </div>
          <div class="col-md-3">
            <a href="{{ route('admin.history') }}" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-clockwise me-1"></i> Reset
            </a>
          </div>
        </form>
      </div>

      <div class="card-soft p-4">
        <h5 class="card-title mb-3">Payslip History Log</h5>
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-sm table-hover">
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAllCheckboxes"></th>
                <th>#</th>
                <th>Employee Name</th>
                <th>Email</th>
                <th>Type</th>
                <th>Honorarium</th>
                <th>Days/Hours</th>
                <th>Sent At</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($histories as $history)
              <tr>
                <td><input type="checkbox" class="record-checkbox" value="{{ $history->id }}"></td>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $history->name }}</td>
                <td>{{ $history->email }}</td>
                <td><span class="badge bg-{{ match($history->employee_type) { 'Fulltime' => 'success', 'Part-time' => 'info', 'Staff' => 'primary', 'Utility' => 'secondary', default => 'light' } }}">{{ $history->employee_type }}</span></td>
                <td>₱{{ number_format($history->total_honorarium ?? 0, 2) }}</td>
                <td>{{ number_format($history->total_hours_or_days ?? 0, 2) }} {{ in_array(strtolower($history->employee_type), ['staff', 'utility']) ? 'days' : 'hrs' }}</td>
                <td>{{ \Carbon\Carbon::parse($history->sent_at)->format('M d, Y h:i A') }}</td>
                <td>
                  @if($history->error)
                    <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $history->error }}">FAILED</span>
                  @else
                    <span class="badge bg-success">SUCCESS</span>
                  @endif
                </td>
                <td>
                  <button onclick="confirmSoftDelete({{ $history->id }})" class="btn btn-danger btn-sm" title="Move to Trash">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center text-muted">No history records found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="d-flex align-items-center">
            <button id="deleteSelectedBtn" class="btn btn-danger btn-sm me-2" disabled onclick="confirmMassSoftDelete()">
              <i class="bi bi-trash"></i> Delete Selected
            </button>
            
            {{-- ⭐️ NEW: The "Delete All" Button ⭐️ --}}
            @if($histories->total() > 0)
            <button id="deleteAllBtn" class="btn btn-outline-danger btn-sm me-4" onclick="confirmSoftDeleteAll()">
              <i class="bi bi-trash"></i> Delete All
            </button>
            @endif
            
            <div class="text-muted small">
              Showing {{ $histories->firstItem() }} to {{ $histories->lastItem() }} of {{ $histories->total() }} results
            </div>
          </div>
          
          {{-- ⭐️ FIX: Idinagdag ang 'pagination-sm' class dito para lumiit ang buttons --}}
          <nav>
            <ul class="pagination pagination-sm mb-0">
              {{-- Previous Page Link --}}
              @if ($histories->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
              @else
                <li class="page-item"><a class="page-link" href="{{ $histories->previousPageUrl() }}">Previous</a></li>
              @endif

              {{-- Pagination Elements (Page Numbers) --}}
              @foreach ($histories->getUrlRange(1, $histories->lastPage()) as $page => $url)
                @if ($page == $histories->currentPage())
                  <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                @else
                  <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
              @endforeach

              {{-- Next Page Link --}}
              @if ($histories->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $histories->nextPageUrl() }}">Next</a></li>
              @else
                <li class="page-item disabled"><span class="page-link">Next</span></li>
              @endif
            </ul>
          </nav>
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
</div>
</body>
</html>