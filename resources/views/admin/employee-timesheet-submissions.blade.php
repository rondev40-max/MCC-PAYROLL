<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Employee Timesheet Submissions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .sidebar, #sidebarMobileBtn, .sidebar-overlay { display: none !important; }
    .sidebar-shift { margin-left: 0 !important; }
  </style>
  @include('layouts.sidebar-styles')
</head>
<body>
  @include('layouts.sidebar')
  <div class="sidebar-shift">
  <div class="container-fluid py-4">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
      <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-clock-history me-2"></i>Employee Timesheet Submissions</h3>
        <div class="text-muted" style="font-size:.9rem;">
          Showing <strong>{{ $submissions->count() }}</strong> item(s) with status <strong>Submitted</strong>.
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-speedometer2 me-1"></i>Back</a>
        <a href="{{ route('admin.employee-timesheets.submissions.print') }}" 
   class="btn btn-outline-secondary btn-sm" 
   target="_blank">
    <i class="bi bi-printer me-1"></i>Print
</a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:70px;">#</th>
                <th>User</th>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Work Type</th>
                <th>Task</th>
                <th>Remarks</th>
                <th>Hours</th>
                <th>Status</th>
                <th style="width:190px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($submissions as $i => $s)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td>
                    <div class="fw-semibold">{{ $s->employee_name ?? $s->user?->name ?? '—' }}</div>
                    <div class="text-muted" style="font-size:.85rem;">{{ $s->email ?? $s->user?->email ?? '' }}</div>
                  </td>
                  <td>{{ $s->date ? $s->date->format('M d, Y') : '—' }}</td>
                  <td>{{ $s->time_in ?? '—' }}</td>
                  <td>{{ $s->time_out ?? '—' }}</td>
                  <td>{{ $s->work_type ?? '—' }}</td>
                  <td style="max-width:260px;">{{ $s->task ?: '—' }}</td>
                  <td style="max-width:260px;">{{ $s->remarks ?: '—' }}</td>
                  <td class="fw-semibold">{{ $s->hours ?? 0 }}</td>
                  <td>
                    <span class="badge text-bg-warning">{{ $s->status }}</span>
                  </td>
                  <td>
                    <form action="{{ route('admin.employee-timesheets.submissions.approve', $s->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button class="btn btn-success btn-sm" type="submit" onclick="return confirm('Approve this submission?')">
                        <i class="bi bi-check2-circle me-1"></i>Approve
                      </button>
                    </form>
                    <form action="{{ route('admin.employee-timesheets.submissions.reject', $s->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Reject this submission?')">
                        <i class="bi bi-x-circle me-1"></i>Reject
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <div class="mt-2">No submitted timesheets found.</div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>

