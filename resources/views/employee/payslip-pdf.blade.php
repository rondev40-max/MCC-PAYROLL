<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- DomPDF: no external CSS, no flexbox, no grid. Table-based layout only. --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
        }

        .page {
            padding: 32px 36px;
        }

        /* ── Header ── */
        .header-table {
            width: 100%;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header-table td { vertical-align: middle; }
        .company-name {
            font-size: 20px;
            font-weight: 700;
            color: #1d4ed8;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        .payslip-badge {
            text-align: right;
        }
        .payslip-badge .badge-text {
            display: inline-block;
            background: #1d4ed8;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            padding: 5px 16px;
            border-radius: 20px;
            letter-spacing: 1px;
        }
        .payslip-badge .period-text {
            display: block;
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* ── Employee Info Box ── */
        .info-box {
            background: #f0f6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 18px;
        }
        .info-box table { width: 100%; }
        .info-box td { vertical-align: top; padding: 4px 0; }
        .info-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .info-value {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            margin-top: 2px;
        }

        /* ── Section Title ── */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-left: 3px solid #1d4ed8;
            padding-left: 8px;
            margin-bottom: 8px;
        }

        /* ── Tables ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 12px;
        }
        .data-table th {
            background: #1d4ed8;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .data-table th.text-right,
        .data-table td.text-right { text-align: right; }

        .data-table tbody tr:nth-child(even) { background: #f8fafc; }
        .data-table tbody tr:nth-child(odd)  { background: #ffffff; }

        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        /* Total row */
        .data-table tr.total-row td {
            background: #1e3a8a;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            border: none;
        }

        /* ── Two-column layout ── */
        .two-col-table { width: 100%; margin-bottom: 18px; }
        .two-col-table td { vertical-align: top; width: 50%; }
        .two-col-table td:first-child { padding-right: 10px; }
        .two-col-table td:last-child  { padding-left: 10px; }

        /* ── Notes box ── */
        .notes-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }
        .notes-box .notes-label {
            font-size: 10px;
            font-weight: 700;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .notes-box .notes-text {
            font-size: 12px;
            color: #78350f;
        }

        /* ── Net Pay highlight ── */
        .net-pay-box {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 18px;
            text-align: center;
        }
        .net-pay-box .net-label {
            font-size: 11px;
            color: #bfdbfe;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }
        .net-pay-box .net-amount {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-top: 4px;
            letter-spacing: 1px;
        }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
        .footer strong { color: #475569; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ── --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">MCC Digital Payroll</div>
                <div class="company-sub">Mindanao Capitol College &nbsp;|&nbsp; Official Payslip</div>
            </td>
            <td class="payslip-badge">
                <span class="badge-text">PAYSLIP</span>
                <span class="period-text">
                    Generated: {{ now()->format('F d, Y') }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ── Employee Info ── --}}
    <div class="info-box">
        <table>
            <tr>
                <td style="width:33%">
                    <div class="info-label">Employee Name</div>
                    <div class="info-value">{{ $payslip->name ?? $user->name ?? '—' }}</div>
                </td>
                <td style="width:34%">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $payslip->email ?? $user->email ?? '—' }}</div>
                </td>
                <td style="width:33%">
                    <div class="info-label">Pay Period</div>
                    <div class="info-value">
                        {{ $payslip->pay_period ?? $payslip->period ?? $payslip->sent_at?->format('F Y') ?? '—' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding-top:10px">
                    <div class="info-label">Designation</div>
                    <div class="info-value">{{ $payslip->designation ?? '—' }}</div>
                </td>
                <td style="padding-top:10px">
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ $payslip->department ?? '—' }}</div>
                </td>
                <td style="padding-top:10px">
                    <div class="info-label">Date Issued</div>
                    <div class="info-value">{{ $payslip->sent_at?->format('M d, Y') ?? now()->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Net Pay Highlight ── --}}
    <div class="net-pay-box">
        <div class="net-label">Total Net Pay</div>
        <div class="net-amount">&#8369;{{ number_format($payslip->total_honorarium ?? 0, 2) }}</div>
    </div>

    {{-- ── Earnings ── --}}
    <div class="section-title">Earnings</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic / Honorarium</td>
                <td class="text-right">&#8369;{{ number_format($payslip->total_honorarium ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Total Hours / Days</td>
                <td class="text-right">{{ $payslip->total_hours_or_days ?? $payslip->days ?? 0 }}</td>
            </tr>
            <tr>
                <td>Rate per Hour</td>
                <td class="text-right">&#8369;{{ number_format($payslip->rate ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Notes ── --}}
    @if(!empty($payslip->error) && $payslip->error !== 'No additional notes.')
    <div class="notes-box">
        <div class="notes-label">&#9888; Notes</div>
        <div class="notes-text">{{ $payslip->error }}</div>
    </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="footer">
        <strong>MCC Digital Payroll System</strong> &nbsp;|&nbsp;
        This is a system-generated payslip and does not require a signature. &nbsp;|&nbsp;
        Generated on {{ now()->format('F d, Y \a\t h:i A') }}
    </div>

</div>
</body>
</html>