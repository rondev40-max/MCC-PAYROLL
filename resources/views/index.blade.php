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
  <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

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
      /* Paper / ledger palette */
      --bg-primary: #FAF7EF;
      --bg-secondary: #F1ECDD;
      --bg-glass: rgba(250, 247, 239, 0.72);
      --bg-glass-border: rgba(27, 36, 32, 0.07);

      --text-primary: #1B2420;
      --text-secondary: #5B6455;
      --text-tertiary: #8B9285;

      --accent: #A9762F;       /* brass */
      --accent-hover: #8F6425;
      --accent-soft: rgba(169, 118, 47, 0.10);
      --accent-glow: rgba(169, 118, 47, 0.20);

      --pine: #1F3D2B;         /* ledger green, secondary ink */
      --pine-soft: rgba(31, 61, 43, 0.08);

      --border: rgba(27, 36, 32, 0.12);
      --border-hover: rgba(27, 36, 32, 0.22);

      --radius-sm: 8px;
      --radius-md: 14px;
      --radius-lg: 20px;
      --radius-pill: 999px;

      --shadow-xs: 0 1px 2px rgba(43, 31, 12, 0.06);
      --shadow-sm: 0 1px 2px rgba(43, 31, 12, 0.04), 0 3px 10px rgba(43, 31, 12, 0.07);
      --shadow-md: 0 2px 4px rgba(43, 31, 12, 0.04), 0 12px 28px rgba(43, 31, 12, 0.10);
      --shadow-lg: 0 4px 10px rgba(43, 31, 12, 0.06), 0 28px 64px rgba(43, 31, 12, 0.16);
      --shadow-card-hover: 0 2px 6px rgba(169, 118, 47, 0.12), 0 18px 40px rgba(169, 118, 47, 0.20);
      --shadow-inset-top: inset 0 1px 0 rgba(255, 255, 255, 0.55);

      --font-display: 'Source Serif 4', Georgia, 'Times New Roman', serif;
      --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      --font-mono: 'IBM Plex Mono', 'SFMono-Regular', Menlo, monospace;
    }

    [data-theme="dark"] {
      --bg-primary: #10140F;
      --bg-secondary: #161B12;
      --bg-glass: rgba(16, 20, 15, 0.78);
      --bg-glass-border: rgba(243, 239, 227, 0.07);

      --text-primary: #F3EFE3;
      --text-secondary: #B5BBA9;
      --text-tertiary: #767E6C;

      --accent: #CC9A50;
      --accent-hover: #E0AD62;
      --accent-soft: rgba(204, 154, 80, 0.12);
      --accent-glow: rgba(204, 154, 80, 0.24);

      --pine: #6FA381;
      --pine-soft: rgba(111, 163, 129, 0.10);

      --border: rgba(243, 239, 227, 0.09);
      --border-hover: rgba(243, 239, 227, 0.18);

      --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.3);
      --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.25), 0 3px 12px rgba(0, 0, 0, 0.28);
      --shadow-md: 0 2px 4px rgba(0, 0, 0, 0.25), 0 12px 30px rgba(0, 0, 0, 0.35);
      --shadow-lg: 0 4px 10px rgba(0, 0, 0, 0.3), 0 28px 64px rgba(0, 0, 0, 0.5);
      --shadow-card-hover: 0 2px 6px rgba(204, 154, 80, 0.16), 0 18px 44px rgba(204, 154, 80, 0.26);
      --shadow-inset-top: inset 0 1px 0 rgba(255, 255, 255, 0.06);
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
      backdrop-filter: blur(20px) saturate(150%);
      -webkit-backdrop-filter: blur(20px) saturate(150%);
      border-bottom: 1px solid var(--bg-glass-border);
      transition: background 0.35s ease;
    }

    .topbar-brand {
      display: flex; align-items: center; gap: 10px;
      font-family: var(--font-display);
      font-weight: 600; font-size: 1rem;
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
      min-height: 62vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding: calc(64px + 56px) 24px 96px;
      isolation: isolate;
    }

    .hero::before {
      content: "";
      position: absolute; inset: 0;
      background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
      filter: blur(2px) brightness(0.3) saturate(1.05) sepia(0.12);
      transform: scale(1.04);
      z-index: -2;
    }

    .hero::after {
      content: "";
      position: absolute; inset: 0;
      background: linear-gradient(
        180deg,
        rgba(16, 20, 15, 0.35) 0%,
        rgba(16, 20, 15, 0.82) 100%
      );
      z-index: -1;
    }

    /* Fine grain texture — a barely-there SVG noise layer that keeps the hero
       from reading as a flat color wash. Kept subtle enough to be felt more
       than seen. */
    .hero-grain {
      position: absolute; inset: 0;
      z-index: -1;
      opacity: 0.05;
      mix-blend-mode: overlay;
      pointer-events: none;
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='120' height='120'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/></filter><rect width='100%25' height='100%25' filter='url(%23n)'/></svg>");
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

    /* Punch-clock readout — the signature element */
    .punch-clock {
      display: inline-flex; align-items: center; gap: 12px;
      font-family: var(--font-mono);
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.16);
      backdrop-filter: blur(8px);
      padding: 10px 18px; border-radius: var(--radius-sm);
      margin-bottom: 26px;
    }
    .punch-clock .dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: #7FD9A6;
      box-shadow: 0 0 6px 1px rgba(127,217,166,0.6);
      animation: pulse-dot 2.4s ease-in-out infinite;
      flex-shrink: 0;
    }
    @keyframes pulse-dot {
      0%, 100% { box-shadow: 0 0 6px 1px rgba(127,217,166,0.6); }
      50%       { box-shadow: 0 0 10px 3px rgba(127,217,166,0.9); }
    }
    .punch-clock-time {
      font-size: 0.95rem; font-weight: 600; letter-spacing: 0.02em;
      color: #fff; font-variant-numeric: tabular-nums;
    }
    .punch-clock-divider { width: 1px; height: 16px; background: rgba(255,255,255,0.2); }
    .punch-clock-date {
      font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase;
      color: rgba(255,255,255,0.65);
    }

    .hero h1 {
      font-family: var(--font-display);
      font-size: clamp(1.9rem, 4vw, 2.9rem);
      font-weight: 600; letter-spacing: -0.01em;
      line-height: 1.15; margin-bottom: 14px;
    }

    .hero p {
      font-size: clamp(0.88rem, 1.5vw, 1rem);
      color: rgba(255,255,255,0.72); line-height: 1.68;
      max-width: 440px; margin: 0 auto 30px;
    }

    /* Scroll hint */
    .scroll-hint {
      display: flex; flex-direction: column;
      align-items: center; gap: 6px;
      opacity: 0.5; font-size: 0.68rem;
      letter-spacing: 0.08em; text-transform: uppercase;
      font-family: var(--font-mono);
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
      margin-top: -56px;
      padding: 0 clamp(16px, 4vw, 40px) 56px;
    }

    .portals-grid {
      display: flex;
      justify-content: center;
      gap: 22px;
      max-width: 1100px;
      margin: 0 auto;
      animation: fadeUp 0.7s ease-out 0.12s both;
    }

    /* Ticket / pass styled portal card */
    .portal-card {
      position: relative;
      display: flex;
      flex-direction: column;
      padding: 26px 26px 24px;
      border-radius: var(--radius-lg);
      background: var(--bg-primary);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-md), var(--shadow-inset-top);
      text-decoration: none;
      transition: all 0.28s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      isolation: isolate;
      flex: 1 1 0;
      min-width: 0;
      max-width: 340px;
      /* reset in case this card is a <button> rather than an <a> */
      font: inherit;
      text-align: left;
      cursor: pointer;
      width: 100%;
      appearance: none;
      -webkit-appearance: none;
    }

    .portal-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-card-hover), var(--shadow-inset-top);
      border-color: var(--accent-glow);
    }

    .portal-card.primary {
      border-color: rgba(169, 118, 47, 0.28);
      background: linear-gradient(160deg, var(--bg-primary) 0%, var(--accent-soft) 100%);
      overflow: hidden;
    }

    /* Subtle light sweep across the primary card on hover — the one deliberate
       "premium" flourish on the page, reserved for the single most-used card
       so it reads as a highlight rather than a gimmick repeated everywhere. */
    .portal-card.primary::before {
      content: "";
      position: absolute;
      top: 0; left: -60%;
      width: 45%; height: 100%;
      background: linear-gradient(
        100deg,
        transparent 0%,
        rgba(255, 255, 255, 0.35) 50%,
        transparent 100%
      );
      transform: skewX(-18deg);
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s ease;
      z-index: 1;
    }
    [data-theme="dark"] .portal-card.primary::before {
      background: linear-gradient(
        100deg,
        transparent 0%,
        rgba(255, 255, 255, 0.10) 50%,
        transparent 100%
      );
    }
    .portal-card.primary:hover::before {
      opacity: 1;
      animation: shine-sweep 1.1s ease forwards;
    }
    @keyframes shine-sweep {
      from { left: -60%; }
      to   { left: 130%; }
    }
    @media (prefers-reduced-motion: reduce) {
      .portal-card.primary::before { display: none; }
    }

    .portal-pass-row {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 18px;
    }
    .portal-pass-code {
      font-family: var(--font-mono);
      font-size: 0.68rem; font-weight: 600; letter-spacing: 0.08em;
      text-transform: uppercase; color: var(--text-tertiary);
    }
    .portal-card .tag {
      font-size: 0.62rem; font-weight: 700; letter-spacing: 0.05em;
      text-transform: uppercase; background: var(--accent); color: #fff;
      padding: 3px 9px; border-radius: var(--radius-pill);
    }

    .portal-icon {
      width: 46px; height: 46px; border-radius: var(--radius-sm);
      background: var(--accent-soft);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 16px; transition: background 0.25s ease;
    }
    .portal-card:hover .portal-icon { background: var(--accent-glow); }
    .portal-icon svg { width: 21px; height: 21px; stroke: var(--accent); }

    .portal-card h3 {
      font-family: var(--font-display);
      font-size: 1.12rem; font-weight: 600;
      margin-bottom: 6px; color: var(--text-primary);
      letter-spacing: -0.005em;
    }

    .portal-card p {
      font-size: 0.84rem; color: var(--text-secondary);
      line-height: 1.58; margin-bottom: 18px; flex-grow: 1;
    }

    /* Tear-line, like a ticket stub, before the CTA */
    .portal-tear {
      position: relative;
      height: 1px;
      margin: 0 -26px 16px;
      background-image: linear-gradient(to right, var(--border) 60%, transparent 0%);
      background-size: 10px 1px;
      background-repeat: repeat-x;
    }
    .portal-tear::before, .portal-tear::after {
      content: "";
      position: absolute; top: 50%; transform: translateY(-50%);
      width: 16px; height: 16px; border-radius: 50%;
      background: var(--bg-secondary);
    }
    .portal-tear::before { left: -8px; }
    .portal-tear::after { right: -8px; }

    .portal-cta {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 0.84rem; font-weight: 650; color: var(--accent);
    }
    .portal-cta svg { width: 14px; height: 14px; transition: transform 0.2s ease; }
    .portal-card:hover .portal-cta svg { transform: translateX(3px); }

    /* ===================== FEATURES STRIP ===================== */
    .features-strip {
      padding: 52px clamp(16px, 4vw, 40px) 56px;
      border-top: 1px solid var(--border);
      background: var(--bg-secondary);
    }

    .features-strip-inner {
      max-width: 860px;
      margin: 0 auto;
    }

    .features-strip-label {
      font-family: var(--font-mono);
      font-size: 0.68rem; font-weight: 600; letter-spacing: 0.1em;
      text-transform: uppercase; color: var(--pine); margin-bottom: 10px;
    }
    .features-strip-heading {
      font-family: var(--font-display);
      font-size: clamp(1.2rem, 2vw, 1.55rem); font-weight: 600;
      color: var(--text-primary); letter-spacing: -0.01em;
      margin-bottom: 30px; line-height: 1.25;
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

    /* ===================== TRUST STRIP ===================== */
    .trust-strip {
      display: flex; flex-wrap: wrap; justify-content: center;
      gap: 10px; max-width: 860px; margin: 0 auto 44px;
      padding: 0 clamp(16px, 4vw, 40px);
    }
    .trust-badge {
      display: inline-flex; align-items: center; gap: 7px;
      font-size: 0.78rem; font-weight: 600; color: var(--text-secondary);
      background: var(--bg-secondary); border: 1px solid var(--border);
      padding: 8px 14px; border-radius: var(--radius-pill);
    }
    .trust-badge svg { width: 14px; height: 14px; stroke: var(--pine); flex-shrink: 0; }

    /* ===================== HOW IT WORKS ===================== */
    .how-it-works {
      padding: 8px clamp(16px, 4vw, 40px) 56px;
      background: var(--bg-primary);
    }
    .how-inner { max-width: 900px; margin: 0 auto; text-align: center; }
    .how-steps {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 22px;
      margin-top: 34px;
      position: relative;
    }
    .how-step { position: relative; padding: 0 10px; }
    .how-step-num {
      font-family: var(--font-display);
      font-size: 2.1rem; font-weight: 600;
      color: var(--accent-glow);
      -webkit-text-stroke: 1.5px var(--accent);
      line-height: 1; margin-bottom: 14px;
    }
    .how-step h4 {
      font-size: 0.94rem; font-weight: 650; color: var(--text-primary); margin-bottom: 6px;
    }
    .how-step p {
      font-size: 0.82rem; color: var(--text-secondary); line-height: 1.55;
    }
    .how-step:not(:last-child)::after {
      content: "";
      position: absolute; top: 18px; left: calc(100% + 4px);
      width: calc(22px - 4px); height: 1px;
      background-image: linear-gradient(to right, var(--border) 60%, transparent 0%);
      background-size: 8px 1px; background-repeat: repeat-x;
      display: none;
    }
    @media (min-width: 641px) {
      .how-step:not(:last-child)::after { display: block; }
    }

    /* ===================== ABOUT MCC ===================== */
    .about-section {
      padding: 8px clamp(16px, 4vw, 40px) 56px;
    }
    .about-inner {
      max-width: 1000px; margin: 0 auto;
      display: grid; grid-template-columns: 1fr 1.1fr;
      gap: 40px; align-items: center;
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px;
    }
    .about-photo {
      width: 100%; aspect-ratio: 4/3; object-fit: cover;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
    }
    .about-label {
      font-family: var(--font-mono);
      font-size: 0.68rem; font-weight: 600; letter-spacing: 0.1em;
      text-transform: uppercase; color: var(--pine); margin-bottom: 10px;
    }
    .about-text h3 {
      font-family: var(--font-display);
      font-size: clamp(1.15rem, 2vw, 1.4rem); font-weight: 600;
      color: var(--text-primary); margin-bottom: 12px; line-height: 1.3;
    }
    .about-text p {
      font-size: 0.86rem; color: var(--text-secondary); line-height: 1.68;
    }
    @media (max-width: 720px) {
      .about-inner { grid-template-columns: 1fr; padding: 22px; gap: 22px; }
    }

    /* ===================== FAQ ===================== */
    .faq-section {
      padding: 8px clamp(16px, 4vw, 40px) 56px;
    }
    .faq-inner { max-width: 720px; margin: 0 auto; }
    .faq-heading-block { text-align: center; margin-bottom: 28px; }
    .faq-list { display: flex; flex-direction: column; gap: 10px; }

    .faq-item {
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      background: var(--bg-primary);
      overflow: hidden;
      transition: border-color 0.2s ease;
    }
    .faq-item.open { border-color: var(--accent-glow); }

    .faq-question {
      width: 100%;
      display: flex; align-items: center; justify-content: space-between;
      gap: 12px;
      padding: 16px 20px;
      background: none; border: none;
      font: inherit; text-align: left; cursor: pointer;
      font-size: 0.88rem; font-weight: 650; color: var(--text-primary);
    }
    .faq-question:hover { color: var(--accent); }
    .faq-chevron {
      flex-shrink: 0; width: 18px; height: 18px;
      stroke: var(--text-tertiary);
      transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .faq-item.open .faq-chevron { transform: rotate(180deg); stroke: var(--accent); }

    .faq-answer-wrap {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .faq-answer {
      padding: 0 20px 18px;
      font-size: 0.83rem; color: var(--text-secondary); line-height: 1.6;
    }
    .faq-answer a { color: var(--accent); font-weight: 600; }


    /* ===================== NOTICE BANNER ===================== */
    .notice-banner {
      margin: 0 clamp(16px, 4vw, 40px) 40px;
      max-width: 760px;
      margin-left: auto;
      margin-right: auto;
      padding: 16px 20px;
      border-radius: var(--radius-md);
      border: 1px solid var(--accent-glow);
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
    .notice-banner-text a { color: var(--accent); font-weight: 600; }

    /* ===================== FOOTER ===================== */
    .site-footer {
      margin-top: auto;
      padding: 24px clamp(16px, 4vw, 40px);
      border-top: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      font-size: 0.78rem; color: var(--text-tertiary);
      background: var(--bg-primary);
    }
    .footer-copy {
      display: flex; flex-direction: column; gap: 2px;
    }
    .footer-credit {
      font-size: 0.7rem; color: var(--text-tertiary); opacity: 0.75;
    }
    .footer-links { display: flex; gap: 16px; }
    .footer-links a { color: var(--text-secondary); transition: color 0.2s ease; }
    .footer-links a:hover { color: var(--accent); }

    /* ===================== QR MODAL ===================== */
    .qr-modal-overlay {
      position: fixed; inset: 0; z-index: 300;
      background: rgba(15, 18, 13, 0.72);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      display: flex; align-items: center; justify-content: center;
      padding: 20px;
      opacity: 0; pointer-events: none;
      transition: opacity 0.25s ease;
    }
    .qr-modal-overlay.open { opacity: 1; pointer-events: auto; }

    .qr-modal {
      position: relative;
      width: 100%; max-width: 340px;
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      padding: 30px 26px 26px;
      text-align: center;
      transform: translateY(14px) scale(0.97);
      transition: transform 0.28s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .qr-modal-overlay.open .qr-modal { transform: translateY(0) scale(1); }

    .qr-modal-close {
      position: absolute; top: 14px; right: 14px;
      width: 32px; height: 32px; border-radius: 50%;
      border: 1px solid var(--border); background: transparent;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: var(--text-secondary); transition: all 0.2s ease;
    }
    .qr-modal-close:hover { background: var(--accent-soft); color: var(--accent); border-color: var(--border-hover); }
    .qr-modal-close svg { width: 15px; height: 15px; }

    .qr-modal-pass-code {
      font-family: var(--font-mono); font-size: 0.66rem; font-weight: 600;
      letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-tertiary);
      margin-bottom: 14px;
    }

    .qr-modal h3 {
      font-family: var(--font-display); font-size: 1.22rem; font-weight: 600;
      color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.005em;
    }
    .qr-modal p.qr-sub {
      font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 22px;
    }

    .qr-box {
      display: inline-flex; background: #fff; padding: 14px;
      border-radius: var(--radius-md); border: 1px solid var(--border);
      box-shadow: var(--shadow-sm); margin-bottom: 20px;
    }
    #qrCanvasWrap { width: 176px; height: 176px; display: flex; align-items: center; justify-content: center; }
    #qrCanvasWrap img, #qrCanvasWrap canvas { display: block; width: 176px !important; height: 176px !important; }

    .qr-modal-tear {
      position: relative; height: 1px; margin: 0 -26px 20px;
      background-image: linear-gradient(to right, var(--border) 60%, transparent 0%);
      background-size: 10px 1px; background-repeat: repeat-x;
    }
    .qr-modal-tear::before, .qr-modal-tear::after {
      content: ""; position: absolute; top: 50%; transform: translateY(-50%);
      width: 16px; height: 16px; border-radius: 50%; background: var(--bg-primary);
    }
    .qr-modal-tear::before { left: -8px; }
    .qr-modal-tear::after { right: -8px; }

    .qr-download-btn {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      width: 100%; color: #fff;
      background: linear-gradient(160deg, var(--accent) 0%, var(--accent-hover) 100%);
      font-size: 0.86rem; font-weight: 650; padding: 13px 20px;
      border-radius: var(--radius-pill); border: none; cursor: pointer;
      text-decoration: none;
      box-shadow: 0 1px 2px rgba(169, 118, 47, 0.15), 0 8px 20px rgba(169, 118, 47, 0.22);
      transition: transform 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.2s ease, filter 0.2s ease;
    }
    .qr-download-btn:hover {
      transform: translateY(-2px);
      filter: brightness(1.05);
      box-shadow: 0 2px 4px rgba(169, 118, 47, 0.18), 0 12px 26px rgba(169, 118, 47, 0.28);
    }
    .qr-download-btn:active { transform: translateY(0); }
    .qr-download-btn svg { width: 15px; height: 15px; }

    .qr-note {
      font-size: 0.72rem; color: var(--text-tertiary); line-height: 1.55; margin-top: 14px;
    }

    .qr-loading, .qr-fallback {
      font-family: var(--font); font-size: 0.74rem; color: var(--text-tertiary);
      text-align: center; line-height: 1.5; padding: 0 10px;
    }
    .qr-loading {
      display: flex; flex-direction: column; align-items: center; gap: 10px;
    }
    .qr-spinner {
      width: 22px; height: 22px; border-radius: 50%;
      border: 2px solid var(--border); border-top-color: var(--accent);
      animation: qr-spin 0.8s linear infinite;
    }
    @keyframes qr-spin { to { transform: rotate(360deg); } }

    /* ===================== ANNOUNCEMENT STRIP ===================== */
    .announce-bar {
      display: flex; align-items: center; gap: 12px;
      max-width: 1100px; margin: 0 auto 20px;
      padding: 12px 18px; border-radius: var(--radius-md);
      background: var(--pine-soft);
      border-left: 3px solid var(--pine);
      animation: fadeUp 0.7s ease-out 0.05s both;
    }
    .announce-tag {
      flex-shrink: 0;
      font-family: var(--font-mono); font-size: 0.62rem; font-weight: 700;
      letter-spacing: 0.06em; text-transform: uppercase;
      background: var(--pine); color: #fff;
      padding: 3px 9px; border-radius: var(--radius-pill);
    }
    .announce-text {
      flex: 1 1 auto; min-width: 0;
      font-size: 0.82rem; color: var(--text-secondary);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .announce-text strong { color: var(--text-primary); font-weight: 650; }
    .announce-date {
      flex-shrink: 0;
      font-family: var(--font-mono); font-size: 0.68rem; color: var(--text-tertiary);
    }
    @media (max-width: 640px) {
      .announce-bar { flex-wrap: wrap; }
      .announce-text { white-space: normal; }
      .announce-date { width: 100%; }
    }

    /* ===================== CONTACT HR ===================== */
    .contact-section {
      padding: 56px clamp(16px, 4vw, 40px) 60px;
      background: var(--bg-primary);
    }
    .contact-inner {
      max-width: 860px; margin: 0 auto;
      text-align: center;
    }
    .contact-label {
      font-family: var(--font-mono);
      font-size: 0.68rem; font-weight: 600; letter-spacing: 0.1em;
      text-transform: uppercase; color: var(--pine); margin-bottom: 10px;
    }
    .contact-heading {
      font-family: var(--font-display);
      font-size: clamp(1.2rem, 2vw, 1.55rem); font-weight: 600;
      color: var(--text-primary); letter-spacing: -0.01em;
      margin-bottom: 12px;
    }
    .contact-sub {
      font-size: 0.86rem; color: var(--text-secondary);
      max-width: 460px; margin: 0 auto 32px; line-height: 1.6;
    }
    .contact-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 14px;
      text-align: left;
    }
    .contact-card {
      display: flex; align-items: flex-start; gap: 14px;
      padding: 20px; border-radius: var(--radius-md);
      background: var(--bg-secondary); border: 1px solid var(--border);
      transition: all 0.2s ease;
    }
    .contact-card:hover { border-color: var(--border-hover); box-shadow: var(--shadow-sm); }
    .contact-icon {
      width: 40px; height: 40px; border-radius: 12px;
      background: var(--accent-soft); flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .contact-icon svg { width: 18px; height: 18px; stroke: var(--accent); }
    .contact-card-text h4 {
      font-size: 0.84rem; font-weight: 650; color: var(--text-primary); margin-bottom: 4px;
    }
    .contact-card-text a, .contact-card-text span.value {
      font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5;
      display: block;
    }
    .contact-card-text a:hover { color: var(--accent); }
    .contact-card-text .hint {
      font-size: 0.72rem; color: var(--text-tertiary); margin-top: 3px;
    }


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
      .punch-clock { flex-wrap: wrap; justify-content: center; padding: 10px 16px; }
      .contact-grid { grid-template-columns: 1fr; }
      .contact-card { text-align: left; }
      .how-steps { grid-template-columns: 1fr; gap: 26px; }
      .how-step:not(:last-child)::after { display: none; }
    }

    @media (max-width: 480px) {
      .hero { padding-top: calc(64px + 36px); padding-bottom: 64px; }
      .features-list { grid-template-columns: 1fr; }
      .topbar-brand span {
        position: absolute; width: 1px; height: 1px; padding: 0;
        margin: -1px; overflow: hidden; clip: rect(0,0,0,0);
        white-space: nowrap; border: 0;
      }
      .scroll-hint { display: none; }
    }

    /* ===================== SCROLL REVEAL ===================== */
    .reveal {
      opacity: 0;
      transform: translateY(18px);
      transition: opacity 0.65s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                  transform 0.65s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .reveal.reveal-visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* ===================== SMOOTH THEME SWITCH ===================== */
    /* Without this, toggling dark/light mode snaps every surface instantly
       while only body's background/color fade — these fill that gap so the
       whole page cross-fades together instead of flashing one element at a time. */
    .topbar,
    .portal-card,
    .feature-item,
    .contact-card,
    .announce-bar,
    .notice-banner,
    .site-footer,
    .features-strip,
    .qr-modal,
    .punch-clock,
    .trust-badge,
    .about-inner,
    .faq-item {
      transition: background 0.35s ease, background-color 0.35s ease,
                  border-color 0.35s ease, color 0.35s ease,
                  box-shadow 0.35s ease, transform 0.28s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    @media (prefers-reduced-motion: reduce) {
      .hero-inner, .portals-grid { animation: none; }
      .punch-clock .dot { animation: none; }
      .scroll-hint-line { animation: none; }
      .reveal {
        opacity: 1;
        transform: none;
        transition: none;
      }
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
      <div class="hero-grain" aria-hidden="true"></div>
      <div class="hero-inner">
        <div class="punch-clock" aria-live="off">
          <span class="dot"></span>
          <span class="punch-clock-time" id="punchTime">--:--:-- --</span>
          <span class="punch-clock-divider"></span>
          <span class="punch-clock-date" id="punchDate">Loading&hellip;</span>
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

      @if(isset($announcement) && $announcement)
        <div class="announce-bar">
          <span class="announce-tag">{{ ucfirst($announcement->type ?? 'General') }}</span>
          <span class="announce-text"><strong>{{ $announcement->title }}</strong> — {{ Str::limit($announcement->message, 100) }}</span>
          <span class="announce-date">{{ $announcement->created_at->diffForHumans() }}</span>
        </div>
      @endif

      <div class="portals-grid">

        <a href="{{ url('/employee/login') }}" class="portal-card primary" id="portal-employee">
          <div class="portal-pass-row">
            <span class="portal-pass-code">PASS · EMP</span>
            <span class="tag">Most used</span>
          </div>
          <div class="portal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4"/>
              <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>
            </svg>
          </div>
          <h3>Employee Portal</h3>
          <p>Access your payslips, review attendance records, and submit timesheet entries.</p>
          <div class="portal-tear"></div>
          <span class="portal-cta">
            Sign in
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </span>
        </a>

        <a href="{{ url('/attendance/attendlog') }}" class="portal-card" id="portal-attendance">
          <div class="portal-pass-row">
            <span class="portal-pass-code">PASS · ATD</span>
          </div>
          <div class="portal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="9"/>
              <path d="M12 7v5l3 3"/>
            </svg>
          </div>
          <h3>Attendance Log</h3>
          <p>Log and verify daily attendance records on-site. For designated attendance staff only.</p>
          <div class="portal-tear"></div>
          <span class="portal-cta">
            Sign in
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </span>
        </a>

        <!-- Download Mobile App Portal Card -->
        <button type="button" class="portal-card" id="portal-download" aria-haspopup="dialog" aria-controls="qrModal">
          <div class="portal-pass-row">
            <span class="portal-pass-code">PASS · APP</span>
          </div>
          <div class="portal-icon" style="background: var(--pine-soft); color: var(--pine);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
              <line x1="12" y1="18" x2="12.01" y2="18"/>
            </svg>
          </div>
          <h3>Download App</h3>
          <p>Get our native Android app to clock in, submit timesheets, and view payslips on-the-go.</p>
          <div class="portal-tear"></div>
          <span class="portal-cta" style="color: var(--pine);">
            Get the app
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="4" width="6" height="6" rx="1"/>
              <rect x="14" y="4" width="6" height="6" rx="1"/>
              <rect x="4" y="14" width="6" height="6" rx="1"/>
              <path d="M14 14h3M14 17h6M20 14v6M17 20h3"/>
            </svg>
          </span>
        </button>

      </div>
    </div>

    <!-- ===================== NOTICE BANNER ===================== -->
    <div class="notice-banner reveal">
      <div class="notice-banner-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 8v4M12 16h.01"/>
        </svg>
      </div>
      <div class="notice-banner-text">
        <strong>First time here?</strong> Contact your HR administrator to have your account created. Use <a href="{{ url('/register') }}">Register</a> only if you were given an access code.
      </div>
    </div>

    <!-- ===================== TRUST STRIP ===================== -->
    <div class="trust-strip reveal">
      <span class="trust-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Role-based access
      </span>
      <span class="trust-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 12l2 2 4-4"/></svg>
        Encrypted sessions
      </span>
      <span class="trust-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3.5-7.14"/><path d="M21 4v5h-5"/></svg>
        Regular backups
      </span>
    </div>

    <!-- ===================== HOW IT WORKS ===================== -->
    <section class="how-it-works">
      <div class="how-inner">
        <div class="about-label reveal">Getting started</div>
        <div class="features-strip-heading reveal">Three steps, and you're clocked in.</div>
        <div class="how-steps">
          <div class="how-step reveal">
            <div class="how-step-num">01</div>
            <h4>Choose your portal</h4>
            <p>Pick Employee or Attendance above, based on your role.</p>
          </div>
          <div class="how-step reveal">
            <div class="how-step-num">02</div>
            <h4>Sign in securely</h4>
            <p>Log in with your email and password — verified in seconds.</p>
          </div>
          <div class="how-step reveal">
            <div class="how-step-num">03</div>
            <h4>Track &amp; get paid</h4>
            <p>Log attendance, submit timesheets, and download payslips.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== FEATURES STRIP ===================== -->
    <section class="features-strip">
      <div class="features-strip-inner">
        <div class="features-strip-label">Platform capabilities</div>
        <div class="features-strip-heading">Everything payroll, in one place.</div>
        <div class="features-list">

          <div class="feature-item reveal">
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

          <div class="feature-item reveal">
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

          <div class="feature-item reveal">
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

          <div class="feature-item reveal">
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

    <!-- ===================== ABOUT MCC ===================== -->
    <section class="about-section">
      <div class="about-inner reveal">
        <img src="{{ asset('images/mcc.jpg') }}" alt="Madridejos Community College campus" class="about-photo">
        <div class="about-text">
          <div class="about-label">About the institution</div>
          <h3>Serving Madridejos, one graduate at a time.</h3>
          <p>Madridejos Community College offers programs across Information Technology, Business Administration, Hospitality Management, and Education. This system supports the faculty and staff who keep the campus running — from attendance and timekeeping to payroll and payslips.</p>
        </div>
      </div>
    </section>

    <!-- ===================== FAQ ===================== -->
    <section class="faq-section">
      <div class="faq-inner">
        <div class="faq-heading-block">
          <div class="about-label reveal">Common questions</div>
          <div class="features-strip-heading reveal">Frequently asked questions</div>
        </div>

        <div class="faq-list">

          <div class="faq-item reveal" data-faq>
            <button type="button" class="faq-question" aria-expanded="false">
              How do I create an account?
              <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="faq-answer-wrap">
              <div class="faq-answer">Accounts are created by your HR administrator. If you were issued an access code, use the <a href="{{ url('/register') }}">Register</a> page to activate it — otherwise, contact HR directly.</div>
            </div>
          </div>

          <div class="faq-item reveal" data-faq>
            <button type="button" class="faq-question" aria-expanded="false">
              I forgot my password. What do I do?
              <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="faq-answer-wrap">
              <div class="faq-answer">Attendance Checker accounts can reset their own password from the Attendance login page. Employee and Admin accounts don't have self-service reset yet — contact HR to have your password reset manually.</div>
            </div>
          </div>

          <div class="faq-item reveal" data-faq>
            <button type="button" class="faq-question" aria-expanded="false">
              When are payslips available?
              <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="faq-answer-wrap">
              <div class="faq-answer">Payslips are released each pay period and sent to the email on file. You can also view and download past payslips anytime from the Employee Portal.</div>
            </div>
          </div>

          <div class="faq-item reveal" data-faq>
            <button type="button" class="faq-question" aria-expanded="false">
              Is my payroll data secure?
              <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="faq-answer-wrap">
              <div class="faq-answer">Access is role-based, so employees can only view their own records, and admin actions are logged. All connections are encrypted end to end.</div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ===================== CONTACT HR ===================== -->
    <section class="contact-section">
      <div class="contact-inner">
        <div class="contact-label">Need help?</div>
        <div class="contact-heading">Talk to HR</div>
        <p class="contact-sub">Account issues, payslip questions, or anything not covered by the portals above — reach out directly.</p>

        <div class="contact-grid">

          <div class="contact-card reveal">
            <div class="contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 6 12 13 2 6"/>
                <path d="M2 6h20v12H2z"/>
              </svg>
            </div>
            <div class="contact-card-text">
              <h4>Email</h4>
              <a href="mailto:wendelldenorte@gmail.com">wendelldenorte@gmail.com</a>
              <span class="hint">Replies within 1–2 business days</span>
            </div>
          </div>

          <div class="contact-card reveal">
            <div class="contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
            </div>
            <div class="contact-card-text">
              <h4>Phone</h4>
              <a href="tel:+639638620157">639638620157</a>
              <span class="hint">Mon–Fri, 8:00 AM – 5:00 PM</span>
            </div>
          </div>

          <div class="contact-card reveal">
            <div class="contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
            </div>
            <div class="contact-card-text">
              <h4>Office</h4>
              <span class="value">HR Office, Admin Building</span>
              <span class="hint">Madridejos Community College</span>
            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

  <!-- ===================== FOOTER ===================== -->
  <footer class="site-footer">
    <div class="footer-copy">
      <span>&copy; {{ date('Y') }} Madridejos Community College</span>
      <span class="footer-credit">Developed by Ronyl Parochel</span>
    </div>
    <div class="footer-links">
      <a href="{{ url('/register') }}">Create Account</a>
      <a href="{{ url('/terms') }}">Terms</a>
    </div>
  </footer>

  <!-- ===================== QR DOWNLOAD MODAL ===================== -->
  <div class="qr-modal-overlay" id="qrModalOverlay">
    <div class="qr-modal" id="qrModal" role="dialog" aria-modal="true" aria-labelledby="qrModalTitle">
      <button type="button" class="qr-modal-close" id="qrModalClose" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M18 6 6 18M6 6l12 12"/>
        </svg>
      </button>

      <div class="qr-modal-pass-code">PASS · APP</div>
      <h3 id="qrModalTitle">Works better in the app</h3>
      <p class="qr-sub">Scan with your phone to install</p>

      <div class="qr-box">
        <div id="qrCanvasWrap">
          <div class="qr-loading" id="qrLoading">
            <span class="qr-spinner"></span>
            Generating QR code&hellip;
          </div>
        </div>
      </div>

      <div class="qr-modal-tear"></div>

      <a href="{{ asset('downloads/mcc-employee-app.apk') }}" class="qr-download-btn" download>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M12 5v14M19 12l-7 7-7-7"/>
        </svg>
        Download APK directly
      </a>

      <div class="qr-note">Android only. You may need to allow installs from unknown sources in your phone's settings.</div>
    </div>
  </div>

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

    // FAQ accordion
    (function () {
      document.querySelectorAll('[data-faq]').forEach(function (item) {
        const btn = item.querySelector('.faq-question');
        const wrap = item.querySelector('.faq-answer-wrap');

        btn.addEventListener('click', function () {
          const isOpen = item.classList.contains('open');

          // close any other open item (accordion behavior)
          document.querySelectorAll('[data-faq].open').forEach(function (other) {
            if (other !== item) {
              other.classList.remove('open');
              other.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
              other.querySelector('.faq-answer-wrap').style.maxHeight = null;
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

    // Scroll reveal for below-the-fold sections
    (function () {
      const items = document.querySelectorAll('.reveal');
      if (!items.length) return;

      if (!('IntersectionObserver' in window)) {
        items.forEach(el => el.classList.add('reveal-visible'));
        return;
      }

      // Stagger cards that share a parent (feature-list, contact-grid) so they
      // cascade in one after another instead of popping in all at once.
      const groups = new Map();
      items.forEach(el => {
        const parent = el.parentElement;
        if (!groups.has(parent)) groups.set(parent, []);
        groups.get(parent).push(el);
      });
      groups.forEach(siblings => {
        siblings.forEach((el, i) => {
          el.style.transitionDelay = (i * 90) + 'ms';
        });
      });

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('reveal-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

      items.forEach(el => observer.observe(el));
    })();

    // Live punch-clock readout in the hero
    (function () {
      const timeEl = document.getElementById('punchTime');
      const dateEl = document.getElementById('punchDate');
      function tick() {
        const now = new Date();
        timeEl.textContent = now.toLocaleTimeString('en-US', {
          hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
        dateEl.textContent = now.toLocaleDateString('en-US', {
          weekday: 'short', month: 'short', day: 'numeric'
        });
      }
      tick();
      setInterval(tick, 1000);
    })();

    // QR download modal
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
                width: 176,
                height: 176,
                colorDark: '#1B2420',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
              });
              qrGenerated = true;
            } catch (err) {
              qrWrap.innerHTML = '<span class="qr-fallback">Couldn\'t generate the QR code. Use the button below to download directly.</span>';
            }
          } else {
            qrWrap.innerHTML = '<span class="qr-fallback">QR code unavailable right now. Use the button below to download directly.</span>';
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

    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Registration Successful!',
        text: '{{ session("success") }}',
        confirmButtonColor: '#A9762F',
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