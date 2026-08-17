{{--
  ╔══════════════════════════════════════════════════════════════════╗
  ║  MCC DIGITAL PAYROLL V2 — EVALUATION QUESTIONNAIRE              ║
  ║  File: resources/views/admin/evaluation.blade.php               ║
  ║                                                                  ║
  ║  HOW TO USE:                                                     ║
  ║  1. Add route in web.php:                                        ║
  ║     Route::get('/admin/evaluation', [AdminController::class, 'evaluationPage'])->name('admin.evaluation');
  ║     Route::post('/admin/evaluation', [AdminController::class, 'storeEvaluation'])->name('admin.evaluation.store');
  ║     Route::get('/admin/evaluation/results', [AdminController::class, 'evaluationResults'])->name('admin.evaluation.results');
  ║                                                                  ║
  ║  2. Create evaluations table migration:                          ║
  ║     php artisan make:migration create_evaluations_table          ║
  ║     Schema::create('evaluations', function (Blueprint $table) {  ║
  ║       $table->id();                                              ║
  ║       $table->string('respondent_role');                         ║
  ║       $table->json('usability_scores');                          ║
  ║       $table->json('efficiency_scores');                         ║
  ║       $table->json('satisfaction_scores');                       ║
  ║       $table->text('feedback')->nullable();                      ║
  ║       $table->decimal('avg_usability',4,2)->default(0);          ║
  ║       $table->decimal('avg_efficiency',4,2)->default(0);         ║
  ║       $table->decimal('avg_satisfaction',4,2)->default(0);       ║
  ║       $table->decimal('overall_avg',4,2)->default(0);            ║
  ║       $table->timestamps();                                      ║
  ║     });                                                          ║
  ╚══════════════════════════════════════════════════════════════════╝
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MCC Digital Payroll V2 — Evaluation</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    /* ── Variables ───────────────────────────────────────── */
    :root {
      --brand:       #1a6fc4;
      --brand-dark:  #0f4e8f;
      --brand-light: #e8f2fc;
      --accent:      #00c9a7;
      --warn:        #f59e0b;
      --danger:      #ef4444;
      --success:     #22c55e;
      --sidebar-bg:  #0b1f3a;
      --bg:          #f0f4f9;
      --card:        #ffffff;
      --text:        #1a2332;
      --text-muted:  #6b7a90;
      --border:      #e4eaf3;
      --radius-sm:   10px;
      --radius-md:   16px;
      --radius-lg:   22px;
      --shadow-sm:   0 2px 8px rgba(15,40,80,0.07);
      --shadow-md:   0 8px 28px rgba(15,40,80,0.11);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
    }

    h1,h2,h3,h4,h5,h6 { font-family: 'Outfit', sans-serif; }

    /* ── Layout ──────────────────────────────────────────── */
    .app { display: flex; min-height: 100vh; }

    /* ── Sidebar (same as dashboard) ─────────────────────── */
    .sidebar {
      background: var(--sidebar-bg);
      width: 256px;
      flex-shrink: 0;
      position: sticky;
      top: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
      transition: transform .3s;
    }

    .sidebar-header { padding: 1.25rem 1.1rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.07); }

    .sidebar-logo { display: flex; align-items: center; gap: 10px; }
    .sidebar-logo img { width: 36px; height: 36px; border-radius: 8px; object-fit: contain; background: rgba(255,255,255,0.1); padding: 4px; }
    .sidebar-logo .brand-text { font-family: 'Outfit', sans-serif; font-size: .9rem; font-weight: 800; color: #fff; line-height: 1.1; }
    .sidebar-logo .brand-sub  { font-size: .68rem; color: rgba(255,255,255,0.5); }

    .sidebar-nav { flex: 1; padding: .75rem; display: flex; flex-direction: column; gap: 2px; }

    .nav-section-label {
      font-size: .65rem; font-family: 'Outfit', sans-serif; font-weight: 700;
      text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,0.3);
      padding: .75rem .5rem .3rem;
    }

    .sidebar .nav-link {
      color: rgba(255,255,255,.72); border-radius: var(--radius-sm); padding: .55rem .75rem;
      font-size: .875rem; font-weight: 500; display: flex; align-items: center; gap: 10px;
      transition: background .18s, color .18s, padding .18s;
    }
    .sidebar .nav-link i { font-size: 1rem; width: 18px; }
    .sidebar .nav-link:hover { background: rgba(255,255,255,0.08); color: #fff; padding-left: 1rem; }
    .sidebar .nav-link.active { background: rgba(26,111,196,0.9); color: #fff; box-shadow: 0 4px 14px rgba(26,111,196,.35); }

    .sidebar-btn {
      background: transparent; color: rgba(255,255,255,.72); border: none; width: 100%;
      text-align: left; padding: .55rem .75rem; border-radius: var(--radius-sm);
      font-size: .875rem; font-family: 'DM Sans', sans-serif; display: flex; align-items: center;
      gap: 10px; transition: all .18s; cursor: pointer;
    }
    .sidebar-btn:hover { background: rgba(255,255,255,0.08); color: #fff; padding-left: 1rem; }
    .sidebar-btn.dropdown-toggle::after { margin-left: auto; }

    .dropdown-menu { border-radius: var(--radius-sm); border: 1px solid var(--border); box-shadow: var(--shadow-md); padding: .4rem; }
    .dropdown-menu .dropdown-item { border-radius: 7px; padding: .5rem .9rem; font-size: .85rem; font-family: 'DM Sans', sans-serif; transition: background .15s; display: flex; align-items: center; gap: 8px; }
    .dropdown-menu .dropdown-item:hover { background: var(--brand-light); color: var(--brand-dark); }

    /* ── Topbar ──────────────────────────────────────────── */
    .content { flex: 1; min-width: 0; display: flex; flex-direction: column; }

    .topbar {
      height: 64px; background: var(--card); border-bottom: 1px solid var(--border);
      padding: 0 1.5rem; position: sticky; top: 0; z-index: 1020;
      display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm);
    }

    /* ── Page Body ───────────────────────────────────────── */
    .page-body { flex: 1; padding: 1.5rem 1.75rem; }

    /* ── Eval Card Shell ─────────────────────────────────── */
    .eval-card {
      background: var(--card); border-radius: var(--radius-md); border: 1px solid var(--border);
      box-shadow: var(--shadow-sm); padding: 1.5rem;
    }

    /* ── Progress Steps ──────────────────────────────────── */
    .steps-bar { display: flex; align-items: center; gap: 0; margin-bottom: 2rem; }

    .step-item {
      display: flex; flex-direction: column; align-items: center; flex: 1; position: relative;
    }

    .step-item:not(:last-child)::after {
      content: ''; position: absolute; top: 18px; left: 50%; width: 100%; height: 2px;
      background: var(--border); z-index: 0;
    }

    .step-item.completed:not(:last-child)::after { background: var(--brand); }

    .step-circle {
      width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center;
      font-family: 'Outfit', sans-serif; font-weight: 700; font-size: .85rem;
      background: var(--border); color: var(--text-muted); border: 2px solid var(--border);
      z-index: 1; transition: all .3s;
    }

    .step-item.active   .step-circle { background: var(--brand); color: #fff; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(26,111,196,.15); }
    .step-item.completed .step-circle { background: var(--accent); color: #fff; border-color: var(--accent); }

    .step-label { font-size: .72rem; font-weight: 600; color: var(--text-muted); margin-top: 6px; text-align: center; white-space: nowrap; }
    .step-item.active .step-label { color: var(--brand); }
    .step-item.completed .step-label { color: var(--accent); }

    /* ── Section Panel ───────────────────────────────────── */
    .eval-section { display: none; animation: fadeIn .4s ease; }
    .eval-section.active { display: block; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .section-badge {
      display: inline-flex; align-items: center; gap: 8px;
      padding: .4rem 1rem; border-radius: 20px; font-size: .8rem;
      font-weight: 700; font-family: 'Outfit', sans-serif; margin-bottom: 1rem;
    }

    /* ── Likert Scale ────────────────────────────────────── */
    .question-block { margin-bottom: 1.5rem; }

    .question-text {
      font-size: .92rem; font-weight: 600; color: var(--text);
      margin-bottom: .75rem; display: flex; align-items: flex-start; gap: 10px;
    }

    .question-num {
      min-width: 26px; height: 26px; border-radius: 7px; background: var(--brand-light);
      color: var(--brand); font-size: .8rem; font-weight: 800; font-family: 'Outfit', sans-serif;
      display: grid; place-items: center; flex-shrink: 0; margin-top: 1px;
    }

    .likert-scale { display: flex; gap: .5rem; flex-wrap: wrap; }

    .likert-option { flex: 1; min-width: 52px; max-width: 80px; }
    .likert-option input[type="radio"] { display: none; }

    .likert-option label {
      display: flex; flex-direction: column; align-items: center; gap: 5px;
      padding: .55rem .3rem; border-radius: var(--radius-sm);
      border: 2px solid var(--border); cursor: pointer;
      transition: all .2s; text-align: center;
      font-size: .72rem; font-weight: 600; color: var(--text-muted);
      background: var(--bg);
    }

    .likert-option label .score-num {
      font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800;
    }

    .likert-option input:checked + label {
      border-color: var(--color, var(--brand));
      background: color-mix(in srgb, var(--color, var(--brand)) 12%, white);
      color: var(--color, var(--brand));
      box-shadow: 0 4px 12px color-mix(in srgb, var(--color, var(--brand)) 25%, transparent);
      transform: translateY(-2px);
    }

    /* Score 1 = red, 2 = orange, 3 = yellow, 4 = blue, 5 = green */
    .s1 { --color: #ef4444; }
    .s2 { --color: #f97316; }
    .s3 { --color: #f59e0b; }
    .s4 { --color: #3b82f6; }
    .s5 { --color: #22c55e; }

    .likert-labels { display: flex; justify-content: space-between; font-size: .68rem; color: var(--text-muted); margin-top: 5px; padding: 0 2px; }

    /* ── Respondent role selector ────────────────────────── */
    .role-card {
      border: 2px solid var(--border); border-radius: var(--radius-md); padding: 1.1rem;
      cursor: pointer; transition: all .25s; text-align: center;
    }
    .role-card:hover { border-color: var(--brand); background: var(--brand-light); }
    .role-card.selected { border-color: var(--brand); background: var(--brand-light); box-shadow: 0 0 0 4px rgba(26,111,196,.12); }
    .role-card i { font-size: 2rem; color: var(--text-muted); transition: color .2s; }
    .role-card.selected i { color: var(--brand); }
    .role-card .role-name { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: .9rem; margin-top: 8px; }
    .role-card .role-desc { font-size: .75rem; color: var(--text-muted); margin-top: 3px; }

    /* ── Nav buttons ─────────────────────────────────────── */
    .eval-nav { display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }

    .btn-eval-next {
      background: linear-gradient(135deg, var(--brand), var(--brand-dark));
      color: #fff; border: none; border-radius: var(--radius-sm);
      padding: .65rem 1.75rem; font-family: 'Outfit', sans-serif;
      font-weight: 700; font-size: .9rem; transition: all .2s; cursor: pointer;
    }
    .btn-eval-next:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(26,111,196,.35); }
    .btn-eval-next:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    .btn-eval-back {
      background: transparent; color: var(--text-muted); border: 1px solid var(--border);
      border-radius: var(--radius-sm); padding: .65rem 1.25rem;
      font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: .88rem;
      transition: all .2s; cursor: pointer;
    }
    .btn-eval-back:hover { background: var(--bg); color: var(--text); }

    /* ── Results / Summary ───────────────────────────────── */
    .score-gauge {
      text-align: center; padding: 1.25rem; border-radius: var(--radius-md);
      border: 1px solid var(--border);
    }

    .gauge-val {
      font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 900; line-height: 1;
    }

    .gauge-label { font-size: .78rem; font-weight: 600; color: var(--text-muted); margin-top: 4px; text-transform: uppercase; letter-spacing: .5px; }

    .gauge-ring {
      width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 8px;
      display: grid; place-items: center;
      background: conic-gradient(var(--ring-color) var(--pct), var(--border) 0);
      position: relative;
    }

    .gauge-ring::before {
      content: ''; position: absolute; inset: 10px; border-radius: 50%; background: var(--card);
    }

    .gauge-ring .gauge-inner {
      position: relative; z-index: 1; font-family: 'Outfit', sans-serif;
      font-size: 1rem; font-weight: 800;
    }

    /* Interpretation badges */
    .interp-badge {
      display: inline-flex; align-items: center; gap: 5px;
      border-radius: 20px; padding: .25rem .75rem;
      font-size: .75rem; font-weight: 700;
    }

    /* ── Admin Results Panel ─────────────────────────────── */
    .result-row {
      display: flex; align-items: center; padding: .75rem 0;
      border-bottom: 1px solid var(--border);
    }
    .result-row:last-child { border-bottom: none; }

    .result-bar-wrap { flex: 1; background: var(--border); border-radius: 4px; height: 8px; margin: 0 1rem; overflow: hidden; }
    .result-bar { height: 100%; border-radius: 4px; transition: width 1.2s cubic-bezier(.16,1,.3,1); }

    /* ── Textarea ────────────────────────────────────────── */
    textarea.form-control {
      border-radius: var(--radius-sm); border-color: var(--border);
      font-family: 'DM Sans', sans-serif; resize: vertical;
      transition: border-color .2s;
    }
    textarea.form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(26,111,196,.1); }

    /* ── Thank you screen ────────────────────────────────── */
    .thank-you { text-align: center; padding: 3rem 1rem; }
    .thank-you .check-circle {
      width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #00e5c0);
      display: grid; place-items: center; margin: 0 auto 1.5rem;
      animation: popIn .5s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 991px) {
      .sidebar { position: fixed; transform: translateX(-100%); z-index: 1030; height: 100vh; }
      .sidebar.show { transform: none; box-shadow: 4px 0 30px rgba(0,0,0,.3); }
    }

    @media (max-width: 576px) {
      .likert-option label { padding: .4rem .2rem; }
      .likert-option label .score-num { font-size: .95rem; }
      .steps-bar { gap: 0; }
      .step-label { font-size: .6rem; }
    }

    /* ── Validation highlight ───────────────────────────── */
    .question-block.invalid .likert-scale { animation: shake .4s; }
    .question-block.invalid .question-text { color: var(--danger); }
    @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }

    /* ── Tab switcher ───────────────────────────────────── */
    .view-tabs { display: flex; gap: 4px; background: var(--bg); border-radius: var(--radius-sm); padding: 4px; border: 1px solid var(--border); }
    .view-tab { flex: 1; text-align: center; padding: .45rem .75rem; border-radius: 7px; font-size: .82rem; font-weight: 600; cursor: pointer; color: var(--text-muted); transition: all .2s; border: none; background: transparent; font-family: 'DM Sans', sans-serif; }
    .view-tab.active { background: var(--card); color: var(--brand); box-shadow: var(--shadow-sm); }
  </style>
</head>
<body>
<div class="app">

  <!-- ══════════ SIDEBAR ══════════ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <img src="{{ asset('images/logo.png') }}" alt="MCC">
        <div>
          <div class="brand-text">MCC Digital</div>
          <div class="brand-sub">Payroll System v2</div>
        </div>
      </div>
    </div>
    <div class="sidebar-nav">
      <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i>Dashboard</a>

      <div class="nav-section-label">Management</div>
      @include('partials.employees-menu')
      <div class="dropdown">
        <button class="sidebar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-building"></i><span>Department</span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('departments.index') }}"><i class="bi bi-gear"></i>Manage Departments</a></li>
        </ul>
      </div>
      <div class="nav-section-label">Records</div>
      <a href="{{ route('admin.history') }}" class="sidebar-btn text-decoration-none"><i class="bi bi-calendar-check"></i><span>History Log</span></a>
      <a href="{{ route('admin.payroll.history') }}" class="sidebar-btn text-decoration-none"><i class="bi bi-scissors"></i><span>Payroll History</span></a>
      <a href="{{ route('master.list') }}" class="sidebar-btn text-decoration-none"><i class="bi bi-list-ul"></i><span>Master List</span></a>

      <div class="nav-section-label">Evaluation</div>
      <a class="nav-link active" href="{{ route('admin.evaluation.results') }}"><i class="bi bi-clipboard-check"></i>Evaluation Form</a>
      <a class="nav-link" href="{{ route('admin.evaluation.results') }}"><i class="bi bi-bar-chart-line"></i>Evaluation Results</a>
    </div>
  </aside>

  <!-- ══════════ CONTENT ══════════ -->
  <div class="content">

    <!-- TOPBAR -->
    <header class="topbar">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" id="mobileMenuBtn">
        <i class="bi bi-list" style="font-size:1.3rem;"></i>
      </button>
      <div>
        <h5 class="mb-0" style="font-weight:800; font-size:1rem;">System Evaluation</h5>
        <small class="text-muted">MCC Digital Payroll V2 Usability Assessment</small>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
        <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger d-none d-md-inline-flex"
           onclick="event.preventDefault(); document.getElementById('logout-form-eval').submit();">
          <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
        <form id="logout-form-eval" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      </div>
    </header>

    <!-- PAGE BODY -->
    <div class="page-body">

      <!-- ─── VIEW SWITCHER (Admin sees both tabs) ─── -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <h4 class="mb-0" style="font-weight:800;">Evaluation Questionnaire</h4>
          <p class="text-muted mb-0" style="font-size:.84rem;">Evaluate usability, efficiency, and user satisfaction of the V2 system</p>
        </div>
        <div class="view-tabs d-none d-md-flex" style="width:260px;">
          <button class="view-tab active" id="tabFillForm" onclick="switchView('form')">
            <i class="bi bi-pencil-square me-1"></i>Fill Form
          </button>
          <button class="view-tab" id="tabResults" onclick="switchView('results')">
            <i class="bi bi-graph-up me-1"></i>Results
          </button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════
           EVALUATION FORM VIEW
      ═══════════════════════════════════════════════════════ -->
      <div id="viewForm">
        <div class="eval-card">

          <!-- Step Progress Bar -->
          <div class="steps-bar" id="stepsBar">
            <div class="step-item active" id="step-0">
              <div class="step-circle">1</div>
              <div class="step-label">Profile</div>
            </div>
            <div class="step-item" id="step-1">
              <div class="step-circle">2</div>
              <div class="step-label">Usability</div>
            </div>
            <div class="step-item" id="step-2">
              <div class="step-circle">3</div>
              <div class="step-label">Efficiency</div>
            </div>
            <div class="step-item" id="step-3">
              <div class="step-circle">4</div>
              <div class="step-label">Satisfaction</div>
            </div>
            <div class="step-item" id="step-4">
              <div class="step-circle">5</div>
              <div class="step-label">Feedback</div>
            </div>
            <div class="step-item" id="step-5">
              <div class="step-circle"><i class="bi bi-check2" style="font-size:.85rem;"></i></div>
              <div class="step-label">Submit</div>
            </div>
          </div>

          <form id="evalForm" action="{{ route('admin.evaluation.store') }}" method="POST">
            @csrf

            <!-- ══ SECTION 0 — Respondent Profile ══ -->
            <div class="eval-section active" id="sec-0">
              <div class="section-badge" style="background:rgba(26,111,196,.1); color:var(--brand);">
                <i class="bi bi-person-badge"></i> Respondent Profile
              </div>
              <p class="text-muted mb-4" style="font-size:.875rem;">
                Please select your role in the institution. Your response will help us better understand how different users experience the system.
              </p>

              <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                  <div class="role-card" onclick="selectRole(this, 'Administrator')" data-role="Administrator">
                    <i class="bi bi-shield-check"></i>
                    <div class="role-name">Administrator</div>
                    <div class="role-desc">System admin / HR</div>
                  </div>
                </div>
                <div class="col-md-3 col-sm-6">
                  <div class="role-card" onclick="selectRole(this, 'Faculty')" data-role="Faculty">
                    <i class="bi bi-mortarboard-fill"></i>
                    <div class="role-name">Faculty</div>
                    <div class="role-desc">Full / Part-time instructor</div>
                  </div>
                </div>
                <div class="col-md-3 col-sm-6">
                  <div class="role-card" onclick="selectRole(this, 'Staff')" data-role="Staff">
                    <i class="bi bi-person-workspace"></i>
                    <div class="role-name">Staff</div>
                    <div class="role-desc">Administrative / office staff</div>
                  </div>
                </div>
                <div class="col-md-3 col-sm-6">
                  <div class="role-card" onclick="selectRole(this, 'Other')" data-role="Other">
                    <i class="bi bi-person"></i>
                    <div class="role-name">Other</div>
                    <div class="role-desc">Utility / evaluator</div>
                  </div>
                </div>
              </div>
              <input type="hidden" name="respondent_role" id="respondentRoleInput" required>

              <div class="eval-nav">
                <span></span>
                <button type="button" class="btn-eval-next" onclick="nextSection(0)" id="nextBtn0" disabled>
                  Next: Usability <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>

            <!-- ══ SECTION 1 — Usability ══ -->
            <div class="eval-section" id="sec-1">
              <div class="section-badge" style="background:rgba(99,102,241,.1); color:#6366f1;">
                <i class="bi bi-hand-index-thumb"></i> Section 1: Usability
              </div>
              <p class="text-muted mb-4" style="font-size:.875rem;">
                Rate how <strong>easy to use</strong> the MCC Digital Payroll V2 system is. 
                <strong>1</strong> = Strongly Disagree &nbsp;·&nbsp; <strong>5</strong> = Strongly Agree
              </p>

              <!-- Q1 -->
              <div class="question-block" id="qb-u1">
                <div class="question-text">
                  <span class="question-num">1</span>
                  The system interface is clean, organized, and easy to navigate.
                </div>
                <div class="likert-scale">
                  @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                  <div class="likert-option {{ $cls }}">
                    <input type="radio" name="usability_1" id="u1_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-1')">
                    <label for="u1_{{ $val }}">
                      <span class="score-num">{{ $val }}</span>
                      <span>{{ $lbl }}</span>
                    </label>
                  </div>
                  @endforeach
                </div>
                <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
              </div>

              <!-- Q2 -->
              <div class="question-block" id="qb-u2">
                <div class="question-text">
                  <span class="question-num">2</span>
                  I can easily find the features I need (e.g., payslips, employee records, reports).
                </div>
                <div class="likert-scale">
                  @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                  <div class="likert-option {{ $cls }}">
                    <input type="radio" name="usability_2" id="u2_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-1')">
                    <label for="u2_{{ $val }}">
                      <span class="score-num">{{ $val }}</span>
                      <span>{{ $lbl }}</span>
                    </label>
                  </div>
                  @endforeach
                </div>
                <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
              </div>

              <!-- Q3 -->
              <div class="question-block" id="qb-u3">
                <div class="question-text">
                  <span class="question-num">3</span>
                  The labels, buttons, and terminology used in the system are clear and understandable.
                </div>
                <div class="likert-scale">
                  @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                  <div class="likert-option {{ $cls }}">
                    <input type="radio" name="usability_3" id="u3_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-1')">
                    <label for="u3_{{ $val }}">
                      <span class="score-num">{{ $val }}</span>
                      <span>{{ $lbl }}</span>
                    </label>
                  </div>
                  @endforeach
                </div>
                <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
              </div>

              <!-- Q4 -->
              <div class="question-block" id="qb-u4">
                <div class="question-text">
                  <span class="question-num">4</span>
                  I did not need additional training or help to use the system effectively.
                </div>
                <div class="likert-scale">
                  @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                  <div class="likert-option {{ $cls }}">
                    <input type="radio" name="usability_4" id="u4_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-1')">
                    <label for="u4_{{ $val }}">
                      <span class="score-num">{{ $val }}</span>
                      <span>{{ $lbl }}</span>
                    </label>
                  </div>
                  @endforeach
                </div>
                <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
              </div>

              <!-- Q5 -->
              <div class="question-block" id="qb-u5">
                <div class="question-text">
                  <span class="question-num">5</span>
                  The real-time analytics dashboard displays information in a way that is easy to understand.
                </div>
                <div class="likert-scale">
                  @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                  <div class="likert-option {{ $cls }}">
                    <input type="radio" name="usability_5" id="u5_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-1')">
                    <label for="u5_{{ $val }}">
                      <span class="score-num">{{ $val }}</span>
                      <span>{{ $lbl }}</span>
                    </label>
                  </div>
                  @endforeach
                </div>
                <div class="likert-labels"><span>Strongly Disagree</span><span>Strongly Agree</span></div>
              </div>

              <div class="eval-nav">
                <button type="button" class="btn-eval-back" onclick="prevSection(1)"><i class="bi bi-arrow-left me-1"></i>Back</button>
                <div class="d-flex align-items-center gap-2">
                  <small class="text-muted" id="usabilityProgress">0/5 answered</small>
                  <button type="button" class="btn-eval-next" onclick="nextSection(1)" id="nextBtn1" disabled>
                    Next: Efficiency <i class="bi bi-arrow-right ms-1"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- ══ SECTION 2 — Efficiency ══ -->
            <div class="eval-section" id="sec-2">
              <div class="section-badge" style="background:rgba(0,201,167,.1); color:#00a887;">
                <i class="bi bi-lightning-charge"></i> Section 2: Efficiency
              </div>
              <p class="text-muted mb-4" style="font-size:.875rem;">
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
              @php $num = $loop->iteration; @endphp
              <div class="question-block" id="qb-{{ $name }}">
                <div class="question-text">
                  <span class="question-num">{{ $num }}</span>
                  {{ $qText }}
                </div>
                <div class="likert-scale">
                  @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                  <div class="likert-option {{ $cls }}">
                    <input type="radio" name="{{ $name }}" id="{{ $name }}_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-2')">
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
                  <small class="text-muted" id="efficiencyProgress">0/5 answered</small>
                  <button type="button" class="btn-eval-next" onclick="nextSection(2)" id="nextBtn2" disabled>
                    Next: Satisfaction <i class="bi bi-arrow-right ms-1"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- ══ SECTION 3 — User Satisfaction ══ -->
            <div class="eval-section" id="sec-3">
              <div class="section-badge" style="background:rgba(245,158,11,.1); color:#b45309;">
                <i class="bi bi-emoji-smile"></i> Section 3: User Satisfaction
              </div>
              <p class="text-muted mb-4" style="font-size:.875rem;">
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
              @php $num = $loop->iteration; @endphp
              <div class="question-block" id="qb-{{ $name }}">
                <div class="question-text">
                  <span class="question-num">{{ $num }}</span>
                  {{ $qText }}
                </div>
                <div class="likert-scale">
                  @foreach([1=>['Strongly Disagree','s1'],2=>['Disagree','s2'],3=>['Neutral','s3'],4=>['Agree','s4'],5=>['Strongly Agree','s5']] as $val=>[$lbl,$cls])
                  <div class="likert-option {{ $cls }}">
                    <input type="radio" name="{{ $name }}" id="{{ $name }}_{{ $val }}" value="{{ $val }}" onchange="onRatingChange('sec-3')">
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
                  <small class="text-muted" id="satisfactionProgress">0/5 answered</small>
                  <button type="button" class="btn-eval-next" onclick="nextSection(3)" id="nextBtn3" disabled>
                    Next: Feedback <i class="bi bi-arrow-right ms-1"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- ══ SECTION 4 — Open Feedback ══ -->
            <div class="eval-section" id="sec-4">
              <div class="section-badge" style="background:rgba(239,68,68,.1); color:#dc2626;">
                <i class="bi bi-chat-left-text"></i> Section 4: Open-Ended Feedback
              </div>
              <p class="text-muted mb-4" style="font-size:.875rem;">
                Share your thoughts, suggestions, and observations about the MCC Digital Payroll V2 system. Your feedback is valuable for further improvements.
              </p>

              <div class="mb-4">
                <label class="fw-semibold mb-2 d-block" style="font-size:.9rem;">
                  <span class="question-num me-2" style="display:inline-grid;">1</span>
                  What features of the V2 system do you find most useful?
                </label>
                <textarea name="feedback_useful" class="form-control" rows="3" placeholder="e.g., Real-time charts, employee analytics, faster payslip generation…"></textarea>
              </div>

              <div class="mb-4">
                <label class="fw-semibold mb-2 d-block" style="font-size:.9rem;">
                  <span class="question-num me-2" style="display:inline-grid;">2</span>
                  What problems or difficulties did you encounter while using the system?
                </label>
                <textarea name="feedback_problems" class="form-control" rows="3" placeholder="Describe any issues, confusing parts, or things that need improvement…"></textarea>
              </div>

              <div class="mb-4">
                <label class="fw-semibold mb-2 d-block" style="font-size:.9rem;">
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

            <!-- ══ SECTION 5 — Review & Submit ══ -->
            <div class="eval-section" id="sec-5">
              <div class="section-badge" style="background:rgba(26,111,196,.1); color:var(--brand);">
                <i class="bi bi-send-check"></i> Review & Submit
              </div>
              <p class="text-muted mb-3" style="font-size:.875rem;">
                Your computed scores are shown below. Click <strong>Submit Evaluation</strong> to save your responses.
              </p>

              <!-- Score Summary Preview -->
              <div class="row g-3 mb-4" id="scoreSummaryRow">
                <div class="col-md-4">
                  <div class="score-gauge">
                    <div class="gauge-ring" id="gaugeUsability" style="--ring-color:#6366f1; --pct:0%;">
                      <span class="gauge-inner" id="gaugeUsabilityVal">—</span>
                    </div>
                    <div class="gauge-val" id="previewUsability" style="color:#6366f1;">—</div>
                    <div class="gauge-label">Usability</div>
                    <div class="mt-2" id="usabilityInterp"></div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="score-gauge">
                    <div class="gauge-ring" id="gaugeEfficiency" style="--ring-color:#00c9a7; --pct:0%;">
                      <span class="gauge-inner" id="gaugeEfficiencyVal">—</span>
                    </div>
                    <div class="gauge-val" id="previewEfficiency" style="color:#00a887;">—</div>
                    <div class="gauge-label">Efficiency</div>
                    <div class="mt-2" id="efficiencyInterp"></div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="score-gauge">
                    <div class="gauge-ring" id="gaugeSatisfaction" style="--ring-color:#f59e0b; --pct:0%;">
                      <span class="gauge-inner" id="gaugeSatisfactionVal">—</span>
                    </div>
                    <div class="gauge-val" id="previewSatisfaction" style="color:#b45309;">—</div>
                    <div class="gauge-label">Satisfaction</div>
                    <div class="mt-2" id="satisfactionInterp"></div>
                  </div>
                </div>
              </div>

              <!-- Overall score banner -->
              <div id="overallBanner" class="p-4 mb-4 text-center" style="background: linear-gradient(135deg, var(--brand), var(--brand-dark)); border-radius: var(--radius-md); color:#fff;">
                <div style="font-size:.85rem; opacity:.8; font-weight:600; letter-spacing:.5px; text-transform:uppercase;">Overall Score</div>
                <div id="overallScore" style="font-family:'Outfit',sans-serif; font-size:3rem; font-weight:900; line-height:1.1;">—</div>
                <div id="overallLabel" style="font-size:.9rem; opacity:.85; font-weight:600;">out of 5.00</div>
                <div id="overallVerdict" class="mt-2" style="font-size:.8rem; opacity:.75;"></div>
              </div>

              <!-- Hidden computed fields -->
              <input type="hidden" name="avg_usability"    id="hiddenUsability">
              <input type="hidden" name="avg_efficiency"   id="hiddenEfficiency">
              <input type="hidden" name="avg_satisfaction" id="hiddenSatisfaction">
              <input type="hidden" name="overall_avg"      id="hiddenOverall">

              <div class="eval-nav">
                <button type="button" class="btn-eval-back" onclick="prevSection(5)"><i class="bi bi-arrow-left me-1"></i>Back</button>
                <button type="submit" class="btn-eval-next" onclick="return confirmSubmit(event)">
                  <i class="bi bi-send-fill me-1"></i>Submit Evaluation
                </button>
              </div>
            </div>

          </form>

          <!-- ══ THANK YOU SCREEN ══ -->
          <div id="thankYouScreen" class="thank-you" style="display:none;">
            <div class="check-circle">
              <i class="bi bi-check2-circle" style="font-size:2.5rem; color:#fff;"></i>
            </div>
            <h4 style="font-weight:800; margin-bottom:.5rem;">Thank You for Your Feedback!</h4>
            <p class="text-muted mb-4">Your evaluation has been recorded and will help improve<br>the MCC Digital Payroll System.</p>
            <div class="d-flex justify-content-center gap-3">
              <a href="{{ route('admin.dashboard') }}" class="btn-eval-next text-decoration-none">
                <i class="bi bi-speedometer2 me-1"></i>Back to Dashboard
              </a>
              <a href="{{ route('admin.evaluation.results') }}" class="btn-eval-back text-decoration-none" style="display:inline-flex; align-items:center;">
                <i class="bi bi-bar-chart-line me-1"></i>View Results
              </a>
            </div>
          </div>

        </div><!-- /.eval-card -->
      </div><!-- /#viewForm -->


      <!-- ═══════════════════════════════════════════════════
           RESULTS VIEW (Admin Only)
      ═══════════════════════════════════════════════════════ -->
      <div id="viewResults" style="display:none;">

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
          @php
            // Replace with real DB queries in AdminController:
            // $responses    = \App\Models\Evaluation::count();
            // $avgUsability    = \App\Models\Evaluation::avg('avg_usability');
            // $avgEfficiency   = \App\Models\Evaluation::avg('avg_efficiency');
            // $avgSatisfaction = \App\Models\Evaluation::avg('avg_satisfaction');
            // $overallAvg      = \App\Models\Evaluation::avg('overall_avg');
            $responses       = $responses ?? 0;
            $avgUsability    = $avgUsability ?? 0;
            $avgEfficiency   = $avgEfficiency ?? 0;
            $avgSatisfaction = $avgSatisfaction ?? 0;
            $overallAvg      = $overallAvg ?? 0;
          @endphp

          <div class="col-md-3 col-sm-6">
            <div class="eval-card text-center">
              <div style="font-family:'Outfit',sans-serif; font-size:2.5rem; font-weight:900; color:var(--brand);">{{ $responses }}</div>
              <div style="font-size:.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px;">Total Responses</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="eval-card text-center" style="border-top: 3px solid #6366f1;">
              <div style="font-family:'Outfit',sans-serif; font-size:2.5rem; font-weight:900; color:#6366f1;">{{ number_format($avgUsability, 2) }}</div>
              <div style="font-size:.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px;">Avg Usability</div>
              <small style="color:var(--text-muted);">out of 5.00</small>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="eval-card text-center" style="border-top: 3px solid #00c9a7;">
              <div style="font-family:'Outfit',sans-serif; font-size:2.5rem; font-weight:900; color:#00a887;">{{ number_format($avgEfficiency, 2) }}</div>
              <div style="font-size:.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px;">Avg Efficiency</div>
              <small style="color:var(--text-muted);">out of 5.00</small>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="eval-card text-center" style="border-top: 3px solid #f59e0b;">
              <div style="font-family:'Outfit',sans-serif; font-size:2.5rem; font-weight:900; color:#b45309;">{{ number_format($avgSatisfaction, 2) }}</div>
              <div style="font-size:.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px;">Avg Satisfaction</div>
              <small style="color:var(--text-muted);">out of 5.00</small>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="row g-3 mb-4">
          <!-- Overall Score Radar -->
          <div class="col-md-4">
            <div class="eval-card" style="height:100%;">
              <div style="font-family:'Outfit',sans-serif; font-weight:700; font-size:.95rem; margin-bottom:1rem;">
                <i class="bi bi-pentagon me-2 text-primary"></i>Category Comparison
              </div>
              <canvas id="radarChart" style="max-height:260px;"></canvas>
            </div>
          </div>

          <!-- Response Distribution Bar -->
          <div class="col-md-4">
            <div class="eval-card" style="height:100%;">
              <div style="font-family:'Outfit',sans-serif; font-weight:700; font-size:.95rem; margin-bottom:1rem;">
                <i class="bi bi-bar-chart me-2 text-success"></i>Average by Category
              </div>
              <canvas id="categoryBarChart" style="max-height:260px;"></canvas>
            </div>
          </div>

          <!-- Respondent Role Breakdown -->
          <div class="col-md-4">
            <div class="eval-card" style="height:100%;">
              <div style="font-family:'Outfit',sans-serif; font-weight:700; font-size:.95rem; margin-bottom:1rem;">
                <i class="bi bi-pie-chart me-2 text-warning"></i>Respondents by Role
              </div>
              <canvas id="roleChart" style="max-height:260px;"></canvas>
              <div class="d-flex flex-wrap gap-2 mt-3">
                @php
                  $roleData = $roleData ?? ['Administrator'=>0,'Faculty'=>0,'Staff'=>0,'Other'=>0];
                @endphp
                @foreach($roleData as $role => $cnt)
                <div style="display:flex; align-items:center; gap:5px; font-size:.75rem; color:var(--text-muted);">
                  <div style="width:10px;height:10px;border-radius:3px;background:{{ ['Administrator'=>'#1a6fc4','Faculty'=>'#7c3aed','Staff'=>'#00c9a7','Other'=>'#f59e0b'][$role] ?? '#999' }};"></div>
                  {{ $role }}: {{ $cnt }}
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Score Table -->
        <div class="eval-card mb-4">
          <div style="font-family:'Outfit',sans-serif; font-weight:700; font-size:.95rem; margin-bottom:1.25rem;">
            <i class="bi bi-list-columns me-2 text-primary"></i>Average Score Per Question
          </div>

          <div style="margin-bottom:1rem;">
            <div style="font-size:.8rem; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:.5px; margin-bottom:.75rem;">Usability</div>
            @php
              $usabilityAvgs = $usabilityAvgs ?? ['U1'=>0,'U2'=>0,'U3'=>0,'U4'=>0,'U5'=>0];
              $usabilityLabels = [
                'U1'=>'Clean and organized interface',
                'U2'=>'Easy to find features',
                'U3'=>'Clear labels and terminology',
                'U4'=>'No extra training needed',
                'U5'=>'Analytics easy to understand',
              ];
            @endphp
            @foreach($usabilityAvgs as $key => $avg)
            <div class="result-row">
              <div style="min-width:200px; font-size:.85rem;">{{ $usabilityLabels[$key] ?? $key }}</div>
              <div class="result-bar-wrap">
                <div class="result-bar" style="background:#6366f1; width:{{ ($avg/5)*100 }}%;"></div>
              </div>
              <div style="min-width:40px; text-align:right; font-family:'Outfit',sans-serif; font-weight:700; color:#6366f1;">{{ number_format($avg,2) }}</div>
            </div>
            @endforeach
          </div>

          <div style="margin-bottom:1rem;">
            <div style="font-size:.8rem; font-weight:700; color:#00a887; text-transform:uppercase; letter-spacing:.5px; margin-bottom:.75rem;">Efficiency</div>
            @php
              $efficiencyAvgs = $efficiencyAvgs ?? ['E1'=>0,'E2'=>0,'E3'=>0,'E4'=>0,'E5'=>0];
              $efficiencyLabels = [
                'E1'=>'Fast payroll computation',
                'E2'=>'Reliable payslip generation',
                'E3'=>'Accurate attendance tracking',
                'E4'=>'Real-time analytics update',
                'E5'=>'Reduces manual workload',
              ];
            @endphp
            @foreach($efficiencyAvgs as $key => $avg)
            <div class="result-row">
              <div style="min-width:200px; font-size:.85rem;">{{ $efficiencyLabels[$key] ?? $key }}</div>
              <div class="result-bar-wrap">
                <div class="result-bar" style="background:#00c9a7; width:{{ ($avg/5)*100 }}%;"></div>
              </div>
              <div style="min-width:40px; text-align:right; font-family:'Outfit',sans-serif; font-weight:700; color:#00a887;">{{ number_format($avg,2) }}</div>
            </div>
            @endforeach
          </div>

          <div>
            <div style="font-size:.8rem; font-weight:700; color:#b45309; text-transform:uppercase; letter-spacing:.5px; margin-bottom:.75rem;">Satisfaction</div>
            @php
              $satisfactionAvgs = $satisfactionAvgs ?? ['S1'=>0,'S2'=>0,'S3'=>0,'S4'=>0,'S5'=>0];
              $satisfactionLabels = [
                'S1'=>'Overall satisfied with system',
                'S2'=>'Improves payroll workflow',
                'S3'=>'Analytics adds meaningful value',
                'S4'=>'Would recommend to others',
                'S5'=>'Clear improvement over V1',
              ];
            @endphp
            @foreach($satisfactionAvgs as $key => $avg)
            <div class="result-row">
              <div style="min-width:200px; font-size:.85rem;">{{ $satisfactionLabels[$key] ?? $key }}</div>
              <div class="result-bar-wrap">
                <div class="result-bar" style="background:#f59e0b; width:{{ ($avg/5)*100 }}%;"></div>
              </div>
              <div style="min-width:40px; text-align:right; font-family:'Outfit',sans-serif; font-weight:700; color:#b45309;">{{ number_format($avg,2) }}</div>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Likert Scale Legend -->
        <div class="eval-card mb-4">
          <div style="font-family:'Outfit',sans-serif; font-weight:700; font-size:.95rem; margin-bottom:1rem;">
            <i class="bi bi-info-circle me-2 text-primary"></i>Score Interpretation Guide
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:.84rem;">
              <thead>
                <tr style="background:var(--bg);">
                  <th style="font-weight:700; color:var(--text-muted);">Score Range</th>
                  <th style="font-weight:700; color:var(--text-muted);">Verbal Interpretation</th>
                  <th style="font-weight:700; color:var(--text-muted);">Meaning</th>
                </tr>
              </thead>
              <tbody>
                <tr><td><span class="interp-badge" style="background:rgba(239,68,68,.1); color:#dc2626;">1.00 – 1.79</span></td><td>Strongly Disagree</td><td>Very poor — needs immediate improvement</td></tr>
                <tr><td><span class="interp-badge" style="background:rgba(249,115,22,.1); color:#ea580c;">1.80 – 2.59</span></td><td>Disagree</td><td>Poor — significant issues present</td></tr>
                <tr><td><span class="interp-badge" style="background:rgba(245,158,11,.1); color:#b45309;">2.60 – 3.39</span></td><td>Neutral / Moderate</td><td>Acceptable — some improvements needed</td></tr>
                <tr><td><span class="interp-badge" style="background:rgba(59,130,246,.1); color:#2563eb;">3.40 – 4.19</span></td><td>Agree</td><td>Good — meets expectations</td></tr>
                <tr><td><span class="interp-badge" style="background:rgba(34,197,94,.1); color:#16a34a;">4.20 – 5.00</span></td><td>Strongly Agree</td><td>Excellent — exceeds expectations</td></tr>
              </tbody>
            </table>
          </div>
        </div>

      </div><!-- /#viewResults -->

    </div><!-- /.page-body -->
  </div><!-- /.content -->
</div><!-- /.app -->

<script>
// ── Step / Section Navigation ──────────────────────────────
let currentSection = 0;
const totalSections = 6;
const sectionAnswers = { 'sec-1': 0, 'sec-2': 0, 'sec-3': 0 };

function showSection(n) {
  document.querySelectorAll('.eval-section').forEach((s, i) => {
    s.classList.toggle('active', i === n);
  });
  for (let i = 0; i < totalSections; i++) {
    const el = document.getElementById('step-' + i);
    if (!el) continue;
    el.classList.remove('active', 'completed');
    if (i < n)      el.classList.add('completed');
    else if (i === n) el.classList.add('active');
  }
  currentSection = n;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextSection(from) {
  if (from === 0) {
    if (!document.getElementById('respondentRoleInput').value) {
      Swal.fire({ icon:'warning', title:'Select a Role', text:'Please select your respondent role before proceeding.', confirmButtonColor:'#1a6fc4' });
      return;
    }
  }
  if ([1,2,3].includes(from)) {
    const secId = 'sec-' + from;
    const answered = countAnswered(secId);
    if (answered < 5) {
      const unanswered = getUnansweredBlocks(secId);
      unanswered.forEach(b => b.classList.add('invalid'));
      setTimeout(() => unanswered.forEach(b => b.classList.remove('invalid')), 800);
      Swal.fire({ icon:'warning', title:'Incomplete', text:'Please answer all 5 questions before proceeding.', confirmButtonColor:'#1a6fc4' });
      return;
    }
  }
  if (from === 4) computeScores();
  showSection(from + 1);
}

function prevSection(from) { showSection(from - 1); }

// ── Role Selection ─────────────────────────────────────────
function selectRole(el, role) {
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('respondentRoleInput').value = role;
  document.getElementById('nextBtn0').disabled = false;
}

// ── Answer Tracking ────────────────────────────────────────
function countAnswered(secId) {
  const sec = document.getElementById(secId);
  const names = new Set([...sec.querySelectorAll('input[type=radio]:checked')].map(i => i.name));
  return names.size;
}

function getUnansweredBlocks(secId) {
  const sec = document.getElementById(secId);
  return [...sec.querySelectorAll('.question-block')].filter(b => !b.querySelector('input[type=radio]:checked'));
}

const progressLabels = { 'sec-1':'usabilityProgress', 'sec-2':'efficiencyProgress', 'sec-3':'satisfactionProgress' };
const nextBtnIds     = { 'sec-1':'nextBtn1', 'sec-2':'nextBtn2', 'sec-3':'nextBtn3' };

function onRatingChange(secId) {
  const answered = countAnswered(secId);
  const labelEl  = document.getElementById(progressLabels[secId]);
  const btnEl    = document.getElementById(nextBtnIds[secId]);
  if (labelEl) labelEl.textContent = answered + '/5 answered';
  if (btnEl)   btnEl.disabled = answered < 5;
}

// ── Score Computation ──────────────────────────────────────
function getAvg(names) {
  const vals = names.map(n => {
    const el = document.querySelector(`input[name="${n}"]:checked`);
    return el ? parseFloat(el.value) : 0;
  });
  return vals.reduce((a, b) => a + b, 0) / vals.length;
}

function interpText(score) {
  if (score >= 4.20) return { label:'Excellent', color:'#16a34a', bg:'rgba(34,197,94,.1)' };
  if (score >= 3.40) return { label:'Good',      color:'#2563eb', bg:'rgba(59,130,246,.1)' };
  if (score >= 2.60) return { label:'Moderate',  color:'#b45309', bg:'rgba(245,158,11,.1)' };
  if (score >= 1.80) return { label:'Poor',      color:'#ea580c', bg:'rgba(249,115,22,.1)' };
  return               { label:'Very Poor',  color:'#dc2626', bg:'rgba(239,68,68,.1)' };
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
  const overall = ((uScore + eScore + sScore) / 3);

  document.getElementById('previewUsability').textContent    = uScore.toFixed(2);
  document.getElementById('previewEfficiency').textContent   = eScore.toFixed(2);
  document.getElementById('previewSatisfaction').textContent = sScore.toFixed(2);
  document.getElementById('overallScore').textContent        = overall.toFixed(2);

  setGauge('gaugeUsability', uScore);
  setGauge('gaugeEfficiency', eScore);
  setGauge('gaugeSatisfaction', sScore);

  const uI = interpText(uScore);
  const eI = interpText(eScore);
  const sI = interpText(sScore);
  const oI = interpText(overall);

  document.getElementById('usabilityInterp').innerHTML   = `<span class="interp-badge" style="background:${uI.bg};color:${uI.color};">${uI.label}</span>`;
  document.getElementById('efficiencyInterp').innerHTML  = `<span class="interp-badge" style="background:${eI.bg};color:${eI.color};">${eI.label}</span>`;
  document.getElementById('satisfactionInterp').innerHTML= `<span class="interp-badge" style="background:${sI.bg};color:${sI.color};">${sI.label}</span>`;
  document.getElementById('overallVerdict').textContent  = `Interpretation: ${oI.label}`;

  document.getElementById('hiddenUsability').value    = uScore.toFixed(2);
  document.getElementById('hiddenEfficiency').value   = eScore.toFixed(2);
  document.getElementById('hiddenSatisfaction').value = sScore.toFixed(2);
  document.getElementById('hiddenOverall').value      = overall.toFixed(2);
}

// ── Submit confirm ─────────────────────────────────────────
function confirmSubmit(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Submit Evaluation?',
    html: 'Your responses will be saved and cannot be edited. Are you sure?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#1a6fc4',
    confirmButtonText: 'Yes, Submit',
    cancelButtonText: 'Review Again'
  }).then(r => {
    if (r.isConfirmed) {
      document.getElementById('evalForm').style.display = 'none';
      document.getElementById('thankYouScreen').style.display = 'block';
      document.getElementById('stepsBar').querySelectorAll('.step-item').forEach(s => s.classList.add('completed'));
      // Uncomment below for real form submission:
      // document.getElementById('evalForm').submit();
    }
  });
  return false;
}

// ── View Tabs ──────────────────────────────────────────────
function switchView(view) {
  const showForm    = view === 'form';
  document.getElementById('viewForm').style.display    = showForm ? 'block' : 'none';
  document.getElementById('viewResults').style.display = showForm ? 'none'  : 'block';
  document.getElementById('tabFillForm').classList.toggle('active', showForm);
  document.getElementById('tabResults').classList.toggle('active', !showForm);
  if (!showForm) initResultCharts();
}

// ── Results Charts ─────────────────────────────────────────
let chartsInited = false;
function initResultCharts() {
  if (chartsInited) return;
  chartsInited = true;

  const uAvg = {{ number_format($avgUsability ?? 0, 2) }};
  const eAvg = {{ number_format($avgEfficiency ?? 0, 2) }};
  const sAvg = {{ number_format($avgSatisfaction ?? 0, 2) }};

  // Radar chart
  new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
      labels: ['Usability','Efficiency','Satisfaction'],
      datasets: [{
        data: [uAvg, eAvg, sAvg],
        backgroundColor: 'rgba(26,111,196,0.15)',
        borderColor: '#1a6fc4',
        pointBackgroundColor: '#1a6fc4',
        pointRadius: 5,
        borderWidth: 2.5,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { r: { beginAtZero: true, max: 5, ticks: { stepSize: 1, font:{ size:11 } }, grid: { color:'rgba(0,0,0,0.06)' } } },
      plugins: { legend: { display: false } }
    }
  });

  // Category bar
  new Chart(document.getElementById('categoryBarChart'), {
    type: 'bar',
    data: {
      labels: ['Usability','Efficiency','Satisfaction'],
      datasets: [{
        data: [uAvg, eAvg, sAvg],
        backgroundColor: ['#6366f1','#00c9a7','#f59e0b'],
        borderRadius: 10,
        barThickness: 40,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { grid:{ display:false }, border:{ display:false }, ticks:{ font:{size:12} } },
        y: { beginAtZero:true, max:5, grid:{ color:'rgba(0,0,0,0.06)' }, border:{ display:false }, ticks:{ stepSize:1 } }
      },
      plugins: { legend:{ display:false } }
    }
  });

  // Role donut
  @php
    $roleData = $roleData ?? ['Administrator' => 0, 'Faculty' => 0, 'Staff' => 0, 'Other' => 0];
  @endphp
  new Chart(document.getElementById('roleChart'), {
    type: 'doughnut',
    data: {
      labels: {!! json_encode(array_keys($roleData)) !!},
      datasets: [{
        data: {!! json_encode(array_values($roleData)) !!},
        backgroundColor: ['#1a6fc4','#7c3aed','#00c9a7','#f59e0b'],
        borderWidth: 3,
        borderColor: '#fff',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: { legend: { display: false } }
    }
  });
}

// ── Mobile Sidebar ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const btn     = document.getElementById('mobileMenuBtn');
  const sidebar = document.getElementById('sidebar');
  if (btn) btn.addEventListener('click', () => sidebar.classList.toggle('show'));

  @if(session('eval_success'))
    Swal.fire({ icon:'success', title:'Evaluation Submitted!', text:'Thank you for your feedback.', confirmButtonColor:'#1a6fc4' });
  @endif
});
</script>
</body>
</html>
