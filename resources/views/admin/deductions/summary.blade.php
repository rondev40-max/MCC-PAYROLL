<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Deduction Summary - MCC Payroll</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --brand: #2563eb; --brand-dark: #1d4ed8; --brand-light: #eff6ff;
      --bg: #f1f5f9; --card: #ffffff; --text: #0f172a; --text-2: #475569; --text-3: #94a3b8;
      --border: #e2e8f0; --accent: #10b981; --danger: #ef4444;
      --r-sm: 10px; --r-md: 14px;
      --shadow-xs: 0 1px 3px rgba(15,23,42,0.06);
      --shadow-sm: 0 2px 8px rgba(15,23,42,0.08);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg); color: var(--text);
      padding: 1.5rem;
    }
    .page-title { font-size: 1.3rem; font-weight: 800; letter-spacing: -.4px; }
    .page-subtitle { font-size: .78rem; color: var(--text-3); margin-top: 2px; }
    .summary-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--r-md); box-shadow: var(--shadow-xs);
      overflow: hidden;
    }
    .summary-header {
      padding: .9rem 1.2rem; border-bottom: 1px solid var(--border);
      font-weight: 700; font-size: .85rem; display: flex; align-items: center; gap: 8px;
    }
    .summary-body { padding: 1rem 1.2rem; }
    .stat-block {
      text-align: center; padding: .7rem .5rem;
      border-radius: var(--r-sm);
    }
    .stat-block .label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text-3); }
    .stat-block .value { font-size: 1.1rem; font-weight: 800; letter-spacing: -.3px; line-height: 1.2; margin-top: 2px; }
    .table { font-size: .78rem; margin-bottom: 0; }
    .table thead th {
      background: var(--brand) !important; color: #fff !important;
      font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
      padding: .5rem .65rem; white-space: nowrap;
    }
    .table tbody td { padding: .45rem .65rem; vertical-align: middle; border-bottom: 1px solid var(--border); }
    .table tbody tr:hover td { background: var(--brand-light); }
    .table tbody tr:nth-child(even) td { background: #f8fafc; }
    .table tbody tr:nth-child(even):hover td { background: var(--brand-light); }
    .amount-cell { font-size: .75rem; font-weight: 600; }
    .total-row td { font-weight: 800; background: #f0f6ff !important; border-top: 2px solid var(--brand) !important; }
    .btn-outline-custom {
      background: transparent; color: var(--text-2); border: 1px solid var(--border);
      border-radius: var(--r-sm); padding: .4rem .9rem;
      font-size: .76rem; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;
      transition: all .18s; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
      text-decoration: none;
    }
    .btn-outline-custom:hover { background: var(--brand-light); color: var(--brand); border-color: var(--brand); }
    @media print {
      .no-print { display: none !important; }
      body { padding: .5rem; background: #fff; }
      .summary-card { box-shadow: none; border: 1px solid #ccc; }
    }
  </style>
</head>
<body>
<div class="container-fluid px-0">
  <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2 no-print">
    <div>
      <div class="page-title"><i class="bi bi-file-earmark-bar-graph me-2" style="color:var(--brand);"></i>Monthly Deduction Summary</div>
      <div class="page-subtitle">{{ $months[$month] ?? '' }} {{ $year }} · Government Deductions Report</div>
    </div>
    <div class="d-flex gap-2">
      <button onclick="window.print()" class="btn-outline-custom"><i class="bi bi-printer"></i> Print</button>
      <a href="{{ route('admin.deductions.index', ['month' => $month, 'year' => $year]) }}" class="btn-outline-custom"><i class="bi bi-arrow-left"></i> Back to Deductions</a>
    </div>
  </div>
  <div class="summary-card mb-3 no-print">
    <div class="summary-body">
      <form method="GET" action="{{ route('admin.deductions.summary') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label" style="font-size:.72rem;font-weight:700;color:var(--text-2);">Month</label>
          <select name="month" class="form-select form-select-sm">
            @foreach($months as $val => $label)
              <option value="{{ $val }}" {{ (int)$month === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label" style="font-size:.72rem;font-weight:700;color:var(--text-2);">Year</label>
          <select name="year" class="form-select form-select-sm">
            @foreach($years as $y)
              <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary btn-sm w-100" style="background:var(--brand);border:none;border-radius:var(--r-sm);font-weight:600;"><i class="bi bi-search"></i> View</button>
        </div>
      </form>
    </div>
  </div>
  <div style="text-align:center;margin-bottom:1.2rem;">
    <div style="font-size:.9rem;font-weight:600;color:var(--text-2);">MADRIDEJOS COMMUNITY COLLEGE</div>
    <div style="font-size:1.1rem;font-weight:800;color:var(--text);">MONTHLY DEDUCTION SUMMARY</div>
    <div style="font-size:.82rem;color:var(--text-3);">{{ $months[$month] ?? '' }} {{ $year }}</div>
  </div>
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
      <div class="summary-card"><div class="summary-body py-2"><div class="stat-block"><div class="label">Employees</div><div class="value" style="color:var(--brand);">{{ $totals['employees'] }}</div></div></div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="summary-card"><div class="summary-body py-2"><div class="stat-block"><div class="label">Gross Pay</div><div class="value" style="color:var(--accent);">₱{{ number_format($totals['gross_pay'], 2) }}</div></div></div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="summary-card"><div class="summary-body py-2"><div class="stat-block"><div class="label">Total Deductions</div><div class="value" style="color:var(--danger);">₱{{ number_format($totals['total_govt_ded'] + $totals['other_deduction'], 2) }}</div></div></div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="summary-card"><div class="summary-body py-2"><div class="stat-block"><div class="label">Total Net Pay</div><div class="value">₱{{ number_format($totals['net_pay'], 2) }}</div></div></div></div>
    </div>
  </div>
  <div class="summary-card">
    <div class="summary-header"><i class="bi bi-table" style="color:var(--brand);"></i>Detailed Deduction Breakdown</div>
    <div class="summary-body p-0">
      <div class="table-responsive">
        <table class="table table-sm">
          <thead><tr><th>#</th><th>Employee</th><th>Designation</th><th class="text-end">Gross Pay</th><th class="text-end">W/Tax</th><th class="text-end">GSIS</th><th class="text-end">PhilHealth</th><th class="text-end">Pag-IBIG</th><th class="text-end">SSS</th><th class="text-end">Other Ded</th><th class="text-end">Total Ded</th><th class="text-end">Net Pay</th></tr></thead>
          <tbody>
            @forelse($summary as $i => $emp)
            <tr>
              <td style="color:var(--text-3);font-size:.68rem;">{{ $i + 1 }}</td>
              <td style="font-weight:700;">{{ $emp['name'] }}</td>
              <td style="font-size:.7rem;color:var(--text-2);">{{ $emp['designation'] ?? '—' }}</td>
              <td class="text-end amount-cell" style="color:var(--accent);">₱{{ number_format($emp['gross_pay'], 2) }}</td>
              <td class="text-end amount-cell" style="color:#dc2626;">₱{{ number_format($emp['withholding_tax'], 2) }}</td>
              <td class="text-end amount-cell" style="color:#2563eb;">₱{{ number_format($emp['gsis'], 2) }}</td>
              <td class="text-end amount-cell" style="color:#059669;">₱{{ number_format($emp['philhealth'], 2) }}</td>
              <td class="text-end amount-cell" style="color:#b45309;">₱{{ number_format($emp['pag_ibig'], 2) }}</td>
              <td class="text-end amount-cell" style="color:#6d28d9;">₱{{ number_format($emp['sss'], 2) }}</td>
              <td class="text-end amount-cell" style="color:var(--text-2);">₱{{ number_format($emp['other_deduction'], 2) }}</td>
              <td class="text-end amount-cell" style="color:var(--danger);font-weight:800;">₱{{ number_format($emp['total_govt_ded'] + $emp['other_deduction'], 2) }}</td>
              <td class="text-end amount-cell" style="font-weight:800;">₱{{ number_format($emp['net_pay'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="12" class="text-center py-4" style="color:var(--text-3);"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;"></i>No records found for this period.</td></tr>
            @endforelse
          </tbody>
          @if($summary->count() > 0)
          <tfoot>
            <tr class="total-row">
              <td colspan="3" class="text-end">TOTALS</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['gross_pay'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['withholding_tax'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['gsis'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['philhealth'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['pag_ibig'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['sss'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['other_deduction'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['total_govt_ded'] + $totals['other_deduction'], 2) }}</td>
              <td class="text-end amount-cell">₱{{ number_format($totals['net_pay'], 2) }}</td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>
  <div class="summary-card mt-3">
    <div class="summary-header"><i class="bi bi-pie-chart-fill" style="color:var(--brand);"></i>Deduction Summary by Type</div>
    <div class="summary-body">
      <div class="row g-2">
        <div class="col-6 col-md-4 col-lg">
          <div class="stat-block" style="background:rgba(220,38,38,.06);">
            <div class="label">Withholding Tax</div>
            <div class="value" style="color:#dc2626;">₱{{ number_format($totals['withholding_tax'], 2) }}</div>
          </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
          <div class="stat-block" style="background:rgba(37,99,235,.06);">
            <div class="label">GSIS</div>
            <div class="value" style="color:#2563eb;">₱{{ number_format($totals['gsis'], 2) }}</div>
          </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
          <div class="stat-block" style="background:rgba(5,150,105,.06);">
            <div class="label">PhilHealth</div>
            <div class="value" style="color:#059669;">₱{{ number_format($totals['philhealth'], 2) }}</div>
          </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
          <div class="stat-block" style="background:rgba(180,83,9,.06);">
            <div class="label">Pag-IBIG</div>
            <div class="value" style="color:#b45309;">₱{{ number_format($totals['pag_ibig'], 2) }}</div>
          </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
          <div class="stat-block" style="background:rgba(109,40,217,.06);">
            <div class="label">SSS</div>
            <div class="value" style="color:#6d28d9;">₱{{ number_format($totals['sss'], 2) }}</div>
          </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
          <div class="stat-block" style="background:rgba(71,85,105,.06);">
            <div class="label">Other Deductions</div>
            <div class="value" style="color:var(--text-2);">₱{{ number_format($totals['other_deduction'], 2) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="text-center mt-3" style="font-size:.7rem;color:var(--text-3);">
    <strong>MCC Digital Payroll</strong> · Generated {{ now()->format('F d, Y \a\t h:i A') }} · This report is system-generated.
  </div>
</div>
<script>
  @if(session('success'))
    Swal.fire({ icon:'success', title:'Success!', text: @json(session('success')), toast:true, position:'top-end', timer:3000, showConfirmButton:false });
  @endif
</script>
</body>
</html>

