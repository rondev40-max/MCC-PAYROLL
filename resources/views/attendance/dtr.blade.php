@extends('layouts.attendance')

@section('title', 'Edit Daily Time Record')

@php
    $employee = $dtr['employee'];
    $statusOptions = [
        '' => 'No entry',
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
        'half_day' => 'Half day',
        'leave' => 'Leave',
        'holiday' => 'Holiday',
        'official_business' => 'Official business',
    ];
    $incompleteCount = collect($dtr['rows'])->filter(function ($row) {
        $count = collect(['am_in', 'am_out', 'pm_in', 'pm_out'])->filter(fn ($key) => !empty($row[$key]))->count();
        return $count > 0 && $count < 4;
    })->count();
    $workedHours = intdiv((int) $dtr['totals']['worked'], 60);
    $workedMinutes = (int) $dtr['totals']['worked'] % 60;
@endphp

@section('content')
    <section class="page-header no-print">
        <div class="page-header__copy">
            <p class="page-kicker">Civil Service Form No. 48</p>
            <h1 class="page-title">Edit daily time record</h1>
            <p class="page-description">{{ $employee['name'] }} <span aria-hidden="true">|</span> {{ $course }} <span aria-hidden="true">|</span> {{ $dtr['month']->format('F Y') }}</p>
        </div>
        <div class="page-actions">
            <a class="btn btn--secondary" href="{{ route('attendance.dtr.index', ['course' => $course, 'month' => $dtr['month']->format('Y-m')]) }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Monthly records
            </a>
            <a class="btn btn--secondary" href="{{ route('attendance.dtr.print', ['course' => $course, 'employeeId' => $employeeId, 'month' => $dtr['month']->format('Y-m'), 'type' => $employeeType]) }}" target="_blank" rel="noopener">
                <i class="bi bi-printer" aria-hidden="true"></i>
                Print form
            </a>
        </div>
    </section>

    <section class="summary-strip no-print" aria-label="DTR summary">
        <div class="summary-item">
            <span class="summary-item__label">Days with entries</span>
            <strong class="summary-item__value" id="dtr-total-days">{{ $dtr['totals']['days'] }}</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Time rendered</span>
            <strong class="summary-item__value summary-item__value--teal" id="dtr-total-worked">{{ $workedHours }}h {{ $workedMinutes }}m</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Total undertime</span>
            <strong class="summary-item__value summary-item__value--blue" id="dtr-total-undertime">{{ $dtr['totals']['hours'] }}h {{ $dtr['totals']['mins'] }}m</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Incomplete entries</span>
            <strong class="summary-item__value summary-item__value--amber" id="dtr-incomplete">{{ $incompleteCount }}</strong>
        </div>
    </section>

    <form method="GET" action="{{ route('attendance.dtr.show', ['course' => $course, 'employeeId' => $employeeId]) }}" class="form-toolbar no-print" id="dtr-period-form">
        <div class="field">
            <label for="course-display">Assigned department</label>
            <input id="course-display" type="text" value="{{ $course }}" readonly>
        </div>
        <div class="field">
            <label for="month">Record month</label>
            <select id="month" name="month">
                @foreach($monthOptions as $value => $label)
                    <option value="{{ $value }}" @selected($dtr['month']->format('Y-m') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="type" value="{{ $employeeType }}">
        <button type="submit" class="btn btn--primary">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            Apply period
        </button>
    </form>

    @if($errors->any())
        <div class="flash flash--error no-print" role="alert">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <div>
                <strong>The DTR was not saved.</strong>
                <ul class="validation-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('attendance.dtr.save', ['course' => $course, 'employeeId' => $employeeId]) }}" id="dtr-form">
        @csrf
        <input type="hidden" name="month" value="{{ $dtr['month']->format('Y-m') }}">
        <input type="hidden" name="employee_name" value="{{ $employee['name'] }}">
        <input type="hidden" name="employee_type" value="{{ $employee['type'] ?? $employeeType }}">

        <div class="form-sheet-wrap">
            <article class="form-sheet form-sheet--editor csc-form">
                <p class="csc-form__number">Civil Service Form No. 48</p>
                <h2 class="csc-form__title">DAILY TIME RECORD</h2>
                <p class="csc-form__subtitle">For officers and employees</p>

                <div class="csc-form__identity">
                    <div class="csc-form__line">
                        <span class="csc-form__line-label">Name</span>
                        <span class="csc-form__line-value">{{ strtoupper($employee['name']) }}<small class="csc-form__line-hint">(Name)</small></span>
                    </div>
                    <div class="csc-form__line">
                        <span class="csc-form__line-label">For the month of</span>
                        <span class="csc-form__line-value">{{ $dtr['month']->format('F Y') }}</span>
                    </div>
                </div>

                <p class="csc-form__schedule">Official hours for arrival and departure: 8:00 AM-12:00 PM and 1:00 PM-5:00 PM</p>

                <table class="csc-table csc-edit-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="csc-day">Day</th>
                            <th rowspan="2" class="csc-weekday">Weekday</th>
                            <th rowspan="2" class="csc-status">Day classification</th>
                            <th colspan="2">A.M.</th>
                            <th colspan="2">P.M.</th>
                            <th colspan="2">Undertime</th>
                            <th rowspan="2" class="csc-clear"><span class="visually-hidden">Clear</span></th>
                        </tr>
                        <tr>
                            <th class="csc-time">Arrival</th>
                            <th class="csc-time">Departure</th>
                            <th class="csc-time">Arrival</th>
                            <th class="csc-time">Departure</th>
                            <th class="csc-undertime">Hours</th>
                            <th class="csc-undertime">Minutes</th>
                        </tr>
                    </thead>
                    <tbody id="csc-entry-body">
                        @foreach($dtr['rows'] as $row)
                            @php
                                $day = $row['day'];
                                $status = old("days.$day.status", $row['status'] ?? '');
                                $remarks = old("days.$day.remarks", $row['remarks'] ?? '');
                                $special = in_array($status, ['absent', 'leave', 'holiday', 'official_business'], true);
                            @endphp
                            <tr class="{{ $row['is_weekend'] ? 'is-weekend' : '' }} {{ $row['is_future'] ? 'is-future' : '' }}" data-day="{{ $day }}" data-future="{{ $row['is_future'] ? '1' : '0' }}">
                                <td class="csc-day">{{ $day }}</td>
                                <td class="csc-weekday">{{ $row['weekday'] }}</td>
                                <td class="csc-status">
                                    <div class="csc-status-fields">
                                        <select name="days[{{ $day }}][status]" data-field="status" aria-label="Status for {{ $row['date'] }}" @disabled($row['is_future'])>
                                            @foreach($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="days[{{ $day }}][remarks]" data-field="remarks" value="{{ $remarks }}" maxlength="255" placeholder="Note" aria-label="Note for {{ $row['date'] }}" @disabled($row['is_future'])>
                                    </div>
                                </td>
                                @foreach(['am_in', 'am_out', 'pm_in', 'pm_out'] as $field)
                                    <td class="csc-time">
                                        <input type="time" name="days[{{ $day }}][{{ $field }}]" data-field="{{ $field }}" value="{{ old("days.$day.$field", $row[$field]) }}" step="60" aria-label="{{ str_replace('_', ' ', $field) }} for {{ $row['date'] }}" @disabled($row['is_future'] || $special)>
                                    </td>
                                @endforeach
                                <td class="csc-undertime csc-metric" data-metric="hours">{{ $row['ut_hours'] ?: '' }}</td>
                                <td class="csc-undertime csc-metric" data-metric="minutes">{{ $row['ut_minutes'] ?: '' }}</td>
                                <td class="csc-clear">
                                    <button class="csc-row-clear no-print" type="button" title="Clear day" aria-label="Clear day {{ $day }}" @disabled($row['is_future'])>
                                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align:right">TOTAL</td>
                            <td id="csc-total-hours">{{ $dtr['totals']['hours'] }}</td>
                            <td id="csc-total-minutes">{{ $dtr['totals']['mins'] }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <p class="csc-certification">I certify on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</p>
                <div class="csc-signatures">
                    <div class="csc-signature">Employee signature</div>
                    <div class="csc-signature">Verified as to prescribed office hours<strong>In-charge</strong></div>
                </div>
            </article>
        </div>

        <div class="sticky-actions no-print">
            <a class="btn btn--secondary" href="{{ route('attendance.dtr.index', ['course' => $course, 'month' => $dtr['month']->format('Y-m')]) }}">Cancel</a>
            <button class="btn btn--primary" type="submit" id="save-dtr">
                <i class="bi bi-floppy" aria-hidden="true"></i>
                Save DTR
            </button>
        </div>
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dtr-form.css') }}">
    <style>
        .form-sheet--editor { width: 1050px; }
        .validation-list { margin: 4px 0 0; padding-left: 18px; }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/dtr-editor.js') }}" defer></script>
@endpush
