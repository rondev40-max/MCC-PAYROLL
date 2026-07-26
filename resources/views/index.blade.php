<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MCC Payroll — Madridejos Community College</title>
  <meta name="description" content="Payroll Management System for Madridejos Community College. Manage attendance, payroll, and payslips.">
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>

  {{-- Applies saved theme before first paint --}}
  <script>
    (function () {
      const saved = localStorage.getItem('mcc-theme');
      if (saved === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    })();
  </script>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-primary: #ffffff;
      --bg-secondary: #f8f9fc;
      --bg-glass: rgba(255, 255, 255, 0.65);
      --bg-glass-border: rgba(0, 0, 0, 0.06);

      --text-primary: #0f1729;
      --text-secondary: #5a6478;
      --text-tertiary: #8892a4;

      --accent: #2563eb;
      --accent-hover: #1d4ed8;
      --accent-soft: rgba(37, 99, 235, 0.08);
      --accent-glow: rgba(37, 99, 235, 0.15);

      --border: rgba(0, 0, 0, 0.06);
      --border-hover: rgba(0, 0, 0, 0.12);

      --radius-sm: 10px;
      --radius-md: 16px;
      --radius-lg: 24px;
      --radius-pill: 999px;

      --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.08);
      --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.10);
      --shadow-card-hover: 0 12px 32px rgba(37, 99, 235, 0.12);

      --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    [data-theme="dark"] {
      --bg-primary: #0c111c;
      --bg-secondary: #111827;
      --bg-glass: rgba(17, 24, 39, 0.75);
      --bg-glass-border: rgba(255, 255, 255, 0.06);

      --text-primary: #f0f2f7;
      --text-secondary: #9ca3b4;
      --text-tertiary: #6b7280;

      --accent-soft: rgba(37, 99, 235, 0.12);
      --accent-glow: rgba(37, 99, 235, 0.20);

      --border: rgba(255, 255, 255, 0.06);
      --border-hover: rgba(255, 255, 255, 0.12);

      --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.2);
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.25);
      --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.3);
      --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.35);
      --shadow-card-hover: 0 12px 32px rgba(37, 99, 235, 0.2);
    }

    html {
      scroll-behavior: smooth;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    body {
      font-family: var(--font);
      color: var(--text-primary);
      background: var(--bg-primary);
      line-height: 1.6;
      transition: background 0.35s ease, color 0.35s ease;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    a { color: inherit; text-decoration: none; }

    .sr-only {
      position: absolute; width: 1px; height: 1px;
      padding: 0; margin: -1px; overflow: hidden;
      clip: rect(0,0,0,0); white-space: nowrap; border: 0;
    }

    /* ===================== SKIP LINK ===================== */
    .skip-link {
      position: absolute; top: -48px; left: 16px; z-index: 200;
      background: var(--accent); color: #fff; font-weight: 600;
      font-size: 0.85rem; padding: 10px 16px;
      border-radius: var(--radius-sm); transition: top 0.2s ease;
    }
    .skip-link:focus { top: 16px; }

    /* ===================== TOP BAR ===================== */
    .topbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 clamp(16px, 4vw, 40px); height: 64px;
      background: var(--bg-glass);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-bottom: 1px solid var(--bg-glass-border);
      transition: background 0.35s ease;
    }

    .topbar-brand {
      display: flex; align-items: center; gap: 10px;
      font-weight: 700; font-size: 0.92rem;
      color: var(--text-primary); letter-spacing: -0.01em;
    }
    .topbar-brand img {
      width: 32px; height: 32px; border-radius: 8px; object-fit: cover;
    }

    .topbar-actions { display: flex; align-items: center; gap: 8px; }

    .btn-register {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 0.82rem; font-weight: 600; color: var(--text-secondary);
      padding: 8px 14px; border-radius: var(--radius-pill);
      border: 1px solid var(--border); background: transparent;
      cursor: pointer; transition: all 0.2s ease; text-decoration: none;
    }
    .btn-register:hover {
      border-color: var(--border-hover);
      background: var(--accent-soft);
      color: var(--accent);
    }
    .btn-register svg { width: 15px; height: 15px; }

    .theme-toggle {
      width: 38px; height: 38px; border-radius: var(--radius-pill);
      border: 1px solid var(--border); background: transparent;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: var(--text-secondary); transition: all 0.2s ease;
    }
    .theme-toggle:hover {
      background: var(--accent-soft); color: var(--accent);
      border-color: var(--border-hover);
    }
    .theme-toggle svg { width: 18px; height: 18px; }
    .theme-toggle .icon-moon { display: none; }
    [data-theme="dark"] .theme-toggle .icon-sun { display: none; }
    [data-theme="dark"] .theme-toggle .icon-moon { display: block; }

    /* ===================== HERO ===================== */
    .hero {
      position: relative;
      min-height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding: calc(64px + 56px) 24px 80px;
      isolation: isolate;
    }

    .hero::before {
      content: "";
      position: absolute; inset: 0;
      background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
      filter: blur(2px) brightness(0.32) saturate(1.2);
      transform: scale(1.04);
      z-index: -2;
    }

    .hero::after {
      content: "";
      position: absolute; inset: 0;
      background: linear-gradient(
        180deg,
        rgba(12, 17, 28, 0.25) 0%,
        rgba(12, 17, 28, 0.72) 100%
      );
      z-index: -1;
    }

    .hero-inner {
      text-align: center;
      max-width: 620px;
      color: #ffffff;
      animation: fadeUp 0.7s ease-out both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .hero-badge {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 0.72rem; font-weight: 700;
      letter-spacing: 0.06em; text-transform: uppercase;
      color: rgba(255,255,255,0.8);
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.15);
      padding: 6px 14px; border-radius: var(--radius-pill);
      backdrop-filter: blur(8px); margin-bottom: 22px;
    }
    .hero-badge .dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: #34d399;
      box-shadow: 0 0 6px 1px rgba(52,211,153,0.6);
      animation: pulse-dot 2.4s ease-in-out infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { box-shadow: 0 0 6px 1px rgba(52,211,153,0.6); }
      50%       { box-shadow: 0 0 10px 3px rgba(52,211,153,0.9); }
    }

    .hero h1 {
      font-size: clamp(1.7rem, 3.8vw, 2.65rem);
      font-weight: 800; letter-spacing: -0.03em;
      line-height: 1.12; margin-bottom: 14px;
    }

    .hero p {
      font-size: clamp(0.88rem, 1.5vw, 1rem);
      color: rgba(255,255,255,0.7); line-height: 1.68;
      max-width: 440px; margin: 0 auto 28px;
    }

    /* Scroll hint */
    .scroll-hint {
      display: flex; flex-direction: column;
      align-items: center; gap: 6px;
      opacity: 0.5; font-size: 0.7rem;
      letter-spacing: 0.04em; text-transform: uppercase;
      color: rgba(255,255,255,0.7);
      animation: fadeUp 0.7s ease-out 0.5s both;
    }
    .scroll-hint-line {
      width: 1px; height: 36px;
      background: linear-gradient(to bottom, rgba(255,255,255,0.6), transparent);
      animation: scroll-line 1.8s ease-in-out infinite;
    }
    @keyframes scroll-line {
      0%   { transform: scaleY(0); transform-origin: top; }
      50%  { transform: scaleY(1); transform-origin: top; }
      51%  { transform: scaleY(1); transform-origin: bottom; }
      100% { transform: scaleY(0); transform-origin: bottom; }
    }

    /* ===================== PORTALS SECTION ===================== */
    .portals-section {
      position: relative;
      z-index: 10;
      margin-top: -48px;
      padding: 0 clamp(16px, 4vw, 40px) 52px;
    }

    /* Two-card centred layout */
    .portals-grid {
      display: flex;
      justify-content: center;
      gap: 20px;
      max-width: 1100px;
      margin: 0 auto;
      animation: fadeUp 0.7s ease-out 0.12s both;
    }

    .portal-card {
      position: relative;
      display: flex;
      flex-direction: column;
      padding: 32px 28px 28px;
      border-radius: var(--radius-lg);
      background: var(--bg-primary);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-md);
      text-decoration: none;
      transition: all 0.28s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      overflow: hidden;
      isolation: isolate;
      /* Each card takes equal width, never collapsing too narrow */
      flex: 1 1 0;
      min-width: 0;
      max-width: 340px;
    }

    .portal-card::before {
      content: "";
      position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: var(--accent); opacity: 0;
      transition: opacity 0.28s ease;
    }

    .portal-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-card-hover);
      border-color: var(--accent-glow);
    }
    .portal-card:hover::before { opacity: 1; }

    /* Primary / "Most used" card */
    .portal-card.primary::before { opacity: 1; }
    .portal-card.primary {
      border-color: rgba(37, 99, 235, 0.18);
      background: linear-gradient(135deg, var(--bg-primary) 0%, var(--accent-soft) 100%);
    }

    .portal-card .tag {
      position: absolute; top: 18px; right: 18px;
      font-size: 0.62rem; font-weight: 700; letter-spacing: 0.04em;
      text-transform: uppercase; background: var(--accent); color: #fff;
      padding: 3px 9px; border-radius: var(--radius-pill);
    }

    .portal-icon {
      width: 48px; height: 48px; border-radius: var(--radius-sm);
      background: var(--accent-soft);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 18px; transition: background 0.25s ease;
    }
    .portal-card:hover .portal-icon { background: var(--accent-glow); }
    .portal-icon svg { width: 22px; height: 22px; stroke: var(--accent); }

    .portal-card h3 {
      font-size: 1.05rem; font-weight: 700;
      margin-bottom: 6px; color: var(--text-primary);
      letter-spacing: -0.01em;
    }

    .portal-card p {
      font-size: 0.84rem; color: var(--text-secondary);
      line-height: 1.58; margin-bottom: 20px; flex-grow: 1;
    }

    /* Access chips below the description */
    .portal-chips {
      display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px;
    }
    .portal-chip {
      font-size: 0.7rem; font-weight: 600; letter-spacing: 0.02em;
      color: var(--text-tertiary); background: var(--bg-secondary);
      border: 1px solid var(--border);
      padding: 3px 9px; border-radius: var(--radius-pill);
    }

    .portal-cta {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 0.84rem; font-weight: 650; color: var(--accent);
    }
    .portal-cta svg { width: 14px; height: 14px; transition: transform 0.2s ease; }
    .portal-card:hover .portal-cta svg { transform: translateX(3px); }

    /* ===================== DIVIDER ===================== */
    .section-divider {
      display: flex; align-items: center; gap: 16px;
      max-width: 860px; margin: 0 auto;
      padding: 0 clamp(16px, 4vw, 40px) 40px;
    }
    .section-divider-line {
      flex: 1; height: 1px; background: var(--border);
    }
    .section-divider-label {
      font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em;
      text-transform: uppercase; color: var(--text-tertiary); white-space: nowrap;
    }

    /* ===================== FEATURES STRIP ===================== */
    .features-strip {
      padding: 48px clamp(16px, 4vw, 40px) 56px;
      border-top: 1px solid var(--border);
      background: var(--bg-secondary);
    }

    .features-strip-inner {
      max-width: 860px;
      margin: 0 auto;
    }

    .features-strip-label {
      font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em;
      text-transform: uppercase; color: var(--text-tertiary); margin-bottom: 8px;
    }
    .features-strip-heading {
      font-size: clamp(1.1rem, 2vw, 1.4rem); font-weight: 700;
      color: var(--text-primary); letter-spacing: -0.02em;
      margin-bottom: 28px; line-height: 1.2;
    }

    .features-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 12px;
    }

    .feature-item {
      display: flex; align-items: flex-start; gap: 14px;
      padding: 18px 16px; border-radius: var(--radius-md);
      background: var(--bg-primary); border: 1px solid var(--border);
      transition: all 0.2s ease;
    }
    .feature-item:hover {
      border-color: var(--border-hover);
      box-shadow: var(--shadow-sm);
      transform: translateY(-2px);
    }

    .feature-dot {
      width: 40px; height: 40px; border-radius: 12px;
      background: var(--accent-soft);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .feature-dot svg { width: 19px; height: 19px; stroke: var(--accent); }

    .feature-item-text h4 {
      font-size: 0.86rem; font-weight: 650;
      color: var(--text-primary); margin-bottom: 3px;
    }
    .feature-item-text span {
      font-size: 0.76rem; color: var(--text-tertiary); line-height: 1.45;
    }

    /* ===================== NOTICE BANNER ===================== */
    .notice-banner {
      margin: 0 clamp(16px, 4vw, 40px) 40px;
      max-width: 760px;
      margin-left: auto;
      margin-right: auto;
      padding: 16px 20px;
      border-radius: var(--radius-md);
      border: 1px solid rgba(37, 99, 235, 0.18);
      background: var(--accent-soft);
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .notice-banner-icon {
      width: 36px; height: 36px; flex-shrink: 0;
      border-radius: 10px; background: var(--accent);
      display: flex; align-items: center; justify-content: center;
    }
    .notice-banner-icon svg { width: 18px; height: 18px; stroke: #fff; }
    .notice-banner-text {
      font-size: 0.82rem; line-height: 1.55; color: var(--text-secondary);
    }
    .notice-banner-text strong { color: var(--text-primary); font-weight: 650; }

    /* ===================== FOOTER ===================== */
    .site-footer {
      margin-top: auto;
      padding: 24px clamp(16px, 4vw, 40px);
      border-top: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      font-size: 0.78rem; color: var(--text-tertiary);
      background: var(--bg-primary);
    }
    .footer-links { display: flex; gap: 16px; }
    .footer-links a { color: var(--text-secondary); transition: color 0.2s ease; }
    .footer-links a:hover { color: var(--accent); }

    /* ===================== RESPONSIVE ===================== */
    @media (max-width: 640px) {
      .portals-grid {
        flex-direction: column;
        align-items: center;
        max-width: 380px;
      }
      .portal-card { max-width: 100%; width: 100%; }
      .features-list { grid-template-columns: 1fr 1fr; }
      .site-footer { flex-direction: column; gap: 8px; text-align: center; }
      .notice-banner { flex-direction: column; text-align: center; }
    }

    @media (max-width: 480px) {
      .hero { padding-top: calc(64px + 36px); padding-bottom: 56px; }
      .features-list { grid-template-columns: 1fr; }
      .topbar-brand span {
        position: absolute; width: 1px; height: 1px; padding: 0;
        margin: -1px; overflow: hidden; clip: rect(0,0,0,0);
        white-space: nowrap; border: 0;
      }
      .scroll-hint { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
      .hero-inner, .portals-grid { animation: none; }
      .hero-badge .dot { animation: none; }
      .scroll-hint-line { animation: none; }
    }
  </style>
</head>
<body>

  <a href="#main-content" class="skip-link">Skip to content</a>

  <!-- ===================== TOP BAR ===================== -->
  <header class="topbar">
    <div class="topbar-brand">
      <img src="{{ asset('images/logo.png') }}" alt="MCC Payroll logo">
      <span>MCC Payroll</span>
    </div>
    <div class="topbar-actions">
      <a href="{{ url('/register') }}" class="btn-register">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="19" y1="8" x2="19" y2="14"/>
          <line x1="22" y1="11" x2="16" y2="11"/>
        </svg>
        Register
      </a>
      <button class="theme-toggle" id="themeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="12" r="4"/>
          <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
        </svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
        </svg>
      </button>
    </div>
  </header>

  <main id="main-content">

    <!-- ===================== HERO ===================== -->
    <section class="hero">
      <div class="hero-inner">
        <div class="hero-badge">
          <span class="dot"></span>
          System Online
        </div>
        <h1>Madridejos Community College</h1>
        <p>Payroll management, attendance tracking, and payslip generation — all in one secure platform.</p>
        <div class="scroll-hint">
          <span>Choose a portal</span>
          <div class="scroll-hint-line"></div>
        </div>
      </div>
    </section>

    <!-- ===================== PORTAL CARDS ===================== -->
    <div class="portals-section">
      <div class="portals-grid">

        <a href="{{ url('/employee/login') }}" class="portal-card primary" id="portal-employee">
          <span class="tag">Most used</span>
          <div class="portal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4"/>
              <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>
            </svg>
          </div>
          <h3>Employee Portal</h3>
          <p>Access your payslips, review attendance records, and submit timesheet entries.</p>
          <div class="portal-chips">
            <span class="portal-chip">Payslips</span>
            <span class="portal-chip">Attendance</span>
            <span class="portal-chip">Timesheets</span>
          </div>
          <span class="portal-cta">
            Sign in
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </span>
        </a>

        <a href="{{ url('/attendance/attendlog') }}" class="portal-card" id="portal-attendance">
          <div class="portal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="9"/>
              <path d="M12 7v5l3 3"/>
            </svg>
          </div>
          <h3>Attendance Log</h3>
          <p>Log and verify daily attendance records on-site. For designated attendance staff only.</p>
          <div class="portal-chips">
            <span class="portal-chip">Daily logs</span>
            <span class="portal-chip">On-site</span>
            <span class="portal-chip">Verification</span>
          </div>
          <span class="portal-cta">
            Sign in
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </span>
        </a>

        <!-- Download Mobile App Portal Card -->
        <a href="{{ asset('downloads/mcc-employee-app.apk') }}" class="portal-card" id="portal-download" download>
          <div class="portal-icon" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
              <line x1="12" y1="18" x2="12.01" y2="18"/>
            </svg>
          </div>
          <h3>Download App</h3>
          <p>Get our native Android app to clock in, submit timesheets, and view payslips on-the-go.</p>
          <div class="portal-chips">
            <span class="portal-chip" style="background: rgba(16, 185, 129, 0.05); color: #10b981; border-color: rgba(16, 185, 129, 0.12);">Android APK</span>
            <span class="portal-chip" style="background: rgba(16, 185, 129, 0.05); color: #10b981; border-color: rgba(16, 185, 129, 0.12);">On-The-Go</span>
            <span class="portal-chip" style="background: rgba(16, 185, 129, 0.05); color: #10b981; border-color: rgba(16, 185, 129, 0.12);">Mobile Sign-In</span>
          </div>
          <span class="portal-cta" style="color: #10b981;">
            Download APK
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M12 5v14M19 12l-7 7-7-7"/>
            </svg>
          </span>
        </a>

      </div>
    </div>

    <!-- ===================== NOTICE BANNER ===================== -->
    <div class="notice-banner">
      <div class="notice-banner-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 8v4M12 16h.01"/>
        </svg>
      </div>
      <div class="notice-banner-text">
        <strong>First time here?</strong> Contact your HR administrator to have your account created. Use <a href="{{ url('/register') }}" style="color: var(--accent); font-weight: 600;">Register</a> only if you were given an access code.
      </div>
    </div>

    <!-- ===================== FEATURES STRIP ===================== -->
    <section class="features-strip">
      <div class="features-strip-inner">
        <div class="features-strip-label">Platform capabilities</div>
        <div class="features-strip-heading">Everything payroll, in one place.</div>
        <div class="features-list">

          <div class="feature-item">
            <div class="feature-dot">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="6" width="18" height="12" rx="2"/>
                <path d="M3 10h18M7 15h4"/>
              </svg>
            </div>
            <div class="feature-item-text">
              <h4>Payroll Processing</h4>
              <span>Full-time, part-time &amp; utility staff</span>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-dot">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="17" rx="2"/>
                <path d="M3 9h18M8 2v4M16 2v4M9 14l2 2 4-4"/>
              </svg>
            </div>
            <div class="feature-item-text">
              <h4>Attendance Tracking</h4>
              <span>Daily logs &amp; real-time records</span>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-dot">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                <path d="M14 2v6h6M9 13h6M9 17h6"/>
              </svg>
            </div>
            <div class="feature-item-text">
              <h4>Payslip Generation</h4>
              <span>Downloadable per pay period</span>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-dot">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="11" width="14" height="10" rx="2"/>
                <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
              </svg>
            </div>
            <div class="feature-item-text">
              <h4>Role-Based Access</h4>
              <span>Secure, permission-level login</span>
            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

  <!-- ===================== FOOTER ===================== -->
  <footer class="site-footer">
    <span>&copy; {{ date('Y') }} Madridejos Community College</span>
    <div class="footer-links">
      <a href="{{ url('/register') }}">Create Account</a>
      <a href="{{ url('/terms') }}">Terms</a>
    </div>
  </footer>

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