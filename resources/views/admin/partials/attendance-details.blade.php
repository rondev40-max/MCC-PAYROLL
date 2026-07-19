@php
    use Carbon\Carbon;
@endphp

<div class="employee-info mb-4">
    <h6 class="border-bottom pb-2 mb-3">Employee Information</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="row g-2">
                <div class="col-12">
                    <strong>Name:</strong> {{ $employee->employee_name }}
                </div>
                <div class="col-12">
                    <strong>Email:</strong> {{ $employee->email }}
                </div>
                <div class="col-12">
                    <strong>Type:</strong> 
                    <span class="badge {{ 
                        $employee->employee_type === 'Part-time' ? 'bg-primary' : 
                        ($employee->employee_type === 'Staff' ? 'bg-success' : 'bg-warning')
                    }}">{{ $employee->employee_type }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row g-2">
                <div class="col-12">
                    <strong>Department:</strong> {{ $employee->department ?: '—' }}
                </div>
                <div class="col-12">
                    <strong>Designation:</strong> {{ $employee->designation ?: '—' }}
                </div>
                @if($timesheet)
                    <div class="col-12">
                        <strong>Rate:</strong> 
                        @if($employee->employee_type === 'Part-time')
                            ₱{{ number_format($timesheet->rate_per_hour, 2) }}/hour
                        @else
                            ₱{{ number_format($timesheet->rate_per_day ?? 0, 2) }}/day
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="attendance-stats mb-4">
    <h6 class="border-bottom pb-2 mb-3">Attendance Summary</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-primary">{{ $stats['presentDays'] }}/{{ $stats['totalDays'] }}</div>
                    <small class="text-muted">Present Days (This Period)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-success">{{ $stats['daysWorked'] }}</div>
                    <small class="text-muted">Total Days Worked</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    @php
                        $attendanceRate = $stats['totalDays'] > 0 
                            ? ($stats['presentDays'] * 100 / $stats['totalDays']) 
                            : 0;
                    @endphp
                    <div class="display-6 {{ 
                        $attendanceRate >= 80 ? 'text-success' : 
                        ($attendanceRate >= 60 ? 'text-warning' : 'text-danger') 
                    }}">{{ number_format($attendanceRate, 1) }}%</div>
                    <small class="text-muted">Attendance Rate</small>
                </div>
            </div>
        </div>
    </div>
</div>

@if($timesheet && !empty($timesheet->days))
    <div class="schedule mb-4">
        <h6 class="border-bottom pb-2 mb-3">Weekly Schedule</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                            <th class="text-center">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            @php
                                $hours = is_array($timesheet->days) 
                                    ? ($timesheet->days[$day] ?? 0) 
                                    : (json_decode($timesheet->days, true)[$day] ?? 0);
                            @endphp
                            <td class="text-center {{ $hours > 0 ? 'table-success' : 'table-light' }}">
                                @if($hours > 0)
                                    @if($employee->employee_type === 'Part-time')
                                        {{ $hours }}hr{{ $hours > 1 ? 's' : '' }}
                                    @else
                                        <i class="bi bi-check-lg text-success"></i>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="attendance-details">
    <h6 class="border-bottom pb-2 mb-3">Daily Records ({{ $startDate->format('F Y') }})</h6>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Status</th>
                    @if($employee->employee_type === 'Part-time')
                        <th>Hours</th>
                    @endif
                    @if($timesheet && isset($timesheet->remarks))
                        <th>Remarks</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                    <tr>
                        <td>{{ $record->attendance_date->format('M d, Y') }}</td>
                        <td>{{ $record->attendance_date->format('l') }}</td>
                        <td>
                            @if($record->is_present)
                                <span class="badge bg-success">Present</span>
                            @else
                                <span class="badge bg-danger">Absent</span>
                            @endif
                        </td>
                        @if($employee->employee_type === 'Part-time')
                            <td>{{ $record->hours_worked > 0 ? number_format($record->hours_worked, 1) : '—' }}</td>
                        @endif
                        @if($timesheet && isset($timesheet->remarks))
                            <td>{{ $record->remarks ?: '—' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>