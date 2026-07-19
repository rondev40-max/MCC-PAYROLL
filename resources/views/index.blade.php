<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MCC_Payroll</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>

  {{-- Applies saved theme before first paint, so there's no flash of the wrong theme --}}
  <script>
    (function () {
      const saved = localStorage.getItem('mcc-theme');
      if (saved === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    })();
  </script>

  <style>
    /* =========================================================
       NOTE ON STRUCTURE
       This is still one file with an inline <style> block, same
       as the pages before it. Splitting this into a separate
       CSS/Vite bundle and reusable Blade components (hero card,
       portal card, footer) is genuinely worth doing next — but
       needs your actual resources/ layout and build config to do
       safely. Happy to do that pass once you share how the
       project is wired up (Vite? Laravel Mix? plain public/css?).
       ========================================================= */

    * { box-sizing: border-box; }

    :root {
      /* Blue marlin palette */
      --marlin-abyss: #061529;
      --marlin-navy: #0f2f66;
      --marlin-blue: #2563eb;
      --marlin-blue-hover: #1d4ed8;
      --marlin-flash: #38bdf8;
      --marlin-belly: #f8fafc;

      --ink: #0f172a;
      --muted: #64748b;
      --surface: #ffffff;
      --surface-alt: #f4f6fb;
      --border: rgba(15, 23, 42, 0.08);

      /* Spacing scale */
      --space-1: 4px;
      --space-2: 8px;
      --space-3: 12px;
      --space-4: 20px;
      --space-5: 32px;
      --space-6: 48px;
      --space-7: 72px;
      --space-8: 104px;

      /* Radius scale */
      --radius-sm: 8px;
      --radius-md: 14px;
      --radius-lg: 22px;
      --radius-pill: 999px;

      /* Shadow scale */
      --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.06);
      --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.12);
      --shadow-lg: 0 24px 60px rgba(6, 21, 41, 0.28);
      --shadow-glow: 0 0 0 1px rgba(56, 189, 248, 0.25), 0 20px 50px rgba(37, 99, 235, 0.25);

      --font-display: 'Outfit', 'Segoe UI', sans-serif;
      --font-body: 'Inter', 'Segoe UI', sans-serif;
    }

    [data-theme="dark"] {
      --ink: #e7edf7;
      --muted: #93a4bf;
      --surface: #0b1730;
      --surface-alt: #081123;
      --border: rgba(255, 255, 255, 0.08);
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.35);
      --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.45);
    }

    html { scroll-behavior: smooth; }

    body {
      margin: 0;
      font-family: var(--font-body);
      color: var(--ink);
      background: var(--surface);
      transition: background 0.3s ease, color 0.3s ease;
    }

    a { color: inherit; }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: var(--space-2);
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--marlin-flash);
      margin-bottom: var(--space-3);
    }

    .eyebrow::before {
      content: "";
      width: 8px; height: 8px;
      border-radius: 50%;
      background: var(--marlin-flash);
      box-shadow: 0 0 10px 2px rgba(56, 189, 248, 0.8);
    }

    /* ===================== THEME TOGGLE ===================== */
    .theme-toggle {
      position: fixed;
      top: var(--space-4);
      right: var(--space-4);
      z-index: 50;
      width: 44px; height: 44px;
      border-radius: var(--radius-pill);
      border: 1px solid rgba(255, 255, 255, 0.25);
      background: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(10px);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      color: white;
      transition: background 0.25s ease, transform 0.25s ease;
    }
    .theme-toggle:hover { background: rgba(255, 255, 255, 0.22); transform: translateY(-1px); }
    .theme-toggle svg { width: 20px; height: 20px; }
    .theme-toggle .icon-moon { display: none; }
    [data-theme="dark"] .theme-toggle .icon-sun { display: none; }
    [data-theme="dark"] .theme-toggle .icon-moon { display: block; }

    /* ===================== HERO ===================== */
    .hero {
      position: relative;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding: var(--space-6) var(--space-4);
      isolation: isolate;
      text-align: center;
      color: white;
    }

    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
      filter: blur(4px) brightness(70%) saturate(110%);
      transform: scale(1.06);
      z-index: -4;
    }

    /* Animated gradient wash — slow drift, not a flashy loop */
    .hero::after {
      content: "";
      position: absolute;
      inset: -10%;
      background: linear-gradient(120deg,
        rgba(6, 21, 41, 0.72) 0%,
        rgba(15, 47, 102, 0.62) 35%,
        rgba(37, 99, 235, 0.55) 65%,
        rgba(56, 189, 248, 0.4) 100%);
      background-size: 200% 200%;
      animation: gradient-drift 18s ease-in-out infinite;
      z-index: -3;
    }

    @keyframes gradient-drift {
      0%   { background-position: 0% 50%; }
      50%  { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    @keyframes float-bob {
      0%, 100% { transform: translateY(0) translateX(0); }
      50%      { transform: translateY(-22px) translateX(10px); }
    }

    @media (prefers-reduced-motion: reduce) {
      .hero::after, .dashboard-mock { animation: none; }
    }

    .hero-content {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      max-width: 720px;
      animation: fade-in-up 0.9s ease both;
    }

    @keyframes fade-in-up {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .hero-logo {
      width: 108px; height: 108px;
      border-radius: 50%;
      border: 3px solid rgba(255, 255, 255, 0.35);
      box-shadow: var(--shadow-lg);
      object-fit: cover;
      margin-bottom: var(--space-4);
    }

    .hero h1 {
      font-family: var(--font-display);
      font-weight: 800;
      font-size: clamp(1.9rem, 4vw, 2.9rem);
      letter-spacing: -0.01em;
      margin: 0 0 var(--space-2) 0;
      text-shadow: 2px 2px 16px rgba(0,0,0,0.45);
    }

    .hero h2 {
      font-family: var(--font-display);
      font-weight: 600;
      font-size: clamp(1rem, 2vw, 1.3rem);
      color: var(--marlin-flash);
      margin: 0 0 var(--space-3) 0;
    }

    .hero p.tagline {
      color: #dce8fa;
      font-size: 1rem;
      line-height: 1.7;
      max-width: 52ch;
      margin: 0 0 var(--space-6) 0;
    }

    /* ===================== PORTAL CARDS ===================== */
    .portals {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: repeat(3, minmax(220px, 260px));
      gap: var(--space-4);
      width: 100%;
      max-width: 840px;
      animation: fade-in-up 0.9s ease 0.15s both;
    }

    .portal-card {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
      text-decoration: none;
      color: white;
      padding: var(--space-4);
      border-radius: var(--radius-lg);
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(16px);
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease;
      order: 0;
    }

    .portal-card.primary { order: -1; background: rgba(37, 99, 235, 0.22); border-color: rgba(56, 189, 248, 0.4); }

    .portal-card:hover,
    .portal-card:focus-visible {
      transform: translateY(-6px);
      box-shadow: var(--shadow-glow);
      border-color: var(--marlin-flash);
      background: rgba(255, 255, 255, 0.14);
      outline: none;
    }

    .portal-card .tag {
      position: absolute;
      top: var(--space-3);
      right: var(--space-3);
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      background: var(--marlin-flash);
      color: var(--marlin-abyss);
      padding: 3px 9px;
      border-radius: var(--radius-pill);
    }

    .portal-icon {
      width: 46px; height: 46px;
      border-radius: var(--radius-md);
      background: rgba(255, 255, 255, 0.14);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: var(--space-3);
    }
    .portal-icon svg { width: 22px; height: 22px; stroke: white; }

    .portal-card h3 {
      font-family: var(--font-display);
      font-size: 1.08rem;
      font-weight: 700;
      margin: 0 0 var(--space-1) 0;
    }

    .portal-card p {
      font-size: 0.86rem;
      line-height: 1.55;
      color: #dbe6f7;
      margin: 0 0 var(--space-4) 0;
      flex-grow: 1;
    }

    .portal-cta {
      font-size: 0.85rem;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: var(--marlin-flash);
    }
    .portal-cta svg { width: 14px; height: 14px; transition: transform 0.2s ease; }
    .portal-card:hover .portal-cta svg { transform: translateX(3px); }

    .hero-footnote {
      position: relative;
      z-index: 1;
      margin-top: var(--space-5);
      font-size: 0.88rem;
      color: #cbd9ef;
    }
    .hero-footnote a { color: var(--marlin-flash); text-decoration: none; font-weight: 600; }
    .hero-footnote a:hover { text-decoration: underline; }

    /* ===================== STATS ===================== */
    .stats {
      background: var(--surface-alt);
      padding: var(--space-7) var(--space-4);
      text-align: center;
    }

    .stats-heading { max-width: 560px; margin: 0 auto var(--space-6) auto; }
    .stats-heading h2 {
      font-family: var(--font-display);
      font-size: clamp(1.4rem, 3vw, 1.9rem);
      font-weight: 800;
      margin: 0 0 var(--space-2) 0;
    }
    .stats-heading p { color: var(--muted); margin: 0; }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: var(--space-5);
      max-width: 900px;
      margin: 0 auto;
    }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: var(--space-5) var(--space-4);
      box-shadow: var(--shadow-sm);
    }

    .stat-number {
      font-family: var(--font-display);
      font-size: clamp(2rem, 4vw, 2.6rem);
      font-weight: 800;
      color: var(--marlin-blue);
      display: block;
      margin-bottom: var(--space-1);
    }

    .stat-label { color: var(--muted); font-size: 0.92rem; font-weight: 500; }

    /* ===================== FEATURES ===================== */
    .features {
      padding: var(--space-7) var(--space-4);
      max-width: 1080px;
      margin: 0 auto;
    }

    .features-heading { text-align: center; max-width: 560px; margin: 0 auto var(--space-6) auto; }
    .features-heading h2 {
      font-family: var(--font-display);
      font-size: clamp(1.4rem, 3vw, 1.9rem);
      font-weight: 800;
      margin: 0 0 var(--space-2) 0;
    }
    .features-heading p { color: var(--muted); margin: 0; }

    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: var(--space-4);
    }

    .feature-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: var(--space-4);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .feature-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

    .feature-icon {
      width: 40px; height: 40px;
      border-radius: var(--radius-sm);
      background: rgba(37, 99, 235, 0.1);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: var(--space-3);
    }
    .feature-icon svg { width: 20px; height: 20px; stroke: var(--marlin-blue); }

    .feature-card h3 { font-family: var(--font-display); font-size: 0.98rem; font-weight: 700; margin: 0 0 6px 0; }
    .feature-card p { font-size: 0.84rem; color: var(--muted); line-height: 1.5; margin: 0; }

    /* ===================== DASHBOARD PREVIEW ===================== */
    .preview {
      background: linear-gradient(180deg, var(--surface-alt) 0%, var(--surface) 100%);
      padding: var(--space-8) var(--space-4);
    }

    .preview-inner {
      max-width: 1080px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--space-7);
      align-items: center;
    }

    .preview-copy h2 {
      font-family: var(--font-display);
      font-size: clamp(1.5rem, 3vw, 2.1rem);
      font-weight: 800;
      margin: 0 0 var(--space-3) 0;
    }
    .preview-copy p { color: var(--muted); line-height: 1.7; margin: 0 0 var(--space-4) 0; }

    .preview-list { list-style: none; padding: 0; margin: 0; }
    .preview-list li {
      display: flex; align-items: center; gap: var(--space-2);
      font-size: 0.92rem; margin-bottom: var(--space-2); color: var(--ink);
    }
    .preview-list li svg { width: 16px; height: 16px; stroke: var(--marlin-blue); flex-shrink: 0; }

    .preview-visual { display: flex; justify-content: center; perspective: 1400px; }

    .dashboard-mock {
      width: 100%;
      max-width: 380px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      transform: rotateY(-10deg) rotateX(4deg);
      animation: float-bob 7s ease-in-out infinite;
    }

    .dashboard-mock-header {
      background: linear-gradient(120deg, var(--marlin-navy), var(--marlin-blue));
      padding: var(--space-3) var(--space-4);
      display: flex; align-items: center; gap: 8px;
    }
    .dashboard-mock-header span { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.5); }

    .dashboard-mock-body { padding: var(--space-4); }

    .mock-chips { display: flex; gap: var(--space-2); margin-bottom: var(--space-4); }
    .mock-chip {
      flex: 1; background: var(--surface-alt); border-radius: var(--radius-sm);
      padding: var(--space-2); text-align: center;
    }
    .mock-chip strong { display: block; font-family: var(--font-display); color: var(--marlin-blue); font-size: 1rem; }
    .mock-chip span { font-size: 0.68rem; color: var(--muted); }

    .mock-bars { display: flex; align-items: flex-end; gap: 6px; height: 70px; margin-bottom: var(--space-4); }
    .mock-bars i {
      flex: 1; border-radius: 4px 4px 0 0;
      background: linear-gradient(180deg, var(--marlin-flash), var(--marlin-blue));
      display: block;
    }

    .mock-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 8px 0; border-top: 1px solid var(--border);
      font-size: 0.78rem; color: var(--muted);
    }
    .mock-row b { color: var(--ink); font-weight: 600; }

    /* ===================== FOOTER ===================== */
    .site-footer {
      background: var(--marlin-abyss);
      color: #b9c8e2;
      padding: var(--space-5) var(--space-4);
      text-align: center;
      font-size: 0.86rem;
    }
    .site-footer a { color: var(--marlin-flash); text-decoration: none; font-weight: 600; }
    .site-footer a:hover { text-decoration: underline; }

    /* ===================== RESPONSIVE ===================== */
    @media (max-width: 860px) {
      .portals { grid-template-columns: 1fr; max-width: 340px; }
      .portal-card.primary { order: 0; }
      .preview-inner { grid-template-columns: 1fr; }
      .preview-visual { order: -1; }
    }

    @media (max-width: 480px) {
      .hero { padding: var(--space-5) var(--space-3); }
      .hero-logo { width: 84px; height: 84px; }
      .theme-toggle { top: var(--space-3); right: var(--space-3); }
    }

    /* ====== reCAPTCHA badge ====== */
    .grecaptcha-badge {
      position: fixed !important;
      bottom: 10px !important;
      right: 10px !important;
      z-index: 9999 !important;
      transform: scale(0.75);
      opacity: 0.5;
      transition: opacity 0.3s;
    }
    .grecaptcha-badge:hover { opacity: 1; }
  </style>
</head>
<body>

  <button class="theme-toggle" id="themeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
  </button>

  {{-- ===================== HERO ===================== --}}
  <section class="hero">
    <div class="hero-content">
      <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="hero-logo">
      <h1>Madridejos Community College</h1>
      <h2>Payroll Management System</h2>
      <p class="tagline">Real-time attendance, payroll, and analytics for the entire MCC community — built for accuracy, speed, and clarity.</p>
    </div>

    <div class="portals">
      <a href="{{ url('/admin/login') }}" class="portal-card">
        <div class="portal-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5l-8-3Z"/></svg>
        </div>
        <h3>Administrator</h3>
        <p>Manage users, payroll runs, and system-wide settings.</p>
        <span class="portal-cta">Continue <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </a>

      <a href="{{ url('/employee/login') }}" class="portal-card primary">
        <span class="tag">Most used</span>
        <div class="portal-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
        </div>
        <h3>Employee</h3>
        <p>View payslips, attendance, and submit your timesheets.</p>
        <span class="portal-cta">Continue <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </a>

      <a href="{{ url('/attendance/attendlog') }}" class="portal-card">
        <div class="portal-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        </div>
        <h3>Attendance Checker</h3>
        <p>Log and verify daily attendance records on-site.</p>
        <span class="portal-cta">Continue <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </a>
    </div>

    <p class="hero-footnote">Need access? <a href="{{ url('/register') }}">Create New Account</a> · Contact your Administrator</p>
  </section>

  {{-- ===================== LIVE STATS ===================== --}}
  {{--
    NOTE: These three values are placeholders. Pass real numbers from your
    controller (e.g. return view('index', ['totalEmployees' => ..., ...]))
    and they'll be used automatically — otherwise these defaults show.
  --}}
  <section class="stats">
    <div class="stats-heading">
      <div class="eyebrow">Live overview</div>
      <h2>Trusted across the MCC community</h2>
      <p>A snapshot of activity on the platform.</p>
    </div>
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-number" data-count="{{ $totalEmployees ?? 250 }}" data-suffix="+">0</span>
        <span class="stat-label">Employees Served</span>
      </div>
      <div class="stat-card">
        <span class="stat-number" data-count="{{ $attendanceRate ?? 98 }}" data-suffix="%">0</span>
        <span class="stat-label">Attendance Rate</span>
      </div>
      <div class="stat-card">
        <span class="stat-number" data-count="{{ $payrollProcessed ?? 24 }}" data-suffix="M+">₱0</span>
        <span class="stat-label">Payroll Processed</span>
      </div>
    </div>
  </section>

  {{-- ===================== FEATURES ===================== --}}
  <section class="features">
    <div class="features-heading">
      <div class="eyebrow" style="color: var(--marlin-blue);">What's inside</div>
      <h2>Everything payroll needs, in one place</h2>
      <p>Built around the actual workflow of MCC's payroll office.</p>
    </div>
    <div class="feature-grid">
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 15h4"/></svg></div>
        <h3>Payroll</h3>
        <p>Automated computation across FT, PT, and utility staff.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4M9 14l2 2 4-4"/></svg></div>
        <h3>Attendance</h3>
        <p>Daily logs, timesheets, and exception tracking.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V9M9 21V3M15 21v-7M21 21V6"/></svg></div>
        <h3>Analytics</h3>
        <p>Real-time dashboards for trends across departments.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/></svg></div>
        <h3>Payslips</h3>
        <p>Downloadable, itemized payslips for every pay period.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></div>
        <h3>Security</h3>
        <p>Role-based access with lockout protection on login.</p>
      </div>
    </div>
  </section>

  {{-- ===================== DASHBOARD PREVIEW ===================== --}}
  <section class="preview">
    <div class="preview-inner">
      <div class="preview-copy">
        <div class="eyebrow" style="color: var(--marlin-blue);">Inside the dashboard</div>
        <h2>See everything at a glance</h2>
        <p>Once you're signed in, your role's dashboard surfaces exactly what you need — no digging through menus.</p>
        <ul class="preview-list">
          <li><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg> Real-time attendance status</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg> Payroll run history at a glance</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg> One-click payslip downloads</li>
        </ul>
      </div>
      <div class="preview-visual">
        <div class="dashboard-mock">
          <div class="dashboard-mock-header"><span></span><span></span><span></span></div>
          <div class="dashboard-mock-body">
            <div class="mock-chips">
              <div class="mock-chip"><strong>248</strong><span>Active</span></div>
              <div class="mock-chip"><strong>98%</strong><span>Present</span></div>
              <div class="mock-chip"><strong>₱2.4M</strong><span>Processed</span></div>
            </div>
            <div class="mock-bars">
              <i style="height:40%"></i><i style="height:65%"></i><i style="height:50%"></i>
              <i style="height:80%"></i><i style="height:60%"></i><i style="height:90%"></i><i style="height:45%"></i>
            </div>
            <div class="mock-row"><span>Full-time staff</span><b>142</b></div>
            <div class="mock-row"><span>Part-time staff</span><b>76</b></div>
            <div class="mock-row"><span>Utility staff</span><b>30</b></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ===================== FOOTER ===================== --}}
  <footer class="site-footer">
    &copy; {{ date('Y') }} MCC Payroll Management System. All Rights Reserved. ·
    <a href="{{ url('/register') }}">Create Account</a>
  </footer>

  {{-- Hidden form for reCAPTCHA submission (if needed) --}}
  <form id="login-form" style="display:none;" action="#" method="POST">
    @csrf
    <input type="hidden" name="g-recaptcha-response" id="recaptcha_token">
  </form>

  <script>
    // Dark mode toggle
    document.getElementById('themeToggle').addEventListener('click', function () {
      const root = document.documentElement;
      const isDark = root.getAttribute('data-theme') === 'dark';
      if (isDark) {
        root.removeAttribute('data-theme');
        localStorage.setItem('mcc-theme', 'light');
      } else {
        root.setAttribute('data-theme', 'dark');
        localStorage.setItem('mcc-theme', 'dark');
      }
    });

    // Animated stat counters — start once the section scrolls into view
    const counters = document.querySelectorAll('.stat-number');
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseFloat(el.dataset.count);
        const suffix = el.dataset.suffix || '';
        const isCurrency = el.textContent.trim().startsWith('₱');
        const prefix = isCurrency ? '₱' : '';
        const duration = 1400;
        const start = performance.now();

        function step(now) {
          const progress = Math.min((now - start) / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          const value = Math.round(target * eased);
          el.textContent = prefix + value + suffix;
          if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
        counterObserver.unobserve(el);
      });
    }, { threshold: 0.4 });
    counters.forEach((c) => counterObserver.observe(c));

    grecaptcha.ready(function() {
      document.getElementById('login-form').addEventListener('submit', function(event) {
        event.preventDefault();
        grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
          document.getElementById('recaptcha_token').value = token;
          event.target.submit();
        });
      });
    });

    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Registration Successful!',
        text: '{{ session("success") }}',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Continue to Login'
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        text: '{{ session("error") }}',
        confirmButtonColor: '#dc3545'
      });
    @endif

    devtools.detect(function(status){
      if(status){
        document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
      }
    });
  </script>
</body>
</html>