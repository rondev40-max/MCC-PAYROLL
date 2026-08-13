<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MCC Payroll — Madridejos Community College</title>
  <meta name="description" content="Payroll Management System for Madridejos Community College. Manage attendance, payroll, and payslips.">
  <meta name="theme-color" content="#f8fafc">
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

  <script>
    (function () {
      const saved = localStorage.getItem('mcc-theme');
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (saved === 'dark' || (!saved && prefersDark)) {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    })();
  </script>

  <style>
    :root {
      --primary: #1e3a8a; /* Navy Blue */
      --primary-hover: #1e40af;
      --secondary: #7f1d1d; /* Maroon */
      --secondary-hover: #991b1b;
      --success: #059669;
      --success-bg: #d1fae5;
      
      --bg-page: #f8fafc;
      --bg-panel: #ffffff;
      --bg-hover: #f1f5f9;
      
      --text-main: #0f172a;
      --text-muted: #475569;
      --text-light: #64748b;
      
      --border: #e2e8f0;
      
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      
      --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
      --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
      
      --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      --transition: 200ms ease;
    }

    [data-theme="dark"] {
      --primary: #3b82f6; 
      --primary-hover: #60a5fa;
      --secondary: #f87171;
      --secondary-hover: #fca5a5;
      --success: #10b981;
      --success-bg: rgba(16, 185, 129, 0.15);
      
      --bg-page: #0b0f19;
      --bg-panel: #1e293b;
      --bg-hover: #334155;
      
      --text-main: #f8fafc;
      --text-muted: #cbd5e1;
      --text-light: #94a3b8;
      
      --border: #334155;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    html { scroll-behavior: smooth; }
    
    body {
      font-family: var(--font-sans);
      background-color: var(--bg-page);
      color: var(--text-main);
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      transition: background-color var(--transition), color var(--transition);
    }
    
    a { text-decoration: none; color: inherit; }
    button { background: none; border: none; font: inherit; cursor: pointer; color: inherit; }
    
    .container {
      width: 100%;
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* Scroll reveal animations */
    .reveal {
      opacity: 0;
      transform: translateY(32px);
      transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }
    /* Stagger children within a reveal group */
    .reveal-stagger > * {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal-stagger.visible > *:nth-child(1) { transition-delay: 0ms; }
    .reveal-stagger.visible > *:nth-child(2) { transition-delay: 80ms; }
    .reveal-stagger.visible > *:nth-child(3) { transition-delay: 160ms; }
    .reveal-stagger.visible > * {
      opacity: 1;
      transform: translateY(0);
    }
    /* Scroll target offset for sticky header */
    section[id] {
      scroll-margin-top: 88px;
    }

    /* Header */
    .navbar {
      background: var(--bg-panel);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 50;
      transition: background-color var(--transition), border-color var(--transition);
    }
    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 72px;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .brand img {
      width: 40px;
      height: 40px;
      object-fit: contain;
    }
    .brand-text {
      display: flex;
      flex-direction: column;
    }
    .brand-title {
      font-weight: 700;
      font-size: 1.125rem;
      color: var(--primary);
      line-height: 1.2;
    }
    [data-theme="dark"] .brand-title { color: var(--text-main); }
    .brand-subtitle {
      font-size: 0.75rem;
      color: var(--text-muted);
      font-weight: 500;
    }
    .nav-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    
    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 20px;
      border-radius: var(--radius-sm);
      font-weight: 600;
      font-size: 0.875rem;
      transition: all var(--transition);
    }
    .btn-primary {
      background: var(--primary);
      color: #ffffff;
      box-shadow: var(--shadow-sm);
    }
    .btn-primary:hover {
      background: var(--primary-hover);
      transform: translateY(-1px);
    }
    .btn-outline {
      background: transparent;
      color: var(--text-main);
      border: 1px solid var(--border);
    }
    .btn-outline:hover {
      background: var(--bg-hover);
    }
    
    .theme-toggle {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      color: var(--text-muted);
      transition: all var(--transition);
    }
    .theme-toggle:hover {
      background: var(--bg-hover);
      color: var(--text-main);
    }
    .icon-moon { display: none; }
    [data-theme="dark"] .icon-sun { display: none; }
    [data-theme="dark"] .icon-moon { display: block; }
    
    main { flex: 1; padding-bottom: 160px; }
    
    /* Hero */
    .hero {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 64px;
      align-items: center;
      min-height: 85vh;
      padding: 120px 0;
    }
    .hero-content {
      text-align: left;
    }
    .hero-kicker {
      display: inline-block;
      font-size: 1rem;
      font-weight: 600;
      color: var(--secondary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 20px;
    }
    [data-theme="dark"] .hero-kicker { color: var(--secondary); }
    .hero-title {
      font-size: 3rem;
      font-weight: 800;
      color: var(--text-main);
      line-height: 1.1;
      margin-bottom: 28px;
      letter-spacing: -0.02em;
    }
    .hero-desc {
      font-size: 1.25rem;
      color: var(--text-muted);
      margin-bottom: 40px;
      line-height: 1.6;
    }
    .hero-actions {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 40px;
    }
    .hero-clock {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      background: var(--bg-panel);
      border: 1px solid var(--border);
      border-radius: 9999px;
      font-size: 0.95rem;
      font-weight: 500;
      color: var(--text-muted);
      box-shadow: var(--shadow-sm);
    }
    .hero-clock strong { color: var(--text-main); font-weight: 600; font-variant-numeric: tabular-nums; }
    .hero-image {
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--border);
    }
    .hero-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    
    /* Quick Access Cards */
    .quick-access {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px;
      margin-bottom: 160px;
    }
    .qa-card {
      background: var(--bg-panel);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shadow-md);
      transition: all var(--transition);
      text-align: left;
      position: relative;
      overflow: hidden;
    }
    .qa-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary);
    }
    .qa-card:focus-visible {
      outline: 2px solid var(--primary);
      outline-offset: 2px;
    }
    .qa-icon {
      width: 56px;
      height: 56px;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 24px;
    }
    .qa-icon svg { width: 28px; height: 28px; stroke-width: 2; }
    .qa-employee .qa-icon { background: rgba(30, 58, 138, 0.1); color: var(--primary); }
    [data-theme="dark"] .qa-employee .qa-icon { background: rgba(59, 130, 246, 0.15); }
    .qa-attendance .qa-icon { background: rgba(5, 150, 105, 0.1); color: var(--success); }
    .qa-app .qa-icon { background: rgba(127, 29, 29, 0.1); color: var(--secondary); }
    [data-theme="dark"] .qa-app .qa-icon { background: rgba(248, 113, 113, 0.15); }
    
    .qa-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }
    .qa-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--text-main);
    }
    .qa-badge {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 9999px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .qa-employee .qa-badge { background: rgba(30, 58, 138, 0.1); color: var(--primary); }
    [data-theme="dark"] .qa-employee .qa-badge { background: rgba(59, 130, 246, 0.15); color: var(--primary); }
    .qa-attendance .qa-badge { background: rgba(5, 150, 105, 0.1); color: var(--success); }
    .qa-app .qa-badge { background: rgba(127, 29, 29, 0.1); color: var(--secondary); }
    [data-theme="dark"] .qa-app .qa-badge { background: rgba(248, 113, 113, 0.15); color: var(--secondary); }
    
    .qa-desc {
      font-size: 0.95rem;
      color: var(--text-muted);
      margin-bottom: 32px;
      flex: 1;
      line-height: 1.5;
    }
    .qa-action {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--primary);
    }
    .qa-action svg {
      width: 18px;
      height: 18px;
      transition: transform var(--transition);
    }
    .qa-card:hover .qa-action svg { transform: translateX(6px); }
    
    /* Announcement */
    .announcement { margin-bottom: 160px; }
    .announcement-card {
      background: var(--bg-panel);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 20px 32px;
      display: flex;
      align-items: center;
      gap: 20px;
      box-shadow: var(--shadow-sm);
    }
    .announce-icon {
      color: var(--primary);
      display: flex;
      flex-shrink: 0;
    }
    .announce-icon svg { width: 24px; height: 24px; }
    .announce-badge {
      background: var(--primary);
      color: #fff;
      font-size: 0.8rem;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 4px;
      text-transform: uppercase;
    }
    .announce-content { flex: 1; font-size: 1rem; color: var(--text-muted); }
    .announce-content strong { color: var(--text-main); }
    .announce-time { font-size: 0.85rem; color: var(--text-light); white-space: nowrap; }

    /* Section Shared */
    .section-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 32px;
    }
    
    /* System Status */
    .system-status { margin-bottom: 160px; }
    .status-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    .status-item {
      background: var(--bg-panel);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 24px;
      display: flex;
      align-items: center;
      gap: 16px;
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--text-main);
      box-shadow: var(--shadow-sm);
    }
    .status-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: var(--success);
      box-shadow: 0 0 0 4px var(--success-bg);
    }
    .status-text {
      margin-left: auto;
      font-size: 0.8rem;
      color: var(--success);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* Features */
    .features { margin-bottom: 160px; }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 48px;
    }
    .feature-card {
      display: flex;
      gap: 20px;
    }
    .feature-icon {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-md);
      background: var(--bg-panel);
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      flex-shrink: 0;
      box-shadow: var(--shadow-sm);
    }
    .feature-icon svg { width: 24px; height: 24px; }
    .feature-content h3 {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--text-main);
      margin-bottom: 8px;
    }
    .feature-content p {
      font-size: 0.95rem;
      color: var(--text-muted);
      line-height: 1.6;
    }

    /* FAQ & HR Grid */
    .bottom-grid {
      display: grid;
      grid-template-columns: 3fr 2fr;
      gap: 80px;
      margin-bottom: 120px;
    }
    
    /* FAQ Accordion */
    .faq-list { display: flex; flex-direction: column; }
    .faq-item {
      border-bottom: 1px solid var(--border);
    }
    .faq-item:first-child { border-top: 1px solid var(--border); }
    .faq-btn {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 24px 0;
      font-weight: 600;
      font-size: 1.05rem;
      color: var(--text-main);
      text-align: left;
    }
    .faq-btn:hover { color: var(--primary); }
    .faq-icon {
      width: 24px;
      height: 24px;
      transition: transform var(--transition);
      color: var(--text-light);
    }
    .faq-item.open .faq-icon { transform: rotate(180deg); color: var(--primary); }
    .faq-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
    }
    .faq-inner {
      padding-bottom: 24px;
      font-size: 0.95rem;
      color: var(--text-muted);
      line-height: 1.6;
    }
    .faq-inner a { color: var(--primary); font-weight: 600; }
    .faq-inner a:hover { text-decoration: underline; }

    /* HR Support Card */
    .hr-card {
      background: var(--bg-panel);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 40px 32px;
      box-shadow: var(--shadow-lg);
    }
    .hr-card p {
      font-size: 0.95rem;
      color: var(--text-muted);
      margin-bottom: 32px;
      line-height: 1.6;
    }
    .hr-list { display: flex; flex-direction: column; gap: 24px; }
    .hr-item {
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }
    .hr-icon {
      color: var(--primary);
      margin-top: 4px;
    }
    .hr-icon svg { width: 24px; height: 24px; }
    .hr-details { display: flex; flex-direction: column; }
    .hr-label { font-size: 0.8rem; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .hr-value { font-size: 1rem; font-weight: 600; color: var(--text-main); }
    a.hr-value:hover { color: var(--primary); text-decoration: underline; }
    .hr-hint { font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }

    /* Footer */
    .footer {
      border-top: 1px solid var(--border);
      background: var(--bg-panel);
      padding: 32px 0;
      margin-top: auto;
    }
    .footer-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }
    .footer-copy {
      display: flex;
      flex-direction: column;
      font-size: 0.875rem;
      color: var(--text-muted);
    }
    .footer-credit { font-size: 0.75rem; color: var(--text-light); margin-top: 4px; }
    .footer-links { display: flex; gap: 24px; }
    .footer-links a { font-size: 0.875rem; font-weight: 500; color: var(--text-muted); }
    .footer-links a:hover { color: var(--primary); }

    /* Modal */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(4px);
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      opacity: 0;
      pointer-events: none;
      transition: opacity var(--transition);
    }
    .modal-overlay.open { opacity: 1; pointer-events: auto; }
    .modal {
      background: var(--bg-panel);
      width: 100%;
      max-width: 400px;
      border-radius: var(--radius-lg);
      padding: 32px;
      position: relative;
      transform: scale(0.95);
      transition: transform var(--transition);
      box-shadow: var(--shadow-lg);
      text-align: center;
      border: 1px solid var(--border);
    }
    .modal-overlay.open .modal { transform: scale(1); }
    .modal-close {
      position: absolute;
      top: 16px;
      right: 16px;
      color: var(--text-muted);
      padding: 8px;
    }
    .modal-close:hover { color: var(--text-main); }
    .modal-icon {
      width: 48px;
      height: 48px;
      background: rgba(127, 29, 29, 0.1);
      color: var(--secondary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .modal-title { font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
    .modal-desc { font-size: 0.875rem; color: var(--text-muted); margin-bottom: 24px; }
    .qr-container {
      background: #ffffff;
      padding: 16px;
      border-radius: var(--radius-md);
      border: 1px solid #e2e8f0;
      display: inline-block;
      margin-bottom: 24px;
    }
    #qrCanvasWrap img, #qrCanvasWrap canvas { display: block; margin: 0 auto; width: 160px !important; height: 160px !important; }
    .modal-note { font-size: 0.75rem; color: var(--text-light); margin-top: 16px; }

    /* Responsive */
    @media (max-width: 992px) {
      .quick-access, .status-grid, .features-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
      .bottom-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .hero { grid-template-columns: 1fr; gap: 32px; }
      .hero-content { text-align: center; }
      .hero-actions { flex-direction: column; justify-content: center; }
      .hero-actions .btn { width: 100%; }
      .hero-title { font-size: 2rem; }
      .nav-container { height: 64px; }
      .brand-subtitle { display: none; }
      .footer-content { flex-direction: column; text-align: center; }
      .footer-links { justify-content: center; width: 100%; }
    }
    
    @media (prefers-reduced-motion: reduce) {
      * { transition-duration: 0.01ms !important; }
      .qa-card:hover { transform: none; }
      .btn-primary:hover { transform: none; }
      .reveal { opacity: 1; transform: none; }
      .reveal-stagger > * { opacity: 1; transform: none; }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="navbar">
    <div class="container nav-container">
      <a href="{{ url('/') }}" class="brand">
        <img src="{{ asset('images/logo.png') }}" alt="MCC Logo">
        <div class="brand-text">
          <span class="brand-title">MCC Payroll</span>
          <span class="brand-subtitle">Madridejos Community College</span>
        </div>
      </a>
      <div class="nav-actions">
        <a href="{{ url('/register') }}" class="btn btn-outline" aria-label="Register Account">
          Register
        </a>
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
          <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
          <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
        </button>
      </div>
    </div>
  </header>

  <main>
    <!-- Hero -->
    <section class="hero container reveal">
      <div class="hero-content">
        <span class="hero-kicker">Madridejos Community College</span>
        <h1 class="hero-title">Payroll & Attendance Portal</h1>
        <p class="hero-desc">Welcome to the central administrative hub. Access your digital payslips, review logs, verify attendance, and submit weekly timesheets securely.</p>
        <div class="hero-clock" aria-label="Real-time Institutional Clock">
          <strong id="clockTime">--:--:--</strong> <span id="clockDate">&hellip;</span>
        </div>
      </div>
      <div class="hero-image">
        <img src="{{ asset('images/mcc.jpg') }}" alt="Madridejos Community College">
      </div>
    </section>

    <div class="container">
      <!-- Quick Access Cards -->
      <section class="quick-access reveal reveal-stagger" aria-label="Quick Access Portals">
        <a href="{{ url('/employee/login') }}" class="qa-card qa-employee">
          <div class="qa-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <div class="qa-header">
            <h2 class="qa-title">Employee Portal</h2>
            <span class="qa-badge">Self-Service</span>
          </div>
          <p class="qa-desc">View digital payslips, inspect attendance records, and manage personal timesheets.</p>
          <div class="qa-action">
            Access Portal
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
        </a>

        <a href="{{ url('/attendance/attendlog') }}" class="qa-card qa-attendance">
          <div class="qa-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="qa-header">
            <h2 class="qa-title">Attendance Log</h2>
            <span class="qa-badge">On-Site</span>
          </div>
          <p class="qa-desc">Clock in, verify hours, and log on-site daily attendance. Restricted to authorized check-in.</p>
          <div class="qa-action">
            Log Attendance
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
        </a>

        <button type="button" class="qa-card qa-app" id="portal-download" aria-haspopup="dialog" aria-controls="qrModalOverlay">
          <div class="qa-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
          </div>
          <div class="qa-header">
            <h2 class="qa-title">Mobile App</h2>
            <span class="qa-badge">Android APK</span>
          </div>
          <p class="qa-desc">Verify timesheets, check logs, and view pay-periods instantly on-the-go with our app.</p>
          <div class="qa-action">
            Download App
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
        </button>
      </section>

      <!-- Announcement -->
      @if(isset($announcement) && $announcement)
      <section class="announcement reveal" aria-label="System Announcement">
        <div class="announcement-card">
          <div class="announce-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <span class="announce-badge">{{ ucfirst($announcement->type ?? 'Notice') }}</span>
          <div class="announce-content">
            <strong>{{ $announcement->title }}</strong> — {{ Str::limit($announcement->message, 80) }}
          </div>
          <span class="announce-time">{{ $announcement->created_at->diffForHumans() }}</span>
        </div>
      </section>
      @endif

      <!-- System Status -->
      <section class="system-status reveal" aria-label="System Status">
        <h2 class="section-title">System Status</h2>
        <div class="status-grid">
          <div class="status-item">
            <div class="status-dot"></div> Employee Portal <span class="status-text">Operational</span>
          </div>
          <div class="status-item">
            <div class="status-dot"></div> Attendance Terminal <span class="status-text">Operational</span>
          </div>
          <div class="status-item">
            <div class="status-dot"></div> Mobile App Services <span class="status-text">Operational</span>
          </div>
        </div>
      </section>

      <!-- Features -->
      <section class="features reveal" aria-label="System Capabilities">
        <h2 class="section-title">Key Features</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
            <div class="feature-content">
              <h3>Encrypted Sessions</h3>
              <p>End-to-end encryption with role-based access tokens.</p>
            </div>
          </div>
          <div class="feature-card">
            <div class="feature-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
            <div class="feature-content">
              <h3>Direct Payslips</h3>
              <p>View your calculations and download past receipts instantly.</p>
            </div>
          </div>
          <div class="feature-card">
            <div class="feature-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            <div class="feature-content">
              <h3>Live Attendance</h3>
              <p>Real-time terminal synchronization with HR records.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- FAQ & HR Support -->
      <section class="bottom-grid reveal">
        <div class="faq-section">
          <h2 class="section-title">Frequently Asked Questions</h2>
          <div class="faq-list">
            <div class="faq-item" data-faq>
              <button class="faq-btn" aria-expanded="false">
                How do I register or activate an account?
                <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="faq-content">
                <div class="faq-inner">Your core profile is generated by the Human Resources Department. If you have been provided with an institutional registration access code, click the <a href="{{ url('/register') }}">Register</a> link in the header to activate your login credentials.</div>
              </div>
            </div>
            <div class="faq-item" data-faq>
              <button class="faq-btn" aria-expanded="false">
                I forgot my password. How do I reset it?
                <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="faq-content">
                <div class="faq-inner">Dedicated Attendance Checkers can perform password recovery directly from the Attendance Login interface. Employee, Faculty, and Admin profiles must contact the Human Resources office to issue a manual password reset.</div>
              </div>
            </div>
            <div class="faq-item" data-faq>
              <button class="faq-btn" aria-expanded="false">
                When are pay period payslips uploaded?
                <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="faq-content">
                <div class="faq-inner">Digital payslips are issued simultaneously with paychecks at the end of each payroll period. A copy is auto-delivered to your registered institutional email, and stays permanently archived in the Employee Portal.</div>
              </div>
            </div>
            <div class="faq-item" data-faq>
              <button class="faq-btn" aria-expanded="false">
                Is my personal payroll and timesheet data secure?
                <svg class="faq-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="faq-content">
                <div class="faq-inner">Yes. Your data is protected under robust role-based privilege systems. Employees can exclusively review their own private evaluations, and all transaction histories are securely logged to audit files.</div>
              </div>
            </div>
          </div>
        </div>

        <div class="hr-section">
          <h2 class="section-title">HR Support</h2>
          <div class="hr-card">
            <p>Reach out directly to our Human Resources administrator for registration, scheduling, or technical support.</p>
            <div class="hr-list">
              <div class="hr-item">
                <svg class="hr-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <div class="hr-details">
                  <span class="hr-label">Email Support</span>
                  <a href="mailto:wendelldenorte@gmail.com" class="hr-value">wendelldenorte@gmail.com</a>
                  <span class="hr-hint">Replies in 1-2 business days</span>
                </div>
              </div>
              <div class="hr-item">
                <svg class="hr-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <div class="hr-details">
                  <span class="hr-label">Inquiries & Hotlines</span>
                  <a href="tel:+639638620157" class="hr-value">+63 963 862 0157</a>
                  <span class="hr-hint">Mon-Fri, 8:00 AM - 5:00 PM</span>
                </div>
              </div>
              <div class="hr-item">
                <svg class="hr-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <div class="hr-details">
                  <span class="hr-label">Office Location</span>
                  <span class="hr-value">HR Office, Admin Building</span>
                  <span class="hr-hint">Madridejos Community College</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <footer class="footer">
    <div class="container footer-content">
      <div class="footer-copy">
        <span>&copy; {{ date('Y') }} Madridejos Community College</span>
        <span class="footer-credit">Developed by Ronyl Parochel</span>
      </div>
      <div class="footer-links">
        <a href="{{ url('/register') }}">Create Account</a>
        <a href="{{ url('/terms') }}">Terms of Service</a>
      </div>
    </div>
  </footer>

  <!-- Modal -->
  <div class="modal-overlay" id="qrModalOverlay" role="dialog" aria-modal="true" aria-labelledby="qrModalTitle">
    <div class="modal">
      <button type="button" class="modal-close" id="qrModalClose" aria-label="Close dialog">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>

      <div class="modal-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
      </div>
      <h2 class="modal-title" id="qrModalTitle">Install Mobile App</h2>
      <p class="modal-desc">Scan the QR code below on your Android device to direct-install the MCC Payroll app package.</p>
      
      <div class="qr-container">
        <div id="qrCanvasWrap"></div>
      </div>

      <a href="{{ asset('downloads/mcc-employee-app.apk') }}" class="btn btn-primary" style="width: 100%;" download>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
        Download APK Directly
      </a>
      <p class="modal-note">Available for Android. Ensure external package installations are enabled in your security settings.</p>
    </div>
  </div>

  <script>
    // Theme toggle
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

    // Live institutional clock
    (function () {
      const timeEl = document.getElementById('clockTime');
      const dateEl = document.getElementById('clockDate');
      function tick() {
        const now = new Date();
        timeEl.textContent = now.toLocaleTimeString('en-US', {
          hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
        dateEl.textContent = now.toLocaleDateString('en-US', {
          weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
        });
      }
      tick();
      setInterval(tick, 1000);
    })();

    // FAQ Accordion mechanism
    (function () {
      document.querySelectorAll('[data-faq]').forEach(function (item) {
        const btn = item.querySelector('.faq-btn');
        const wrap = item.querySelector('.faq-content');

        btn.addEventListener('click', function () {
          const isOpen = item.classList.contains('open');

          document.querySelectorAll('[data-faq].open').forEach(function (other) {
            if (other !== item) {
              other.classList.remove('open');
              other.querySelector('.faq-btn').setAttribute('aria-expanded', 'false');
              other.querySelector('.faq-content').style.maxHeight = null;
            }
          });

          if (isOpen) {
            item.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
            wrap.style.maxHeight = null;
          } else {
            item.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
            wrap.style.maxHeight = wrap.scrollHeight + 'px';
          }
        });
      });
    })();

    // QR download modal logic
    (function () {
      const apkUrl = "{{ asset('downloads/mcc-employee-app.apk') }}";
      const trigger = document.getElementById('portal-download');
      const overlay = document.getElementById('qrModalOverlay');
      const closeBtn = document.getElementById('qrModalClose');
      const qrWrap = document.getElementById('qrCanvasWrap');
      let qrGenerated = false;
      let lastFocused = null;

      function openModal() {
        if (!qrGenerated) {
          if (window.QRCode) {
            try {
              qrWrap.innerHTML = '';
              new QRCode(qrWrap, {
                text: apkUrl,
                width: 160,
                height: 160,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
              });
              qrGenerated = true;
            } catch (err) {
              qrWrap.innerHTML = '<span class="modal-note">Could not generate the QR code. Tap download button to install APK directly.</span>';
            }
          } else {
            qrWrap.innerHTML = '<span class="modal-note">QR engine unavailable. Tap download button to install APK directly.</span>';
          }
        }
        lastFocused = document.activeElement;
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
      }

      function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        if (lastFocused) lastFocused.focus();
      }

      trigger.addEventListener('click', openModal);
      closeBtn.addEventListener('click', closeModal);
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
      });
    })();

    // SwAlert integrations for registrations
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Registration Successful!',
        text: '{{ session("success") }}',
        confirmButtonColor: '#1e3a8a',
        confirmButtonText: 'Continue to Login'
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        text: '{{ session("error") }}',
        confirmButtonColor: '#7f1d1d'
      });
    @endif

    // Scroll reveal animations
    (function () {
      var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var reveals = document.querySelectorAll('.reveal');

      if (prefersReduced) {
        reveals.forEach(function (el) { el.classList.add('visible'); });
        return;
      }

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

      reveals.forEach(function (el) { observer.observe(el); });
    })();
  </script>
</body>
</html>
