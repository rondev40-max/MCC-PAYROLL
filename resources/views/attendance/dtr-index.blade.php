@extends('layouts.attendance')

@section('title', 'Monthly DTR Records')

@php
    $employeeCount = $employees->count();
    $recordedDays = (int) $employees->sum('days_recorded');
    $recordedHours = (float) $employees->sum('hours');
    $notStarted = $employees->filter(fn ($employee) => (int) $employee->days_recorded === 0)->count();
@endphp

@section('content')
    <section class="page-header">
        <div class="page-header__copy">
            <p class="page-kicker">Civil Service Form No. 48</p>
            <h1 class="page-title">Daily Time Records</h1>
            <p class="page-description">Open, review, and print the official monthly attendance form for personnel in your assigned department.</p>
        </div>
        <div class="page-actions">
            <a class="btn btn--secondary" href="{{ route('attendance.dashboard') }}">
                <i class="bi bi-table" aria-hidden="true"></i>
                Attendance register
            </a>
        </div>
    </section>

    <section class="summary-strip" aria-label="Monthly record summary">
        <div class="summary-item">
            <span class="summary-item__label">Personnel listed</span>
            <strong class="summary-item__value">{{ number_format($employeeCount) }}</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Days recorded</span>
            <strong class="summary-item__value summary-item__value--teal">{{ number_format($recordedDays) }}</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Hours rendered</span>
            <strong class="summary-item__value summary-item__value--blue">{{ number_format($recordedHours, 2) }}</strong>
        </div>
        <div class="summary-item">
            <span class="summary-item__label">Not started</span>
            <strong class="summary-item__value summary-item__value--amber">{{ number_format($notStarted) }}</strong>
        </div>
    </section>

    <form method="GET" action="{{ route('attendance.dtr.index') }}" class="form-toolbar no-print" id="period-filter">
        <div class="field">
            <label for="course-display">Assigned department</label>
            <input id="course-display" type="text" value="{{ $course }}" readonly>
            <input type="hidden" name="course" value="{{ $course }}">
        </div>
        <div class="field">
            <label for="month">Record month</label>
            <select id="month" name="month">
                @foreach($monthOptions as $value => $label)
                    <option value="{{ $value }}" @selected($month->format('Y-m') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn--primary">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            Apply period
        </button>
        <label class="search-field dtr-roster-search" for="roster-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Search personnel</span>
            <input id="roster-search" type="search" placeholder="Search personnel" autocomplete="off">
        </label>
    </form>

    <section class="panel" aria-labelledby="roster-title">
        <header class="panel-header">
            <div>
                <h2 class="panel-title" id="roster-title">{{ $course }} monthly register</h2>
                <p class="panel-subtitle">{{ $month->format('F Y') }} <span aria-hidden="true">|</span> <span id="visible-record-count">{{ $employeeCount }}</span> personnel</p>
            </div>
        </header>

        @if($employees->isEmpty())
            <div class="roster-empty">
                <i class="bi bi-calendar2-x" aria-hidden="true"></i>
                <h2>No monthly records found</h2>
                <p>Attendance has not been entered for {{ $course }} in {{ $month->format('F Y') }}. Use the attendance register to begin the period.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="data-table dtr-roster-table">
                    <thead>
                        <tr>
                            <th class="cell-employee">Personnel</th>
                            <th class="cell-type">Employment</th>
                            <th class="cell-number">Days</th>
                            <th class="cell-number">Hours</th>
                            <th class="cell-status">Record status</th>
                            <th class="cell-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="roster-body">
                        @foreach($employees as $employee)
                            @php
                                $params = [
                                    'course' => $course,
                                    'employeeId' => $employee->employee_id,
                                    'month' => $month->format('Y-m'),
                                    'type' => $employee->employee_type,
                                ];
                                $search = strtolower(implode(' ', [
                                    $employee->employee_name,
                                    $employee->employee_type,
                                    $employee->employee_id,
                                ]));
                                $hasRecords = (int) $employee->days_recorded > 0;
                            @endphp
                            <tr data-roster-search="{{ $search }}">
                                <td class="cell-employee">
                                    <div class="employee-name">{{ $employee->employee_name ?: 'Employee #'.$employee->employee_id }}</div>
                                    <div class="employee-meta">Employee ID {{ $employee->employee_id }}</div>
                                </td>
                                <td class="cell-type"><span class="type-label">{{ $employee->employee_type ?: 'Employee' }}</span></td>
                                <td class="cell-number">{{ number_format((int) $employee->days_recorded) }}</td>
                                <td class="cell-number">{{ number_format((float) $employee->hours, 2) }}</td>
                                <td class="cell-status">
                                    <span class="status-badge status-badge--{{ $hasRecords ? 'ready' : 'empty' }}">
                                        {{ $hasRecords ? 'Recorded' : 'Not started' }}
                                    </span>
                                </td>
                                <td class="cell-actions">
                                    <div class="row-actions">
                                        <a class="row-action row-action--edit" href="{{ route('attendance.dtr.show', $params) }}" title="Open DTR" aria-label="Open DTR for {{ $employee->employee_name }}">
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        </a>
                                        <a class="row-action" href="{{ route('attendance.dtr.print', $params) }}" target="_blank" rel="noopener" title="Print DTR" aria-label="Print DTR for {{ $employee->employee_name }}">
                                            <i class="bi bi-printer" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="roster-search-empty" id="roster-search-empty" hidden>No personnel match your search.</div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('roster-search');
            const body = document.getElementById('roster-body');
            const count = document.getElementById('visible-record-count');
            const empty = document.getElementById('roster-search-empty');
            const month = document.getElementById('month');

            if (month) month.addEventListener('change', function () { document.getElementById('period-filter').submit(); });
            if (!search || !body) return;

            search.addEventListener('input', function () {
                const query = search.value.trim().toLowerCase();
                let visible = 0;
                body.querySelectorAll('tr').forEach(function (row) {
                    const matches = !query || row.dataset.rosterSearch.includes(query);
                    row.hidden = !matches;
                    if (matches) visible += 1;
                });
                count.textContent = String(visible);
                empty.hidden = visible !== 0;
            });
        });
    </script>
@endpush
