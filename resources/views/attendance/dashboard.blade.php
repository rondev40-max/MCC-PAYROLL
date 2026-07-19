<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Checker Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #007bff, #4da6ff, #e3f2fd);
            min-height: 100vh;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #007bff;
            font-size: 1.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-badge {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            color: #007bff;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card p {
            color: #666;
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .course-badge {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .user-info {
                flex-direction: column;
                gap: 0.5rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You will be logged out of your session.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, logout'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
            }
        });
    </script>

    <div class="header">
        <h1><i class="fas fa-clipboard-check"></i> Attendance Checker Dashboard</h1>
        <div class="user-info">
            <div class="user-badge">
                <i class="fas fa-user"></i> {{ session('user_name') }}
                @if(session('user_course'))
                    <span class="course-badge">{{ strtoupper(session('user_course')) }}</span>
                @endif
            </div>
            <a id="logoutBtn" class="logout-btn" href="{{ route('logout') }}">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    <div class="container">
        <div class="card">
            @php
                $userCourse = strtoupper(session('user_course', 'BSIT'));
                $courseInfo = [
                    'BSIT' => [
                        'name' => 'BSIT Department',
                        'description' => 'Information Technology Instructors Attendance Management',
                        'icon' => 'fas fa-laptop-code',
                        'color' => 'bsit-color'
                    ],
                    'BSBA' => [
                        'name' => 'BSBA Department',
                        'description' => 'Business Administration Instructors Attendance Management',
                        'icon' => 'fas fa-briefcase',
                        'color' => 'bsba-color'
                    ],
                    'BSHM' => [
                        'name' => 'BSHM Department',
                        'description' => 'Hospitality Management Instructors Attendance Management',
                        'icon' => 'fas fa-hotel',
                        'color' => 'bshm-color'
                    ],
                    'EDUCATION' => [
                        'name' => 'Education Department',
                        'description' => 'Education Faculty Attendance Management',
                        'icon' => 'fas fa-graduation-cap',
                        'color' => 'education-color'
                    ]
                ];
                $currentCourse = $courseInfo[$userCourse] ?? $courseInfo['BSIT'];
            @endphp

            <h3><i class="{{ $currentCourse['icon'] }}"></i> {{ $currentCourse['name'] }}</h3>
            <p>{{ $currentCourse['description'] }}</p>
            <div class="course-selection">
                <div class="single-course-grid">
                    <div class="course-card" onclick="loadAttendance('{{ strtolower($userCourse) }}')">
                        <div class="course-icon {{ $currentCourse['color'] }}">
                            <i class="{{ $currentCourse['icon'] }}"></i>
                        </div>
                        <h4>{{ $userCourse }} Attendance</h4>
                        <p>{{ $currentCourse['name'] }}</p>
                        <span class="course-count" id="course-count">0 Instructors</span>
                        <div class="start-btn">
                            <i class="fas fa-play"></i> Start Attendance
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="attendance-section" class="card" style="display: none;">
            <h3 id="attendance-title"><i class="fas fa-calendar-check"></i> Attendance Management</h3>

            <div class="date-controls">
                <button class="btn btn-secondary" onclick="previousWeek()">
                    <i class="fas fa-chevron-left"></i> Previous Week
                </button>
                <span class="current-week" id="current-week"></span>
                <button class="btn btn-secondary" id="next-week-btn" onclick="nextWeek()">
                    Next Week <i class="fas fa-chevron-right"></i>
                </button>
                <button class="btn" onclick="goToCurrentWeek()">
                    <i class="fas fa-calendar-day"></i> Current Week
                </button>
            </div>

            <div class="table-container" id="attendance-table-container">
                <div class="loading-spinner" id="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Loading attendance data...
                </div>
                <table id="attendance-table" style="display: none;">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()" title="Select All">
                            </th>
                            <th>Employee ID</th>
                            <th>Instructor Name</th>
                            <th>Designation</th>
                            <th>Type</th>
                            <th class="day-header">Mon<br><small id="mon-date"></small></th>
                            <th class="day-header">Tue<br><small id="tue-date"></small></th>
                            <th class="day-header">Wed<br><small id="wed-date"></small></th>
                            <th class="day-header">Thu<br><small id="thu-date"></small></th>
                            <th class="day-header">Fri<br><small id="fri-date"></small></th>
                            <th class="day-header">Sat<br><small id="sat-date"></small></th>
                        </tr>
                    </thead>
                    <tbody id="attendance-tbody"></tbody>
                </table>
            </div>

            <div class="quick-actions">
                <button class="btn" onclick="markAllPresent()">
                    <i class="fas fa-check-double"></i> Mark All Present
                </button>
                <button class="btn btn-secondary" onclick="markAllAbsent()">
                    <i class="fas fa-times"></i> Mark All Absent
                </button>
                <button class="btn" onclick="saveAttendance(event)">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button class="btn btn-secondary" onclick="exportAttendance()">
                    <i class="fas fa-file-export"></i> Export Report
                </button>
            </div>

            <div class="bulk-actions" id="bulk-actions" style="display: none;">
                <div class="bulk-actions-info">
                    <span id="selected-count">0</span> employee(s) selected
                </div>
                <div class="bulk-actions-buttons">
                    <button class="btn btn-danger" onclick="bulkDeleteSelected()">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                    <button class="btn btn-secondary" onclick="clearSelection()">
                        <i class="fas fa-times"></i> Clear Selection
                    </button>
                </div>
            </div>
        </div>

        <div id="stats-section" class="card" style="display: none;">
            <h3><i class="fas fa-chart-pie"></i> Attendance Statistics</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value" id="total-instructors">0</div>
                    <div class="stat-label">Total Instructors</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-success" id="present-today">0</div>
                    <div class="stat-label">Present Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-danger" id="absent-today">0</div>
                    <div class="stat-label">Absent Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="attendance-rate">0%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .course-selection { margin-top: 1rem; }

        .single-course-grid {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .course-card {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #e3f2fd;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            max-width: 350px;
            width: 100%;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 123, 255, 0.25);
            border-color: #007bff;
        }

        .course-card .start-btn,
        .start-btn {
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .start-btn:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
        }

        .course-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .bsit-color      { background: #dc3545; }
        .bsba-color      { background: #28a745; }
        .bshm-color      { background: #ffc107; color: #333 !important; }
        .education-color { background: #6f42c1; }

        .course-card h4 { margin: 0.5rem 0; color: #333; font-size: 1.2rem; }
        .course-card p  { margin: 0.5rem 0; color: #666; font-size: 0.9rem; }

        .course-count {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .date-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }

        .current-week {
            font-weight: bold;
            font-size: 1.1rem;
            color: #007bff;
            padding: 0.5rem 1rem;
            background: rgba(0, 123, 255, 0.1);
            border-radius: 5px;
        }

        .table-container { overflow-x: auto; margin-top: 1rem; }

        .loading-spinner {
            text-align: center;
            padding: 2rem;
            color: #007bff;
            font-size: 1.1rem;
        }

        .loading-spinner i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background: linear-gradient(135deg, #007bff, #4da6ff);
            color: white;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        table th { font-weight: 600; font-size: 0.9rem; }

        table tbody tr:nth-child(even) { background-color: #f8f9fa; }

        table tbody tr:hover { background-color: rgba(0, 123, 255, 0.1); }

        .attendance-cell {
            cursor: pointer;
            padding: 8px !important;
            font-size: 1.2rem;
        }

        .attendance-cell:hover { background-color: rgba(0, 123, 255, 0.2) !important; }

        .attendance-present { color: #28a745; }
        .attendance-absent  { color: #dc3545; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            border: 1px solid #e3f2fd;
        }

        .stat-value { font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem; }
        .stat-label { color: #666; font-size: 0.9rem; }

        .quick-actions { margin-top: 1.5rem; text-align: center; }
        .quick-actions .btn { margin: 0.25rem; }

        .bulk-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .bulk-actions-info  { font-weight: bold; color: #495057; }
        .bulk-actions-buttons { display: flex; gap: 0.5rem; }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
            transform: translateY(-2px);
        }

        input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        .employee-checkbox { cursor: pointer; }
        .selected-row { background-color: rgba(0, 123, 255, 0.1) !important; }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease-out;
        }

        .notification.success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .notification.error   { background: linear-gradient(135deg, #dc3545, #fd7e14); color: white; }

        .notification-content {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .notification-close:hover { opacity: 1; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0);   opacity: 1; }
        }

        @media (max-width: 768px) {
            .single-course-grid { padding: 0 1rem; }
            .course-card        { max-width: 100%; }
            .date-controls      { flex-direction: column; }
            .stats-grid         { grid-template-columns: repeat(2, 1fr); }
            .notification       { left: 20px; right: 20px; min-width: auto; }
        }
    </style>

    <script>
        let currentDate    = new Date();
        let selectedCourse = '';
        let attendanceData = [];

        // ── FIX #2: getWeekMonday ─────────────────────────────────────────────
        // The original `date - getDay() + 1` formula was wrong on Sundays:
        // getDay() returns 0, so the expression became `date + 1`, jumping
        // FORWARD to next Monday instead of back to the previous one.
        function getWeekMonday(date) {
            const d   = new Date(date);
            const day = d.getDay();                 // 0=Sun … 6=Sat
            const diff = day === 0 ? -6 : 1 - day; // Sunday → −6, else → 1−day
            d.setDate(d.getDate() + diff);
            return d;
        }

        // ── FIX #3: formatLocalDate ───────────────────────────────────────────
        // toISOString() converts to UTC before formatting. In UTC+ timezones
        // (Philippines = UTC+8) midnight local time is still the previous UTC
        // day, so toISOString().split('T')[0] returned yesterday's date.
        function formatLocalDate(d) {
            const yyyy = d.getFullYear();
            const mm   = String(d.getMonth() + 1).padStart(2, '0');
            const dd   = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        // ── FIX #5: escapeHtml ────────────────────────────────────────────────
        // Prevents XSS when inserting API values into innerHTML.
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        // ── Init ──────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            loadCourseCounts();
        });

        function loadCourseCounts() {
            const userCourse = '{{ strtolower(session("user_course", "bsit")) }}';
            fetch(`/attendance/api/course-counts?course=${encodeURIComponent(userCourse)}`)
                .then(response => {
                    // FIX: Handle 401 from middleware (session expired) gracefully.
                    if (response.status === 401) {
                        window.location.href = '{{ route("attendance.attendlog.form") }}';
                        return null;
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) return;
                    const count = data.count || 0;
                    document.getElementById('course-count').textContent =
                        `${count} Instructor${count !== 1 ? 's' : ''}`;
                })
                .catch(error => {
                    console.error('Error loading course counts:', error);
                    document.getElementById('course-count').textContent = '0 Instructors';
                });
        }

        function loadAttendance(course) {
            selectedCourse = course.toLowerCase();

            document.getElementById('attendance-section').style.display = 'block';
            document.getElementById('stats-section').style.display      = 'block';

            const courseUpper = course.toUpperCase();
            document.getElementById('attendance-title').innerHTML =
                `<i class="fas fa-calendar-check"></i> ${escapeHtml(courseUpper)} Attendance Management`;

            document.getElementById('loading-spinner').style.display  = 'block';
            document.getElementById('attendance-table').style.display = 'none';

            updateWeekDisplay();
            fetchAttendanceData();
        }

        function updateWeekDisplay() {
            const today       = new Date();
            const startOfWeek = getWeekMonday(currentDate);
            const endOfWeek   = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 5); // Mon → Sat

            document.getElementById('current-week').textContent =
                `${startOfWeek.toLocaleDateString()} - ${endOfWeek.toLocaleDateString()}`;

            // FIX #7: Disable Next Week button when already on the current week.
            // Compare week starts (not endOfWeek > today) to prevent navigating
            // to a week that starts today but has future days still editable.
            const nextWeekBtn    = document.getElementById('next-week-btn');
            const currentMonday  = getWeekMonday(today);
            nextWeekBtn.disabled = startOfWeek >= currentMonday;

            // Update day-header dates.
            const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            days.forEach((day, i) => {
                const dayDate = new Date(startOfWeek);
                dayDate.setDate(startOfWeek.getDate() + i);
                document.getElementById(`${day}-date`).textContent =
                    dayDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
        }

        function fetchAttendanceData() {
    if (!selectedCourse) {
        showNotification('❌ Please select a course first.', 'error');
        return;
    }
 
    const startOfWeek = getWeekMonday(currentDate);
    const formattedDate = formatLocalDate(startOfWeek);
 
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('attendance-table').style.display = 'none';
    document.getElementById('attendance-tbody').innerHTML = '';
 
    console.log('Fetching attendance data for:', selectedCourse, 'week starting:', formattedDate);
 
    fetch(`/attendance/api/attendance-data/${encodeURIComponent(selectedCourse)}?week_start=${encodeURIComponent(formattedDate)}`)
        .then(response => {
            // FIX #4: Better error handling for various HTTP status codes
            if (response.status === 401) {
                console.warn('Session expired');
                window.location.href = '{{ route("attendance.attendlog.form") }}';
                return null;
            }
 
            if (response.status === 403) {
                throw new Error('Unauthorized access to this department');
            }
 
            if (response.status === 500) {
                throw new Error('Server error. Please try again later.');
            }
 
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
 
            return response.json();
        })
        .then(data => {
            if (!data) return; // Null from 401 redirect
 
            // FIX #5: Validate response data structure
            if (data.error) {
                throw new Error(data.error);
            }
 
            if (!Array.isArray(data)) {
                console.error('Unexpected response format:', data);
                throw new Error('Invalid response format from server');
            }
 
            attendanceData = [];
            const tbody = document.getElementById('attendance-tbody');
            tbody.innerHTML = '';
 
            let processedCount = 0;
 
            data.forEach((employee, index) => {
                try {
                    // FIX #6: Validate employee object structure
                    if (!employee || typeof employee !== 'object') {
                        console.warn('Invalid employee object at index', index, employee);
                        return;
                    }
 
                    if (!employee.employee_name || !employee.id) {
                        console.warn('Missing required employee fields at index', index);
                        return;
                    }
 
                    // FIX #7: Safe property access with defaults
                    const empId = String(employee.id).trim();
                    const empName = String(employee.employee_name).trim();
                    const empDesignation = String(employee.designation || 'N/A').trim();
                    const empType = String(employee.employee_type || 'Employee').trim();
 
                    // FIX #8: Safe JSON parsing with fallback
                    let existingAttendance = {};
                    if (employee.days && typeof employee.days === 'object') {
                        existingAttendance = employee.days;
                    } else if (typeof employee.days === 'string') {
                        try {
                            existingAttendance = JSON.parse(employee.days) || {};
                        } catch (e) {
                            console.warn('Failed to parse days JSON for', empId);
                            existingAttendance = {};
                        }
                    }
 
                    // FIX #9: Safe boolean conversion for attendance days
                    const attendance = {
                        monday:    convertToBoolean(existingAttendance.monday),
                        tuesday:   convertToBoolean(existingAttendance.tuesday),
                        wednesday: convertToBoolean(existingAttendance.wednesday),
                        thursday:  convertToBoolean(existingAttendance.thursday),
                        friday:    convertToBoolean(existingAttendance.friday),
                        saturday:  convertToBoolean(existingAttendance.saturday)
                    };
 
                    const row = createAttendanceRow(empId, empName, empDesignation, empType, attendance);
                    tbody.appendChild(row);
                    processedCount++;
 
                    // Store in attendanceData array
                    attendanceData.push({
                        id: empId,
                        employee_name: empName,
                        designation: empDesignation,
                        employee_type: empType,
                        attendance: attendance
                    });
 
                } catch (error) {
                    console.error('Error processing employee at index', index, ':', error);
                }
            });
 
            console.log(`Processed ${processedCount} employees successfully`);
 
            document.getElementById('loading-spinner').style.display = 'none';
            document.getElementById('attendance-table').style.display = 'block';
 
            if (processedCount === 0) {
                showNotification('⚠️ No employee data found for this department and week.', 'warning');
            } else {
                showNotification(`✅ Loaded ${processedCount} employee records.`, 'success');
            }
 
            updateStatistics();
        })
        .catch(error => {
            console.error('Error fetching attendance data:', error);
            document.getElementById('loading-spinner').style.display = 'none';
            document.getElementById('attendance-table').style.display = 'block';
 
            const errorMessage = error.message || 'Failed to load attendance data';
            showNotification(`❌ Error: ${escapeHtml(errorMessage)}`, 'error');
 
            // Show helpful message
            const tbody = document.getElementById('attendance-tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i><br>
                        <strong>Unable to load attendance data</strong><br>
                        <small>${escapeHtml(errorMessage)}</small><br>
                        <small>Please refresh the page or contact support if the problem persists.</small>
                    </td>
                </tr>
            `;
        });
}
 
// FIX #3: Add helper function for safe boolean conversion
function convertToBoolean(value) {
    if (value === undefined || value === null) {
        return false;
    }
    if (typeof value === 'boolean') {
        return value;
    }
    if (typeof value === 'number') {
        return value > 0;
    }
    if (typeof value === 'string') {
        return value.toLowerCase() === 'true' || Number(value) > 0;
    }
    return false;
}
 
// FIX #2: Improved updateStatistics with null checks
function updateStatistics() {
    const totalEmployees = attendanceData.length;
    
    if (totalEmployees === 0) {
        document.getElementById('total-employees').textContent = '0';
        document.getElementById('avg-attendance').textContent = '0%';
        return;
    }
 
    let totalPresent = 0;
    let totalDays = 0;
 
    attendanceData.forEach(emp => {
        const attendance = emp.attendance || {};
        const daysList = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
 
        daysList.forEach(day => {
            totalDays++;
            if (attendance[day] === true) {
                totalPresent++;
            }
        });
    });
 
    document.getElementById('total-employees').textContent = totalEmployees;
    
    const avgAttendance = totalDays > 0 
        ? Math.round((totalPresent / totalDays) * 100) 
        : 0;
    document.getElementById('avg-attendance').textContent = avgAttendance + '%';
}
 
// FIX #1: Improve saveAttendance with better validation
function saveAttendance(event) {
    event.preventDefault();
 
    if (!selectedCourse) {
        showNotification('❌ Please select a course first.', 'error');
        return;
    }
 
    if (!attendanceData || attendanceData.length === 0) {
        showNotification('❌ No attendance data loaded. Please load data first.', 'error');
        return;
    }
 
    // Validate that at least one employee has attendance marked
    const hasAnyAttendance = attendanceData.some(emp => {
        return emp.attendance && Object.values(emp.attendance).some(day => day === true);
    });
 
    if (!hasAnyAttendance) {
        Swal.fire({
            icon: 'warning',
            title: 'No attendance marked',
            text: 'You haven\'t marked any attendance. Do you want to save anyway?',
            showCancelButton: true,
            confirmButtonText: 'Yes, save',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                performSaveAttendance();
            }
        });
        return;
    }
 
    performSaveAttendance();
}
 
function performSaveAttendance() {
    const startOfWeek = getWeekMonday(currentDate);
    const saveData = {
        course: selectedCourse.toUpperCase(),
        week_start: formatLocalDate(startOfWeek),
        attendance_data: attendanceData.map(emp => ({
            id: emp.id,
            employee_name: emp.employee_name,
            name: emp.employee_name,
            designation: emp.designation,
            employee_type: emp.employee_type,
            type: emp.employee_type,
            attendance: emp.attendance
        }))
    };
 
    const saveBtn = event?.target?.closest('button') || document.getElementById('save-attendance-btn');
    if (!saveBtn) return;
 
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
 
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
 
    fetch('/attendance/api/save-attendance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(saveData)
    })
    .then(response => {
        if (response.status === 401) {
            window.location.href = '{{ route("attendance.attendlog.form") }}';
            return null;
        }
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;
 
        if (data.success) {
            showNotification('✅ ' + (data.message || 'Attendance saved successfully!'), 'success');
            setTimeout(() => {
                fetchAttendanceData();
                loadCourseCounts();
            }, 500);
 
            // Fire-and-forget history save
            fetch('/attendance/api/save-attendance-history', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(saveData)
            }).catch(err => console.warn('History save failed (non-critical):', err));
        } else {
            showNotification('❌ Error: ' + (data.message || 'Failed to save attendance'), 'error');
        }
    })
    .catch(error => {
        console.error('Error saving attendance:', error);
        showNotification('❌ Error: ' + (error.message || 'Failed to save attendance. Please try again.'), 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}
 
// FIX #10: Add data validation summary before save
function validateAttendanceData() {
    const errors = [];
 
    if (!attendanceData || attendanceData.length === 0) {
        errors.push('No attendance data loaded');
    }
 
    attendanceData.forEach((emp, idx) => {
        if (!emp.id) errors.push(`Employee ${idx}: Missing ID`);
        if (!emp.employee_name) errors.push(`Employee ${idx}: Missing name`);
        if (!emp.attendance || typeof emp.attendance !== 'object') {
            errors.push(`Employee ${idx}: Invalid attendance data`);
        }
    });
 
    return errors;
}