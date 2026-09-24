@extends('layouts.admin')

@section('content')
<style>
.ap-hero {
    background: linear-gradient(135deg,#1d4ed8 0%,#2563eb 50%,#0891b2 100%);
    border-radius: 14px; padding: 1.5rem 2rem; margin-bottom: 1.5rem; color: #fff;
    position: relative; overflow: hidden;
}
.ap-hero::after {
    content:''; position:absolute; right:-40px; top:-40px;
    width:200px; height:200px; border-radius:50%;
    background: rgba(255,255,255,.06);
}
.ap-hero h1 { font-size:1.35rem; font-weight:800; margin:0 0 .25rem; }
.ap-hero p  { margin:0; opacity:.8; font-size:.875rem; }
.ap-hero .ap-hero-meta { display:flex; gap:1.5rem; margin-top:1rem; flex-wrap:wrap; }
.ap-hero .ap-hero-stat { background:rgba(255,255,255,.12); border-radius:10px; padding:.5rem 1rem; }
.ap-hero .ap-hero-stat .v { font-size:1.4rem; font-weight:800; line-height:1; }
.ap-hero .ap-hero-stat .l { font-size:.7rem; opacity:.75; text-transform:uppercase; letter-spacing:.5px; }

.ap-filter-card {
    background:#fff; border:1px solid #e6ebf2; border-radius:12px;
    padding:1rem 1.25rem; margin-bottom:1.25rem;
    display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap;
}
.ap-filter-card label { font-size:.75rem; font-weight:700; color:#8494a9; display:block; margin-bottom:.3rem; text-transform:uppercase; letter-spacing:.4px; }
.ap-filter-card input[type=date] {
    border:1px solid #e6ebf2; border-radius:8px; padding:.45rem .75rem;
    font-size:.875rem; background:#f8fafc; color:#0f1729;
    transition: border-color .15s;
}
.ap-filter-card input[type=date]:focus { border-color:#2563eb; outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.ap-filter-btn {
    background:#2563eb; color:#fff; border:none; border-radius:8px;
    padding:.46rem 1.1rem; font-size:.875rem; font-weight:700; cursor:pointer;
    transition: background .15s, transform .1s;
}
.ap-filter-btn:hover { background:#1d4ed8; transform:translateY(-1px); }

/* Employee cards */
.ap-emp-card {
    background:#fff; border:1px solid #e6ebf2; border-radius:14px;
    margin-bottom:1.25rem; overflow:hidden;
}
.ap-emp-hd {
    padding:1rem 1.25rem;
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem;
    border-bottom:1px solid #f1f4f9;
}
.ap-emp-hd-left { display:flex; align-items:center; gap:.9rem; }
.ap-emp-avatar {
    width:40px; height:40px; border-radius:50%;
    background:linear-gradient(135deg,#2563eb,#0891b2);
    display:grid; place-items:center; color:#fff;
    font-weight:800; font-size:.9rem; flex-shrink:0;
}
.ap-emp-name { font-weight:700; font-size:.95rem; color:#0f1729; }
.ap-emp-id   { font-size:.72rem; color:#8494a9; }
.ap-emp-metrics { display:flex; gap:.75rem; flex-wrap:wrap; }
.ap-metric {
    display:flex; align-items:center; gap:.35rem;
    background:#f6f8fb; border:1px solid #e6ebf2; border-radius:8px;
    padding:.3rem .7rem; font-size:.775rem; font-weight:600; color:#4b5a70;
}
.ap-metric i { color:#2563eb; }
.ap-metric.m-warn { background:rgba(217,119,6,.07); border-color:rgba(217,119,6,.25); color:#d97706; }
.ap-metric.m-warn i { color:#d97706; }
.ap-metric.m-ok   { background:rgba(5,150,105,.07); border-color:rgba(5,150,105,.25); color:#059669; }
.ap-metric.m-ok   i { color:#059669; }

/* Estimates */
.ap-est { padding:.75rem 1.25rem; background:#f8fafc; border-bottom:1px solid #f1f4f9; }
.ap-est-row { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; font-size:.82rem; color:#4b5a70; }
.ap-est-pill {
    display:inline-flex; align-items:center; gap:.3rem;
    background:#fff; border:1px solid #e6ebf2; border-radius:8px; padding:.25rem .65rem;
    font-size:.775rem; font-weight:700;
}
.ap-est-pill.ep-hr { color:#2563eb; border-color:rgba(37,99,235,.3); background:rgba(37,99,235,.05); }
.ap-est-pill.ep-gross { color:#059669; border-color:rgba(5,150,105,.3); background:rgba(5,150,105,.05); }
.ap-est-pill.ep-net { color:#0f1729; border-color:#e6ebf2; }
.ap-est-pill.ep-warn { color:#d97706; border-color:rgba(217,119,6,.3); background:rgba(217,119,6,.05); }

/* Enable form */
.ap-enable-form {
    padding:.75rem 1.25rem; background:rgba(37,99,235,.03);
    border-bottom:1px solid #e6ebf2;
    display:flex; align-items:flex-end; gap:.75rem; flex-wrap:wrap;
}
.ap-enable-form label { font-size:.72rem; font-weight:700; color:#8494a9; display:block; margin-bottom:.25rem; text-transform:uppercase; letter-spacing:.4px; }
.ap-enable-form input[type=date] {
    border:1px solid #e6ebf2; border-radius:8px; padding:.42rem .7rem; font-size:.82rem; background:#fff;
}
.ap-enable-form button {
    background:#2563eb; color:#fff; border:none; border-radius:8px;
    padding:.42rem 1rem; font-size:.82rem; font-weight:700; cursor:pointer;
}
.ap-active-tag {
    display:inline-flex; align-items:center; gap:.3rem;
    background:rgba(5,150,105,.08); color:#059669;
    border:1px solid rgba(5,150,105,.2); border-radius:8px;
    font-size:.775rem; font-weight:700; padding:.3rem .7rem;
}

/* Sessions details */
.ap-sessions-wrap { padding:.75rem 1.25rem 1.25rem; }
.ap-sessions-toggle {
    display:flex; align-items:center; gap:.5rem;
    font-size:.82rem; font-weight:700; color:#2563eb;
    background:none; border:none; cursor:pointer; padding:.3rem 0;
}
.ap-sessions-toggle:hover { color:#1d4ed8; }

.ap-sess-table { width:100%; border-collapse:collapse; font-size:.82rem; margin-top:.75rem; }
.ap-sess-table th { padding:.45rem .85rem; text-align:left; font-size:.7rem; text-transform:uppercase; letter-spacing:.4px; color:#8494a9; font-weight:700; background:#f8fafc; border-bottom:1px solid #e6ebf2; }
.ap-sess-table td { padding:.55rem .85rem; border-bottom:1px solid #f1f4f9; color:#4b5a70; vertical-align:middle; }
.ap-sess-table tr:last-child td { border-bottom:none; }
.ap-sess-table tr:hover td { background:#f4f8ff; }
.ap-sess-table .td-bold { font-weight:700; color:#0f1729; font-variant-numeric:tabular-nums; }
.ap-sess-table .td-mono { font-variant-numeric:tabular-nums; }
.ap-sess-pill { display:inline-flex; align-items:center; gap:.25rem; border-radius:99px; padding:.1rem .5rem; font-size:.68rem; font-weight:700; }
.ap-sess-pill.complete     { background:rgba(5,150,105,.1);   color:#059669; }
.ap-sess-pill.open         { background:rgba(37,99,235,.1);   color:#2563eb; }
.ap-sess-pill.needs_review { background:rgba(217,119,6,.1);   color:#d97706; }
.ap-sess-pill.void         { background:rgba(100,116,139,.1); color:#64748b; }

.ap-review-form { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.5rem; align-items:flex-end; }
.ap-review-form select, .ap-review-form input[type=number], .ap-review-form input[type=text] {
    border:1px solid #e6ebf2; border-radius:8px; padding:.38rem .65rem; font-size:.8rem; background:#fff; color:#0f1729;
}
.ap-review-form select { width:auto; }
.ap-review-form input[type=number] { width:100px; }
.ap-review-form input[type=text]   { flex:1; min-width:180px; }
.ap-review-btn {
    background:#2563eb; color:#fff; border:none; border-radius:8px;
    padding:.38rem .85rem; font-size:.8rem; font-weight:700; cursor:pointer; white-space:nowrap;
}
.ap-review-btn:hover { background:#1d4ed8; }
</style>

<div class="container-fluid py-4">

  {{-- Hero --}}
  <div class="ap-hero">
    <h1><i class="bi bi-clock-history me-2"></i>Attendance Payroll</h1>
    <p>Web punches automatically supply duty hours for payroll. Activate per employee from a chosen cutoff date.</p>
    <div class="ap-hero-meta">
      @php
        $totalSessions  = \App\Models\AttendanceSession::count();
        $pendingCount   = \App\Models\AttendanceSession::whereIn('status',['open','needs_review'])->count();
        $activeCount    = \App\Models\Employee::whereNotNull('attendance_payroll_from')->count();
      @endphp
      <div class="ap-hero-stat"><div class="v">{{ $totalSessions }}</div><div class="l">Total Sessions</div></div>
      <div class="ap-hero-stat"><div class="v" style="{{ $pendingCount ? 'color:#fbbf24;' : '' }}">{{ $pendingCount }}</div><div class="l">Needs Review</div></div>
      <div class="ap-hero-stat"><div class="v">{{ $activeCount }}</div><div class="l">Auto-Payroll Active</div></div>
    </div>
  </div>

  {{-- Alerts --}}
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

  {{-- How it works info --}}
  <div class="alert alert-info d-flex gap-2 align-items-start" style="border-radius:12px;">
    <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
    <div>
      <strong>How auto-payroll works:</strong>
      Hourly pay uses <strong>recorded hours</strong>. Daily pay uses <em>hours ÷ {{ config('attendance.hours_per_day') }}</em>, capped at one day per duty date.
      Unpaid breaks require a time-out. Overnight duty belongs to the time-in date.
      Sessions over {{ config('attendance.max_session_hours') }} hours are flagged for review.
      Overtime and paid leave require a separate payroll adjustment.
    </div>
  </div>

  {{-- Date range filter --}}
  <form class="ap-filter-card" method="GET">
    <div>
      <label for="start_date">From</label>
      <input class="" id="start_date" name="start_date" type="date" value="{{ $start }}" required>
    </div>
    <div>
      <label for="end_date">Through</label>
      <input class="" id="end_date" name="end_date" type="date" value="{{ $end }}" required>
    </div>
    <button type="submit" class="ap-filter-btn"><i class="bi bi-search me-1"></i>View Duty Hours</button>
  </form>

  {{-- Employee cards --}}
  @foreach($employees as $person)
    @php $summary = $summaries[$person->id]; @endphp
    <div class="ap-emp-card">

      {{-- Header --}}
      <div class="ap-emp-hd">
        <div class="ap-emp-hd-left">
          <div class="ap-emp-avatar">{{ strtoupper(substr($person->name, 0, 1)) }}</div>
          <div>
            <div class="ap-emp-name">{{ $person->name }}</div>
            <div class="ap-emp-id">Employee #{{ $person->id }} &middot; {{ $person->position ?? 'No position' }}</div>
          </div>
        </div>
        <div class="ap-emp-metrics">
          <span class="ap-metric"><i class="bi bi-hourglass-split"></i> {{ number_format($summary['hours'], 2) }} duty hours</span>
          <span class="ap-metric"><i class="bi bi-calendar-day"></i> {{ number_format($summary['days'], 4) }} days</span>
          @if($summary['pending'])
            <span class="ap-metric m-warn"><i class="bi bi-exclamation-circle"></i> {{ $summary['pending'] }} unresolved</span>
          @else
            <span class="ap-metric m-ok"><i class="bi bi-check-circle"></i> All resolved</span>
          @endif
        </div>
      </div>

      {{-- Payroll estimates --}}
      @if(count($previews[$person->id]))
        <div class="ap-est">
          @foreach($previews[$person->id] as $preview)
            <div class="ap-est-row">
              <strong style="font-size:.8rem;">{{ $preview['type'] }}</strong>
              <span class="ap-est-pill ep-hr">
                <i class="bi bi-clock"></i>
                {{ number_format($preview['rate'], 2) }} / {{ $preview['unit'] }}
              </span>
              <span class="ap-est-pill ep-gross">
                <i class="bi bi-cash"></i>
                Gross PHP {{ number_format($preview['gross'], 2) }}
              </span>
              <span class="ap-est-pill ep-net">
                <i class="bi bi-receipt"></i>
                Net PHP {{ number_format($preview['net'], 2) }}
              </span>
              @if($summary['pending'])
                <span class="ap-est-pill ep-warn"><i class="bi bi-clock-history"></i> Resolve {{ $summary['pending'] }} pending before release</span>
              @else
                <span class="ap-est-pill m-ok" style="background:rgba(5,150,105,.06);border-color:rgba(5,150,105,.25);color:#059669;"><i class="bi bi-check-circle"></i> Ready for payroll</span>
              @endif
            </div>
          @endforeach
        </div>
      @else
        <div class="ap-est">
          <span style="font-size:.82rem;color:#d97706;"><i class="bi bi-exclamation-triangle"></i> No linked payroll timesheet for this cutoff. Create/link the employee row and set the rate first.</span>
        </div>
      @endif

      {{-- Enable / active tag --}}
      <div class="ap-enable-form">
        @if($person->attendance_payroll_from)
          <span class="ap-active-tag"><i class="bi bi-lightning-fill"></i> Auto-payroll active from {{ \Carbon\Carbon::parse($person->attendance_payroll_from)->format('M d, Y') }}</span>
          <small class="text-muted" style="font-size:.75rem;">Recorded hours replace manual scheduled hours from this date onward.</small>
        @else
          <form action="{{ route('admin.attendance-payroll.enable', $person) }}" method="POST" style="display:contents;">
            @csrf
            <div>
              <label for="effective-{{ $person->id }}">Start cutoff (1st or 16th)</label>
              <input id="effective-{{ $person->id }}" type="date" name="effective_from" value="{{ $start }}" required>
            </div>
            <button type="submit"><i class="bi bi-lightning me-1"></i>Enable Auto-Payroll</button>
            <small class="text-muted" style="font-size:.75rem;align-self:center;">Verify full cutoff punches first. Recorded hours replace manual hours from this date.</small>
          </form>
        @endif
      </div>

      {{-- Session list (collapsible) --}}
      <div class="ap-sessions-wrap">
        <button class="ap-sessions-toggle" type="button" onclick="this.nextElementSibling.hidden=!this.nextElementSibling.hidden; this.querySelector('i').className='bi bi-'+(this.nextElementSibling.hidden?'chevron-down':'chevron-up');">
          <i class="bi bi-chevron-down"></i>
          View {{ $summary['sessions']->count() }} punch{{ $summary['sessions']->count() !== 1 ? 'es' : '' }} &amp; resolve exceptions
        </button>
        <div hidden>
          <div style="overflow-x:auto;">
            <table class="ap-sess-table">
              <thead>
                <tr>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Duty Hrs</th>
                  <th>Status &amp; Review</th>
                </tr>
              </thead>
              <tbody>
                @forelse($summary['sessions'] as $session)
                  <tr>
                    <td class="td-mono">{{ $session->clocked_in_at->format('M d, Y H:i:s') }}</td>
                    <td class="td-mono">{{ $session->clocked_out_at?->format('M d, Y H:i:s') ?? '<em style="color:#8494a9;">Missing</em>' }}</td>
                    <td class="td-bold">{{ number_format($session->worked_seconds / 3600, 2) }}</td>
                    <td>
                      @php $st = strtolower(str_replace(' ','_',$session->status)); @endphp
                      <span class="ap-sess-pill {{ $st }}">
                        @if($session->status === 'complete') <i class="bi bi-check-circle-fill" style="font-size:.6rem;"></i>
                        @elseif($session->status === 'open') <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
                        @elseif($session->status === 'needs_review') <i class="bi bi-exclamation-circle-fill" style="font-size:.6rem;"></i>
                        @else <i class="bi bi-slash-circle" style="font-size:.6rem;"></i>
                        @endif
                        {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                      </span>
                      @if($session->auto_closed)
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.68rem;" title="Shift automatically closed by system"><i class="bi bi-robot me-1"></i>Auto-Closed</span>
                      @endif
                      @if($session->ip_address)
                        <span class="badge bg-light text-secondary border ms-1" style="font-size:.65rem;" title="Clock-in IP: {{ $session->ip_address }}"><i class="bi bi-geo-alt"></i> {{ $session->ip_address }}</span>
                      @endif
                      @if($session->review_note)
                        <div style="font-size:.72rem;color:#8494a9;margin-top:.2rem;">
                          {{ $session->review_note }}
                          @if($session->reviewed_by)
                            <span style="opacity:.6;">(reviewer #{{ $session->reviewed_by }})</span>
                          @endif
                        </div>
                      @endif
                      @if(in_array($session->status, ['open', 'needs_review']))
                        <form method="POST" action="{{ route('admin.attendance-payroll.review', $session) }}" class="ap-review-form">
                          @csrf
                          <select name="decision" aria-label="Review decision">
                            <option value="complete">✅ Approve hours</option>
                            <option value="void">🚫 Exclude session</option>
                          </select>
                          <input type="number" name="hours" min="0" max="18" step="0.01" placeholder="Verified hrs" aria-label="Verified duty hours">
                          <input type="text" name="note" required minlength="5" maxlength="1000" placeholder="Reason (required, min 5 chars)" aria-label="Reason">
                          <button type="submit" class="ap-review-btn">Save Review</button>
                        </form>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" style="text-align:center;padding:1.5rem;color:#8494a9;">No punches in this period.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  @endforeach

  {{ $employees->links() }}
</div>
@endsection
