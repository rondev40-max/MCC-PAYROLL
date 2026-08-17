<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSC Form No. 48 - {{ $dtr['employee']['name'] }} - {{ $dtr['month']->format('F Y') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; letter-spacing: 0; }
        body { margin: 0; background: #dfe3e5; color: #111; font-family: Arial, sans-serif; }
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            min-height: 58px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #c5ccd1;
            background: #fff;
            box-shadow: 0 2px 8px rgba(24,33,43,.08);
        }
        .print-toolbar__title { font-size: 13px; font-weight: 700; }
        .print-toolbar__actions { display: flex; gap: 8px; }
        .print-button {
            min-height: 36px;
            padding: 7px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid #adb6bd;
            border-radius: 6px;
            background: #fff;
            color: #18212b;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }
        .print-button--primary { border-color: #087466; background: #087466; color: #fff; }
        .print-preview-scroll { overflow-x: auto; }
        .print-page {
            width: 8.5in;
            min-height: 13in;
            margin: 24px auto;
            padding: .34in .32in;
            background: #fff;
            box-shadow: 0 5px 24px rgba(24,33,43,.18);
        }
        .form-pair {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .2in;
            align-items: start;
        }
        .dtr-copy {
            min-width: 0;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5px;
            line-height: 1.15;
        }
        .dtr-copy__number {
            min-height: 14px;
            margin: 0 0 2px;
            display: flex;
            justify-content: space-between;
            gap: 6px;
            font-size: 7px;
        }
        .dtr-copy__title {
            margin: 0;
            font-family: "Times New Roman", serif;
            font-size: 14px;
            line-height: 1;
            text-align: center;
        }
        .dtr-copy__ornament { margin: 2px 0 8px; text-align: center; }
        .dtr-copy__name {
            margin: 0 10px 2px;
            padding: 0 4px 2px;
            border-bottom: 1px solid #000;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }
        .dtr-copy__name-hint { margin: 0 0 7px; font-size: 6.5px; text-align: center; }
        .dtr-copy__month { margin: 0 0 5px; font-size: 7.5px; }
        .dtr-copy__month strong {
            min-width: 150px;
            padding: 0 4px 1px;
            display: inline-block;
            border-bottom: 1px solid #000;
            font-size: 8px;
            text-align: center;
        }
        .dtr-copy__hours {
            margin-bottom: 7px;
            display: grid;
            grid-template-columns: 1.1fr 1.8fr;
            gap: 5px;
            align-items: center;
        }
        .dtr-copy__hours-label { line-height: 1.25; }
        .dtr-copy__hours-values { display: grid; gap: 3px; }
        .dtr-copy__hours-row { display: grid; grid-template-columns: 58px 1fr; align-items: end; gap: 3px; }
        .dtr-copy__hours-line {
            min-height: 12px;
            padding-bottom: 1px;
            border-bottom: 1px solid #000;
            font-size: 7px;
            text-align: center;
        }
        .dtr-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .dtr-grid th, .dtr-grid td {
            height: .205in;
            padding: 1px 2px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
            font-variant-numeric: tabular-nums;
        }
        .dtr-grid th { height: .22in; font-size: 6.5px; font-weight: 700; }
        .dtr-grid .day-column { width: 8%; font-weight: 700; }
        .dtr-grid .time-column { width: 17%; }
        .dtr-grid .ut-column { width: 12%; }
        .dtr-grid .special-entry {
            overflow: hidden;
            font-size: 6.2px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .dtr-grid tfoot td { height: .22in; font-weight: 700; }
        .dtr-copy__notes { min-height: 12px; margin: 4px 2px 0; font-size: 6px; line-height: 1.2; }
        .dtr-copy__certification {
            margin: 8px 5px 0;
            font-family: "Times New Roman", serif;
            font-size: 7.5px;
            font-style: italic;
            line-height: 1.25;
            text-align: justify;
            text-indent: 14px;
        }
        .dtr-copy__signature-line {
            width: 72%;
            margin: 22px auto 2px;
            border-top: 1px solid #000;
            text-align: center;
        }
        .dtr-copy__verified { margin: 12px 4px 0; font-size: 7.5px; }
        .dtr-copy__in-charge {
            width: 72%;
            margin: 24px auto 0;
            padding-top: 2px;
            border-top: 1px solid #000;
            font-size: 7px;
            text-align: center;
        }
        .dtr-copy__instruction { margin: 10px 0 0; font-size: 6px; font-weight: 700; text-align: center; }
        @page { size: 8.5in 13in; margin: .3in; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .print-page { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        }
        @media (max-width: 900px) {
            .print-page { margin: 14px; }
        }
    </style>
</head>
<body>
    @php
        $employee = $dtr['employee'];
        $formatTime = static function ($value) {
            if (!$value) return '';
            try {
                return \Illuminate\Support\Carbon::createFromFormat('H:i', $value)->format('g:i');
            } catch (\Throwable $e) {
                return $value;
            }
        };
        $statusLabels = [
            'absent' => 'Absent',
            'late' => 'Late',
            'half_day' => 'Half day',
            'leave' => 'Leave',
            'holiday' => 'Holiday',
            'official_business' => 'Official business',
        ];
        $presentNotes = collect($dtr['rows'])
            ->filter(fn ($row) => $row['has_entry'] && !empty($row['remarks']))
            ->map(fn ($row) => $row['day'] . ' - ' . \Illuminate\Support\Str::limit($row['remarks'], 72))
            ->values();
    @endphp

    <header class="print-toolbar no-print">
        <div class="print-toolbar__title">{{ $employee['name'] }} <span aria-hidden="true">|</span> {{ $dtr['month']->format('F Y') }}</div>
        <div class="print-toolbar__actions">
            <a class="print-button" href="{{ route('attendance.dtr.show', ['course' => $course, 'employeeId' => $employee['id'], 'month' => $dtr['month']->format('Y-m'), 'type' => $employee['type']]) }}">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                Edit
            </a>
            <button class="print-button print-button--primary" type="button" onclick="window.print()">
                <i class="bi bi-printer" aria-hidden="true"></i>
                Print
            </button>
        </div>
    </header>

    <div class="print-preview-scroll">
        <main class="print-page">
            <div class="form-pair">
                @for($copy = 0; $copy < 2; $copy++)
                    <section class="dtr-copy" aria-label="Daily Time Record copy {{ $copy + 1 }}">
                        <p class="dtr-copy__number">
                            <span>Civil Service Form No. 48</span>
                            <span>Employee No. __________</span>
                        </p>
                        <h1 class="dtr-copy__title">DAILY TIME RECORD</h1>
                        <p class="dtr-copy__ornament">-----o0o-----</p>

                        <p class="dtr-copy__name">{{ $employee['name'] }}</p>
                        <p class="dtr-copy__name-hint">(Name)</p>
                        <p class="dtr-copy__month">For the month of <strong>{{ $dtr['month']->format('F Y') }}</strong></p>

                        <div class="dtr-copy__hours">
                            <div class="dtr-copy__hours-label">Official hours for arrival and departure</div>
                            <div class="dtr-copy__hours-values">
                                <div class="dtr-copy__hours-row">
                                    <span>Regular days</span>
                                    <span class="dtr-copy__hours-line">8:00-12:00; 1:00-5:00</span>
                                </div>
                                <div class="dtr-copy__hours-row">
                                    <span>Saturdays</span>
                                    <span class="dtr-copy__hours-line"></span>
                                </div>
                            </div>
                        </div>

                        <table class="dtr-grid">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="day-column">Day</th>
                                    <th colspan="2">A.M.</th>
                                    <th colspan="2">P.M.</th>
                                    <th colspan="2">Undertime</th>
                                </tr>
                                <tr>
                                    <th class="time-column">Arrival</th>
                                    <th class="time-column">Departure</th>
                                    <th class="time-column">Arrival</th>
                                    <th class="time-column">Departure</th>
                                    <th class="ut-column">Hours</th>
                                    <th class="ut-column">Minutes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dtr['rows'] as $row)
                                    @php
                                        $status = $row['status'] ?? '';
                                        $specialLabel = $statusLabels[$status] ?? null;
                                        if ($specialLabel && !empty($row['remarks'])) {
                                            $specialLabel .= ' - ' . \Illuminate\Support\Str::limit($row['remarks'], 34);
                                        }
                                        if (!$row['has_entry'] && !$specialLabel && $row['is_weekend']) {
                                            $specialLabel = $row['weekday'] === 'Sat'
                                                ? 'Service not required - Sat'
                                                : 'Service not required - Sun';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="day-column">{{ $row['day'] }}</td>
                                        @if($specialLabel && !$row['has_entry'])
                                            <td colspan="4" class="special-entry">{{ $specialLabel }}</td>
                                        @else
                                            <td>{{ $formatTime($row['am_in']) }}</td>
                                            <td>{{ $formatTime($row['am_out']) }}</td>
                                            <td>{{ $formatTime($row['pm_in']) }}</td>
                                            <td>{{ $formatTime($row['pm_out']) }}</td>
                                        @endif
                                        <td>{{ $row['ut_hours'] ?: '' }}</td>
                                        <td>{{ $row['ut_minutes'] ?: '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" style="text-align:right">TOTAL</td>
                                    <td>{{ $dtr['totals']['hours'] }}</td>
                                    <td>{{ $dtr['totals']['mins'] }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        @if($presentNotes->isNotEmpty())
                            <p class="dtr-copy__notes"><strong>Remarks:</strong> {{ $presentNotes->join('; ') }}</p>
                        @endif
                        <p class="dtr-copy__certification">I certify on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</p>
                        <div class="dtr-copy__signature-line">Employee signature</div>
                        <p class="dtr-copy__verified">VERIFIED as to the prescribed office hours:</p>
                        <div class="dtr-copy__in-charge">In Charge</div>
                        <p class="dtr-copy__instruction">(SEE INSTRUCTION ON BACK)</p>
                    </section>
                @endfor
            </div>
        </main>
    </div>
</body>
</html>
