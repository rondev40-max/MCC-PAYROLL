@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
  <h1 class="h3">Attendance payroll</h1>
  <p>Completed web punches automatically supply payroll hours after activation. Payroll matches the master-list employee ID or email. Maintain rates and deductions in the payroll timesheet.</p>
  <div class="alert alert-info">Hourly pay uses recorded hours. Daily pay uses hours / {{ config('attendance.hours_per_day') }}, capped at one regular day per duty date. Unpaid breaks require time out. Overnight duty belongs to the time-in date. Overtime premiums and paid leave require a separate payroll adjustment.</div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
  <form class="d-flex gap-3 flex-wrap align-items-end mb-4" method="GET">
    <div><label for="start_date" class="form-label">From</label><input class="form-control" id="start_date" name="start_date" type="date" value="{{ $start }}" required></div>
    <div><label for="end_date" class="form-label">Through</label><input class="form-control" id="end_date" name="end_date" type="date" value="{{ $end }}" required></div>
    <button class="btn btn-primary">View duty hours</button>
  </form>
  @foreach($employees as $person)
    @php $summary = $summaries[$person->id]; @endphp
    <div class="card mb-3">
      <div class="card-body">
        <h2 class="h5">{{ $person->name }} <small class="text-muted">#{{ $person->id }}</small></h2>
        <p>{{ number_format($summary['hours'], 2) }} duty hours &middot; {{ number_format($summary['days'], 4) }} day equivalents &middot; {{ $summary['pending'] }} unresolved sessions</p>
        @forelse($previews[$person->id] as $preview)
          <p><strong>Attendance estimate ({{ $preview['type'] }}):</strong> {{ number_format($preview['rate'], 2) }} / {{ $preview['unit'] }} &middot; Gross PHP {{ number_format($preview['gross'], 2) }} &middot; Net PHP {{ number_format($preview['net'], 2) }}. {{ $summary['pending'] ? 'Resolve pending sessions before release.' : 'Ready for payroll review.' }}</p>
        @empty
          <p class="text-danger">No linked payroll timesheet for this cutoff. Create or link the employee's payroll row and set the rate before releasing payroll.</p>
        @endforelse
        @if($person->attendance_payroll_from)
          <p class="text-success">Automatic attendance payroll from {{ $person->attendance_payroll_from }}. Unrecorded days contribute zero hours.</p>
        @else
          <form action="{{ route('admin.attendance-payroll.enable', $person) }}" method="POST" class="d-flex align-items-end flex-wrap gap-2 mb-3">
            @csrf
            <div><label class="form-label" for="effective-{{ $person->id }}">Start cutoff (1st or 16th)</label><input id="effective-{{ $person->id }}" class="form-control" type="date" name="effective_from" value="{{ $start }}" required></div>
            <button class="btn btn-outline-primary">Enable automatic payroll</button>
            <small class="text-muted">Verify the full cutoff's punches first. Recorded hours replace manual scheduled hours from this date.</small>
          </form>
        @endif
        <details>
          <summary>View punches and resolve exceptions ({{ $summary['sessions']->count() }})</summary>
          <div class="table-responsive mt-2">
            <table class="table table-sm align-middle">
              <thead><tr><th>Time in</th><th>Time out</th><th>Hours</th><th>Status / review</th></tr></thead>
              <tbody>
              @forelse($summary['sessions'] as $session)
                <tr>
                  <td>{{ $session->clocked_in_at->format('M d, Y H:i:s') }}</td>
                  <td>{{ $session->clocked_out_at?->format('M d, Y H:i:s') ?? 'Missing' }}</td>
                  <td>{{ number_format($session->worked_seconds / 3600, 2) }}</td>
                  <td>
                    {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                    @if($session->review_note)<p>{{ $session->review_note }} (reviewer #{{ $session->reviewed_by }})</p>@endif
                    @if(in_array($session->status, ['open', 'needs_review']))
                      <form method="POST" action="{{ route('admin.attendance-payroll.review', $session) }}" class="d-flex flex-wrap gap-2 mt-2">
                        @csrf
                        <select name="decision" class="form-select w-auto" aria-label="Review decision"><option value="complete">Approve duty hours</option><option value="void">Exclude session</option></select>
                        <input class="form-control w-auto" name="hours" type="number" min="0" max="18" step="0.01" placeholder="Verified hours" aria-label="Verified duty hours">
                        <input class="form-control" name="note" required minlength="5" maxlength="1000" placeholder="Reason for correction (required)" aria-label="Reason for correction">
                        <button class="btn btn-outline-primary btn-sm">Save review</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="4">No punches in this period.</td></tr>
              @endforelse
              </tbody>
            </table>
          </div>
        </details>
      </div>
    </div>
  @endforeach
  {{ $employees->links() }}
</div>
@endsection
