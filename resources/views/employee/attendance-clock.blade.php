<div class="card mb-3" style="padding:1.25rem;">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <h3 class="card-title mb-2">Time in / Time out</h3>
      <p class="mb-1">{{ now()->format('l, F j, Y') }} &middot; {{ config('app.timezone') }}</p>
      <p class="mb-2" role="status">
        @if($openClockSession)
          Clocked in since <strong>{{ $openClockSession->clocked_in_at->format('M d, h:i A') }}</strong>
        @else
          You are currently clocked out.
        @endif
      </p>
      <small>Times are recorded by the server. Time out for unpaid breaks, then time in when you return.</small>
    </div>
    @if($employee)
      <form method="POST" action="{{ route('employee.attendance.punch') }}" onsubmit="this.querySelector('button').disabled=true;">
        @csrf
        <input type="hidden" name="action" value="{{ $openClockSession ? 'out' : 'in' }}">
        <input type="hidden" name="last_session_id" value="{{ $clockSessions->first()?->id ?? 0 }}">
        <button type="submit" class="btn {{ $openClockSession ? 'btn-danger' : 'btn-primary' }}">
          <i class="bi bi-clock"></i> {{ $openClockSession ? 'Time out' : 'Time in' }}
        </button>
      </form>
    @else
      <p class="text-danger">Ask payroll to link your account to the employee master list to enable punching.</p>
    @endif
  </div>
  <p class="mt-3 mb-0 text-muted">
    @if($employee?->attendance_payroll_from)
      Attendance payroll starts {{ $employee->attendance_payroll_from }}. Completed sessions supply your duty hours; flagged sessions require admin review.
    @else
      Your punches are saved for admin review. Payroll must enable attendance-based calculation for your account.
    @endif
  </p>
</div>
<div class="card mb-3">
  <div class="card-hd"><h3 class="card-title">Web attendance &middot; latest 60 sessions</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead><tr><th>Duty date</th><th>Time in</th><th>Time out</th><th>Duty hours</th><th>Status / review</th></tr></thead>
      <tbody>
      @forelse($clockSessions as $clockSession)
        <tr>
          <td>{{ $clockSession->work_date->format('M d, Y') }}</td>
          <td>{{ $clockSession->clocked_in_at->format('M d, h:i:s A') }}</td>
          <td>{{ $clockSession->clocked_out_at?->format('M d, h:i:s A') ?? 'Not recorded' }}</td>
          <td>{{ $clockSession->status === 'complete' ? number_format($clockSession->worked_seconds / 3600, 2).' h' : 'Pending / excluded' }}</td>
          <td>{{ ucfirst(str_replace('_', ' ', $clockSession->status)) }} @if($clockSession->review_note)<br><small>{{ $clockSession->review_note }}</small>@endif</td>
        </tr>
      @empty
        <tr><td colspan="5">No web punches yet. Use Time in to start recording your duty hours.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
