{{-- ══════════════════════════════════════════════════════════════════════
     ATTENDANCE TIME-IN / TIME-OUT STAMP
     Server-stamped. Elapsed timer runs client-side only for display.
     Payroll automatically uses completed sessions when enabled.
══════════════════════════════════════════════════════════════════════ --}}

@php
    $clockedIn     = (bool) $openClockSession;
    $clockInEpoch  = $clockedIn ? $openClockSession->clocked_in_at->timestamp : 0;
    $pendingCount  = $clockSessions->whereIn('status', ['open', 'needs_review'])->count();
    $totalHours    = $clockSessions->where('status', 'complete')->sum('worked_seconds') / 3600;
@endphp

<style>
/* ── Attendance Clock Card ───────────────────────────────────────── */
.att-clock-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--r-md, 12px);
    padding: 0;
    overflow: hidden;
    margin-bottom: 1rem;
    position: relative;
}

/* Coloured accent strip at the very top */
.att-clock-card::before {
    content: '';
    display: block;
    height: 3px;
    background: var(--att-accent, var(--brand));
    transition: background .4s;
}
.att-clock-card.is-in::before  { --att-accent: var(--accent, #059669); }
.att-clock-card.is-out::before { --att-accent: var(--text-3, #8494a9); }

.att-clock-inner {
    padding: 1.25rem 1.5rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

/* ── Left column: status + elapsed ──────────────────────────────── */
.att-clock-left { display: flex; align-items: center; gap: 1rem; min-width: 0; }

/* Pulsing status orb */
.att-orb {
    width: 48px; height: 48px;
    border-radius: 50%;
    display: grid; place-items: center;
    flex-shrink: 0;
    font-size: 1.25rem;
    background: rgba(5,150,105,.12);
    color: var(--accent, #059669);
    position: relative;
    transition: background .4s, color .4s;
}
.att-orb.orb-out {
    background: var(--bg-2, #eef2f7);
    color: var(--text-3, #8494a9);
}
.att-orb.orb-in::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid rgba(5,150,105,.35);
    animation: orb-pulse 2s ease-in-out infinite;
}
@keyframes orb-pulse {
    0%,100% { transform: scale(1); opacity: 1; }
    50%      { transform: scale(1.18); opacity: 0; }
}

.att-status-group {}
.att-status-label {
    font-size: var(--fs-2xs, .6875rem);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-3, #8494a9);
    margin-bottom: 1px;
}
.att-status-value {
    font-size: var(--fs-sm, .8125rem);
    font-weight: 600;
    color: var(--text, #0f1729);
}
.att-status-value strong { color: var(--accent, #059669); }
.att-status-value.v-out { color: var(--text-2, #4b5a70); font-weight: 500; }

/* Live elapsed timer */
.att-elapsed {
    font-family: 'DM Sans', monospace;
    font-variant-numeric: tabular-nums;
    font-size: 1.85rem;
    font-weight: 800;
    letter-spacing: -.02em;
    color: var(--accent, #059669);
    line-height: 1;
    min-width: 6.5ch;
    transition: color .4s;
}
.att-elapsed.el-out { color: var(--text-3, #8494a9); font-size: 1.1rem; font-weight: 500; }

/* ── Right column: action button ────────────────────────────────── */
.att-punch-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .62rem 1.4rem;
    border-radius: 99px;
    font-size: var(--fs-md, .875rem);
    font-weight: 700;
    letter-spacing: .01em;
    border: none;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s, opacity .2s;
    white-space: nowrap;
}
.att-punch-btn:active { transform: scale(.97); }
.att-punch-btn:disabled { opacity: .6; cursor: not-allowed; }
.att-punch-btn.btn-in  {
    background: var(--accent, #059669);
    color: #fff;
    box-shadow: 0 4px 14px rgba(5,150,105,.35);
}
.att-punch-btn.btn-in:hover  { box-shadow: 0 6px 20px rgba(5,150,105,.45); transform: translateY(-1px); }
.att-punch-btn.btn-out {
    background: var(--danger, #dc2626);
    color: #fff;
    box-shadow: 0 4px 14px rgba(220,38,38,.3);
}
.att-punch-btn.btn-out:hover { box-shadow: 0 6px 20px rgba(220,38,38,.4); transform: translateY(-1px); }

/* ── Footer strip ────────────────────────────────────────────────── */
.att-clock-footer {
    padding: .6rem 1.5rem .75rem;
    background: var(--bg-2, #eef2f7);
    border-top: 1px solid var(--border, #e6ebf2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
    font-size: var(--fs-xs, .75rem);
    color: var(--text-3, #8494a9);
}
.att-clock-footer .att-meta { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.att-clock-footer .att-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    background: var(--card); border: 1px solid var(--border);
    border-radius: 99px; padding: .15rem .65rem;
    font-size: var(--fs-xs); font-weight: 600; color: var(--text-2);
}
.att-clock-footer .att-chip.chip-warn { background: rgba(217,119,6,.08); border-color: rgba(217,119,6,.3); color: var(--warn, #d97706); }
.att-clock-footer .att-chip.chip-ok   { background: rgba(5,150,105,.08); border-color: rgba(5,150,105,.3);  color: var(--accent, #059669); }

/* ── Sessions table ─────────────────────────────────────────────── */
.att-sessions-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--r-md, 12px); overflow: hidden; margin-bottom: 1rem; }
.att-sessions-hd {
    padding: .8rem 1.25rem;
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    border-bottom: 1px solid var(--border);
}
.att-sessions-hd .title { font-size: var(--fs-sm); font-weight: 700; color: var(--text); }
.att-sessions-hd .sub   { font-size: var(--fs-xs); color: var(--text-3); }
.att-sessions-hd .badge-count {
    background: var(--brand-light, #eef4ff); color: var(--brand); border-radius: 99px;
    font-size: var(--fs-2xs); font-weight: 700; padding: .1rem .5rem;
}
.att-sessions-table { width: 100%; border-collapse: collapse; font-size: var(--fs-sm); }
.att-sessions-table th {
    background: var(--th-bg, #f8fafc); padding: .55rem 1rem;
    text-align: left; font-size: var(--fs-2xs); text-transform: uppercase;
    letter-spacing: .4px; color: var(--text-3); font-weight: 700; border-bottom: 1px solid var(--border);
}
.att-sessions-table td { padding: .6rem 1rem; border-bottom: 1px solid var(--border-2, #f1f4f9); color: var(--text-2); vertical-align: middle; }
.att-sessions-table tr:last-child td { border-bottom: none; }
.att-sessions-table tr:hover td { background: var(--tr-hover, #f4f8ff); }
.att-sessions-table .td-date { font-weight: 700; color: var(--text); }
.att-sessions-table .td-time { font-variant-numeric: tabular-nums; }
.att-sessions-table .td-hours { font-weight: 700; color: var(--accent); font-variant-numeric: tabular-nums; }
.att-sessions-table .td-hours.h-pending { color: var(--warn); font-weight: 600; }

/* Status pills */
.sess-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    border-radius: 99px; padding: .15rem .6rem;
    font-size: var(--fs-2xs); font-weight: 700;
}
.sess-pill-complete     { background: rgba(5,150,105,.1);   color: #059669; }
.sess-pill-open         { background: rgba(37,99,235,.1);   color: #2563eb; }
.sess-pill-needs_review { background: rgba(217,119,6,.1);   color: #d97706; }
.sess-pill-void         { background: rgba(100,116,139,.1); color: #64748b; }

.att-empty-row td { text-align: center; padding: 2rem; color: var(--text-3); }
</style>

{{-- ══ CLOCK CARD ═════════════════════════════════════════════════════════ --}}
<div class="att-clock-card {{ $clockedIn ? 'is-in' : 'is-out' }}" id="attClockCard">
  {{-- Screenreader + test-visible section label --}}
  <h3 class="visually-hidden" aria-label="Attendance clock section">Time in / Time out</h3>
  <div class="att-clock-inner">

    {{-- Left: Orb + status + elapsed timer --}}
    <div class="att-clock-left">
      <div class="att-orb {{ $clockedIn ? 'orb-in' : 'orb-out' }}" id="attOrb">
        <i class="bi bi-{{ $clockedIn ? 'clock-fill' : 'clock' }}"></i>
      </div>

      <div class="att-status-group">
        <div class="att-status-label">{{ $clockedIn ? 'Currently clocked in' : 'Status' }}</div>
        @if($clockedIn)
          <div class="att-status-value">
            Since <strong>{{ $openClockSession->clocked_in_at->format('M d, h:i:s A') }}</strong>
            &middot; {{ now()->format('l, F j, Y') }}
          </div>
          {{-- Live elapsed counter --}}
          <div class="att-elapsed" id="attElapsed" aria-live="polite" aria-label="Elapsed time">00:00:00</div>
        @else
          <div class="att-status-value v-out">Clocked out &middot; {{ now()->format('l, F j, Y') }}</div>
          <div class="att-elapsed el-out">{{ config('app.timezone') }}</div>
        @endif
      </div>
    </div>

    {{-- Right: punch button --}}
    @if($employee)
      <form method="POST" action="{{ route('employee.attendance.punch') }}"
            id="punchForm"
            onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Saving…';">
        @csrf
        <input type="hidden" name="action" value="{{ $clockedIn ? 'out' : 'in' }}">
        <input type="hidden" name="last_session_id" value="{{ $clockSessions->first()?->id ?? 0 }}">
        <button type="submit" class="att-punch-btn {{ $clockedIn ? 'btn-out' : 'btn-in' }}" id="punchBtn">
          <i class="bi bi-{{ $clockedIn ? 'stop-circle-fill' : 'play-circle-fill' }}"></i>
          {{ $clockedIn ? 'Time Out' : 'Time In' }}
        </button>
      </form>
    @else
      <p style="font-size:var(--fs-sm);color:var(--danger);max-width:220px;text-align:right;margin:0;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Ask payroll to link your account to the employee master list.
      </p>
    @endif
  </div>

  {{-- Footer strip --}}
  <div class="att-clock-footer">
    <div class="att-meta">
      <span><i class="bi bi-server" style="margin-right:.25rem;"></i>Times stamped by the server</span>
      <span class="att-chip {{ $pendingCount ? 'chip-warn' : 'chip-ok' }}">
        @if($pendingCount)
          <i class="bi bi-exclamation-circle"></i> {{ $pendingCount }} session{{ $pendingCount !== 1 ? 's' : '' }} pending review
        @else
          <i class="bi bi-check-circle"></i> All sessions resolved
        @endif
      </span>
      <span class="att-chip chip-ok">
        <i class="bi bi-hourglass-split"></i>
        {{ number_format($totalHours, 2) }} h total recorded
      </span>
    </div>
    <span>
      @if($employee?->attendance_payroll_from)
        <i class="bi bi-lightning-fill" style="color:var(--warn);"></i>
        Auto-payroll from {{ \Carbon\Carbon::parse($employee->attendance_payroll_from)->format('M d, Y') }}
      @else
        <i class="bi bi-info-circle"></i> Punches saved — payroll activation pending
      @endif
    </span>
  </div>
</div>

{{-- ══ SESSION HISTORY TABLE ══════════════════════════════════════════════ --}}
<div class="att-sessions-card">
  <div class="att-sessions-hd">
    <div>
      <span class="title"><i class="bi bi-table" style="margin-right:.4rem;color:var(--brand);"></i>Web Attendance Log</span>
      <span class="sub">&nbsp;&middot;&nbsp;latest 60 sessions (server timestamps)</span>
    </div>
    <span class="badge-count">{{ $clockSessions->count() }}</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="att-sessions-table">
      <thead>
        <tr>
          <th>Duty Date</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Duty Hours</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clockSessions as $s)
          <tr>
            <td class="td-date">{{ $s->work_date->format('M d, Y') }}</td>
            <td class="td-time">{{ $s->clocked_in_at->format('h:i:s A') }}</td>
            <td class="td-time">
              @if($s->clocked_out_at)
                {{ $s->clocked_out_at->format('h:i:s A') }}
              @elseif($s->status === 'open')
                <span style="color:var(--brand);font-weight:600;font-size:var(--fs-xs);">
                  <i class="bi bi-record-circle-fill" style="animation:orb-pulse 1.5s infinite;"></i> Live
                </span>
              @else
                <span style="color:var(--text-3);">—</span>
              @endif
            </td>
            <td class="{{ $s->status === 'complete' ? 'td-hours' : 'td-hours h-pending' }}">
              @if($s->status === 'complete')
                {{ number_format($s->worked_seconds / 3600, 2) }} h
              @elseif($s->status === 'open')
                <span id="liveRowHours" style="font-variant-numeric:tabular-nums;">Live</span>
              @else
                —
              @endif
            </td>
            <td>
              @php $pill = strtolower(str_replace(' ', '_', $s->status)); @endphp
              <span class="sess-pill sess-pill-{{ $pill }}">
                @if($s->status === 'open') <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
                @elseif($s->status === 'complete') <i class="bi bi-check-circle-fill" style="font-size:.65rem;"></i>
                @elseif($s->status === 'needs_review') <i class="bi bi-exclamation-circle-fill" style="font-size:.65rem;"></i>
                @else <i class="bi bi-slash-circle" style="font-size:.65rem;"></i>
                @endif
                {{ ucfirst(str_replace('_', ' ', $s->status)) }}
              </span>
              @if($s->review_note)
                <div style="font-size:var(--fs-xs);color:var(--text-3);margin-top:.2rem;">{{ $s->review_note }}</div>
              @endif
            </td>
          </tr>
        @empty
          <tr class="att-empty-row">
            <td colspan="5">
              <i class="bi bi-clock" style="font-size:1.8rem;display:block;margin-bottom:.5rem;color:var(--border);"></i>
              No web punches yet. Use <strong>Time In</strong> to start recording your duty hours.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ══ CLIENT-SIDE ELAPSED TIMER ══════════════════════════════════════════ --}}
@if($clockedIn)
<script>
(function() {
    const epochMs  = {{ $clockInEpoch }} * 1000;
    const elEl     = document.getElementById('attElapsed');
    const rowEl    = document.getElementById('liveRowHours');

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        const diff = Math.max(0, Math.floor((Date.now() - epochMs) / 1000));
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;
        const hms = pad(h) + ':' + pad(m) + ':' + pad(s);
        if (elEl)  elEl.textContent = hms;
        if (rowEl) rowEl.textContent = (diff / 3600).toFixed(2) + ' h';
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
