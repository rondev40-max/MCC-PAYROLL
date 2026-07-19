<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trash Records</title>
   <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0"><i class="bi bi-trash me-2"></i>Trash Records</h3>
      <div>
        <a href="{{ route('admin.history') }}" class="btn btn-outline-primary me-2"><i class="bi bi-arrow-left"></i> Back to History</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-house"></i> Dashboard</a>
      </div>
    </div>

    @if(!isset($tableReady) || !$tableReady)
      <div class="alert alert-warning">
        <strong>Heads up:</strong> History table is not ready. Please run migrations:
        <code>php artisan migrate --path=database/migrations/2025_09_22_120000_create_payslip_histories.php</code>
      </div>
    @endif

    <div class="card mb-3">
      <div class="card-body">
        <form class="row g-2" method="GET" action="{{ route('admin.history.trash') }}">
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Search email" {{ (!isset($tableReady) || !$tableReady) ? 'disabled' : '' }}>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <button class="btn btn-primary w-100" {{ (!isset($tableReady) || !$tableReady) ? 'disabled' : '' }}><i class="bi bi-search"></i> Filter</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Type</th>
                <th>Total</th>
                <th>Days</th>
                <th>Sent At</th>
                <th>Deleted At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @if(isset($tableReady) && $tableReady)
                @forelse($histories as $i => $h)
                  <tr>
                    <td>{{ $histories->firstItem() + $i }}</td>
                    <td>{{ $h->name }}</td>
                    <td>{{ $h->email }}</td>
                    <td>{{ $h->employee_type }}</td>
                    <td>₱ {{ number_format($h->total_honorarium, 2) }}</td>
                    <td>{{ $h->days }}</td>
                    <td>{{ \Carbon\Carbon::parse($h->sent_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($h->deleted_at)->format('Y-m-d H:i') }}</td>
                    <td>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="confirmRestore({{ $h->id }})">
                          <i class="bi bi-arrow-counterclockwise"></i> Restore
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmForceDelete({{ $h->id }})">
                          <i class="bi bi-trash3"></i> Delete Permanently
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center py-4 text-muted">No trash records found.</td>
                  </tr>
                @endforelse
              @else
                <tr>
                  <td colspan="9" class="text-center py-4 text-muted">Table not available yet. Please run migrations.</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
        <div class="p-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                {{-- ⭐️ NEW: The "Delete All Permanently" Button ⭐️ --}}
                @if($histories->total() > 0)
                <button id="forceDeleteAllBtn" class="btn btn-danger btn-sm me-4" onclick="confirmForceDeleteAll()">
                    <i class="bi bi-trash3"></i> Delete All Permanently
                </button>
                @endif
                <div class="text-muted small">
                    Showing {{ $histories->firstItem() }} to {{ $histories->lastItem() }} of {{ $histories->total() }} results
                </div>
            </div>
            
            {{-- ⭐️ FIX: Pinalitan ng pagination-sm class para lumiit ang buttons ⭐️ --}}
            <nav class="pagination-sm">
                @if(isset($tableReady) && $tableReady)
                    {{ $histories->links('pagination::bootstrap-5') }}
                @endif
            </nav>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    function csrfToken() {
      const el = document.querySelector('meta[name="csrf-token"]');
      return el ? el.getAttribute('content') : '{{ csrf_token() }}';
    }

    async function sendRequest(url, method = 'DELETE') {
      const response = await fetch(url, {
        method: method,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        }
      });
      if (!response.ok) {
        let msg = 'Failed';
        try { const data = await response.json(); msg = data.message || msg; } catch (e) {}
        throw new Error(msg);
      }
      return response.json();
    }

    function confirmRestore(id) {
      Swal.fire({
        title: 'Restore Record?',
        text: 'This record will be restored to the history.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, restore',
        cancelButtonText: 'Cancel'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            await sendRequest(`{{ url('/admin/history') }}/${id}/restore`, 'PATCH');
            Swal.fire('Restored!', 'Record restored to history.', 'success').then(() => window.location.reload());
          } catch (err) {
            Swal.fire('Error', err.message, 'error');
          }
        }
      });
    }

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
            await sendRequest(`{{ url('/admin/history') }}/${id}/force`);
            Swal.fire('Deleted!', 'Record permanently deleted.', 'success').then(() => window.location.reload());
          } catch (err) {
            Swal.fire('Error', err.message, 'error');
          }
        }
      });
    }
    function confirmForceDeleteAll() {
        const totalRecords = {{ $histories->total() }};
        if (totalRecords === 0) {
            Swal.fire('No records found', 'The trash table is already empty.', 'info');
            return;
        }

        Swal.fire({
            title: 'PERMANENTLY DELETE ALL RECORDS?',
            text: `You are about to permanently delete all ${totalRecords} records from trash. THIS ACTION CANNOT BE UNDONE.`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33', // Danger Red
            confirmButtonText: 'Yes, DELETE ALL PERMANENTLY',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    // Call the new route (admin.history.force.delete.all)
                    const response = await sendRequest(`{{ route('admin.history.force.delete.all') }}`, 'DELETE'); 
                    Swal.fire('Deleted All!', response.message, 'success').then(() => window.location.reload());
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
    document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
  }
});
</script>
</body>
</html>

