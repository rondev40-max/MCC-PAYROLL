<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Employee Timesheet Submissions (Print)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <h4 class="fw-bold mb-1">Employee Timesheet Submissions</h4>
        <div class="text-muted" style="font-size:.9rem;">Status: <strong>Submitted</strong></div>
      </div>
      <div class="text-muted" style="font-size:.85rem;">Generated: {{ now()->format('M d, Y H:i') }}</div>
    </div>

    <table class="table table-sm table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Date</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Work Type</th>
          <th>Hours</th>
          <th>Task</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        @forelse($submissions as $i => $s)
          <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $s->employee_name ?? $s->user?->name ?? '—' }}<br><span class="text-muted">{{ $s->email ?? $s->user?->email ?? '' }}</span></td>
            <td>{{ $s->date ? $s->date->format('M d, Y') : '—' }}</td>
            <td>{{ $s->time_in ?? '—' }}</td>
            <td>{{ $s->time_out ?? '—' }}</td>
            <td>{{ $s->work_type ?? '—' }}</td>
            <td>{{ $s->hours ?? 0 }}</td>
            <td style="max-width:240px;">{{ $s->task ?: '—' }}</td>
            <td style="max-width:240px;">{{ $s->remarks ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center text-muted py-4">No data.</td></tr>
        @endforelse
      </tbody>
    </table>

    <script>window.print();</script>
  </div>
</body>
</html>

