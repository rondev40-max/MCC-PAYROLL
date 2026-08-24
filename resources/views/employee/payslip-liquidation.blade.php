{{--
    Wage liquidation — the full accounting of one pay period, for the employee.

    Everything here comes from the breakdown stored on the payslip when it was
    issued (see App\Support\WageLiquidation). Nothing is recalculated at view
    time: timesheets stay editable after a payroll run, and an employee opening
    a payslip months later must see the figures they were actually paid, not
    what the same timesheet would produce today.

    When a payslip carries no breakdown — it predates the columns — this page
    says so rather than printing ₱0.00 next to every deduction, which is what
    the old payslip PDF did.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wage Liquidation — {{ $payslip->pay_period ?? 'Payslip' }}</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --brand: #2563eb; --brand-dark: #1d4ed8; --brand-light: #eef4ff;
      --accent: #059669; --danger: #dc2626; --warn: #d97706;
      --bg: #f6f8fb; --card: #ffffff;
      --text: #0f1729; --text-2: #4b5a70; --text-3: #8494a9;
      --border: #e6ebf2; --border-2: #f1f4f9;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'DM Sans', system-ui, sans-serif;
      background: var(--bg); color: var(--text);
      font-size: 14px; line-height: 1.55;
      -webkit-font-smoothing: antialiased;
    }
    .wrap { max-width: 860px; margin: 0 auto; padding: 28px 20px 64px; }

    /* ── Back / actions ── */
    .topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .btn {
      display: inline-flex; align-items: center; gap: 7px;
      font: inherit; font-size: .8rem; font-weight: 600;
      padding: 8px 15px; border-radius: 9px; border: 1px solid var(--border);
      background: var(--card); color: var(--text-2);
      text-decoration: none; cursor: pointer; transition: .15s;
    }
    .btn:hover { background: var(--brand-light); border-color: #c9dbfb; color: var(--brand-dark); }
    .btn--primary { background: var(--brand); border-color: var(--brand); color: #fff; }
    .btn--primary:hover { background: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
    .btn-group { display: flex; gap: 8px; flex-wrap: wrap; }

    /* ── Document ── */
    .doc { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
    .doc-head { background: #101a2d; color: #fff; padding: 22px 26px; }
    .doc-eyebrow { font-size: .58rem; font-weight: 800; letter-spacing: .9px; text-transform: uppercase; color: rgba(255,255,255,.42); }
    .doc-title { font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.35rem; letter-spacing: -.02em; margin-top: 2px; }
    .doc-sub { font-size: .74rem; color: rgba(255,255,255,.55); margin-top: 5px; }
    .doc-head-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
    .doc-period { text-align: right; }
    .doc-period-label { font-size: .58rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: rgba(255,255,255,.42); }
    .doc-period-val { font-family: 'Sora', sans-serif; font-weight: 700; font-size: .95rem; margin-top: 2px; }

    /* ── Identity ── */
    .ident { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px 22px; padding: 18px 26px; border-bottom: 1px solid var(--border); background: #fbfcfe; }
    .ident-label { font-size: .6rem; font-weight: 800; letter-spacing: .55px; text-transform: uppercase; color: var(--text-3); }
    .ident-val { font-size: .87rem; font-weight: 600; margin-top: 2px; word-break: break-word; }

    /* ── Section ── */
    .sec { padding: 22px 26px; border-bottom: 1px solid var(--border); }
    .sec-title { font-family: 'Sora', sans-serif; font-size: .72rem; font-weight: 800; letter-spacing: .7px; text-transform: uppercase; color: var(--brand-dark); display: flex; align-items: center; gap: 8px; margin-bottom: 14px; }
    .sec-title i { font-size: .9rem; }

    /* ── Line items ── */
    .line { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--border-2); }
    .line:last-of-type { border-bottom: 0; }
    .line-label { font-size: .84rem; font-weight: 600; }
    .line-note { font-size: .71rem; color: var(--text-3); margin-top: 1px; max-width: 46ch; }
    .line-amt { font-family: 'Sora', sans-serif; font-size: .88rem; font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .line-amt--minus { color: var(--danger); }
    .line-amt--zero { color: var(--text-3); font-weight: 600; }

    .subtotal { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 12px; padding-top: 12px; border-top: 2px solid var(--border); }
    .subtotal-label { font-family: 'Sora', sans-serif; font-size: .8rem; font-weight: 800; }
    .subtotal-amt { font-family: 'Sora', sans-serif; font-size: 1rem; font-weight: 800; font-variant-numeric: tabular-nums; }

    /* ── The arithmetic, shown rather than asserted ── */
    .maths { display: flex; align-items: stretch; gap: 0; flex-wrap: wrap; padding: 20px 26px; background: #fbfcfe; border-bottom: 1px solid var(--border); }
    .maths-cell { flex: 1 1 120px; padding: 4px 14px; }
    .maths-op { display: grid; place-items: center; font-family: 'Sora', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--text-3); padding: 0 2px; }
    .maths-label { font-size: .6rem; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--text-3); }
    .maths-val { font-family: 'Sora', sans-serif; font-size: 1.02rem; font-weight: 800; margin-top: 3px; font-variant-numeric: tabular-nums; }

    /* ── Net pay ── */
    .net { padding: 24px 26px; background: linear-gradient(135deg, #065f46, #059669); color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap; }
    .net-label { font-size: .62rem; font-weight: 800; letter-spacing: .9px; text-transform: uppercase; color: rgba(255,255,255,.7); }
    .net-caption { font-size: .74rem; color: rgba(255,255,255,.75); margin-top: 4px; max-width: 40ch; }
    .net-amt { font-family: 'Sora', sans-serif; font-size: 2rem; font-weight: 800; letter-spacing: -.02em; font-variant-numeric: tabular-nums; }

    /* ── Notice ── */
    .notice { display: flex; gap: 11px; padding: 15px 18px; border-radius: 11px; font-size: .8rem; line-height: 1.6; }
    .notice i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
    .notice--warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .notice--info { background: var(--brand-light); border: 1px solid #c9dbfb; color: #1e3a8a; }

    .foot { padding: 16px 26px; font-size: .71rem; color: var(--text-3); text-align: center; }

    /* ── Print ── */
    @media print {
      body { background: #fff; }
      .wrap { padding: 0; max-width: none; }
      .topbar { display: none; }
      .doc { border: 0; border-radius: 0; }
      .doc-head { background: #101a2d !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .net { background: #059669 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    @media (max-width: 560px) {
      .net-amt { font-size: 1.6rem; }
      .doc-period { text-align: left; }
    }
  </style>
</head>
<body>
<div class="wrap">

  <div class="topbar">
    <a class="btn" href="{{ route('employee.dashboard', ['tab' => 'payslips']) }}">
      <i class="bi bi-arrow-left"></i> Back to payslips
    </a>
    <div class="btn-group">
      <button class="btn" type="button" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
      </button>
      <a class="btn btn--primary" href="{{ route('employee.payslip.download', $payslip->id) }}">
        <i class="bi bi-download"></i> Download payslip
      </a>
    </div>
  </div>

  <div class="doc">

    {{-- ── Header ── --}}
    <div class="doc-head">
      <div class="doc-head-row">
        <div>
          <div class="doc-eyebrow">Wage liquidation</div>
          <div class="doc-title">How your pay was computed</div>
          <div class="doc-sub">MCC Digital Payroll · Employee copy</div>
        </div>
        <div class="doc-period">
          <div class="doc-period-label">Pay period</div>
          <div class="doc-period-val">{{ $payslip->pay_period ?? $payslip->sent_at?->format('F Y') ?? '—' }}</div>
          <div class="doc-sub">Issued {{ $payslip->sent_at?->format('M d, Y') ?? '—' }}</div>
        </div>
      </div>
    </div>

    {{-- ── Who this is for ── --}}
    <div class="ident">
      <div>
        <div class="ident-label">Employee</div>
        <div class="ident-val">{{ $payslip->name ?? $user->name ?? '—' }}</div>
      </div>
      <div>
        <div class="ident-label">Designation</div>
        <div class="ident-val">{{ $payslip->designation ?: '—' }}</div>
      </div>
      <div>
        <div class="ident-label">Employment</div>
        <div class="ident-val">{{ $payslip->employee_type ?: '—' }}</div>
      </div>
      <div>
        <div class="ident-label">Email</div>
        <div class="ident-val">{{ $payslip->email ?? $user->email ?? '—' }}</div>
      </div>
    </div>

    @if($liquidation === null)
      {{-- No breakdown was recorded for this payslip. Say so plainly. Printing
           ₱0.00 against every deduction — which is what the payslip PDF used to
           do — tells the employee something false about their pay. --}}
      <div class="sec">
        <div class="notice notice--warn">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div>
            <strong>This payslip is not itemised.</strong><br>
            It was issued before the payroll system began recording a line-by-line
            breakdown, so only the total below was kept. Payslips issued from now
            on carry the full liquidation. For a breakdown of this period, please
            ask the payroll office.
          </div>
        </div>
      </div>

      <div class="net">
        <div>
          <div class="net-label">Total honorarium</div>
          <div class="net-caption">The only figure recorded for this pay period.</div>
        </div>
        <div class="net-amt">₱{{ number_format($payslip->total_honorarium ?? 0, 2) }}</div>
      </div>
    @else

      {{-- ── Earnings ── --}}
      <div class="sec">
        <div class="sec-title"><i class="bi bi-cash-coin"></i> Earnings</div>

        <div class="line">
          <div>
            <div class="line-label">Time rendered</div>
            <div class="line-note">Hours and days as approved on your timesheet for this period.</div>
          </div>
          <div class="line-amt">{{ \App\Support\WageLiquidation::unitLabel($liquidation['rate_unit'], $liquidation['units']) }}</div>
        </div>

        <div class="line">
          <div>
            <div class="line-label">Rate</div>
            <div class="line-note">Your approved rate per {{ $liquidation['rate_unit'] ?? 'unit' }}.</div>
          </div>
          <div class="line-amt">₱{{ number_format($liquidation['rate'], 2) }}</div>
        </div>

        <div class="subtotal">
          <div class="subtotal-label">Gross pay</div>
          <div class="subtotal-amt">₱{{ number_format($liquidation['gross'], 2) }}</div>
        </div>
      </div>

      {{-- ── Deductions ── --}}
      <div class="sec">
        <div class="sec-title"><i class="bi bi-dash-circle"></i> Deductions withheld</div>

        @foreach($liquidation['lines'] as $line)
          <div class="line">
            <div>
              <div class="line-label">{{ $line['label'] }}</div>
              <div class="line-note">{{ $line['note'] }}</div>
            </div>
            <div class="line-amt {{ $line['amount'] > 0 ? 'line-amt--minus' : 'line-amt--zero' }}">
              {{ $line['amount'] > 0 ? '−' : '' }}₱{{ number_format($line['amount'], 2) }}
            </div>
          </div>
        @endforeach

        <div class="line">
          <div>
            <div class="line-label">Other deductions</div>
            <div class="line-note">Adjustments recorded against this period by the payroll office.</div>
          </div>
          <div class="line-amt {{ $liquidation['other_deductions'] > 0 ? 'line-amt--minus' : 'line-amt--zero' }}">
            {{ $liquidation['other_deductions'] > 0 ? '−' : '' }}₱{{ number_format($liquidation['other_deductions'], 2) }}
          </div>
        </div>

        <div class="subtotal">
          <div class="subtotal-label">Total deductions</div>
          <div class="subtotal-amt" style="color:var(--danger);">−₱{{ number_format($liquidation['total_deductions'], 2) }}</div>
        </div>
      </div>

      {{-- ── The arithmetic ── --}}
      <div class="maths">
        <div class="maths-cell">
          <div class="maths-label">Gross pay</div>
          <div class="maths-val">₱{{ number_format($liquidation['gross'], 2) }}</div>
        </div>
        <div class="maths-op">−</div>
        <div class="maths-cell">
          <div class="maths-label">Deductions</div>
          <div class="maths-val" style="color:var(--danger);">₱{{ number_format($liquidation['total_deductions'], 2) }}</div>
        </div>
        <div class="maths-op">=</div>
        <div class="maths-cell">
          <div class="maths-label">Net pay</div>
          <div class="maths-val" style="color:var(--accent);">₱{{ number_format($liquidation['net'], 2) }}</div>
        </div>
      </div>

      {{-- ── Net ── --}}
      <div class="net">
        <div>
          <div class="net-label">Net pay — what you received</div>
          <div class="net-caption">
            @if($liquidation['take_home_rate'] !== null)
              {{ $liquidation['take_home_rate'] }}% of your gross pay for this period.
            @else
              Gross pay less all deductions withheld for this period.
            @endif
          </div>
        </div>
        <div class="net-amt">₱{{ number_format($liquidation['net'], 2) }}</div>
      </div>

      @if(!empty($payslip->error) && $payslip->error !== 'No additional notes.')
        <div class="sec">
          <div class="notice notice--warn">
            <i class="bi bi-info-circle-fill"></i>
            <div><strong>Note from payroll:</strong> {{ $payslip->error }}</div>
          </div>
        </div>
      @endif

      <div class="sec">
        <div class="notice notice--info">
          <i class="bi bi-shield-check"></i>
          <div>
            These figures were recorded when this payslip was issued and do not
            change afterwards. If a line does not match your records, contact the
            payroll office with the pay period above.
          </div>
        </div>
      </div>
    @endif

    <div class="foot">
      System-generated liquidation · No signature required ·
      Viewed {{ now()->format('F d, Y \a\t g:i A') }}
    </div>
  </div>
</div>
</body>
</html>
