@extends('layouts.attendance')

@section('title', 'Attendance Register')

@php
    // $course is resolved by AttendanceController::dashboard() from the
    // signed-in checker's own account. It is deliberately not read from the
    // session with a 'BSIT' fallback any more: an account with no assigned
    // department used to render the BSIT register and point the API at BSIT,
    // which then refused it.
    $course = strtoupper(trim((string) $course));
    $departmentNames = [
        'BSIT' => 'Bachelor of Science in Information Technology',
        'BSBA' => 'Bachelor of Science in Business Administration',
        'BSHM' => 'Bachelor of Science in Hospitality Management',
        'BSED' => 'Bachelor of Secondary Education',
        'BEED' => 'Bachelor of Elementary Education',
        'EDUCATION' => 'Education Department',
    ];
    $departmentName = $course === ''
        ? 'No department assigned'
        : ($departmentNames[$course] ?? $course . ' Department');
@endphp

@section('content')
    <section class="page-header">
        <div class="page-header__copy">
            <p class="page-kicker">{{ $course !== '' ? $course . ' personnel office' : 'Personnel office' }}</p>
            <h1 class="page-title">Attendance register</h1>
            <p class="page-description">Review the assigned department's half-month register and maintain the four daily time entries used for Civil Service Form No. 48.</p>
        </div>
        <div class="page-actions">
            <a class="btn btn--secondary" href="{{ route('attendance.dtr.index') }}">
                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                Monthly DTR records
            </a>
        </div>
    </section>

    <section class="summary-strip" aria-label="Cutoff summary">
        <div class="summary-item">
            <span class="summary-item__label">Personnel in register</span>
            <strong class="summary-item__value" id="summary-personnel">0</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Daily records entered</span>
            <strong class="summary-item__value summary-item__value--teal" id="summary-records">0</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Hours rendered</span>
            <strong class="summary-item__value summary-item__value--blue" id="summary-hours">0.00</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Entries for review</span>
            <strong class="summary-item__value summary-item__value--amber" id="summary-review">0</strong>
        </div>
    </section>

    <section class="panel" aria-labelledby="register-title">
        <header class="panel-header">
            <div>
                <h2 class="panel-title" id="register-title">{{ $departmentName }}</h2>
                <p class="panel-subtitle">Civil Service attendance cutoff</p>
            </div>
            <div class="period-control" aria-label="Cutoff navigation">
                <button class="icon-button" type="button" id="previous-cutoff" title="Previous cutoff" aria-label="Previous cutoff">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
                <strong class="period-label" id="cutoff-label">Loading period...</strong>
                <button class="icon-button" type="button" id="next-cutoff" title="Next cutoff" aria-label="Next cutoff">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div class="register-toolbar no-print">
            <div class="toolbar-group">
                <button class="btn btn--secondary" type="button" id="current-cutoff">
                    <i class="bi bi-calendar2-event" aria-hidden="true"></i>
                    Current cutoff
                </button>
                <button class="btn btn--secondary" type="button" id="export-attendance">
                    <i class="bi bi-download" aria-hidden="true"></i>
                    Export CSV
                </button>
            </div>
            <label class="search-field" for="employee-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span class="visually-hidden">Search personnel</span>
                <input id="employee-search" type="search" placeholder="Search personnel" autocomplete="off">
            </label>
        </div>

        <div class="policy-bar">
            <i class="bi bi-clock-history" aria-hidden="true"></i>
            <span><strong>Prescribed schedule:</strong> 8:00 AM-12:00 PM and 1:00 PM-5:00 PM. Worked time excludes the noon break.</span>
        </div>

        <div id="register-loading" class="loading-state" role="status">
            <div class="state-content">
                <div class="spinner" aria-hidden="true"></div>
                <p class="state-title">Loading attendance register</p>
                <p class="state-copy">Retrieving personnel and saved time entries.</p>
            </div>
        </div>

        <div id="register-error" class="error-state" role="alert" aria-live="assertive" hidden>
            <div class="state-content">
                <i class="bi bi-exclamation-triangle state-icon" aria-hidden="true"></i>
                <p class="state-title">The register could not be loaded</p>
                <p class="state-copy" id="register-error-message"></p>
                <button class="btn btn--secondary" type="button" id="retry-register">
                    <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                    Retry
                </button>
            </div>
        </div>

        <div id="register-empty" class="empty-state" hidden>
            <div class="state-content">
                <i class="bi bi-person-x state-icon" aria-hidden="true"></i>
                <p class="state-title">No personnel found</p>
                <p class="state-copy">There are no matching timesheet records for this department.</p>
            </div>
        </div>

        <div class="table-scroll" id="register-table-wrap" hidden>
            <table class="data-table" id="register-table">
                <thead>
                    <tr>
                        <th class="cell-check"><input type="checkbox" id="select-all" aria-label="Select all personnel"></th>
                        <th class="cell-employee">Personnel</th>
                        <th class="cell-type">Employment</th>
                        <th class="cell-number">Days</th>
                        <th class="cell-number">Hours</th>
                        <th class="cell-number">Late</th>
                        <th class="cell-number">Undertime</th>
                        <th class="cell-status">Record status</th>
                        <th class="cell-actions"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="register-body"></tbody>
            </table>
        </div>

        <div class="bulk-bar no-print" id="bulk-actions" hidden>
            <span class="bulk-count"><span id="selected-count">0</span> selected</span>
            <div class="toolbar-group">
                <button class="btn btn--quiet btn--small" type="button" id="clear-selection">Clear selection</button>
                <button class="btn btn--danger btn--small" type="button" id="delete-selected">
                    <i class="bi bi-trash3" aria-hidden="true"></i>
                    Delete this cutoff
                </button>
            </div>
        </div>
    </section>

    <dialog class="dtr-dialog" id="dtr-dialog" aria-labelledby="dialog-title">
        <div class="dialog-layout">
            <header class="dialog-header">
                <div>
                    <p class="dialog-kicker">Civil Service Form No. 48</p>
                    <h2 class="dialog-title" id="dialog-title">Daily time entries</h2>
                    <p class="dialog-meta"><span id="dialog-employee"></span> <span aria-hidden="true">|</span> <span id="dialog-period"></span></p>
                </div>
                <button class="icon-button" type="button" id="close-dialog" title="Close" aria-label="Close daily time entries">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>

            <div class="dialog-body">
                <table class="entry-table">
                    <thead>
                        <tr>
                            <th style="width:130px;text-align:left">Date</th>
                            <th style="width:135px">Status</th>
                            <th style="width:180px;text-align:left">Remarks</th>
                            <th>AM arrival</th>
                            <th>AM departure</th>
                            <th>PM arrival</th>
                            <th>PM departure</th>
                            <th style="width:74px">Worked</th>
                            <th style="width:82px">Undertime</th>
                            <th style="width:42px"><span class="visually-hidden">Clear</span></th>
                        </tr>
                    </thead>
                    <tbody id="entry-body"></tbody>
                </table>
            </div>

            <footer class="dialog-footer">
                <div class="dialog-footer__metrics" id="dialog-metrics">0 days | 0.00 hours</div>
                <div class="dialog-footer__actions">
                    <button class="btn btn--secondary" type="button" id="cancel-dialog">Cancel</button>
                    <button class="btn btn--primary" type="button" id="save-entries">
                        <i class="bi bi-floppy" aria-hidden="true"></i>
                        Save entries
                    </button>
                </div>
            </footer>
        </div>
    </dialog>

    <div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="true"></div>
@endsection

@php
    // Built here rather than inline in @json(...).
    //
    // Blade finds the end of a directive's argument by counting parentheses, so
    // a multi-line array literal containing nested url()/route() calls gets cut
    // short at the first point where the counts happen to balance. That emitted
    // a truncated json_encode( ... ) with an unclosed '[', and the dashboard
    // died with a ParseError before rendering a single byte.
    //
    // Passing one variable gives the directive nothing it can miscount.
    $attendancePortalConfig = [
        'course' => $course,
        'routes' => [
            'attendanceData' => url('/attendance/api/attendance-data'),
            'saveAttendance' => url('/attendance/api/save-attendance'),
            'saveHistory'    => url('/attendance/api/save-attendance-history'),
            'bulkDelete'     => url('/attendance/api/bulk-delete-attendance'),
            'login'          => route('attendance.attendlog.form'),
            'dtrBase'        => url('/attendance/dtr'),
        ],
    ];
@endphp

@push('scripts')
    <script>
        window.attendancePortal = @json($attendancePortalConfig);
    </script>
    <script src="{{ \App\Support\Asset::versioned('js/attendance-dashboard.js') }}" defer></script>
@endpush
