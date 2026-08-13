<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MCC Payroll — Madridejos Community College</title>
  <meta name="description" content="Payroll Management System for Madridejos Community College. Manage attendance, payroll, and payslips.">
  <meta name="theme-color" content="#060911">
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

  {{-- Applies the theme before first paint to avoid a light-to-dark flash --}}
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
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-page: #f8fafc;
      --bg-panel: #ffffff;
      --bg-panel-solid: #ffffff;
      
      --border-color: #e2e8f0;
      --border-color-soft: #f1f5f9;
      --border-width: 1px;

      --text-primary: #0f172a;
      --text-secondary: #475569;
      --text-tertiary: #64748b;

      --accent-blue: #1e4fbf;
      --accent-blue-hover: #163e9b;
      --accent-blue-soft: #f0f4ff;

      --accent-maroon: #7e1618;
      --accent-maroon-hover: #631113;
      --accent-maroon-soft: #fef2f2;

      --accent-slate: #334155;
      --accent-slate-soft: #f8fafc;

      --radius-lg: 16px;
      --radius-md: 12px;
      --radius-sm: 8px;
      --radius-pill: 999px;

      --shadow-soft-lg: 0 10px 25px -3px rgba(15, 23, 42, 0.03), 0 4px 12px -4px rgba(15, 23, 42, 0.02);
      --shadow-soft-md: 0 8px 16px -2px rgba(15, 23, 42, 0.02), 0 2px 6px -2px rgba(15, 23, 42, 0.01);
      --shadow-soft-sm: 0 2px 8px -1px rgba(15, 23, 42, 0.02);
      --shadow-focus: 0 0 0 3px rgba(30, 79, 191, 0.08);

      --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    [data-theme="dark"] {
      --bg-page: #0b0f19;
      --bg-panel: #0f172a;
      --bg-panel-solid: #1e293b;

      --border-color: #1e293b;
      --border-color-soft: #111827;

      --text-primary: #f8fafc;
      --text-secondary: #94a3b8;
      --text-tertiary: #64748b;

      --accent-blue: #60a5fa;
      --accent-blue-hover: #93c5fd;
      --accent-blue-soft: rgba(96, 165, 250, 0.06);

      --accent-maroon: #f87171;
      --accent-maroon-hover: #fca5a5;
      --accent-maroon-soft: rgba(248, 113, 113, 0.06);

      --accent-slate: #94a3b8;
      --accent-slate-soft: rgba(148, 163, 184, 0.06);

      --shadow-soft-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.2), 0 4px 12px -4px rgba(0, 0, 0, 0.1);
      --shadow-soft-md: 0 8px 16px -2px rgba(0, 0, 0, 0.15), 0 2px 6px -2px rgba(0, 0, 0, 0.08);
      --shadow-soft-sm: 0 2px 8px -1px rgba(0, 0, 0, 0.1);
    }

    html { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; scroll-behavior: smooth; }

    /* ===================== MINIMALIST BACKGROUND ===================== */
    body {
      font-family: var(--font);
      font-size: 0.9rem;
      color: var(--text-primary);
      line-height: 1.5;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow-x: hidden;
      
      background-color: var(--bg-page);
      transition: background-color 0.25s ease;
    }

    /* ===================== LAYOUT CONTAINERS ===================== */
    .app-container {
      width: 100%;
      max-width: 1120px;
      margin: 0 auto;
      padding-inline: clamp(16px, 4vw, 32px);
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }

    /* ===================== NAVBAR ===================== */
    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 72px;
      margin-top: 16px;
      padding: 0 20px;
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-soft-sm);
      z-index: 100;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text-primary);
    }

    .brand-mark {
      width: 34px;
      height: 34px;
      padding: 1px;
      background: #ffffff;
      border: var(--border-width) solid var(--border-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .brand-mark img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }

    .brand-info {
      display: flex;
      flex-direction: column;
      line-height: 1.2;
    }

    .brand-name {
      font-size: 0.95rem;
      font-weight: 700;
      letter-spacing: -0.02em;
    }

    .brand-subtitle {
      font-size: 0.72rem;
      color: var(--text-tertiary);
      font-weight: 500;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-outline {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-height: 38px;
      padding: 0 16px;
      font-family: inherit;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-primary);
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-pill);
      cursor: pointer;
      text-decoration: none;
      box-shadow: var(--shadow-soft-sm);
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-outline:hover {
      background: var(--border-color-soft);
      border-color: var(--text-tertiary);
      transform: translateY(-1px);
    }

    .btn-outline:active {
      transform: translateY(0);
    }

    .btn-outline svg {
      width: 14px;
      height: 14px;
    }

    .theme-toggle-btn {
      width: 38px;
      padding: 0;
      justify-content: center;
    }

    .theme-toggle-btn .icon-moon { display: none; }
    [data-theme="dark"] .theme-toggle-btn .icon-sun { display: none; }
    [data-theme="dark"] .theme-toggle-btn .icon-moon { display: block; }

    /* ===================== HERO / PORTALS MAIN SECTION ===================== */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1.1fr 1.0fr;
      gap: clamp(24px, 5vw, 48px);
      align-items: center;
      margin-top: 40px;
      margin-bottom: 56px;
    }

    /* Left Column: Branding, Clock & Hero Content */
    .intro-panel {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .institution-pill {
      align-self: flex-start;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--text-tertiary);
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      padding: 6px 14px;
      border-radius: var(--radius-pill);
      box-shadow: var(--shadow-soft-sm);
    }

    .institution-pill .dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10b981; /* Soft green */
    }

    .intro-title h1 {
      font-size: clamp(2.1rem, 4vw, 2.8rem);
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.03em;
      margin-bottom: 8px;
    }

    .intro-title h2 {
      font-size: clamp(1.3rem, 2.5vw, 1.7rem);
      font-weight: 600;
      line-height: 1.2;
      color: var(--accent-blue);
      letter-spacing: -0.02em;
    }

    .intro-desc {
      font-size: 0.92rem;
      color: var(--text-secondary);
      line-height: 1.6;
      max-width: 460px;
    }

    /* Digital Clock Card */
    .clock-card {
      align-self: flex-start;
      display: inline-flex;
      flex-direction: column;
      gap: 2px;
      padding: 14px 20px;
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-soft-sm);
    }

    .clock-time {
      font-size: 1.5rem;
      font-weight: 700;
      font-variant-numeric: tabular-nums;
      letter-spacing: -0.02em;
      color: var(--text-primary);
    }

    .clock-date {
      font-size: 0.72rem;
      font-weight: 500;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: var(--text-tertiary);
    }

    /* Right Column: Portal Stack */
    .portal-stack {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .portal-card {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 20px;
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-soft-md);
      text-decoration: none;
      color: inherit;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .portal-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-soft-lg);
      border-color: var(--portal-accent);
    }

    .portal-card:active {
      transform: translateY(0);
    }

    .portal-card--employee { --portal-accent: var(--accent-blue); --portal-soft: var(--accent-blue-soft); }
    .portal-card--attendance { --portal-accent: var(--text-primary); --portal-soft: var(--accent-slate-soft); }
    .portal-card--app { --portal-accent: var(--accent-maroon); --portal-soft: var(--accent-maroon-soft); }

    .portal-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 46px;
      height: 46px;
      border-radius: var(--radius-md);
      background: var(--portal-soft);
      color: var(--portal-accent);
      flex-shrink: 0;
      transition: transform 0.2s ease;
    }

    .portal-icon svg {
      width: 22px;
      height: 22px;
      stroke: currentColor;
      stroke-width: 1.8;
    }

    .portal-info {
      flex-grow: 1;
      min-width: 0;
    }

    .portal-info h3 {
      font-size: 1.0rem;
      font-weight: 700;
      letter-spacing: -0.015em;
      margin-bottom: 2px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .portal-badge {
      font-size: 0.62rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      color: var(--portal-accent);
      background: var(--portal-soft);
      border: var(--border-width) solid var(--border-color);
      padding: 2px 8px;
      border-radius: var(--radius-pill);
    }

    .portal-info p {
      font-size: 0.82rem;
      color: var(--text-secondary);
      line-height: 1.4;
    }

    .portal-chevron {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      color: var(--text-tertiary);
      transition: all 0.2s ease;
    }

    .portal-card:hover .portal-chevron {
      color: var(--portal-accent);
      transform: translateX(2px);
    }

    /* ===================== ANNOUNCEMENT BANNER ===================== */
    .announcement-bar {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 18px;
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-soft-sm);
      margin-bottom: 16px;
      font-size: 0.82rem;
    }

    .announcement-badge {
      font-size: 0.62rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      color: var(--accent-blue);
      background: var(--accent-blue-soft);
      padding: 2px 8px;
      border-radius: var(--radius-pill);
      flex-shrink: 0;
    }

    .announcement-content {
      color: var(--text-secondary);
      flex-grow: 1;
      min-width: 0;
    }

    .announcement-content strong {
      color: var(--text-primary);
      font-weight: 600;
    }

    .announcement-date {
      font-size: 0.72rem;
      color: var(--text-tertiary);
      font-weight: 500;
      flex-shrink: 0;
    }

    /* ===================== FEATURES STRIP ===================== */
    .features-strip {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: clamp(16px, 3vw, 24px);
      padding: 24px;
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-soft-sm);
      margin-bottom: 56px;
    }

    .feature-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
    }

    .feature-icon-wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: var(--radius-sm);
      background: var(--accent-blue-soft);
      color: var(--accent-blue);
      flex-shrink: 0;
    }

    .feature-icon-wrapper svg {
      width: 18px;
      height: 18px;
      stroke-width: 1.8;
    }

    .feature-text h4 {
      font-size: 0.9rem;
      font-weight: 700;
      letter-spacing: -0.01em;
      margin-bottom: 2px;
    }

    .feature-text p {
      font-size: 0.76rem;
      color: var(--text-secondary);
      line-height: 1.4;
    }

    /* ===================== GRID BELOW THE FOLD ===================== */
    .secondary-grid {
      display: grid;
      grid-template-columns: 1.25fr 0.75fr;
      gap: clamp(24px, 5vw, 48px);
      margin-bottom: 56px;
    }

    /* FAQ accordion */
    .faq-panel h2 {
      font-size: 1.4rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      margin-bottom: 20px;
    }

    .faq-accordion {
      display: flex;
      flex-direction: column;
    }

    .faq-item {
      background: transparent;
      border-bottom: var(--border-width) solid var(--border-color);
      transition: all 0.2s ease;
    }

    .faq-item:first-child {
      border-top: var(--border-width) solid var(--border-color);
    }

    .faq-q {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 16px 8px;
      background: transparent;
      border: none;
      cursor: pointer;
      font: inherit;
      font-size: 0.86rem;
      font-weight: 600;
      color: var(--text-primary);
      text-align: left;
    }

    .faq-q:hover {
      color: var(--accent-blue);
    }

    .faq-icon-arrow {
      width: 16px;
      height: 16px;
      stroke: var(--text-tertiary);
      stroke-width: 2.2;
      transition: transform 0.25s ease;
    }

    .faq-item.open .faq-icon-arrow {
      transform: rotate(180deg);
      stroke: var(--accent-blue);
    }

    .faq-a-wrap {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .faq-a {
      padding: 0 8px 16px;
      font-size: 0.8rem;
      color: var(--text-secondary);
      line-height: 1.5;
    }

    .faq-a a {
      color: var(--accent-blue);
      font-weight: 600;
      text-decoration: none;
    }
    .faq-a a:hover {
      text-decoration: underline;
    }

    /* Support Card */
    .support-card {
      align-self: flex-start;
      display: flex;
      flex-direction: column;
      gap: 18px;
      padding: 24px;
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-soft-md);
    }

    .support-card-head {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .support-card-head h2 {
      font-size: 1.15rem;
      font-weight: 700;
      letter-spacing: -0.015em;
    }

    .support-card-head p {
      font-size: 0.78rem;
      color: var(--text-secondary);
    }

    .support-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .support-row {
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .support-row-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: var(--radius-sm);
      background: var(--accent-blue-soft);
      color: var(--accent-blue);
      flex-shrink: 0;
    }

    .support-row-icon svg {
      width: 16px;
      height: 16px;
      stroke-width: 1.8;
    }

    .support-details {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .support-label {
      font-size: 0.62rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: var(--text-tertiary);
      margin-bottom: 1px;
    }

    .support-value {
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--text-primary);
      text-decoration: none;
      word-break: break-word;
    }

    .support-value:hover {
      color: var(--accent-blue);
      text-decoration: underline;
    }

    .support-hint {
      font-size: 0.7rem;
      color: var(--text-tertiary);
      margin-top: 1px;
    }

    /* ===================== FOOTER ===================== */
    .site-footer {
      margin-top: auto;
      border-top: var(--border-width) solid var(--border-color);
      background: var(--bg-panel);
    }

    .footer-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      padding-block: 20px;
      font-size: 0.78rem;
      color: var(--text-tertiary);
    }

    .footer-copy {
      display: flex;
      flex-direction: column;
      gap: 1px;
    }

    .footer-credit {
      font-size: 0.7rem;
      color: var(--text-tertiary);
      font-weight: 500;
    }

    .footer-links {
      display: flex;
      gap: 20px;
    }

    .footer-links a {
      color: var(--text-secondary);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.15s ease;
    }

    .footer-links a:hover {
      color: var(--accent-blue);
    }

    /* ===================== QR CODE DOWNLOAD MODAL ===================== */
    .modal-overlay {
      position: fixed;
      inset: 0;
      z-index: 350;
      background: rgba(11, 15, 25, 0.4);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }

    .modal-overlay.open {
      opacity: 1;
      pointer-events: auto;
    }

    .modal {
      position: relative;
      width: 100%;
      max-width: 340px;
      background: var(--bg-panel);
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-lg);
      padding: 30px 24px 22px;
      text-align: center;
      box-shadow: var(--shadow-soft-lg);
      max-height: calc(100vh - 40px);
      overflow-y: auto;
      transform: translateY(10px);
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .modal-overlay.open .modal {
      transform: none;
    }

    .modal-close {
      position: absolute;
      top: 14px;
      right: 14px;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: transparent;
      border: var(--border-width) solid var(--border-color);
      color: var(--text-tertiary);
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .modal-close:hover {
      color: var(--text-primary);
      background: var(--border-color-soft);
    }

    .modal-close svg {
      width: 14px;
      height: 14px;
    }

    .modal-head {
      margin-bottom: 18px;
    }

    .modal-kicker {
      display: inline-block;
      font-size: 0.64rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      color: var(--accent-maroon);
      background: var(--accent-maroon-soft);
      border: var(--border-width) solid var(--border-color);
      padding: 2px 8px;
      border-radius: var(--radius-pill);
      margin-bottom: 8px;
    }

    .modal-head h2 {
      font-size: 1.15rem;
      font-weight: 700;
      letter-spacing: -0.015em;
      margin-bottom: 4px;
    }

    .modal-sub {
      font-size: 0.78rem;
      color: var(--text-secondary);
    }

    .qr-box {
      display: flex;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      border: var(--border-width) solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 12px;
      margin-bottom: 18px;
    }

    #qrCanvasWrap {
      width: 168px;
      height: 168px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #qrCanvasWrap img, #qrCanvasWrap canvas {
      display: block;
      width: 168px !important;
      height: 168px !important;
    }

    .btn-solid {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      min-height: 42px;
      font-family: inherit;
      font-size: 0.82rem;
      font-weight: 600;
      color: #ffffff;
      background: var(--accent-blue);
      border: none;
      border-radius: var(--radius-pill);
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 4px 10px rgba(30, 79, 191, 0.15);
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-solid:hover {
      background: var(--accent-blue-hover);
      transform: translateY(-1px);
      box-shadow: 0 6px 14px rgba(30, 79, 191, 0.22);
    }

    .btn-solid:active {
      transform: translateY(0);
    }

    .btn-solid svg {
      width: 15px;
      height: 15px;
    }

    .modal-note {
      font-size: 0.7rem;
      color: var(--text-tertiary);
      line-height: 1.4;
      margin-top: 12px;
    }

    .qr-fallback {
      font-size: 0.7rem;
      color: var(--text-tertiary);
      text-align: center;
      line-height: 1.4;
      padding: 0 8px;
    }

    /* ===================== RESPONSIVE STYLES ===================== */
    @media (max-width: 900px) {
      .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 32px;
        margin-top: 32px;
        text-align: center;
      }

      .institution-pill, .clock-card {
        align-self: center;
      }

      .intro-desc {
        max-width: 100%;
      }

      .secondary-grid {
        grid-template-columns: 1fr;
        gap: 32px;
      }

      .features-strip {
        grid-template-columns: 1fr;
        gap: 16px;
      }
    }

    @media (max-width: 600px) {
      .navbar {
        margin-top: 12px;
        height: 64px;
      }

      .brand-subtitle {
        display: none;
      }

      .footer-inner {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
      }
    }

    @media (max-width: 400px) {
      .btn-outline .btn-text {
        display: none;
      }
      .btn-outline {
        padding: 0 10px;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .portal-card:hover, .btn-solid:hover, .btn-outline:hover { transform: none !important; box-shadow: none !important; }
      * { transition-duration: 0.01ms !important; }
    }
  </style>
</head>
<body>

  <div class="app-container">

    <!-- ===================== NAVBAR ===================== -->
    <header class="navbar">
      <a href="{{ url('/') }}" class="brand">
        <span class="brand-mark"><img src="{{ asset('images/logo.png') }}" alt="MCC Logo"></span>
        <div class="brand-info">
          <span class="brand-name">MCC Payroll</span>
          <span class="brand-subtitle">Madridejos Community College</span>
        </div>
      </a>
      <div class="nav-actions">
        <a href="{{ url('/register') }}" class="btn-outline" aria-label="Register Account">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/>
            <line x1="22" y1="11" x2="16" y2="11"/>
          </svg>
          <span class="btn-text">Register</span>
        </a>
        <button class="btn-outline theme-toggle-btn" id="themeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
          <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
          </svg>
          <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
          </svg>
        </button>
      </div>
    </header>

    <main id="main">

      <!-- ===================== HERO GRID ===================== -->
      <section class="dashboard-grid">

        <!-- Left Column: Branding and Intro -->
        <div class="intro-panel">
          <div class="institution-pill">
            <span class="dot" aria-hidden="true"></span>
            <span>Integritas &middot; Honor &middot; Servitium</span>
          </div>
          <div class="intro-title">
            <h1>Madridejos Community College</h1>
            <h2>Payroll &amp; Attendance Portal</h2>
          </div>
          <p class="intro-desc">Welcome to the central administrative hub. Access your digital payslips, review logs, verify attendance, and submit weekly timesheets securely.</p>

          <!-- Digital Clock Card -->
          <div class="clock-card" aria-label="Real-time Institutional Clock">
            <span class="clock-time" id="clockTime">--:--:--</span>
            <span class="clock-date" id="clockDate">&hellip;</span>
          </div>
        </div>

        <!-- Right Column: Portal Cards Stack -->
        <div class="portal-stack" aria-label="Available Portals">

          @if(isset($announcement) && $announcement)
            <div class="announcement-bar">
              <span class="announcement-badge">{{ ucfirst($announcement->type ?? 'Notice') }}</span>
              <div class="announcement-content">
                <strong>{{ $announcement->title }}</strong> — {{ Str::limit($announcement->message, 80) }}
              </div>
              <span class="announcement-date">{{ $announcement->created_at->diffForHumans() }}</span>
            </div>
          @endif

          <!-- Employee Portal -->
          <a href="{{ url('/employee/login') }}" class="portal-card portal-card--employee">
            <div class="portal-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <div class="portal-info">
              <h3>Employee Portal <span class="portal-badge">Self-Service</span></h3>
              <p>View digital payslips, inspect attendance records, and manage personal timesheets.</p>
            </div>
            <div class="portal-chevron" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </div>
          </a>

          <!-- Attendance Log Portal -->
          <a href="{{ url('/attendance/attendlog') }}" class="portal-card portal-card--attendance">
            <div class="portal-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
            </div>
            <div class="portal-info">
              <h3>Attendance Log <span class="portal-badge" style="color: var(--text-primary); background: var(--bg-page);">On-Site</span></h3>
              <p>Clock in, verify hours, and log on-site daily attendance. Restricted to authorized terminal check-in.</p>
            </div>
            <div class="portal-chevron" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </div>
          </a>

          <!-- App Download Button -->
          <button type="button" class="portal-card portal-card--app" id="portal-download" aria-haspopup="dialog" aria-controls="qrModalOverlay">
            <span class="portal-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                <line x1="12" y1="18" x2="12.01" y2="18"/>
              </svg>
            </span>
            <span class="portal-info">
              <h3>Download Mobile App <span class="portal-badge" style="color: var(--accent-maroon); background: var(--accent-maroon-soft);">Android APK</span></h3>
              <p>Verify timesheets, check logs, and view pay-periods instantly on-the-go.</p>
            </span>
            <span class="portal-chevron" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </span>
          </button>

        </div>

      </section>

      <!-- ===================== FEATURES STRIP ===================== -->
      <section class="features-strip" aria-label="System Capabilities">
        <div class="feature-item">
          <div class="feature-icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <div class="feature-text">
            <h4>Encrypted Sessions</h4>
            <p>End-to-end encryption with role-based access tokens.</p>
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
          <div class="feature-text">
            <h4>Direct Payslips</h4>
            <p>View your calculations and download past receipts instantly.</p>
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </div>
          <div class="feature-text">
            <h4>Live Attendance</h4>
            <p>Real-time terminal synchronization with HR records.</p>
          </div>
        </div>
      </section>

      <!-- ===================== SECONDARY FAQ & SUPPORT GRID ===================== -->
      <section class="secondary-grid">

        <!-- FAQ Accordions -->
        <div class="faq-panel">
          <h2>Frequently Asked Questions</h2>
          <div class="faq-accordion">

            <div class="faq-item" data-faq>
              <button type="button" class="faq-q" aria-expanded="false">
                How do I register or activate an account?
                <svg class="faq-icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </button>
              <div class="faq-a-wrap">
                <div class="faq-a">Your core profile is generated by the Human Resources Department. If you have been provided with an institutional registration access code, click the <a href="{{ url('/register') }}">Register</a> link in the header to activate your login credentials.</div>
              </div>
            </div>

            <div class="faq-item" data-faq>
              <button type="button" class="faq-q" aria-expanded="false">
                I forgot my password. How do I reset it?
                <svg class="faq-icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </button>
              <div class="faq-a-wrap">
                <div class="faq-a">Dedicated Attendance Checkers can perform password recovery directly from the Attendance Login interface. Employee, Faculty, and Admin profiles must contact the Human Resources office to issue a manual password reset.</div>
              </div>
            </div>

            <div class="faq-item" data-faq>
              <button type="button" class="faq-q" aria-expanded="false">
                When are pay period payslips uploaded?
                <svg class="faq-icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </button>
              <div class="faq-a-wrap">
                <div class="faq-a">Digital payslips are issued simultaneously with paychecks at the end of each payroll period. A copy is auto-delivered to your registered institutional email, and stays permanently archived in the Employee Portal.</div>
              </div>
            </div>

            <div class="faq-item" data-faq>
              <button type="button" class="faq-q" aria-expanded="false">
                Is my personal payroll and timesheet data secure?
                <svg class="faq-icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </button>
              <div class="faq-a-wrap">
                <div class="faq-a">Yes. Your data is protected under robust role-based privilege systems. Employees can exclusively review their own private evaluations, and all transaction histories are securely logged to audit files.</div>
              </div>
            </div>

          </div>
        </div>

        <!-- Contact Support Card -->
        <div class="support-card">
          <div class="support-card-head">
            <h2>Need HR Assistance?</h2>
            <p>Reach out directly to our Human Resources administrator for registration, scheduling, or technical support.</p>
          </div>
          <div class="support-list">
            <div class="support-row">
              <div class="support-row-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                  <polyline points="22,6 12,13 2,6"/>
                </svg>
              </div>
              <div class="support-details">
                <span class="support-label">Email Support</span>
                <a href="mailto:wendelldenorte@gmail.com" class="support-value">wendelldenorte@gmail.com</a>
                <span class="support-hint">Replies in 1-2 business days</span>
              </div>
            </div>
            <div class="support-row">
              <div class="support-row-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
              </div>
              <div class="support-details">
                <span class="support-label">Inquiries &amp; Hotlines</span>
                <a href="tel:+639638620157" class="support-value">+63 963 862 0157</a>
                <span class="support-hint">Mon-Fri, 8:00 AM - 5:00 PM</span>
              </div>
            </div>
            <div class="support-row">
              <div class="support-row-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
              </div>
              <div class="support-details">
                <span class="support-label">Office Location</span>
                <span class="support-value">HR Office, Admin Building</span>
                <span class="support-hint">Madridejos Community College</span>
              </div>
            </div>
          </div>
        </div>

      </section>

    </main>

    <!-- ===================== FOOTER ===================== -->
    <footer class="site-footer">
      <div class="footer-inner">
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

  </div>

  <!-- ===================== QR CODE DOWNLOAD MODAL ===================== -->
  <div class="modal-overlay" id="qrModalOverlay" role="dialog" aria-modal="true" aria-labelledby="qrModalTitle">
    <div class="modal">
      <button type="button" class="modal-close" id="qrModalClose" aria-label="Close dialog">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M18 6 6 18M6 6l12 12"/>
        </svg>
      </button>

      <div class="modal-head">
        <span class="modal-kicker">Mobile Application</span>
        <h2 id="qrModalTitle">Install the APK</h2>
        <p class="modal-sub">Scan the code below on your mobile device to direct-install the system package.</p>
      </div>

      <div class="qr-box">
        <div id="qrCanvasWrap"></div>
      </div>

      <a href="{{ asset('downloads/mcc-employee-app.apk') }}" class="btn-solid" download>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <path d="M12 5v14M19 12l-7 7-7-7"/>
        </svg>
        Download APK Directly
      </a>

      <p class="modal-note">Available for Android operating systems. Be sure to enable external package installations in security preferences.</p>
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
        const btn = item.querySelector('.faq-q');
        const wrap = item.querySelector('.faq-a-wrap');

        btn.addEventListener('click', function () {
          const isOpen = item.classList.contains('open');

          document.querySelectorAll('[data-faq].open').forEach(function (other) {
            if (other !== item) {
              other.classList.remove('open');
              other.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
              other.querySelector('.faq-a-wrap').style.maxHeight = null;
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
                width: 168,
                height: 168,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
              });
              qrGenerated = true;
            } catch (err) {
              qrWrap.innerHTML = '<span class="qr-fallback">Could not generate the QR code. Tap download button to install APK directly.</span>';
            }
          } else {
            qrWrap.innerHTML = '<span class="qr-fallback">QR engine unavailable. Tap download button to install APK directly.</span>';
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
        confirmButtonColor: '#1e4fbf',
        confirmButtonText: 'Continue to Login'
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        text: '{{ session("error") }}',
        confirmButtonColor: '#7e1618'
      });
    @endif
  </script>
</body>
</html>
