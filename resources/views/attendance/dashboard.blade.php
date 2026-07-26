<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Checker Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #007bff, #4da6ff, #e3f2fd);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(255,255,255,.95); backdrop-filter: blur(10px);
            padding: 1rem 2rem; box-shadow: 0 2px 10px rgba(0,0,0,.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { color: #007bff; font-size: 1.5rem; }
        .user-info  { display: flex; align-items: center; gap: 1rem; }
        .user-badge {
            background: #007bff; color: white; padding: .5rem 1rem;
            border-radius: 20px; font-size: .9rem; font-weight: bold;
        }
        .logout-btn {
            background: #dc3545; color: white; padding: .5rem 1rem;
            border: none; border-radius: 5px; text-decoration: none;
            font-size: .9rem; transition: background .3s; cursor: pointer;
        }
        .logout-btn:hover { background: #c82333; }

        /* Container / Cards */
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        .card {
            background: rgba(255,255,255,.95); backdrop-filter: blur(10px);
            border-radius: 10px; padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,.1); margin-bottom: 2rem;
        }
        .card h3 { color: #007bff; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }

        /* Buttons */
        .btn {
            display: inline-block; background: #007bff; color: white;
            padding: .75rem 1.5rem; border: none; border-radius: 5px;
            text-decoration: none; font-size: 1rem; cursor: pointer; transition: background .3s;
        }
        .btn:hover        { background: #0056b3; }
        .btn-secondary    { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        .btn-danger       { background: linear-gradient(135deg,#dc3545,#c82333); color: white; border: none; }
        .btn-danger:hover { background: linear-gradient(135deg,#c82333,#a71e2a); transform: translateY(-2px); }
        .btn-success      { background: #28a745; }
        .btn-success:hover{ background: #218838; }

        /* Course selector */
        .single-course-grid { display: flex; justify-content: center; margin-top: 1rem; }
        .course-card {
            background: rgba(255,255,255,.9); border: 2px solid #e3f2fd;
            border-radius: 15px; padding: 2rem; text-align: center;
            cursor: pointer; transition: all .3s ease; max-width: 350px; width: 100%;
        }
        .course-card:hover { transform: translateY(-8px); box-shadow: 0 12px 35px rgba(0,123,255,.25); border-color: #007bff; }
        .course-card h4 { margin: .5rem 0; color: #333; font-size: 1.2rem; }
        .course-card p  { margin: .5rem 0; color: #666; font-size: .9rem; }
        .course-icon {
            width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem; font-size: 1.5rem; color: white;
        }
        .bsit-color      { background: #dc3545; }
        .bsba-color      { background: #28a745; }
        .bshm-color      { background: #ffc107; color: #333 !important; }
        .education-color { background: #6f42c1; }
        .course-count {
            display: inline-block; background: #007bff; color: white;
            padding: .25rem .75rem; border-radius: 15px; font-size: .8rem; font-weight: bold;
        }
        .start-btn {
            margin-top: 1rem; padding: .75rem 1.5rem;
            background: linear-gradient(135deg,#dc3545,#c82333);
            color: white; border-radius: 25px; font-weight: bold; font-size: 1rem;
        }

        /* Date controls */
        .date-controls {
            display: flex; justify-content: center; align-items: center;
            gap: 1rem; margin: 1.5rem 0; flex-wrap: wrap;
        }
        .current-week {
            font-weight: bold; font-size: 1.1rem; color: #007bff;
            padding: .5rem 1rem; background: rgba(0,123,255,.1); border-radius: 5px;
        }

        /* Legend */
        .legend {
            display: flex; gap: 1rem; flex-wrap: wrap; font-size: .75rem;
            margin-top: .5rem; padding: .5rem; background: #f8f9fa;
            border-radius: 6px; align-items: center;
        }
        .legend-item { display: flex; align-items: center; gap: 4px; }
        .legend-dot  { width: 10px; height: 10px; border-radius: 50%; }

        /* Table */
        .table-container { overflow-x: auto; margin-top: 1rem; }
        .loading-spinner { text-align: center; padding: 2rem; color: #007bff; font-size: 1.1rem; }
        .loading-spinner i { font-size: 2rem; display: block; margin-bottom: .5rem; }

        table {
            width: 100%; border-collapse: collapse; background: white;
            border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.1); min-width: 1300px;
        }
        table thead              { background: linear-gradient(135deg,#007bff,#4da6ff); color: white; }
        table th, table td       { padding: 7px 5px; text-align: center; border: 1px solid #dee2e6; }
        table th                 { font-weight: 600; font-size: .8rem; }
        table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        table tbody tr:hover     { background-color: rgba(0,123,255,.05); }

        .day-group-th {
            background: linear-gradient(135deg,#0056b3,#007bff);
            color: white; font-weight: bold; font-size: .75rem;
            border-bottom: 2px solid rgba(255,255,255,.3);
        }

        /* Time inputs */
        .time-cell { padding: 3px !important; }
        .time-input-group { display: flex; flex-direction: column; gap: 2px; }
        .time-input-group label {
            font-size: .6rem; color: #888; text-transform: uppercase; font-weight: 600;
        }
        .time-input-group input[type="time"] {
            width: 88px; padding: 2px 4px; border: 1px solid #ccc;
            border-radius: 4px; font-size: .72rem; color: #333; background: #fff;
            transition: border-color .2s;
        }
        .time-input-group input[type="time"]:focus {
            outline: none; border-color: #007bff; box-shadow: 0 0 0 2px rgba(0,123,255,.15);
        }
        .time-input-group input.has-time { border-color: #28a745; background: #f0fff4; }
        .time-input-group input.late     { border-color: #fd7e14; background: #fff8f0; }

        /* Metric badges */
        .day-metrics { font-size: .6rem; margin-top: 2px; display: flex; gap: 2px; flex-wrap: wrap; justify-content: center; }
        .badge-hours { background: #007bff; color: white; border-radius: 3px; padding: 1px 3px; }
        .badge-late  { background: #fd7e14; color: white; border-radius: 3px; padding: 1px 3px; }
        .badge-under { background: #dc3545; color: white; border-radius: 3px; padding: 1px 3px; }
        .badge-over  { background: #28a745; color: white; border-radius: 3px; padding: 1px 3px; }

        .col-id   { min-width: 55px; }
        .col-name { min-width: 140px; text-align: left !important; }
        .col-desg { min-width: 90px; font-size: .8rem; }
        .col-type { min-width: 70px; font-size: .8rem; }

        /* Stats */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit,minmax(140px,1fr));
            gap: 1rem; margin-top: 1rem;
        }
        .stat-item {
            text-align: center; padding: 1rem;
            background: rgba(255,255,255,.9); border-radius: 8px; border: 1px solid #e3f2fd;
        }
        .stat-value   { font-size: 1.8rem; font-weight: bold; margin-bottom: .5rem; }
        .stat-label   { color: #666; font-size: .85rem; }
        .text-success { color: #28a745; }
        .text-danger  { color: #dc3545; }
        .text-warning { color: #fd7e14; }
        .text-primary { color: #007bff; }

        /* Actions */
        .quick-actions { margin-top: 1.5rem; text-align: center; }
        .quick-actions .btn { margin: .25rem; }

        .bulk-actions {
            display: flex; justify-content: space-between; align-items: center;
            background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
            padding: 1rem; margin: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .bulk-actions-info    { font-weight: bold; color: #495057; }
        .bulk-actions-buttons { display: flex; gap: .5rem; }

        input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
        .selected-row { background-color: rgba(0,123,255,.08) !important; }

        /* Notifications */
        .notification {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            min-width: 300px; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15); animation: slideIn .3s ease-out;
        }
        .notification.success { background: linear-gradient(135deg,#28a745,#20c997); color: white; }
        .notification.error   { background: linear-gradient(135deg,#dc3545,#fd7e14); color: white; }
        .notification.warning { background: linear-gradient(135deg,#fd7e14,#ffc107); color: white; }
        .notification-content {
            padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;
        }
        .notification-close { background: none; border: none; color: white; cursor: pointer; font-size: 1rem; opacity: .8; }
        .notification-close:hover { opacity: 1; }

        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        @media (max-width: 768px) {
            .header        { flex-direction: column; gap: 1rem; text-align: center; }
            .date-controls { flex-direction: column; }
            .stats-grid    { grid-template-columns: repeat(2,1fr); }
            .notification  { left: 20px; right: 20px; min-width: auto; }
        }
    </style>
</head>
<body>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?', text: 'You will be logged out.',
                        icon: 'warning', showCancelButton: true,
                        confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, logout'
                    }).then(r => { if (r.isConfirmed) document.getElementById('logout-form').submit(); });
                });
            }
        });
    </script>

    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-clipboard-check"></i> Attendance Checker Dashboard</h1>
        <div class="user-info">
            <div class="user-badge">
                <i class="fas fa-user"></i> {{ session('user_name') }}
                @if(session('user_course'))
                    <span style="background:rgba(255,255,255,.3);padding:.1rem .5rem;border-radius:10px;font-size:.75rem;">
                        {{ strtoupper(session('user_course')) }}
                    </span>
                @endif
            </div>
            <a id="logoutBtn" class="logout-btn" href="{{ route('attendance.logout') }}">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('attendance.logout') }}" method="POST" style="display:none;">@csrf</form>
        </div>
    </div>

    <div class="container">

        <!-- Course Card -->
        <div class="card">
            @php
                $userCourse    = strtoupper(session('user_course', 'BSIT'));
                $courseInfo    = [
                    'BSIT'      => ['name'=>'BSIT Department',      'icon'=>'fas fa-laptop-code',    'color'=>'bsit-color'],
                    'BSBA'      => ['name'=>'BSBA Department',      'icon'=>'fas fa-briefcase',      'color'=>'bsba-color'],
                    'BSHM'      => ['name'=>'BSHM Department',      'icon'=>'fas fa-hotel',          'color'=>'bshm-color'],
                    'EDUCATION' => ['name'=>'Education Department',  'icon'=>'fas fa-graduation-cap', 'color'=>'education-color'],
                ];
                $currentCourse = $courseInfo[$userCourse] ?? $courseInfo['BSIT'];
            @endphp
            <h3><i class="{{ $currentCourse['icon'] }}"></i> {{ $currentCourse['name'] }}</h3>
            <p>CSC-Compliant Attendance â€” records AM In / AM Out / PM In / PM Out per employee per day.</p>
            <div class="single-course-grid">
                <div class="course-card" onclick="loadAttendance('{{ strtolower($userCourse) }}')">
                    <div class="course-icon {{ $currentCourse['color'] }}">
                        <i class="{{ $currentCourse['icon'] }}"></i>
                    </div>
                    <h4>{{ $userCourse }} Attendance</h4>
                    <p>{{ $currentCourse['name'] }}</p>
                    <span class="course-count" id="course-count">0 Instructors</span>
                    <div class="start-btn"><i class="fas fa-play"></i> Start Attendance</div>
                </div>
            </div>
        </div>

        <!-- Attendance Section -->
        <div id="attendance-section" class="card" style="display:none;">
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

            <div class="legend">
                <strong>Legend:</strong>
                <span class="legend-item"><span class="legend-dot" style="background:#28a745"></span> Has time entry</span>
                <span class="legend-item"><span class="legend-dot" style="background:#fd7e14"></span> Late / Early departure</span>
                <span class="legend-item"><span class="legend-dot" style="background:#dc3545"></span> Undertime</span>
                <span class="legend-item"><span class="legend-dot" style="background:#007bff"></span> Hours worked</span>
                <span style="margin-left:auto;font-style:italic;color:#888;font-size:.7rem;">
                    Official: 08:00â€“12:00 | 13:00â€“17:00
                </span>
            </div>

            <div class="table-container" id="attendance-table-container">
                <div class="loading-spinner" id="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Loading attendance data...
                </div>
                <table id="attendance-table" style="display:none;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:35px;">
                                <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()" title="Select All">
                            </th>
                            <th rowspan="2" class="col-id">ID</th>
                            <th rowspan="2" class="col-name" style="text-align:left;">Instructor Name</th>
                            <th rowspan="2" class="col-desg">Designation</th>
                            <th rowspan="2" class="col-type">Type</th>
                            <th colspan="2" class="day-group-th">Mon <small id="mon-date"></small></th>
                            <th colspan="2" class="day-group-th">Tue <small id="tue-date"></small></th>
                            <th colspan="2" class="day-group-th">Wed <small id="wed-date"></small></th>
                            <th colspan="2" class="day-group-th">Thu <small id="thu-date"></small></th>
                            <th colspan="2" class="day-group-th">Fri <small id="fri-date"></small></th>
                            <th colspan="2" class="day-group-th">Sat <small id="sat-date"></small></th>
                        </tr>
                        <tr>
                            <th style="font-size:.68rem;min-width:90px;">AM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">PM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">AM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">PM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">AM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">PM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">AM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">PM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">AM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">PM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">AM In/Out</th>
                            <th style="font-size:.68rem;min-width:90px;">PM In/Out</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-tbody"></tbody>
                </table>
            </div>

            <div class="quick-actions">
                <button class="btn btn-success" onclick="markAllPresent()">
                    <i class="fas fa-check-double"></i> Fill Default Times
                </button>
                <button class="btn btn-secondary" onclick="clearAllTimes()">
                    <i class="fas fa-times"></i> Clear All Times
                </button>
                <button class="btn" id="save-attendance-btn" onclick="saveAttendance(event)">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
                <button class="btn btn-secondary" onclick="exportAttendance()">
                    <i class="fas fa-file-export"></i> Export CSV
                </button>
            </div>

            <div class="bulk-actions" id="bulk-actions" style="display:none;">
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

        <!-- Statistics -->
        <div id="stats-section" class="card" style="display:none;">
            <h3><i class="fas fa-chart-pie"></i> Weekly Attendance Statistics</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value text-primary" id="total-employees">0</div>
                    <div class="stat-label">Total Employees</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-success" id="present-today">0</div>
                    <div class="stat-label">Days with Entries</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-danger" id="absent-today">0</div>
                    <div class="stat-label">Days without Entries</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-warning" id="avg-hours">0h</div>
                    <div class="stat-label">Avg Daily Hours</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="attendance-rate">0%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
</div>

<script>
const OFF_AM_IN='08:00', OFF_AM_OUT='12:00', OFF_PM_IN='13:00', OFF_PM_OUT='17:00';
const DAYS=['monday','tuesday','wednesday','thursday','friday','saturday'];
let currentDate=new Date(), selectedCourse='', attendanceData=[];

function getWeekMonday(d){const x=new Date(d),day=x.getDay();x.setDate(x.getDate()+(day===0?-6:1-day));return x;}
function formatLocalDate(d){return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');}
function escapeHtml(s){const d=document.createElement('div');d.appendChild(document.createTextNode(String(s??'')));return d.innerHTML;}
function toMins(t){if(!t)return null;const[h,m]=t.split(':').map(Number);return h*60+m;}
function fmtMins(m){if(!m||m<=0)return '';const h=Math.floor(m/60),mn=m%60;return h>0?(mn>0?h+'h '+mn+'m':h+'h'):mn+'m';}
function calcMetrics(aI,aO,pI,pO){
    let l=0,u=0,ov=0,w=0;
    const ai=toMins(aI),ao=toMins(aO),pi=toMins(pI),po=toMins(pO);
    const oi=toMins(OFF_AM_IN),oo=toMins(OFF_PM_OUT);
    if(ai!==null&&ai>oi)l=ai-oi;
    if(po!==null){if(po<oo)u=oo-po;if(po>oo)ov=po-oo;}
    if(ai!==null&&ao!==null&&ao>ai)w+=ao-ai;
    if(pi!==null&&po!==null&&po>pi)w+=po-pi;
    return{lateness:l,undertime:u,overtime:ov,worked:w};
}

document.addEventListener('DOMContentLoaded', loadCourseCounts);
function redirect401(){window.location.href='{{ route("attendance.attendlog.form") }}';}

function loadCourseCounts(){
    const c='{{ strtolower(session("user_course","bsit")) }}';
    fetch('/attendance/api/course-counts?course='+encodeURIComponent(c))
        .then(r=>{if(r.status===401){redirect401();return null;}return r.json();})
        .then(d=>{if(!d)return;const n=d.count||0;document.getElementById('course-count').textContent=n+' Instructor'+(n!==1?'s':'');})
        .catch(()=>{document.getElementById('course-count').textContent='0 Instructors';});
}

function loadAttendance(course){
    selectedCourse=course.toLowerCase();
    document.getElementById('attendance-section').style.display='block';
    document.getElementById('stats-section').style.display='block';
    document.getElementById('attendance-title').innerHTML='<i class="fas fa-calendar-check"></i> '+escapeHtml(course.toUpperCase())+' Attendance &mdash; CSC Form No. 48';
    updateWeekDisplay();
    fetchAttendanceData();
}

function updateWeekDisplay(){
    const today=new Date(),som=getWeekMonday(currentDate),eom=new Date(som);
    eom.setDate(som.getDate()+5);
    document.getElementById('current-week').textContent=som.toLocaleDateString()+' \u2013 '+eom.toLocaleDateString();
    document.getElementById('next-week-btn').disabled=som>=getWeekMonday(today);
    ['mon','tue','wed','thu','fri','sat'].forEach((d,i)=>{
        const dd=new Date(som);dd.setDate(som.getDate()+i);
        document.getElementById(d+'-date').textContent=dd.toLocaleDateString('en-US',{month:'short',day:'numeric'});
    });
}

function previousWeek(){currentDate.setDate(currentDate.getDate()-7);updateWeekDisplay();fetchAttendanceData();}
function nextWeek(){currentDate.setDate(currentDate.getDate()+7);updateWeekDisplay();fetchAttendanceData();}
function goToCurrentWeek(){currentDate=new Date();updateWeekDisplay();fetchAttendanceData();}

function fetchAttendanceData(){
    if(!selectedCourse){showNotification('Please select a course first.','error');return;}
    const som=getWeekMonday(currentDate),date=formatLocalDate(som);
    document.getElementById('loading-spinner').style.display='block';
    document.getElementById('attendance-table').style.display='none';
    document.getElementById('attendance-tbody').innerHTML='';
    fetch('/attendance/api/attendance-data/'+encodeURIComponent(selectedCourse)+'?week_start='+encodeURIComponent(date))
        .then(r=>{
            if(r.status===401){redirect401();return null;}
            if(r.status===403)throw new Error('Unauthorized access to this department');
            if(!r.ok)throw new Error('HTTP '+r.status);
            return r.json();
        })
        .then(data=>{
            if(!data)return;
            if(data.error)throw new Error(data.error);
            if(!Array.isArray(data))throw new Error('Invalid response format');
            attendanceData=[];
            const tbody=document.getElementById('attendance-tbody');
            tbody.innerHTML='';
            let count=0;
            data.forEach((emp,idx)=>{
                try{
                    if(!emp||!emp.id||!emp.employee_name)return;
                    const empId=String(emp.id).trim();
                    const empName=String(emp.employee_name).trim();
                    const empDesg=String(emp.designation||'N/A').trim();
                    const empType=String(emp.employee_type||'Employee').trim();
                    const attendance={};
                    DAYS.forEach(day=>{
                        const saved=emp.saved_times&&emp.saved_times[day];
                        if(saved){
                            attendance[day]={am_in:saved.am_in||'',am_out:saved.am_out||'',pm_in:saved.pm_in||'',pm_out:saved.pm_out||''};
                        } else {
                            const leg=!!(emp.days&&emp.days[day]);
                            attendance[day]={am_in:leg?OFF_AM_IN:'',am_out:leg?OFF_AM_OUT:'',pm_in:leg?OFF_PM_IN:'',pm_out:leg?OFF_PM_OUT:''};
                        }
                    });
                    tbody.appendChild(createAttendanceRow(empId,empName,empDesg,empType,attendance));
                    attendanceData.push({id:empId,employee_name:empName,designation:empDesg,employee_type:empType,attendance});
                    count++;
                }catch(e){console.error('Row error',idx,e);}
            });
            document.getElementById('loading-spinner').style.display='none';
            document.getElementById('attendance-table').style.display='block';
            if(count===0)showNotification('No employee data found.','warning');
            else showNotification('Loaded '+count+' employee records.','success');
            updateStatistics();
        })
        .catch(err=>{
            document.getElementById('loading-spinner').style.display='none';
            document.getElementById('attendance-table').style.display='block';
            document.getElementById('attendance-tbody').innerHTML='<tr><td colspan="17" style="text-align:center;padding:20px;color:#dc3545;"><i class="fas fa-exclamation-triangle"></i><br><strong>Unable to load data</strong><br><small>'+escapeHtml(err.message)+'</small></td></tr>';
            showNotification(escapeHtml(err.message),'error');
        });
}

// â”€â”€â”€ ROW BUILDER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function createAttendanceRow(empId,empName,empDesg,empType,attendance){
    const tr=document.createElement('tr');
    tr.dataset.empId=empId;
    const chkTd=document.createElement('td');
    const chk=document.createElement('input');
    chk.type='checkbox';chk.className='employee-checkbox';chk.dataset.empId=empId;
    chk.addEventListener('change',updateBulkActionsUI);
    chkTd.appendChild(chk);tr.appendChild(chkTd);
    appendTd(tr,empId,'col-id');
    appendTd(tr,empName,'col-name',true);
    appendTd(tr,empDesg,'col-desg');
    appendTd(tr,empType,'col-type');
    DAYS.forEach(day=>{
        const d=attendance[day]||{am_in:'',am_out:'',pm_in:'',pm_out:''};
        tr.appendChild(buildTimeCell(empId,day,'am',d.am_in,d.am_out));
        tr.appendChild(buildTimeCell(empId,day,'pm',d.pm_in,d.pm_out));
    });
    return tr;
}
function appendTd(tr,text,cls,leftAlign){
    const td=document.createElement('td');
    if(cls)td.className=cls;
    if(leftAlign)td.style.textAlign='left';
    td.textContent=text;
    tr.appendChild(td);
}
function buildTimeCell(empId,day,period,inVal,outVal){
    const tdEl=document.createElement('td');
    tdEl.className='time-cell';
    const group=document.createElement('div');
    group.className='time-input-group';
    const inKey=period==='am'?'am_in':'pm_in';
    const outKey=period==='am'?'am_out':'pm_out';
    const inLbl=period==='am'?'AM In':'PM In';
    const outLbl=period==='am'?'AM Out':'PM Out';
    group.appendChild(wrapLbl(buildTimeInput(empId,day,inKey,inLbl,inVal||''),inLbl));
    group.appendChild(wrapLbl(buildTimeInput(empId,day,outKey,outLbl,outVal||''),outLbl));
    const metrics=document.createElement('div');
    metrics.className='day-metrics';
    metrics.id='metrics-'+empId+'-'+day+'-'+period;
    group.appendChild(metrics);
    if(inVal||outVal)setTimeout(()=>refreshDayMetrics(empId,day),0);
    tdEl.appendChild(group);
    return tdEl;
}
function wrapLbl(input,text){
    const w=document.createElement('div'),l=document.createElement('label');
    l.textContent=text;w.appendChild(l);w.appendChild(input);return w;
}
function buildTimeInput(empId,day,key,lbl,initVal){
    const inp=document.createElement('input');
    inp.type='time';inp.id='time_'+empId+'_'+day+'_'+key;
    inp.value=initVal;inp.title=lbl+' - '+day;
    applyInputStyle(inp,key,initVal);
    inp.addEventListener('change',function(){
        const emp=attendanceData.find(e=>e.id===empId);
        if(emp){if(!emp.attendance[day])emp.attendance[day]={};emp.attendance[day][key]=this.value;}
        applyInputStyle(inp,key,this.value);
        refreshDayMetrics(empId,day);
        updateStatistics();
    });
    return inp;
}
function applyInputStyle(inp,key,val){
    inp.classList.remove('has-time','late');
    if(!val)return;
    inp.classList.add('has-time');
    if(key==='am_in'&&val>OFF_AM_IN)inp.classList.add('late');
    if(key==='am_out'&&val<OFF_AM_OUT)inp.classList.add('late');
    if(key==='pm_in'&&val>OFF_PM_IN)inp.classList.add('late');
    if(key==='pm_out'&&val<OFF_PM_OUT)inp.classList.add('late');
}
function refreshDayMetrics(empId,day){
    const emp=attendanceData.find(e=>e.id===empId);
    if(!emp)return;
    const d=emp.attendance[day]||{};
    const amEl=document.getElementById('metrics-'+empId+'-'+day+'-am');
    if(amEl){
        amEl.innerHTML='';
        if(d.am_in&&d.am_out){
            const mins=toMins(d.am_out)-toMins(d.am_in);
            const late=toMins(d.am_in)>toMins(OFF_AM_IN)?toMins(d.am_in)-toMins(OFF_AM_IN):0;
            amEl.innerHTML='<span class="badge-hours">'+fmtMins(mins)+'</span>';
            if(late)amEl.innerHTML+='<span class="badge-late">+'+fmtMins(late)+'</span>';
        }
    }
    const pmEl=document.getElementById('metrics-'+empId+'-'+day+'-pm');
    if(pmEl){
        pmEl.innerHTML='';
        if(d.pm_in&&d.pm_out){
            const mins=toMins(d.pm_out)-toMins(d.pm_in);
            const under=toMins(d.pm_out)<toMins(OFF_PM_OUT)?toMins(OFF_PM_OUT)-toMins(d.pm_out):0;
            const over=toMins(d.pm_out)>toMins(OFF_PM_OUT)?toMins(d.pm_out)-toMins(OFF_PM_OUT):0;
            pmEl.innerHTML='<span class="badge-hours">'+fmtMins(mins)+'</span>';
            if(under)pmEl.innerHTML+='<span class="badge-under">-'+fmtMins(under)+'</span>';
            if(over)pmEl.innerHTML+='<span class="badge-over">+'+fmtMins(over)+'</span>';
        }
    }
}

// â”€â”€â”€ QUICK ACTIONS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function markAllPresent(){
    attendanceData.forEach(emp=>{
        DAYS.forEach(day=>{
            emp.attendance[day]={am_in:OFF_AM_IN,am_out:OFF_AM_OUT,pm_in:OFF_PM_IN,pm_out:OFF_PM_OUT};
            ['am_in','am_out','pm_in','pm_out'].forEach(k=>{
                const inp=document.getElementById('time_'+emp.id+'_'+day+'_'+k);
                if(inp){inp.value=emp.attendance[day][k];applyInputStyle(inp,k,inp.value);}
            });
            refreshDayMetrics(emp.id,day);
        });
    });
    updateStatistics();
    showNotification('Official times applied to all employees.','success');
}
function clearAllTimes(){
    attendanceData.forEach(emp=>{
        DAYS.forEach(day=>{
            emp.attendance[day]={am_in:'',am_out:'',pm_in:'',pm_out:''};
            ['am_in','am_out','pm_in','pm_out'].forEach(k=>{
                const inp=document.getElementById('time_'+emp.id+'_'+day+'_'+k);
                if(inp){inp.value='';applyInputStyle(inp,k,'');}
            });
            refreshDayMetrics(emp.id,day);
        });
    });
    updateStatistics();
    showNotification('All time entries cleared.','warning');
}

// â”€â”€â”€ SAVE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function saveAttendance(event){
    event.preventDefault();
    if(!selectedCourse){showNotification('Please select a course first.','error');return;}
    if(!attendanceData.length){showNotification('No attendance data loaded.','error');return;}
    const hasAny=attendanceData.some(emp=>DAYS.some(day=>{const d=emp.attendance[day];return d&&(d.am_in||d.am_out||d.pm_in||d.pm_out);}));
    if(!hasAny){
        Swal.fire({icon:'warning',title:'No times entered',text:'No time entries found. Save anyway?',showCancelButton:true,confirmButtonText:'Yes, save'})
            .then(r=>{if(r.isConfirmed)performSaveAttendance();});
        return;
    }
    performSaveAttendance();
}
function performSaveAttendance(){
    const som=getWeekMonday(currentDate);
    const saveData={
        course:selectedCourse.toUpperCase(),
        week_start:formatLocalDate(som),
        attendance_data:attendanceData.map(emp=>({
            id:emp.id,employee_name:emp.employee_name,name:emp.employee_name,
            designation:emp.designation,employee_type:emp.employee_type,type:emp.employee_type,
            attendance:emp.attendance
        }))
    };
    const btn=document.getElementById('save-attendance-btn'),orig=btn.innerHTML;
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving...';
    btn.disabled=true;
    const csrfMeta=document.querySelector('meta[name="csrf-token"]');
    const csrf=csrfMeta?csrfMeta.getAttribute('content'):'';
    fetch('/attendance/api/save-attendance',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        body:JSON.stringify(saveData)
    })
    .then(r=>{if(r.status===401){redirect401();return null;}if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(data=>{
        if(!data)return;
        if(data.success){
            showNotification(data.message||'Attendance saved!','success');
            setTimeout(()=>{fetchAttendanceData();loadCourseCounts();},500);
            fetch('/attendance/api/save-attendance-history',{
                method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify(saveData)
            }).catch(e=>console.warn('History save failed:',e));
        } else {
            showNotification(data.message||'Failed to save.','error');
        }
    })
    .catch(err=>showNotification(err.message||'Failed to save.','error'))
    .finally(()=>{btn.innerHTML=orig;btn.disabled=false;});
}

// â”€â”€â”€ STATISTICS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function updateStatistics(){
    const total=attendanceData.length;
    document.getElementById('total-employees').textContent=total;
    if(!total){
        ['present-today','absent-today','avg-hours','attendance-rate'].forEach(id=>document.getElementById(id).textContent='0');
        return;
    }
    let dp=0,da=0,tm=0;
    attendanceData.forEach(emp=>{
        DAYS.forEach(day=>{
            const d=emp.attendance[day]||{};
            if(d.am_in||d.am_out||d.pm_in||d.pm_out){dp++;tm+=calcMetrics(d.am_in,d.am_out,d.pm_in,d.pm_out).worked;}
            else da++;
        });
    });
    document.getElementById('present-today').textContent=dp;
    document.getElementById('absent-today').textContent=da;
    document.getElementById('avg-hours').textContent=(dp>0?(tm/dp/60).toFixed(1):0)+'h';
    document.getElementById('attendance-rate').textContent=Math.round(dp/(total*6)*100)+'%';
}

// â”€â”€â”€ SELECTION â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function toggleSelectAll(){
    const all=document.getElementById('select-all-checkbox').checked;
    document.querySelectorAll('.employee-checkbox').forEach(c=>{c.checked=all;c.closest('tr').classList.toggle('selected-row',all);});
    updateBulkActionsUI();
}
function updateBulkActionsUI(){
    const sel=document.querySelectorAll('.employee-checkbox:checked').length;
    document.getElementById('selected-count').textContent=sel;
    document.getElementById('bulk-actions').style.display=sel>0?'flex':'none';
}
function clearSelection(){
    document.querySelectorAll('.employee-checkbox').forEach(c=>{c.checked=false;c.closest('tr').classList.remove('selected-row');});
    document.getElementById('select-all-checkbox').checked=false;
    updateBulkActionsUI();
}
function bulkDeleteSelected(){
    const sel=[...document.querySelectorAll('.employee-checkbox:checked')].map(c=>c.dataset.empId);
    if(!sel.length)return;
    Swal.fire({icon:'warning',title:'Delete '+sel.length+' record(s)?',text:'Removes attendance for this week.',
        showCancelButton:true,confirmButtonColor:'#dc3545',confirmButtonText:'Delete'})
    .then(r=>{
        if(!r.isConfirmed)return;
        const csrfMeta=document.querySelector('meta[name="csrf-token"]');
        const csrf=csrfMeta?csrfMeta.getAttribute('content'):'';
        fetch('/attendance/api/bulk-delete-attendance',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:JSON.stringify({course:selectedCourse.toUpperCase(),employee_ids:sel})
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.success){showNotification(data.message,'success');fetchAttendanceData();}
            else showNotification(data.message||'Delete failed.','error');
        })
        .catch(()=>showNotification('Delete request failed.','error'));
    });
}

// â”€â”€â”€ EXPORT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function exportAttendance(){
    if(!attendanceData.length){showNotification('No data to export.','error');return;}
    const rows=[['Employee Name','Designation','Type','Day','AM In','AM Out','PM In','PM Out','Total Hours','Lateness (min)','Undertime (min)','Overtime (min)']];
    attendanceData.forEach(emp=>{
        DAYS.forEach(day=>{
            const d=emp.attendance[day]||{},m=calcMetrics(d.am_in,d.am_out,d.pm_in,d.pm_out);
            rows.push([emp.employee_name,emp.designation,emp.employee_type,
                day.charAt(0).toUpperCase()+day.slice(1),
                d.am_in||'',d.am_out||'',d.pm_in||'',d.pm_out||'',
                (m.worked/60).toFixed(2),m.lateness,m.undertime,m.overtime]);
        });
    });
    const csv=rows.map(r=>r.map(v=>'"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
    const blob=new Blob([csv],{type:'text/csv'}),url=URL.createObjectURL(blob),a=document.createElement('a');
    a.href=url;
    a.download='attendance_'+selectedCourse+'_'+formatLocalDate(getWeekMonday(currentDate))+'.csv';
    a.click();
    URL.revokeObjectURL(url);
    showNotification('CSV export downloaded.','success');
}

// â”€â”€â”€ NOTIFICATIONS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showNotification(message,type){
    type=type||'success';
    document.querySelectorAll('.notification').forEach(n=>n.remove());
    const div=document.createElement('div');
    div.className='notification '+type;
    div.innerHTML='<div class="notification-content"><span>'+message+'</span><button class="notification-close" onclick="this.closest(\'.notification\').remove()">\u00D7</button></div>';
    document.body.appendChild(div);
    setTimeout(()=>{if(div.parentNode)div.remove();},5000);
}
</script>
</body>
</html>
