{{--
  ╔══════════════════════════════════════════════════════════════════════╗
  ║  MCC EMPLOYEE PORTAL — EVALUATION FORM                              ║
  ║  File: resources/views/employee/evaluation.blade.php                ║
  ║                                                                      ║
  ║  ROUTES TO ADD in web.php (inside employee group):                  ║
  ║    Route::get('/evaluation', [EvaluationController::class,          ║
  ║      'showEmployeeForm'])->name('employee.evaluation.form');         ║
  ║    Route::post('/evaluation', [EvaluationController::class,         ║
  ║      'storeEvaluation'])->name('employee.evaluation.store');         ║
  ║                                                                      ║
  ║  CONTROLLER — EvaluationController::showEmployeeForm()              ║
  ║    $alreadySubmitted = \App\Models\Evaluation::where(               ║
  ║      'user_id', auth()->id())->exists();                             ║
  ║    return view('employee.evaluation', compact('employee',            ║
  ║      'alreadySubmitted'));                                           ║
  ║                                                                      ║
  ║  DB MIGRATION UPDATE — add user_id to evaluations table:            ║
  ║    $table->unsignedBigInteger('user_id')->nullable()->after('id');   ║
  ║    $table->foreign('user_id')->references('id')->on('users');        ║
  ╚══════════════════════════════════════════════════════════════════════╝
--}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>System Evaluation — MCC Employee Portal</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

  <script>
    (function(){
      const t = localStorage.getItem('mcc-theme') || 'light';
      document.documentElement.dataset.theme = t;
    })();
  </script>

  <style>
  /* ══════════════════════════════════════════════
     DESIGN TOKENS — mirrors dashboard-v2 exactly
  ══════════════════════════════════════════════ */
  :root {
    --brand:        #2563eb;
    --brand-dark:   #1e40af;
    --brand-light:  #eff6ff;
    --brand-mid:    #dbeafe;
    --accent:       #10b981;
    --warn:         #f59e0b;
    --danger:       #ef4444;
    --purple:       #7c3aed;

    --sb-w: 228px;
    --tb-h: 56px;

    --bg:           #f0f4f8;
    --bg-2:         #e8edf4;
    --card:         #ffffff;

    --text:         #0d1526;
    --text-2:       #44546a;
    --text-3:       #8595a8;
    --text-inv:     #ffffff;

    --border:       #e0e7ef;

    --sb-bg-1:      #0a1628;
    --sb-bg-2:      #112566;
    --sb-bg-3:      #1a52d0;
    --sb-link-hover: rgba(255,255,255,.09);
    --sb-link-active: rgba(255,255,255,.16);
    --sb-text:      rgba(255,255,255,.55);
    --sb-text-hi:   rgba(255,255,255,.92);
    --sb-label:     rgba(255,255,255,.22);
    --sb-border:    rgba(255,255,255,.07);

    --sh-sm: 0 2px 8px rgba(13,21,38,.07), 0 1px 3px rgba(13,21,38,.05);
    --sh-md: 0 6px 24px rgba(13,21,38,.10), 0 2px 8px rgba(13,21,38,.06);

    --r-sm: 8px;
    --r-md: 12px;
    --r-lg: 16px;
    --ease: cubic-bezier(.4,0,.2,1);
    --t: all .18s var(--ease);
  }

  [data-theme="dark"] {
    --bg:      #0d1117; --bg-2: #111823;
    --card:    #161d2b;
    --text:    #e8edf5; --text-2: #8fa3be; --text-3: #4a6080;
    --border:  #1e2d42;
    --brand-light: rgba(37,99,235,.12);
    --brand-mid:   rgba(37,99,235,.18);
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; transition: background .3s, color .3s; }
  h1,h2,h3,h4,h5,h6 { font-family: 'Sora', sans-serif; }

  /* ── App Shell ───────────────────────────────────────── */
  .app-shell { display: flex; min-height: 100vh; }

  /* ── Sidebar ─────────────────────────────────────────── */
  .sidebar {
    width: var(--sb-w); flex-shrink: 0;
    background: linear-gradient(180deg, var(--sb-bg-1) 0%, var(--sb-bg-2) 55%, var(--sb-bg-3) 100%);
    position: sticky; top: 0; height: 100vh;
    display: flex; flex-direction: column;
    overflow-y: auto; overflow-x: hidden;
    transition: transform .3s var(--ease);
    z-index: 1030;
  }
  .sb-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 1.1rem .9rem .9rem;
    border-bottom: 1px solid var(--sb-border);
  }
  .sb-brand-icon {
    width: 34px; height: 34px; border-radius: 9px;
    background: rgba(255,255,255,.12); display: grid; place-items: center; flex-shrink: 0;
  }
  .sb-brand-icon img { width: 22px; height: 22px; object-fit: contain; }
  .sb-brand-text { font-family: 'Sora', sans-serif; font-size: .82rem; font-weight: 800; color: #fff; line-height: 1.1; }
  .sb-brand-sub  { font-size: .65rem; color: var(--sb-text); }

  .sb-profile {
    padding: .7rem .9rem .6rem;
    border-bottom: 1px solid var(--sb-border);
  }
  .sb-profile-inner { display: flex; align-items: center; gap: 9px; }
  .sb-avatar {
    width: 34px; height: 34px; border-radius: 9px;
    background: rgba(255,255,255,.15); color: #fff;
    font-family: 'Sora', sans-serif; font-size: .75rem; font-weight: 700;
    display: grid; place-items: center; flex-shrink: 0; position: relative;
  }
  .sb-avatar-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); border: 2px solid var(--sb-bg-1); position: absolute; bottom: -2px; right: -2px; }
  .sb-name { font-size: .78rem; font-weight: 600; color: var(--sb-text-hi); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
  .sb-role { font-size: .65rem; color: var(--sb-text); margin-top: 1px; }

  .sb-nav { flex: 1; padding: .6rem .6rem; display: flex; flex-direction: column; gap: 1px; }
  .nav-label { font-size: .58rem; font-family: 'Sora', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 1.3px; color: var(--sb-label); padding: .7rem .4rem .25rem; }

  .sb-link {
    display: flex; align-items: center; gap: 9px;
    padding: .52rem .7rem; border-radius: var(--r-sm);
    font-size: .8rem; font-weight: 500; color: var(--sb-text);
    background: none; border: none; width: 100%; text-align: left;
    cursor: pointer; text-decoration: none;
    transition: var(--t);
  }
  .sb-link i { font-size: .88rem; width: 16px; flex-shrink: 0; }
  .sb-link:hover { background: var(--sb-link-hover); color: var(--sb-text-hi); padding-left: .95rem; }
  .sb-link.active { background: var(--sb-link-active); color: #fff; }
  .sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: .6rem; font-weight: 700; border-radius: 10px; padding: 1px 6px; }
  .sb-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); margin-left: auto; }

  .sb-footer { padding: .75rem .6rem; border-top: 1px solid var(--sb-border); }
  .logout-btn {
    display: flex; align-items: center; gap: 9px; width: 100%;
    padding: .52rem .7rem; border-radius: var(--r-sm);
    background: none; border: none; color: rgba(255,255,255,.45);
    font-size: .8rem; font-family: 'DM Sans', sans-serif; cursor: pointer;
    transition: var(--t);
  }
  .logout-btn:hover { background: rgba(239,68,68,.15); color: #fca5a5; }

  /* ── Main ────────────────────────────────────────────── */
  .main-content { flex: 1; min-width: 0; display: flex; flex-direction: column; }

  .topbar {
    height: var(--tb-h); background: var(--card);
    border-bottom: 1px solid var(--border);
    padding: 0 1.25rem; position: sticky; top: 0; z-index: 1020;
    display: flex; align-items: center; gap: 1rem;
    box-shadow: var(--sh-sm);
    transition: background .3s, border-color .3s;
  }
  .icon-btn {
    width: 36px; height: 36px; border-radius: var(--r-sm);
    background: none; border: 1px solid var(--border);
    display: grid; place-items: center; cursor: pointer;
    color: var(--text-2); transition: var(--t);
  }
  .icon-btn:hover { background: var(--bg); color: var(--brand); }
  .tb-title { font-family: 'Sora', sans-serif; font-size: .88rem; font-weight: 700; color: var(--text); }
  .tb-breadcrumb { font-size: .68rem; color: var(--text-3); display: flex; align-items: center; gap: 4px; margin-top: 1px; }

  .page-body { flex: 1; padding: 1.5rem 1.5rem; }

  /* ── Eval Card ───────────────────────────────────────── */
  .eval-card {
    background: var(--card); border-radius: var(--r-lg);
    border: 1px solid var(--border); box-shadow: var(--sh-sm);
    padding: 1.75rem 1.75rem;
    transition: background .3s, border-color .3s;
    max-width: 860px; margin: 0 auto;
  }

  /* ── Step Progress Bar ───────────────────────────────── */
  .steps-bar { display: flex; align-items: center; margin-bottom: 2rem; }
  .step-item { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
  .step-item:not(:last-child)::after {
    content: ''; position: absolute; top: 17px; left: 50%; width: 100%; height: 2px;
    background: var(--border); z-index: 0; transition: background .4s;
  }
  .step-item.completed:not(:last-child)::after { background: var(--accent); }
  .step-circle {
    width: 34px; height: 34px; border-radius: 50%;
    display: grid; place-items: center;
    font-family: 'Sora', sans-serif; font-weight: 700; font-size: .8rem;
    background: var(--border); color: var(--text-3);
    border: 2px solid var(--border); z-index: 1; transition: all .3s;
  }
  .step-item.active   .step-circle { background: var(--brand); color: #fff; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(37,99,235,.15); }
  .step-item.completed .step-circle { background: var(--accent); color: #fff; border-color: var(--accent); }
  .step-label { font-size: .67rem; font-weight: 600; color: var(--text-3); margin-top: 5px; text-align: center; white-space: nowrap; }
  .step-item.active .step-label { color: var(--brand); }
  .step-item.completed .step-label { color: var(--accent); }

  /* ── Eval Sections ───────────────────────────────────── */
  .eval-section { display: none; animation: fadeIn .3s var(--ease); }
  .eval-section.active { display: block; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

  .section-badge {
    display: inline-flex; align-items: center; gap: 7px;
    padding: .35rem .9rem; border-radius: 20px; font-size: .77rem;
    font-weight: 700; font-family: 'Sora', sans-serif; margin-bottom: .9rem;
  }

  /* ── Role Cards ──────────────────────────────────────── */
  .role-card {
    border: 2px solid var(--border); border-radius: var(--r-md);
    padding: 1.1rem; cursor: pointer; transition: var(--t);
    text-align: center; background: var(--bg);
  }
  .role-card:hover { border-color: var(--brand); background: var(--brand-light); }
  .role-card.selected { border-color: var(--brand); background: var(--brand-light); box-shadow: 0 0 0 4px rgba(37,99,235,.1); }
  .role-card i { font-size: 1.9rem; color: var(--text-3); transition: color .2s; }
  .role-card.selected i { color: var(--brand); }
  .role-card .role-name { font-family: 'Sora', sans-serif; font-weight: 700; font-size: .88rem; margin-top: 7px; color: var(--text); }
  .role-card .role-desc { font-size: .73rem; color: var(--text-3); margin-top: 2px; }

  /* ── Likert Scale ────────────────────────────────────── */
  .question-block { margin-bottom: 1.5rem; }
  .question-text { font-size: .9rem; font-weight: 600; color: var(--text); margin-bottom: .65rem; display: flex; align-items: flex-start; gap: 9px; }
  .question-num {
    min-width: 24px; height: 24px; border-radius: 6px;
    background: var(--brand-light); color: var(--brand);
    font-size: .75rem; font-weight: 700; font-family: 'Sora', sans-serif;
    display: grid; place-items: center; flex-shrink: 0; margin-top: 1px;
  }
  .likert-scale { display: flex; gap: .45rem; flex-wrap: wrap; }
  .likert-option { flex: 1; min-width: 52px; max-width: 78px; }
  .likert-option input[type="radio"] { display: none; }
  .likert-option label {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: .5rem .25rem; border-radius: var(--r-sm);
    border: 2px solid var(--border); cursor: pointer; transition: var(--t);
    text-align: center; font-size: .7rem; font-weight: 600; color: var(--text-3);
    background: var(--bg);
  }
  .likert-option label .score-num { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 800; }
  .likert-option input:checked + label {
    border-color: var(--color, var(--brand));
    background: color-mix(in srgb, var(--color, var(--brand)) 10%, var(--card));
    color: var(--color, var(--brand));
    box-shadow: 0 3px 10px color-mix(in srgb, var(--color, var(--brand)) 22%, transparent);
    transform: translateY(-2px);
  }
  .s1 { --color: #ef4444; } .s2 { --color: #f97316; }
  .s3 { --color: #f59e0b; } .s4 { --color: #3b82f6; } .s5 { --color: #10b981; }
  .likert-labels { display: flex; justify-content: space-between; font-size: .65rem; color: var(--text-3); margin-top: 4px; padding: 0 2px; }

  /* Validation */
  .question-block.invalid .likert-scale { animation: shake .35s; }
  .question-block.invalid .question-text { color: var(--danger); }
  @keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }

  /* ── Nav Buttons ─────────────────────────────────────── */
  .eval-nav { display: flex; justify-content: space-between; align-items: center; margin-top: 1.75rem; padding-top: 1.1rem; border-top: 1px solid var(--border); }
  .btn-eval-next {
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    color: #fff; border: none; border-radius: var(--r-sm);
    padding: .62rem 1.5rem; font-family: 'Sora', sans-serif;
    font-weight: 700; font-size: .85rem; transition: var(--t); cursor: pointer;
  }
  .btn-eval-next:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(37,99,235,.35); }
  .btn-eval-next:disabled { opacity: .45; cursor: not-allowed; transform: none; }
  .btn-eval-back {
    background: transparent; color: var(--text-2); border: 1px solid var(--border);
    border-radius: var(--r-sm); padding: .62rem 1.1rem;
    font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .85rem;
    transition: var(--t); cursor: pointer;
  }
  .btn-eval-back:hover { background: var(--bg); color: var(--text); }

  /* ── Gauge / Score Preview ───────────────────────────── */
  .score-gauge { text-align: center; padding: 1.25rem; border-radius: var(--r-md); border: 1px solid var(--border); background: var(--bg); }
  .gauge-ring {
    width: 76px; height: 76px; border-radius: 50%; margin: 0 auto 8px;
    display: grid; place-items: center;
    background: conic-gradient(var(--ring-color) var(--pct), var(--border) 0);
    position: relative;
  }
  .gauge-ring::before { content: ''; position: absolute; inset: 10px; border-radius: 50%; background: var(--card); }
  .gauge-ring .gauge-inner { position: relative; z-index: 1; font-family: 'Sora', sans-serif; font-size: .95rem; font-weight: 800; color: var(--text); }
  .gauge-val { font-family: 'Sora', sans-serif; font-size: 2.2rem; font-weight: 900; line-height: 1; }
  .gauge-label { font-size: .73rem; font-weight: 600; color: var(--text-3); margin-top: 3px; text-transform: uppercase; letter-spacing: .4px; }
  .interp-badge { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: .22rem .7rem; font-size: .72rem; font-weight: 700; }

  /* ── Textarea ────────────────────────────────────────── */
  textarea.form-control { border-radius: var(--r-sm); border-color: var(--border); font-family: 'DM Sans', sans-serif; resize: vertical; background: var(--bg); color: var(--text); transition: border-color .2s; }
  textarea.form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(37,99,235,.1); background: var(--card); }

  /* ── Thank You / Already Submitted ──────────────────── */
  .thank-you { text-align: center; padding: 3rem 1.5rem; }
  .check-circle {
    width: 80px; height: 80px; border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), #059669);
    display: grid; place-items: center; margin: 0 auto 1.5rem;
    animation: popIn .5s cubic-bezier(.34,1.56,.64,1);
    box-shadow: 0 8px 30px rgba(16,185,129,.35);
  }
  @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }

  .locked-icon {
    width: 80px; height: 80px; border-radius: 50%;
    background: linear-gradient(135deg, var(--warn), #d97706);
    display: grid; place-items: center; margin: 0 auto 1.5rem;
    box-shadow: 0 8px 30px rgba(245,158,11,.3);
  }

  /* ── Responsive ──────────────────────────────────────── */
  @media (max-width: 991px) {
    .sidebar { position: fixed; transform: translateX(-100%); height: 100vh; }
    .sidebar.show { transform: none; box-shadow: 8px 0 40px rgba(0,0,0,.3); }
    .main-content { margin-left: 0; }
  }
  @media (max-width: 576px) {
    .eval-card { padding: 1.25rem; }
    .likert-option label { padding: .4rem .2rem; }
    .step-label { font-size: .58rem; }
  }
  </style>
</head>
<body>
<div class="app-shell">

  <!-- ═══════════ SIDEBAR ═══════════ -->
  <aside class="sidebar" id="sidebar">
    <div class="sb-brand">
      <div class="sb-brand-icon">
        <img src="{{ asset('images/logo.png') }}" alt="MCC">
      </div>
      <div>
        <div class="sb-brand-text">MCC Portal</div>
        <div class="sb-brand-sub">Digital Payroll System</div>
      </div>
    </div>

    @php $initials = collect(explode(' ', $employee->name ?? 'E'))->map(fn($w) => strtoupper($w[0]))->take(2)->implode(''); @endphp
    <div class="sb-profile">
      <div class="sb-profile-inner">
        <div class="sb-avatar">
          {{ $initials }}
          <div class="sb-avatar-dot"></div>
        </div>
        <div style="min-width:0;">
          <div class="sb-name">{{ $employee->name ?? 'Employee' }}</div>
          <div class="sb-role">{{ $employee->position ?? ($employee->type ?? 'Employee') }}</div>
        </div>
      </div>
    </div>

    <nav class="sb-nav">
      <div class="nav-label">Main</div>
      <a class="sb-link" href="{{ route('employee.dashboard') }}">
        <i class="bi bi-grid-1x2-fill"></i> Overview
      </a>
      <a class="sb-link" href="{{ route('employee.attendance') }}">
        <i class="bi bi-calendar-check-fill"></i> Attendance
      </a>
      <a class="sb-link" href="{{ route('employee.timesheets') }}">
        <i class="bi bi-clock-history"></i> Timesheets
      </a>
      <div class="nav-label">Payroll</div>
      <a class="sb-link" href="{{ route('employee.payslips') }}">
        <i class="bi bi-receipt-cutoff"></i> Payslips
      </a>
      <div class="nav-label">Info</div>
      <a class="sb-link" href="{{ route('employee.announcements') }}">
        <i class="bi bi-megaphone-fill"></i> Announcements
      </a>
      <a class="sb-link" href="{{ route('employee.profile') }}">
        <i class="bi bi-person-circle"></i> My Profile
      </a>
      <div class="nav-label">Other</div>
      <a class="sb-link active" href="{{ route('employee.evaluation.form') }}">
        <i class="bi bi-clipboard-check-fill"></i> Evaluation
      </a>
      <button class="sb-link" onclick="Swal.fire({icon:'info',title:'Need Help?',html:'Contact HR or your System Administrator.',confirmButtonColor:\'#2563eb\'})">
        <i class="bi bi-question-circle"></i> Help & Support
      </button>
    </nav>

    <div class="sb-footer">
      <form action="{{ route('logout') }}" method="POST" id="logout-form">@csrf</form>
      <button class="logout-btn" onclick="document.getElementById('logout-form').submit()">
        <i class="bi bi-box-arrow-left"></i> Sign Out
      </button>
    </div>
  </aside>

  <!-- ═══════════ MAIN ═══════════ -->
  <div class="main-content">

    <header class="topbar">
      <button class="icon-btn d-lg-none" id="mobileMenuBtn" style="border:none;">
        <i class="bi bi-list" style="font-size:1.1rem;"></i>
      </button>
      <div>
        <div class="tb-title">System Evaluation</div>
        <div class="tb-breadcrumb">
          <i class="bi bi-house-fill" style="font-size:.58rem;"></i>
          <span>MCC Portal</span>
          <i class="bi bi-chevron-right" style="font-size:.52rem;"></i>
          <span>Evaluation</span>
        </div>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <button class="icon-btn" id="themeToggle" title="Toggle theme">
          <i class="bi bi-moon-stars-fill" id="themeIcon" style="font-size:.8rem;"></i>
        </button>
        <div class="d-none d-md-flex align-items-center gap-2 ps-2" style="border-left:1px solid var(--border);">
          <div class="sb-avatar" style="width:32px;height:32px;border-radius:8px;background:var(--brand-light);color:var(--brand);font-size:.72rem;">{{ $initials }}</div>
          <div class="tb-title" style="font-size:.78rem;">{{ $employee->name ?? 'Employee' }}</div>
        </div>
      </div>
    </header>

    <div class="page-body">

      <!-- Page Header -->
      <div class="mb-3">
        <h5 style="font-weight:800; margin-bottom:.2rem;">Usability Evaluation</h5>
        <p style="font-size:.84rem; color:var(--text-3); margin:0;">
          Evaluate the usability, efficiency, and satisfaction of the MCC Digital Payroll System V2.
        </p>
      </div>

      @if($alreadySubmitted ?? false)
      <!-- ════ ALREADY SUBMITTED STATE ════ -->
      <div class="eval-card">
        <div class="thank-you">
          <div class="locked-icon">
            <i class="bi bi-check2-circle" style="font-size:2.4rem; color:#fff;"></i>
          </div>
          <h5 style="font-weight:800; margin-bottom:.5rem;">You've Already Submitted!</h5>
          <p style="color:var(--text-2); font-size:.88rem; max-width:380px; margin:0 auto 1.75rem;">
            Your evaluation has been recorded. Only one response per account is allowed. Thank you for your valuable feedback!
          </p>
          <a href="{{ route('employee.dashboard') }}" class="btn-eval-next text-decoration-none" style="display:inline-flex; align-items:center; gap:6px;">
            <i class="bi bi-grid-1x2-fill"></i> Back to Dashboard
          </a>
        </div>
      </div>

      @else
      <!-- ════ EVALUATION FORM ════ -->
      <div class="eval-card">

        <!-- Step Bar -->
        <div class="steps-bar" id="stepsBar">
          @php
            $steps = [['1','Profile'],['2','Usability'],['3','Efficiency'],['4','Satisfaction'],['5','Feedback'],['✓','Submit']];
          @endphp
          @foreach($steps as $i => $step)
          <div class="step-item {{ $i === 0 ? 'active' : '' }}" id="step-{{ $i }}">
            <div class="step-circle">{{ $step[0] }}</div>
            <div class="step-label">{{ $step[1] }}</div>
          </div>
          @endforeach
        </div>

        <form id="evalForm" action="{{ route('employee.evaluation.store') }}" method="POST">
          @csrf

          <!-- ══ SEC 0 — Respondent Profile ══ -->
          <div class="eval-section active" id="sec-0">
            <div class="section-badge" style="background:var(--brand-light); color:var(--brand);">
              <i class="bi bi-person-badge"></i> Respondent Profile
            </div>
            <p style="font-size:.875rem; color:var(--text-2); margin-bottom:1.25rem;">
              Select your role at Madridejos Community College. This helps contextualize the evaluation data.
            </p>
            <div class="row g-3 mb-3">
              @php
                $roles = [
                  ['Administrator','shield-check','System admin / HR'],
                  ['Faculty','mortarboard-fill','Full / Part-time instructor'],
                  ['Staff','person-workspace','Administrative / office staff'],
                  ['Other','person','Utility / evaluator'],
                ];
              @endphp
              @foreach($roles as [$rName, $rIcon, $rDesc])
              <div class="col-md-3 col-sm-6">
                <div class="role-card" onclick="selectRole(this,'{{ $rName }}')" data-role="{{ $rName }}">
                  <i class="bi bi-{{ $rIcon }}"></i>
                  <div class="role-name">{{ $rName }}</div>
                  <div class="role-desc">{{ $rDesc }}</div>
                </div>
              </div>
              @endforeach
            </div>
            <input type="hidden" name="respondent_role" id="respondentRoleInput" required>
            <div class="eval-nav">
              <span></span>
              <button type="button" class="btn-eval-next" onclick="nextSection(0)" id="nextBtn0" disabled>
                Next: Usability <i class="bi bi-arrow-right ms-1"></i>
              </button>
            </div>
          </div>

          <!-- ══ SEC 1 — Usability ══ -->
          <div class="eval-section" id="sec-1">
            <div class="section-badge" style="background:rgba(99,102,241,.1); color:#6366f1;">
              <i class="bi bi-hand-index-thumb"></i> Section 1: Usability
            </div>
            <p style="font-size:.875rem; color:var(--text-2); margin-bottom:1.25rem;">
              Rate how <strong>easy to use</strong> the MCC Digital Payroll V2 system is.
              <strong>1</strong> = Strongly Disagree &nbsp;·&nbsp; <strong>5</strong> = Strongly Agree
            </p>

            @php
              $usabilityQuestions = [
                'usability_1' => 'The system interface is clean, organized, and easy to navigate.',
                'usability_2' => 'I can easily find the features I need (e.g., payslips, employee records, reports).',
                'usability_3' => 'The labels, buttons, and terminology used in the system are clear and understandable.',
                'usability_4' => 'I did not need additional training or help to use the system effectively.',
                'usability_5' => 'The real-time analytics dashboard displays information in a way that is easy to understand.',
              ];
            @endphp

            @foreach($usabilityQuestions as $name => $qText)
            <div class="question-block" id="qb-{{ $name }}">
              <div class="question-text">
                <span class="question-num">{{ $loop->iteration }}</span>
                {{ $qText }}
              </div>
              <div class="likert-scale">
                @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                <div class="likert-option {{ $cls }}">
                  <input type="radio" name="{{ $name }}" id="{{ $name }}_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-1','usabilityProgress',['usability_1','usability_2','usability_3','usability_4','usability_5'],1)">
                  <label for="{{ $name }}_{{ $val }}">
                    <span class="score-num">{{ $val }}</span>
                    <span>{{ $lbl }}</span>
                  </label>
                </div>
                @endforeach
              </div>
              <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
            </div>
            @endforeach

            <div class="eval-nav">
              <button type="button" class="btn-eval-back" onclick="prevSection(1)"><i class="bi bi-arrow-left me-1"></i>Back</button>
              <div class="d-flex align-items-center gap-2">
                <small style="color:var(--text-3);" id="usabilityProgress">0/5 answered</small>
                <button type="button" class="btn-eval-next" onclick="nextSection(1)" id="nextBtn1" disabled>
                  Next: Efficiency <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- ══ SEC 2 — Efficiency ══ -->
          <div class="eval-section" id="sec-2">
            <div class="section-badge" style="background:rgba(16,185,129,.1); color:#059669;">
              <i class="bi bi-lightning-charge"></i> Section 2: Efficiency
            </div>
            <p style="font-size:.875rem; color:var(--text-2); margin-bottom:1.25rem;">
              Rate how <strong>fast and accurate</strong> the system performs its functions.
              <strong>1</strong> = Strongly Disagree &nbsp;·&nbsp; <strong>5</strong> = Strongly Agree
            </p>

            @php
              $efficiencyQuestions = [
                'eff_1' => 'The system processes payroll computations quickly and without errors.',
                'eff_2' => 'Generating payslips and sending them to employees is fast and reliable.',
                'eff_3' => 'The attendance monitoring feature accurately captures and reflects employee check-ins.',
                'eff_4' => 'The real-time analytics update promptly and reflect current data without delay.',
                'eff_5' => 'The system reduces the time I spend on manual payroll tasks compared to the previous method.',
              ];
            @endphp

            @foreach($efficiencyQuestions as $name => $qText)
            <div class="question-block" id="qb-{{ $name }}">
              <div class="question-text">
                <span class="question-num">{{ $loop->iteration }}</span>
                {{ $qText }}
              </div>
              <div class="likert-scale">
                @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                <div class="likert-option {{ $cls }}">
                  <input type="radio" name="{{ $name }}" id="{{ $name }}_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-2','efficiencyProgress',['eff_1','eff_2','eff_3','eff_4','eff_5'],2)">
                  <label for="{{ $name }}_{{ $val }}">
                    <span class="score-num">{{ $val }}</span>
                    <span>{{ $lbl }}</span>
                  </label>
                </div>
                @endforeach
              </div>
              <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
            </div>
            @endforeach

            <div class="eval-nav">
              <button type="button" class="btn-eval-back" onclick="prevSection(2)"><i class="bi bi-arrow-left me-1"></i>Back</button>
              <div class="d-flex align-items-center gap-2">
                <small style="color:var(--text-3);" id="efficiencyProgress">0/5 answered</small>
                <button type="button" class="btn-eval-next" onclick="nextSection(2)" id="nextBtn2" disabled>
                  Next: Satisfaction <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- ══ SEC 3 — Satisfaction ══ -->
          <div class="eval-section" id="sec-3">
            <div class="section-badge" style="background:rgba(245,158,11,.1); color:#b45309;">
              <i class="bi bi-emoji-smile"></i> Section 3: User Satisfaction
            </div>
            <p style="font-size:.875rem; color:var(--text-2); margin-bottom:1.25rem;">
              Rate your overall <strong>satisfaction</strong> using the MCC Digital Payroll V2 system.
              <strong>1</strong> = Strongly Disagree &nbsp;·&nbsp; <strong>5</strong> = Strongly Agree
            </p>

            @php
              $satisfactionQuestions = [
                'sat_1' => 'Overall, I am satisfied with the MCC Digital Payroll V2 system.',
                'sat_2' => 'The system significantly improves the payroll workflow at Madridejos Community College.',
                'sat_3' => 'The real-time analytics feature (employee stats, attendance chart, payroll chart) adds meaningful value to my work.',
                'sat_4' => 'I would recommend this system to other educational institutions.',
                'sat_5' => 'The V2 system is a clear and noticeable improvement over the previous (V1) version.',
              ];
            @endphp

            @foreach($satisfactionQuestions as $name => $qText)
            <div class="question-block" id="qb-{{ $name }}">
              <div class="question-text">
                <span class="question-num">{{ $loop->iteration }}</span>
                {{ $qText }}
              </div>
              <div class="likert-scale">
                @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                <div class="likert-option {{ $cls }}">
                  <input type="radio" name="{{ $name }}" id="{{ $name }}_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-3','satisfactionProgress',['sat_1','sat_2','sat_3','sat_4','sat_5'],3)">
                  <label for="{{ $name }}_{{ $val }}">
                    <span class="score-num">{{ $val }}</span>
                    <span>{{ $lbl }}</span>
                  </label>
                </div>
                @endforeach
              </div>
              <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
            </div>
            @endforeach

            <div class="eval-nav">
              <button type="button" class="btn-eval-back" onclick="prevSection(3)"><i class="bi bi-arrow-left me-1"></i>Back</button>
              <div class="d-flex align-items-center gap-2">
                <small style="color:var(--text-3);" id="satisfactionProgress">0/5 answered</small>
                <button type="button" class="btn-eval-next" onclick="nextSection(3)" id="nextBtn3" disabled>
                  Next: Feedback <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- ══ SEC 4 — Open Feedback ══ -->
          <div class="eval-section" id="sec-4">
            <div class="section-badge" style="background:rgba(239,68,68,.1); color:#dc2626;">
              <i class="bi bi-chat-left-text"></i> Section 4: Open-Ended Feedback
            </div>
            <p style="font-size:.875rem; color:var(--text-2); margin-bottom:1.25rem;">
              Share your thoughts, suggestions, and observations. All fields are optional.
            </p>

            <div class="mb-4">
              <label class="fw-semibold mb-2 d-block" style="font-size:.88rem; color:var(--text);">
                <span class="question-num me-2" style="display:inline-grid;">1</span>
                What features of the V2 system do you find most useful?
              </label>
              <textarea name="feedback_useful" class="form-control" rows="3" placeholder="e.g., Real-time charts, employee analytics, faster payslip generation…"></textarea>
            </div>

            <div class="mb-4">
              <label class="fw-semibold mb-2 d-block" style="font-size:.88rem; color:var(--text);">
                <span class="question-num me-2" style="display:inline-grid;">2</span>
                What problems or difficulties did you encounter while using the system?
              </label>
              <textarea name="feedback_problems" class="form-control" rows="3" placeholder="Describe any issues, confusing parts, or things that need improvement…"></textarea>
            </div>

            <div class="mb-4">
              <label class="fw-semibold mb-2 d-block" style="font-size:.88rem; color:var(--text);">
                <span class="question-num me-2" style="display:inline-grid;">3</span>
                What additional features or improvements would you suggest for the next version?
              </label>
              <textarea name="feedback_suggestions" class="form-control" rows="3" placeholder="e.g., Mobile app, biometric integration, automatic deduction calculator…"></textarea>
            </div>

            <div class="eval-nav">
              <button type="button" class="btn-eval-back" onclick="prevSection(4)"><i class="bi bi-arrow-left me-1"></i>Back</button>
              <button type="button" class="btn-eval-next" onclick="nextSection(4)">
                Preview & Submit <i class="bi bi-eye ms-1"></i>
              </button>
            </div>
          </div>

          <!-- ══ SEC 5 — Review & Submit ══ -->
          <div class="eval-section" id="sec-5">
            <div class="section-badge" style="background:var(--brand-light); color:var(--brand);">
              <i class="bi bi-send-check"></i> Review & Submit
            </div>
            <p style="font-size:.875rem; color:var(--text-2); margin-bottom:1.25rem;">
              Your computed scores are shown below. Review them before submitting — this cannot be edited after submission.
            </p>

            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <div class="score-gauge">
                  <div class="gauge-ring" id="gaugeUsability" style="--ring-color:#6366f1; --pct:0%;">
                    <span class="gauge-inner">—</span>
                  </div>
                  <div class="gauge-val" id="previewUsability" style="color:#6366f1;">—</div>
                  <div class="gauge-label">Usability</div>
                  <div class="mt-2" id="usabilityInterp"></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="score-gauge">
                  <div class="gauge-ring" id="gaugeEfficiency" style="--ring-color:#10b981; --pct:0%;">
                    <span class="gauge-inner">—</span>
                  </div>
                  <div class="gauge-val" id="previewEfficiency" style="color:#059669;">—</div>
                  <div class="gauge-label">Efficiency</div>
                  <div class="mt-2" id="efficiencyInterp"></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="score-gauge">
                  <div class="gauge-ring" id="gaugeSatisfaction" style="--ring-color:#f59e0b; --pct:0%;">
                    <span class="gauge-inner">—</span>
                  </div>
                  <div class="gauge-val" id="previewSatisfaction" style="color:#b45309;">—</div>
                  <div class="gauge-label">Satisfaction</div>
                  <div class="mt-2" id="satisfactionInterp"></div>
                </div>
              </div>
            </div>

            <!-- Overall Banner -->
            <div class="p-4 mb-4 text-center" style="background:linear-gradient(135deg,var(--brand),var(--brand-dark));border-radius:var(--r-md);color:#fff;">
              <div style="font-size:.78rem;opacity:.75;font-weight:600;letter-spacing:.5px;text-transform:uppercase;">Overall Score</div>
              <div id="overallScore" style="font-family:'Sora',sans-serif;font-size:3rem;font-weight:900;line-height:1.1;">—</div>
              <div style="font-size:.85rem;opacity:.8;font-weight:600;">out of 5.00</div>
              <div id="overallVerdict" class="mt-1" style="font-size:.78rem;opacity:.7;"></div>
            </div>

            <!-- Hidden fields -->
            <input type="hidden" name="avg_usability"    id="hiddenUsability">
            <input type="hidden" name="avg_efficiency"   id="hiddenEfficiency">
            <input type="hidden" name="avg_satisfaction" id="hiddenSatisfaction">
            <input type="hidden" name="overall_avg"      id="hiddenOverall">

            <div class="eval-nav">
              <button type="button" class="btn-eval-back" onclick="prevSection(5)"><i class="bi bi-arrow-left me-1"></i>Back</button>
              <button type="submit" class="btn-eval-next" onclick="return confirmSubmit(event)">
                <i class="bi bi-send-fill me-1"></i> Submit Evaluation
              </button>
            </div>
          </div>

        </form>

        <!-- Thank You Screen -->
        <div id="thankYouScreen" class="thank-you" style="display:none;">
          <div class="check-circle">
            <i class="bi bi-check2-circle" style="font-size:2.4rem; color:#fff;"></i>
          </div>
          <h5 style="font-weight:800; margin-bottom:.5rem;">Thank You for Your Feedback!</h5>
          <p style="color:var(--text-2); font-size:.88rem; max-width:380px; margin:0 auto 1.75rem;">
            Your evaluation has been saved and will help improve the MCC Digital Payroll System V2. We appreciate your time!
          </p>
          <a href="{{ route('employee.dashboard') }}" class="btn-eval-next text-decoration-none" style="display:inline-flex;align-items:center;gap:6px;">
            <i class="bi bi-grid-1x2-fill"></i> Back to Dashboard
          </a>
        </div>

      </div><!-- /.eval-card -->
      @endif

    </div><!-- /.page-body -->
  </div><!-- /.main-content -->
</div><!-- /.app-shell -->

<!-- Mobile sidebar overlay -->
<div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1029;" onclick="closeSidebar()"></div>

<script>
let currentSection = 0;

// ── Section Navigation ──────────────────────────────────
function showSection(n) {
  document.querySelectorAll('.eval-section').forEach(s => s.classList.remove('active'));
  document.getElementById('sec-' + n).classList.add('active');

  document.querySelectorAll('.step-item').forEach((item, i) => {
    item.classList.remove('active','completed');
    if (i < n) item.classList.add('completed');
    if (i === n) item.classList.add('active');
  });

  currentSection = n;
  if (n === 5) computeScores();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextSection(n) {
  if (n === 0 && !document.getElementById('respondentRoleInput').value) {
    Swal.fire({ icon:'warning', title:'Select your role', text:'Please choose your role to continue.', confirmButtonColor:'#2563eb' });
    return;
  }
  const sectionNames = [[], ['usability_1','usability_2','usability_3','usability_4','usability_5'], ['eff_1','eff_2','eff_3','eff_4','eff_5'], ['sat_1','sat_2','sat_3','sat_4','sat_5']];
  if (sectionNames[n]) {
    const unanswered = sectionNames[n].filter(nm => !document.querySelector(`input[name="${nm}"]:checked`));
    if (unanswered.length > 0) {
      unanswered.forEach(nm => {
        const qb = document.getElementById('qb-' + nm);
        if (qb) { qb.classList.add('invalid'); setTimeout(() => qb.classList.remove('invalid'), 600); }
      });
      Swal.fire({ icon:'warning', title:'Incomplete Section', text:'Please answer all questions before proceeding.', confirmButtonColor:'#2563eb' });
      return;
    }
  }
  showSection(n + 1);
}

function prevSection(n) { showSection(n - 1); }

// ── Role Selection ──────────────────────────────────────
function selectRole(el, role) {
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('respondentRoleInput').value = role;
  document.getElementById('nextBtn0').disabled = false;
}

// ── Progress Counter ────────────────────────────────────
function onRatingChange(secId, progressId, names, sectionIdx) {
  const answered = names.filter(n => document.querySelector(`input[name="${n}"]:checked`)).length;
  const el = document.getElementById(progressId);
  if (el) el.textContent = answered + '/' + names.length + ' answered';
  const nextBtn = document.getElementById('nextBtn' + sectionIdx);
  if (nextBtn) nextBtn.disabled = answered < names.length;
}

// ── Score Computation ───────────────────────────────────
function getAvg(names) {
  const vals = names.map(n => { const el = document.querySelector(`input[name="${n}"]:checked`); return el ? parseFloat(el.value) : 0; });
  return vals.reduce((a,b) => a+b, 0) / vals.length;
}
function interpText(score) {
  if (score >= 4.20) return { label:'Excellent', color:'#16a34a', bg:'rgba(34,197,94,.1)' };
  if (score >= 3.40) return { label:'Good',      color:'#2563eb', bg:'rgba(37,99,235,.1)' };
  if (score >= 2.60) return { label:'Moderate',  color:'#b45309', bg:'rgba(245,158,11,.1)' };
  if (score >= 1.80) return { label:'Poor',      color:'#ea580c', bg:'rgba(249,115,22,.1)' };
  return                     { label:'Very Poor', color:'#dc2626', bg:'rgba(239,68,68,.1)'  };
}
function setGauge(id, score) {
  const pct = ((score / 5) * 100).toFixed(0) + '%';
  const el  = document.getElementById(id);
  if (el) { el.style.setProperty('--pct', pct); el.querySelector('.gauge-inner').textContent = score.toFixed(1); }
}
function computeScores() {
  const uScore = getAvg(['usability_1','usability_2','usability_3','usability_4','usability_5']);
  const eScore = getAvg(['eff_1','eff_2','eff_3','eff_4','eff_5']);
  const sScore = getAvg(['sat_1','sat_2','sat_3','sat_4','sat_5']);
  const overall = (uScore + eScore + sScore) / 3;

  document.getElementById('previewUsability').textContent    = uScore.toFixed(2);
  document.getElementById('previewEfficiency').textContent   = eScore.toFixed(2);
  document.getElementById('previewSatisfaction').textContent = sScore.toFixed(2);
  document.getElementById('overallScore').textContent        = overall.toFixed(2);

  setGauge('gaugeUsability',    uScore);
  setGauge('gaugeEfficiency',   eScore);
  setGauge('gaugeSatisfaction', sScore);

  const uI = interpText(uScore), eI = interpText(eScore), sI = interpText(sScore), oI = interpText(overall);
  document.getElementById('usabilityInterp').innerHTML    = `<span class="interp-badge" style="background:${uI.bg};color:${uI.color};">${uI.label}</span>`;
  document.getElementById('efficiencyInterp').innerHTML   = `<span class="interp-badge" style="background:${eI.bg};color:${eI.color};">${eI.label}</span>`;
  document.getElementById('satisfactionInterp').innerHTML = `<span class="interp-badge" style="background:${sI.bg};color:${sI.color};">${sI.label}</span>`;
  document.getElementById('overallVerdict').textContent   = 'Interpretation: ' + oI.label;

  document.getElementById('hiddenUsability').value    = uScore.toFixed(2);
  document.getElementById('hiddenEfficiency').value   = eScore.toFixed(2);
  document.getElementById('hiddenSatisfaction').value = sScore.toFixed(2);
  document.getElementById('hiddenOverall').value      = overall.toFixed(2);
}

// ── Submit Confirm ──────────────────────────────────────
function confirmSubmit(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Submit Evaluation?',
    html: 'Your responses will be saved permanently and <strong>cannot be edited</strong>. Are you sure?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    confirmButtonText: 'Yes, Submit',
    cancelButtonText: 'Review Again'
  }).then(r => {
    if (r.isConfirmed) {
      const form = document.getElementById('evalForm');
      form.submit();
    }
  });
  return false;
}

// ── Mobile Sidebar ──────────────────────────────────────
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('show');
  document.getElementById('sidebarOverlay').style.display = 'none';
}
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('mobileMenuBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (btn) btn.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
  });

  // Dark mode toggle
  const toggle = document.getElementById('themeToggle');
  const icon   = document.getElementById('themeIcon');
  if (toggle) toggle.addEventListener('click', () => {
    const isDark = document.documentElement.dataset.theme === 'dark';
    document.documentElement.dataset.theme = isDark ? 'light' : 'dark';
    localStorage.setItem('mcc-theme', isDark ? 'light' : 'dark');
    icon.className = isDark ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
  });
  const savedTheme = localStorage.getItem('mcc-theme') || 'light';
  if (icon) icon.className = savedTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';

  // Flash messages
  @if(session('eval_success'))
    Swal.fire({ icon:'success', title:'Submitted!', text:'Your evaluation has been recorded. Thank you!', confirmButtonColor:'#2563eb' });
  @endif
  @if(session('eval_error'))
    Swal.fire({ icon:'error', title:'Error', text:'{{ session("eval_error") }}', confirmButtonColor:'#2563eb' });
  @endif
});
</script>
</body>
</html>
