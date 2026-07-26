@use('Illuminate\Support\Str')
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MCC Employee Portal — {{ $employee->name ?? 'Dashboard' }}</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

  <script>
    (function(){
      const t = localStorage.getItem('mcc-theme') || 'light';
      document.documentElement.dataset.theme = t;
    })();
  </script>

  <style>
  /* ══════════════════════════════════════════════
     DESIGN TOKENS — LIGHT MODE
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
    --cyan:         #0ea5e9;

    --sb-w: 232px;
    --tb-h: 58px;

    --bg:           #f0f4f8;
    --bg-2:         #e8edf4;
    --card:         #ffffff;
    --card-hover:   #fafbff;

    --text:         #0d1526;
    --text-2:       #44546a;
    --text-3:       #8595a8;
    --text-inv:     #ffffff;

    --border:       #e0e7ef;
    --border-2:     #f0f4f8;

    --th-bg:        #f5f8fc;
    --tr-hover:     #f0f6ff;
    --tr-stripe:    #fafcff;

    --input-bg:     #f7fafc;
    --input-focus:  #ffffff;

    --sb-bg-1:      #071022;
    --sb-bg-2:      #0e1f4a;
    --sb-bg-3:      #1845c2;
    --sb-link-hover: rgba(255,255,255,.08);
    --sb-link-active: rgba(255,255,255,.15);
    --sb-text:      rgba(255,255,255,.52);
    --sb-text-hi:   rgba(255,255,255,.92);
    --sb-label:     rgba(255,255,255,.2);
    --sb-border:    rgba(255,255,255,.06);

    --sh-xs: 0 1px 3px rgba(13,21,38,.05), 0 1px 2px rgba(13,21,38,.04);
    --sh-sm: 0 2px 8px rgba(13,21,38,.07), 0 1px 3px rgba(13,21,38,.05);
    --sh-md: 0 6px 24px rgba(13,21,38,.10), 0 2px 8px rgba(13,21,38,.06);
    --sh-lg: 0 16px 48px rgba(13,21,38,.14);

    --r-sm: 8px;
    --r-md: 12px;
    --r-lg: 16px;
    --r-xl: 20px;

    --ease: cubic-bezier(.4,0,.2,1);
    --t:    all .18s var(--ease);
    --t-slow: all .3s var(--ease);
  }

  /* ══════════════════════════════════════════════
     DARK MODE TOKENS
  ══════════════════════════════════════════════ */
  [data-theme="dark"] {
    --bg:           #0d1117;
    --bg-2:         #111823;
    --card:         #161d2b;
    --card-hover:   #1c2436;

    --text:         #e8edf5;
    --text-2:       #8fa3be;
    --text-3:       #4a6080;
    --text-inv:     #0d1526;

    --border:       #1e2d42;
    --border-2:     #162030;

    --th-bg:        #121a28;
    --tr-hover:     #1a2540;
    --tr-stripe:    #131c2a;

    --input-bg:     #121a28;
    --input-focus:  #1a2540;

    --brand-light:  rgba(37,99,235,.15);
    --brand-mid:    rgba(37,99,235,.25);

    --sh-xs: 0 1px 3px rgba(0,0,0,.25);
    --sh-sm: 0 2px 8px rgba(0,0,0,.3);
    --sh-md: 0 6px 24px rgba(0,0,0,.4);
    --sh-lg: 0 16px 48px rgba(0,0,0,.5);
  }

  /* ══════════════════════════════════════════════
     RESET & BASE
  ══════════════════════════════════════════════ */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    -webkit-font-smoothing: antialiased;
    transition: background .3s var(--ease), color .3s var(--ease);
  }

  h1,h2,h3,h4,h5,h6 { font-family: 'Sora', sans-serif; }

  /* ══════════════════════════════════════════════
     APP SHELL
  ══════════════════════════════════════════════ */
  .app-shell {
    display: flex;
    height: 100vh;
    overflow: hidden;
  }

  /* ══════════════════════════════════════════════
     SIDEBAR
  ══════════════════════════════════════════════ */
  .sidebar {
    width: var(--sb-w);
    background: linear-gradient(180deg, var(--sb-bg-1) 0%, var(--sb-bg-2) 55%, var(--sb-bg-3) 100%);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    height: 100vh;
    overflow: hidden;
    position: relative;
    z-index: 100;
    border-right: 1px solid rgba(255,255,255,.04);
  }

  .sidebar::before {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 220px; height: 220px; border-radius: 50%;
    background: radial-gradient(circle, rgba(59,130,246,.2) 0%, transparent 70%);
    pointer-events: none;
  }
  .sidebar::after {
    content: '';
    position: absolute; bottom: 60px; left: -40px;
    width: 160px; height: 160px; border-radius: 50%;
    background: radial-gradient(circle, rgba(14,165,233,.12) 0%, transparent 70%);
    pointer-events: none;
  }

  /* Brand */
  .sb-brand {
    padding: 1.05rem .95rem .9rem;
    border-bottom: 1px solid var(--sb-border);
    display: flex; align-items: center; gap: 10px;
    flex-shrink: 0; position: relative; z-index: 1;
  }
  .sb-brand-icon {
    width: 34px; height: 34px; border-radius: 9px;
    background: rgba(255,255,255,.13);
    display: grid; place-items: center; flex-shrink: 0;
    border: 1px solid rgba(255,255,255,.2);
    box-shadow: 0 2px 10px rgba(0,0,0,.25);
  }
  .sb-brand-icon img { max-width: 20px; filter: brightness(0) invert(1); }
  .sb-brand-text  { font-family: 'Sora', sans-serif; font-size: .82rem; font-weight: 800; color: #fff; line-height: 1.1; letter-spacing: -.01em; }
  .sb-brand-sub   { font-size: .58rem; color: rgba(255,255,255,.28); margin-top: 2px; letter-spacing: .05em; text-transform: uppercase; }

  /* Profile pill */
  .sb-profile {
    padding: .65rem .9rem .72rem;
    border-bottom: 1px solid var(--sb-border);
    position: relative; z-index: 1;
  }
  .sb-profile-inner {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 11px;
    padding: .55rem .8rem;
    display: flex; align-items: center; gap: 9px;
    transition: background .15s;
    cursor: default;
  }
  .sb-profile-inner:hover { background: rgba(255,255,255,.11); }
  .sb-avatar {
    width: 34px; height: 34px; border-radius: 9px;
    background: linear-gradient(135deg, #3b82f6, #0ea5e9);
    display: grid; place-items: center;
    font-family: 'Sora', sans-serif; font-size: .72rem; font-weight: 800;
    color: #fff; flex-shrink: 0; position: relative;
    box-shadow: 0 2px 8px rgba(59,130,246,.35);
  }
  .sb-avatar-dot {
    position: absolute; bottom: -2px; right: -2px;
    width: 8px; height: 8px; border-radius: 50%;
    background: #10b981; border: 2px solid #0d1a3a;
  }
  .sb-name { font-family: 'Sora', sans-serif; font-size: .75rem; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
  .sb-role { font-size: .6rem; color: rgba(255,255,255,.36); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }

  /* Nav */
  .sb-nav { flex: 1; padding: .5rem .72rem; overflow-y: auto; position: relative; z-index: 1; }
  .sb-nav::-webkit-scrollbar { display: none; }

  .nav-label {
    font-size: .52rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px;
    color: var(--sb-label); padding: .72rem .4rem .22rem;
    font-family: 'Sora', sans-serif;
  }

  .sb-link {
    display: flex; align-items: center; gap: 9px;
    padding: .52rem .72rem;
    border-radius: 10px;
    color: var(--sb-text);
    font-size: .78rem; font-weight: 500;
    cursor: pointer; transition: var(--t);
    border: none; background: transparent; width: 100%;
    text-decoration: none; text-align: left;
    font-family: 'DM Sans', sans-serif;
    position: relative; letter-spacing: .01em;
    margin-bottom: 1px;
  }
  .sb-link i { font-size: .82rem; width: 16px; flex-shrink: 0; transition: transform .15s; }
  .sb-link:hover { background: var(--sb-link-hover); color: var(--sb-text-hi); }
  .sb-link:hover i { transform: translateX(1px); }
  .sb-link.active {
    background: var(--sb-link-active);
    color: #fff; font-weight: 600;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.07);
  }
  .sb-link.active::before {
    content: '';
    position: absolute; left: 0; top: 22%; bottom: 22%;
    width: 3px; background: linear-gradient(180deg, #93c5fd, #3b82f6);
    border-radius: 0 3px 3px 0;
  }

  .sb-badge {
    margin-left: auto; font-size: .54rem; font-weight: 800;
    background: var(--warn); color: #fff;
    border-radius: 20px; padding: 1px 7px; min-width: 16px; text-align: center;
    box-shadow: 0 1px 4px rgba(245,158,11,.4);
  }
  .sb-dot {
    margin-left: auto; width: 7px; height: 7px; border-radius: 50%;
    background: var(--warn); flex-shrink: 0;
    box-shadow: 0 0 6px rgba(245,158,11,.6);
    animation: pulse-dot 2s infinite;
  }
  @keyframes pulse-dot {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.3); opacity: .7; }
  }

  /* Sidebar footer */
  .sb-footer {
    padding: .7rem .72rem;
    border-top: 1px solid var(--sb-border);
    flex-shrink: 0; position: relative; z-index: 1;
  }
  .logout-btn {
    display: flex; align-items: center; gap: 8px;
    width: 100%; padding: .52rem .72rem;
    border-radius: 10px;
    background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.18);
    color: rgba(255,140,140,.8); font-size: .78rem; font-weight: 600;
    cursor: pointer; transition: var(--t); font-family: 'DM Sans', sans-serif;
    letter-spacing: .01em;
  }
  .logout-btn:hover { background: rgba(239,68,68,.2); color: #fff; border-color: rgba(239,68,68,.35); }

  /* ══════════════════════════════════════════════
     MAIN CONTENT
  ══════════════════════════════════════════════ */
  .main-content {
    flex: 1; display: flex; flex-direction: column;
    height: 100vh; overflow: hidden; min-width: 0;
  }

  /* ── Topbar ── */
  .topbar {
    height: var(--tb-h);
    background: var(--card);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: .7rem;
    padding: 0 1.5rem;
    box-shadow: 0 1px 0 var(--border), 0 2px 12px rgba(13,21,38,.04);
    flex-shrink: 0; z-index: 50;
    transition: background .3s, border-color .3s;
  }

  .tb-page-info .tb-title {
    font-family: 'Sora', sans-serif; font-size: .88rem; font-weight: 800;
    color: var(--text); line-height: 1; letter-spacing: -.01em;
  }
  .tb-page-info .tb-breadcrumb {
    font-size: .62rem; color: var(--text-3); margin-top: 2px;
    display: flex; align-items: center; gap: 3px;
  }

  .tb-divider { width: 1px; height: 20px; background: var(--border); flex-shrink: 0; }

  .tb-clock {
    font-family: 'DM Sans', sans-serif; font-size: .72rem; font-weight: 600;
    color: var(--text-2); background: var(--bg);
    border: 1px solid var(--border); border-radius: 8px;
    padding: .28rem .7rem; white-space: nowrap;
    transition: background .3s, color .3s;
  }

  .icon-btn {
    width: 34px; height: 34px; border-radius: 9px;
    background: var(--bg); border: 1px solid var(--border);
    display: grid; place-items: center;
    color: var(--text-2); cursor: pointer; transition: var(--t);
    position: relative; flex-shrink: 0;
  }
  .icon-btn:hover { background: var(--brand-light); color: var(--brand); border-color: var(--brand-mid); }
  .icon-btn .n-dot {
    position: absolute; top: 6px; right: 6px;
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--warn); border: 1.5px solid var(--card);
    box-shadow: 0 0 5px rgba(245,158,11,.6);
  }

  #themeToggle { font-size: .8rem; }
  [data-theme="dark"] #themeToggle { color: #fbbf24; border-color: rgba(251,191,36,.25); background: rgba(251,191,36,.08); }
  [data-theme="dark"] #themeToggle:hover { background: rgba(251,191,36,.15); border-color: rgba(251,191,36,.4); }

  .tb-user {
    display: flex; align-items: center; gap: 8px;
    padding: .25rem .4rem .25rem .7rem;
    border-left: 1px solid var(--border);
    cursor: pointer; transition: var(--t); border-radius: 0 9px 9px 0;
    margin-left: .1rem;
  }
  .tb-user:hover { background: var(--brand-light); }
  .tb-avatar {
    width: 30px; height: 30px; border-radius: 8px;
    background: linear-gradient(135deg, var(--brand), var(--cyan));
    display: grid; place-items: center;
    font-family: 'Sora', sans-serif; font-size: .68rem; font-weight: 800;
    color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(37,99,235,.3);
  }
  .tb-uname { font-size: .74rem; font-weight: 700; color: var(--text); line-height: 1.1; }
  .tb-urole { font-size: .6rem; color: var(--text-3); }

  /* ── Page Body ── */
  .page-body {
    flex: 1; overflow-y: auto; overflow-x: hidden;
    padding: 1.1rem 1.3rem 1.4rem;
    background: var(--bg);
    transition: background .3s;
  }
  .page-body::-webkit-scrollbar { width: 5px; }
  .page-body::-webkit-scrollbar-track { background: transparent; }
  .page-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

  /* ══════════════════════════════════════════════
     TAB PANELS
  ══════════════════════════════════════════════ */
  .tab-panel { display: none; }
  .tab-panel.active { display: block; animation: panelIn .25s var(--ease) both; }
  @keyframes panelIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

  /* ══════════════════════════════════════════════
     SECTION HEADER
  ══════════════════════════════════════════════ */
  .ph { margin-bottom: 1.1rem; display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: .7rem; }
  .ph-title { font-family: 'Sora', sans-serif; font-weight: 800; font-size: .95rem; color: var(--text); margin: 0; letter-spacing: -.01em; }
  .ph-sub   { font-size: .72rem; color: var(--text-3); margin: .2rem 0 0; }

  /* ══════════════════════════════════════════════
     CARDS
  ══════════════════════════════════════════════ */
  .card {
    background: var(--card);
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    box-shadow: var(--sh-sm);
    overflow: hidden;
    transition: background .3s, border-color .3s, box-shadow .2s;
  }
  .card-hd {
    padding: .82rem 1.15rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; gap: .7rem;
    transition: border-color .3s;
  }
  .card-title {
    font-family: 'Sora', sans-serif; font-size: .78rem; font-weight: 800;
    color: var(--text); display: flex; align-items: center; gap: 8px; letter-spacing: -.01em;
  }
  .ct-icon {
    width: 26px; height: 26px; border-radius: 7px;
    display: grid; place-items: center; font-size: .75rem; flex-shrink: 0;
  }
  .card-body { padding: .95rem 1.15rem; }

  /* ══════════════════════════════════════════════
     KPI CARDS
  ══════════════════════════════════════════════ */
  .kpi {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: 1rem 1.1rem;
    box-shadow: var(--sh-sm);
    position: relative; overflow: hidden;
    transition: var(--t-slow);
  }
  .kpi:hover { transform: translateY(-2px); box-shadow: var(--sh-md); }

  .kpi-accent {
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--kpi-c, var(--brand)) 0%, transparent 130%);
  }
  .kpi::before {
    content: '';
    position: absolute; bottom: -20px; right: -14px;
    width: 80px; height: 80px; border-radius: 50%;
    background: radial-gradient(circle, var(--kpi-c, var(--brand)) 0%, transparent 70%);
    opacity: .07; pointer-events: none;
  }

  .kpi-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: grid; place-items: center; font-size: .88rem;
    background: var(--kpi-bg, rgba(37,99,235,.1));
    color: var(--kpi-c, var(--brand)); flex-shrink: 0;
  }
  .kpi-header  { display: flex; align-items: center; justify-content: space-between; }
  .kpi-period  { font-size: .57rem; color: var(--text-3); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
  .kpi-val     { font-family: 'Sora', sans-serif; font-size: 1.7rem; font-weight: 800; line-height: 1; color: var(--text); margin-top: .42rem; letter-spacing: -.025em; }
  .kpi-label   { font-size: .63rem; font-weight: 700; color: var(--text-2); margin-top: 4px; text-transform: uppercase; letter-spacing: .3px; }

  /* ══════════════════════════════════════════════
     ATTENDANCE CALENDAR
  ══════════════════════════════════════════════ */
  .att-cal-wrap { padding: .85rem 1.05rem 1rem; }

  .cal-month-header {
    display: grid; grid-template-columns: repeat(7, 1fr);
    gap: 3px; margin-bottom: 4px;
  }
  .cal-dow {
    text-align: center; font-family: 'Sora', sans-serif;
    font-size: .57rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .8px; color: var(--text-3); padding: .2rem 0;
  }
  .cal-dow:first-child, .cal-dow:last-child { color: rgba(239,68,68,.6); }

  .cal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr);
    gap: 3px;
  }
  .cal-cell {
    border-radius: 8px; padding: .38rem .15rem .28rem;
    min-height: 48px; display: flex; flex-direction: column;
    align-items: center; justify-content: flex-start;
    transition: var(--t); cursor: default;
    background: var(--bg-2); border: 1.5px solid transparent;
    position: relative;
  }
  button.cal-cell {
    font: inherit;
    width: 100%;
    -webkit-tap-highlight-color: transparent;
  }
  button.cal-cell:not(:disabled) { cursor: pointer; }
  button.cal-cell:disabled { cursor: default; }
  .cal-cell:hover:not(.cal-empty):not(.cal-future-day) {
    transform: scale(1.04); box-shadow: var(--sh-md); z-index: 2;
  }
  .cal-cell.cal-empty {
    background: transparent; pointer-events: none;
  }
  .cal-num {
    font-family: 'Sora', sans-serif; font-size: .74rem; font-weight: 700;
    line-height: 1; color: var(--text-2);
  }
  .cal-time-in {
    font-size: .52rem; margin-top: 3px; font-weight: 600;
    white-space: nowrap; opacity: .8;
  }
  .cal-status-dot {
    width: 5px; height: 5px; border-radius: 50%; margin-top: 4px;
    flex-shrink: 0;
  }

  /* today */
  .cal-cell.cal-today {
    border-color: var(--brand);
    background: var(--brand-light);
    box-shadow: 0 0 0 2px rgba(37,99,235,.12);
  }
  .cal-cell.cal-today .cal-num { color: var(--brand); }

  /* present */
  .cal-cell.cal-present { background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.22); }
  .cal-cell.cal-present .cal-num { color: #059669; }
  .cal-cell.cal-present .cal-time-in { color: #059669; }
  .cal-cell.cal-present .cal-status-dot { background: #10b981; }

  /* late */
  .cal-cell.cal-late { background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.22); }
  .cal-cell.cal-late .cal-num { color: #b45309; }
  .cal-cell.cal-late .cal-time-in { color: #b45309; }
  .cal-cell.cal-late .cal-status-dot { background: #f59e0b; }

  /* absent */
  .cal-cell.cal-absent { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.18); }
  .cal-cell.cal-absent .cal-num { color: #dc2626; }
  .cal-cell.cal-absent .cal-status-dot { background: #ef4444; }

  /* on leave */
  .cal-cell.cal-on_leave, .cal-cell.cal-leave {
    background: rgba(124,58,237,.08); border-color: rgba(124,58,237,.18);
  }
  .cal-cell.cal-on_leave .cal-num, .cal-cell.cal-leave .cal-num { color: #6d28d9; }
  .cal-cell.cal-on_leave .cal-status-dot, .cal-cell.cal-leave .cal-status-dot { background: #7c3aed; }

  /* future */
  .cal-cell.cal-future-day { opacity: .35; background: transparent; pointer-events: none; }

  /* weekend (no record) */
  .cal-cell.cal-weekend-empty { background: transparent; border-color: transparent; opacity: .55; }
  .cal-cell.cal-weekend-empty .cal-num { color: rgba(239,68,68,.5); }

  /* weekday with no attendance record yet (distinct from styled statuses above) */
  .cal-cell.cal-no-record { background: transparent; border: 1.5px dashed var(--border); }
  .cal-cell.cal-no-record .cal-num { color: var(--text-3); }
  .cal-cell.cal-no-record::after {
    content: '';
    position: absolute; top: 5px; right: 5px;
    width: 5px; height: 5px; border-radius: 50%;
    border: 1.5px solid var(--warn);
  }

  /* custom tooltip (replaces native title attr — accessible on tap, not just hover) */
  .cal-tip {
    display: none;
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: var(--text);
    color: var(--card);
    font-size: .64rem;
    font-weight: 600;
    padding: .35rem .6rem;
    border-radius: 7px;
    white-space: nowrap;
    box-shadow: var(--sh-md);
    z-index: 20;
    pointer-events: none;
  }
  .cal-tip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: var(--text);
  }
  .cal-cell.show-tip .cal-tip,
  .cal-cell:hover .cal-tip,
  .cal-cell:focus-visible .cal-tip { display: block; }

  /* calendar legend */
  .cal-legend {
    display: flex; flex-wrap: wrap; gap: 8px 16px;
    padding: .7rem 1.05rem .5rem;
    border-top: 1px solid var(--border-2);
  }
  .cal-legend-item {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .62rem; font-weight: 600; color: var(--text-2);
  }
  .cal-legend-swatch {
    width: 10px; height: 10px; border-radius: 3px;
  }

  /* ══════════════════════════════════════════════
     LATEST PAYSLIP WIDGET
  ══════════════════════════════════════════════ */
  .payslip-card {
    background: linear-gradient(135deg, #071022 0%, #1843c0 52%, #0ea5e9 110%);
    border-radius: 11px; padding: 1.05rem 1.15rem;
    color: #fff; margin-bottom: .8rem; position: relative; overflow: hidden;
  }
  .payslip-card::before {
    content: ''; position: absolute; top: -28px; right: -28px;
    width: 96px; height: 96px; border-radius: 50%;
    background: rgba(255,255,255,.07);
  }
  .payslip-card::after {
    content: ''; position: absolute; bottom: -18px; left: 28%;
    width: 64px; height: 64px; border-radius: 50%;
    background: rgba(255,255,255,.04);
  }

  /* ══════════════════════════════════════════════
     TODAY QUICK STATUS
  ══════════════════════════════════════════════ */
  .today-status-card {
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    background: var(--card);
    padding: .82rem 1.05rem;
    margin-bottom: .6rem;
  }
  .today-label {
    font-size: .57rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px;
    color: var(--text-3); margin-bottom: .45rem; display: block;
  }

  /* ══════════════════════════════════════════════
     DATA TABLE
  ══════════════════════════════════════════════ */
  .data-table { width: 100%; border-collapse: collapse; font-size: .79rem; }
  .data-table thead th {
    background: var(--th-bg);
    color: var(--text-2);
    font-family: 'Sora', sans-serif; font-weight: 800; font-size: .62rem;
    text-transform: uppercase; letter-spacing: .6px;
    border-bottom: 2px solid var(--border);
    padding: .65rem 1.05rem; white-space: nowrap;
    position: sticky; top: 0; z-index: 2;
    transition: background .3s;
  }
  .data-table tbody td {
    border-bottom: 1px solid var(--border-2);
    vertical-align: middle;
    padding: .62rem 1.05rem; color: var(--text);
    transition: background .1s;
  }
  .data-table tbody tr:last-child td { border-bottom: none; }
  .data-table tbody tr:nth-child(even) td { background: var(--tr-stripe); }
  .data-table tbody tr:hover td { background: var(--tr-hover) !important; }

  /* ══════════════════════════════════════════════
     PAYSLIP LIST ITEMS
  ══════════════════════════════════════════════ */
  .ps-item {
    display: flex; align-items: center; gap: .9rem;
    background: var(--card); border: 1px solid var(--border);
    border-radius: var(--r-md); padding: .9rem 1.15rem;
    transition: var(--t); margin-bottom: .5rem;
  }
  .ps-item:hover { border-color: var(--brand-mid); box-shadow: var(--sh-md); transform: translateX(2px); }
  .ps-icon {
    width: 42px; height: 42px; border-radius: 11px;
    background: var(--brand-light); color: var(--brand);
    display: grid; place-items: center; font-size: 1rem; flex-shrink: 0;
    border: 1px solid var(--brand-mid);
  }

  /* ══════════════════════════════════════════════
     BADGES
  ══════════════════════════════════════════════ */
  .badge {
    display: inline-flex; align-items: center; gap: 4px;
    border-radius: 20px; padding: .2rem .65rem;
    font-size: .63rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; white-space: nowrap;
    font-family: 'DM Sans', sans-serif;
  }
  .badge-dot { width: 4px; height: 4px; border-radius: 50%; flex-shrink: 0; }
  .badge-present  { background: rgba(16,185,129,.12);  color: #059669; border: 1px solid rgba(16,185,129,.2); }
  .badge-absent   { background: rgba(239,68,68,.1);    color: #dc2626; border: 1px solid rgba(239,68,68,.18); }
  .badge-late     { background: rgba(245,158,11,.12);  color: #b45309; border: 1px solid rgba(245,158,11,.2); }
  .badge-pending  { background: rgba(124,58,237,.1);   color: #6d28d9; border: 1px solid rgba(124,58,237,.18); }
  .badge-approved { background: rgba(16,185,129,.12);  color: #059669; border: 1px solid rgba(16,185,129,.2); }
  .badge-submitted{ background: rgba(37,99,235,.1);    color: #1d4ed8; border: 1px solid rgba(37,99,235,.18); }
  .badge-released { background: rgba(37,99,235,.1);    color: #1d4ed8; border: 1px solid rgba(37,99,235,.18); }
  .badge-on_leave { background: rgba(124,58,237,.1);   color: #6d28d9; border: 1px solid rgba(124,58,237,.18); }

  [data-theme="dark"] .badge-present  { background: rgba(16,185,129,.18); color: #34d399; }
  [data-theme="dark"] .badge-absent   { background: rgba(239,68,68,.18);  color: #f87171; }
  [data-theme="dark"] .badge-late     { background: rgba(245,158,11,.18); color: #fbbf24; }
  [data-theme="dark"] .badge-pending  { background: rgba(124,58,237,.18); color: #a78bfa; }
  [data-theme="dark"] .badge-approved { background: rgba(16,185,129,.18); color: #34d399; }
  [data-theme="dark"] .badge-submitted{ background: rgba(37,99,235,.18);  color: #60a5fa; }
  [data-theme="dark"] .badge-released { background: rgba(37,99,235,.18);  color: #60a5fa; }
  [data-theme="dark"] .badge-on_leave { background: rgba(124,58,237,.18); color: #a78bfa; }

  /* ══════════════════════════════════════════════
     FORMS
  ══════════════════════════════════════════════ */
  .f-label { font-size: .72rem; font-weight: 700; color: var(--text-2); margin-bottom: .3rem; display: block; letter-spacing: .02em; }
  .f-input {
    border: 1.5px solid var(--border);
    border-radius: 9px; padding: .52rem .88rem;
    font-size: .82rem; font-family: 'DM Sans', sans-serif;
    width: 100%; color: var(--text);
    background: var(--input-bg); outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
  }
  .f-input:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    background: var(--input-focus);
  }
  .f-input:disabled { opacity: .55; cursor: not-allowed; }

  /* ══════════════════════════════════════════════
     BUTTONS
  ══════════════════════════════════════════════ */
  .btn-primary {
    background: linear-gradient(135deg, var(--brand-dark), var(--brand) 70%, #3b82f6);
    color: #fff; border: none; border-radius: 9px; padding: .52rem 1.15rem;
    font-family: 'Sora', sans-serif; font-size: .76rem; font-weight: 700;
    cursor: pointer; transition: var(--t); display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 2px 8px rgba(37,99,235,.25); text-decoration: none;
    letter-spacing: .01em;
  }
  .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 18px rgba(37,99,235,.38); color: #fff; }
  .btn-primary:active { transform: none; }
  .btn-primary.btn-sm { padding: .37rem .88rem; font-size: .72rem; border-radius: 8px; }

  .btn-outline {
    background: transparent; color: var(--brand); border: 1.5px solid var(--brand-mid);
    border-radius: 9px; padding: .48rem 1rem;
    font-family: 'Sora', sans-serif; font-size: .75rem; font-weight: 700;
    cursor: pointer; transition: var(--t); display: inline-flex; align-items: center; gap: 5px;
    text-decoration: none; letter-spacing: .01em;
  }
  .btn-outline:hover { background: var(--brand-light); color: var(--brand-dark); border-color: var(--brand); }
  .btn-outline.btn-sm { padding: .35rem .78rem; font-size: .7rem; border-radius: 8px; }

  [data-theme="dark"] .btn-outline { border-color: rgba(37,99,235,.35); }
  [data-theme="dark"] .btn-outline:hover { background: rgba(37,99,235,.18); color: #93c5fd; }

  .btn-ghost {
    width: 30px; height: 30px; border-radius: 8px; display: grid; place-items: center;
    border: 1px solid var(--border); background: var(--bg);
    color: var(--text-2); cursor: pointer; transition: var(--t); font-size: .75rem;
    text-decoration: none;
  }
  .btn-ghost:hover { background: var(--brand-light); color: var(--brand); border-color: var(--brand-mid); }

  /* ══════════════════════════════════════════════
     ANNOUNCEMENT CARDS
  ══════════════════════════════════════════════ */
  .ann-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: .95rem 1.15rem .95rem 1.35rem;
    transition: var(--t-slow); position: relative; overflow: hidden;
    margin-bottom: .6rem;
  }
  .ann-card::before {
    content: '';
    position: absolute; top: 0; left: 0;
    width: 3.5px; height: 100%;
    background: var(--ann-c, var(--brand));
  }
  .ann-card:hover { box-shadow: var(--sh-md); transform: translateX(2px); }

  /* ══════════════════════════════════════════════
     PROFILE
  ══════════════════════════════════════════════ */
  .info-row {
    display: flex; align-items: center; gap: 10px;
    padding: .5rem 0; border-bottom: 1px solid var(--border-2);
    font-size: .8rem;
  }
  .info-row:last-child { border-bottom: none; }
  .info-icon  { color: var(--brand); width: 15px; flex-shrink: 0; }
  .info-label { font-size: .63rem; font-weight: 700; color: var(--text-3); text-transform: uppercase; letter-spacing: .4px; width: 84px; flex-shrink: 0; }
  .info-val   { color: var(--text); font-weight: 500; flex: 1; }

  .profile-hero {
    background: linear-gradient(140deg, #071022 0%, #1843c0 55%, #0ea5e9 110%);
    padding: 1.6rem 1.3rem 1.4rem; text-align: center;
    position: relative; overflow: hidden;
  }
  .profile-hero::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(ellipse at 70% 25%, rgba(59,130,246,.3) 0%, transparent 60%);
  }
  .ph-avatar {
    width: 70px; height: 70px; border-radius: 16px;
    background: rgba(255,255,255,.15); border: 2px solid rgba(255,255,255,.3);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Sora', sans-serif; font-size: 1.65rem; font-weight: 800;
    color: #fff; margin: 0 auto .7rem; position: relative; z-index: 1;
    box-shadow: 0 8px 28px rgba(0,0,0,.2);
  }

  /* ══════════════════════════════════════════════
     EMPTY STATE
  ══════════════════════════════════════════════ */
  .empty-state { text-align: center; padding: 2.4rem 1rem; }
  .empty-state i { font-size: 2.2rem; color: var(--text-3); }
  .empty-state p { color: var(--text-3); margin-top: .6rem; font-size: .8rem; line-height: 1.7; }

  /* ══════════════════════════════════════════════
     PAYSLIP VIEW MODAL
  ══════════════════════════════════════════════ */
  #payslipModal .modal-content {
    border-radius: var(--r-xl) !important;
    border: 1px solid var(--border) !important;
    overflow: hidden;
    background: var(--card) !important;
  }

  .ps-modal-loader {
    padding: 3rem; text-align: center;
  }
  .ps-modal-loader .spin {
    width: 2.2rem; height: 2.2rem; border-radius: 50%;
    border: 3px solid var(--border);
    border-top-color: var(--brand);
    animation: spin .65s linear infinite;
    margin: 0 auto;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Payslip rendered document */
  .ps-doc-header {
    background: linear-gradient(135deg, #071022 0%, #1843c0 52%, #0ea5e9 110%);
    padding: 1.5rem 1.8rem;
  }
  .ps-doc-section { padding: 0 1.8rem; }
  .ps-doc-section + .ps-doc-section { padding-top: .8rem; }
  .ps-doc-footer { padding: .85rem 1.8rem 1.5rem; }

  .ps-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .52rem .85rem; border-bottom: 1px solid var(--border-2);
    font-size: .8rem;
  }
  .ps-row:last-child { border-bottom: none; }
  .ps-row-key { color: var(--text-2); }
  .ps-row-val { font-weight: 700; color: var(--text); }
  .ps-row-val.deduct { color: #dc2626; }
  .ps-row-val.earn   { color: #059669; }

  .ps-subtotal {
    display: flex; justify-content: space-between; align-items: center;
    padding: .6rem .85rem; font-size: .82rem; font-weight: 700;
  }

  .ps-net-pay {
    background: linear-gradient(135deg, #071022, #1843c0 60%, #0ea5e9);
    border-radius: 12px; padding: 1.1rem 1.3rem;
    display: flex; align-items: center; justify-content: space-between;
    margin: .8rem 1.8rem 0;
  }

  .ps-table-bg {
    background: var(--bg-2); border-radius: 9px;
    overflow: hidden; border: 1px solid var(--border-2);
    margin-top: .5rem;
  }

  /* ══════════════════════════════════════════════
     MODAL SHARED
  ══════════════════════════════════════════════ */
  .modal-content { background: var(--card); border: 1px solid var(--border); transition: background .3s; }
  .modal-body    { background: var(--card); transition: background .3s; }
  .modal-footer  { background: var(--card); transition: background .3s; }

  /* ══════════════════════════════════════════════
     STAGGER ANIMATIONS
  ══════════════════════════════════════════════ */
  .s0 { animation: fadeUp .32s var(--ease) .03s both; }
  .s1 { animation: fadeUp .32s var(--ease) .08s both; }
  .s2 { animation: fadeUp .32s var(--ease) .13s both; }
  .s3 { animation: fadeUp .32s var(--ease) .18s both; }
  .s4 { animation: fadeUp .32s var(--ease) .23s both; }
  @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

  /* ══════════════════════════════════════════════
     RESPONSIVE
  ══════════════════════════════════════════════ */
  @media (max-width: 991px) {
    .sidebar {
      position: fixed; transform: translateX(-100%);
      transition: transform .3s var(--ease);
      box-shadow: none;
    }
    .sidebar.show { transform: none; box-shadow: 8px 0 40px rgba(0,0,0,.3); }
    .main-content { margin-left: 0; }
    .page-body { padding: .9rem; }
  }
  @media (max-width: 767px) {
    .cal-cell { min-height: 38px; padding: .28rem .1rem .2rem; }
    .cal-time-in { display: none; }
    .cal-num { font-size: .68rem; }
  }
  @media (max-width: 576px) {
    .topbar { padding: 0 .9rem; }
    .kpi-val { font-size: 1.4rem; }
    .page-body { padding: .75rem; }
    .ps-doc-header, .ps-doc-section, .ps-doc-footer { padding-left: 1.2rem; padding-right: 1.2rem; }
    .ps-net-pay { margin-left: 1.2rem; margin-right: 1.2rem; }
  }
  </style>
</head>
<body>
<div class="app-shell">

  <!-- ════════════════════
       SIDEBAR
  ════════════════════ -->
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

    @php
      $initials = collect(explode(' ', $employee->name ?? 'E'))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');

      // Hoisted derived values — computed once, up top, so nothing below depends on
      // markup order. (Previously some of these were computed inline inside whichever
      // component happened to render first, which broke silently if blocks got reordered.)
      $unread = isset($announcements) ? $announcements->where('is_read', false)->count() : 0;
      $newPay = isset($payslips) ? $payslips->where('viewed', false)->count() : 0;

      // Attendance calendar month — driven by ?month=&year= so it's navigable from the
      // Overview tab; defaults to the current month. NOTE: this only shows data for
      // months your controller actually loads into $attendances — if that query is
      // scoped to "this month only" server-side, the nav below will render an empty
      // calendar for any other month until the controller accepts these params too.
      $calYear    = (int) request('year', now()->year);
      $calMonth   = (int) request('month', now()->month);
      $calCursor  = \Carbon\Carbon::create($calYear, $calMonth, 1);
      $calYear    = $calCursor->year;
      $calMonth   = $calCursor->month;
      $isCurrentCalMonth = $calCursor->isSameMonth(now());

      $prevCursor = $calCursor->copy()->subMonth();
      $nextCursor = $calCursor->copy()->addMonth();

      $calBaseQuery = request()->except(['month', 'year']);
      $currentTab   = request('tab', 'overview');
      $calPrevUrl   = url()->current() . '?' . http_build_query(array_merge($calBaseQuery, ['month' => $prevCursor->month, 'year' => $prevCursor->year, 'tab' => $currentTab]));
      $calNextUrl   = url()->current() . '?' . http_build_query(array_merge($calBaseQuery, ['month' => $nextCursor->month, 'year' => $nextCursor->year, 'tab' => $currentTab]));
      $calTodayUrl  = url()->current() . '?' . http_build_query(array_merge($calBaseQuery, ['month' => now()->month, 'year' => now()->year, 'tab' => $currentTab]));

      $todayDay  = now()->day;
      $daysInMo  = $calCursor->daysInMonth;
      $startDow  = $calCursor->copy()->startOfMonth()->dayOfWeek; // 0=Sun

      /* Build per-day attendance lookup for the viewed month */
      $attByDay  = collect($attendances ?? [])->filter(function($a) use ($calYear, $calMonth) {
        $d = \Carbon\Carbon::parse($a->date);
        return $d->year === $calYear && $d->month === $calMonth;
      })->keyBy(fn($a) => (int)\Carbon\Carbon::parse($a->date)->format('j'));
    @endphp

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
      <button class="sb-link active" data-tab="overview" id="nav-overview">
        <i class="bi bi-grid-1x2-fill"></i> Overview
      </button>
      <button class="sb-link" data-tab="attendance" id="nav-attendance">
        <i class="bi bi-calendar-check-fill"></i> Attendance
      </button>
      <button class="sb-link" data-tab="timesheets" id="nav-timesheets">
        <i class="bi bi-clock-history"></i> Timesheets
      </button>

      <div class="nav-label">Payroll</div>
      <button class="sb-link" data-tab="payslips" id="nav-payslips">
        <i class="bi bi-receipt-cutoff"></i> Payslips
        @if($newPay > 0) <span class="sb-badge">{{ $newPay }}</span> @endif
      </button>

      <div class="nav-label">Info</div>
      <button class="sb-link" data-tab="announcements" id="nav-announcements">
        <i class="bi bi-megaphone-fill"></i> Announcements
        @if($unread > 0) <span class="sb-dot"></span> @endif
      </button>
      <button class="sb-link" data-tab="profile" id="nav-profile">
        <i class="bi bi-person-circle"></i> My Profile
      </button>

      <div class="nav-label">Other</div>
      <a class="sb-link" href="{{ route('employee.evaluation.form') }}">
        <i class="bi bi-clipboard-check"></i> Evaluation
      </a>
      <button class="sb-link" onclick="showHelp()">
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

  <!-- ════════════════════
       MAIN CONTENT
  ════════════════════ -->
  <div class="main-content">

    <header class="topbar">
      <button class="icon-btn d-lg-none" id="mobileMenuBtn" style="border:none;">
        <i class="bi bi-list" style="font-size:1.1rem;"></i>
      </button>

      <div class="tb-page-info">
        <div class="tb-title" id="tbTitle">Overview</div>
        <div class="tb-breadcrumb">
          <i class="bi bi-house-fill" style="font-size:.58rem;"></i>
          <span>MCC Portal</span>
          <i class="bi bi-chevron-right" style="font-size:.52rem;"></i>
          <span id="tbBreadcrumb">Dashboard</span>
        </div>
      </div>

      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="tb-clock d-none d-md-block" id="liveClock"></div>
        <div class="tb-divider d-none d-md-block"></div>

        <button class="icon-btn" id="themeToggle" title="Toggle dark/light mode">
          <i class="bi bi-moon-stars-fill" id="themeIcon" style="font-size:.82rem;"></i>
        </button>

        <div class="icon-btn" id="notifBtn" title="Announcements" style="cursor:pointer;">
          <i class="bi bi-bell" style="font-size:.82rem;"></i>
          @if(isset($unread) && $unread > 0) <span class="n-dot"></span> @endif
        </div>

        <div class="tb-user" id="profileBtn">
          <div class="tb-avatar">{{ $initials }}</div>
          <div class="d-none d-sm-block">
            <div class="tb-uname">
              {{ Str::words($employee->name ?? 'Employee', 1, '') }}
              @if(Auth::user()->email_verified_at)
                <i class="bi bi-patch-check-fill text-primary" title="Email Verified" style="font-size:.7rem;"></i>
              @endif
            </div>
            <div class="tb-urole">{{ $employee->position ?? 'Employee' }}</div>
          </div>
          <i class="bi bi-chevron-down d-none d-sm-block" style="font-size:.6rem;color:var(--text-3);margin-left:2px;"></i>
        </div>
      </div>
    </header>

    <div class="page-body">

      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius: var(--r-md); font-size: .8rem;">
          <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="border-radius: var(--r-md); font-size: .8rem;">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert" style="border-radius: var(--r-md); font-size: .8rem;">
          <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(is_null(Auth::user()->email_verified_at))
        <div class="alert alert-warning mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-radius: var(--r-md); border-left: 4px solid #f59e0b; background: rgba(245,158,11,0.1); color: var(--text);">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-envelope-exclamation-fill text-warning fs-4"></i>
            <div>
              <strong style="font-size: .85rem;">Email Address Not Verified</strong>
              <div style="font-size: .75rem; color: var(--text-2);">
                Please verify your email ({{ Auth::user()->email }}) to ensure secure delivery and access to your e-payslips.
              </div>
            </div>
          </div>
          <form method="POST" action="{{ route('verification.resend') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm fw-bold px-3" style="border-radius: 8px; font-size: .76rem;">
              <i class="bi bi-send-fill me-1"></i> Resend Verification Email
            </button>
          </form>
        </div>
      @endif

      <!-- ════════════════════════════════
           PANEL: OVERVIEW
      ════════════════════════════════ -->
      <div class="tab-panel active" id="panel-overview">

        <!-- Premium Hero Welcome Banner -->
        @php
          $hour = (int)date('H');
          if ($hour < 12) {
              $greeting = 'Good Morning';
              $greetingIcon = 'bi-brightness-high-fill';
          } elseif ($hour < 18) {
              $greeting = 'Good Afternoon';
              $greetingIcon = 'bi-sun-fill';
          } else {
              $greeting = 'Good Evening';
              $greetingIcon = 'bi-moon-stars-fill';
          }
          $latestPayslip = isset($payslips) && $payslips->count() > 0 ? $payslips->first() : null;
          $netPayVal = $latestPayslip ? '₱' . number_format($latestPayslip->net_pay ?? $latestPayslip->total_net_pay ?? 0, 2) : '—';
        @endphp

        <div class="hero-welcome-card mb-3 p-3 p-md-4 text-white position-relative overflow-hidden"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%); border-radius: var(--r-xl); box-shadow: 0 10px 30px rgba(37,99,235,0.22); border: 1px solid rgba(255,255,255,0.15);">
          <!-- Ambient glowing background orbs -->
          <div style="position:absolute; top:-40px; right:-40px; width:200px; height:200px; border-radius:50%; background:rgba(59,130,246,0.25); filter:blur(30px); pointer-events:none;"></div>
          <div style="position:absolute; bottom:-60px; right:100px; width:160px; height:160px; border-radius:50%; background:rgba(16,185,129,0.2); filter:blur(35px); pointer-events:none;"></div>

          <div class="row align-items-center position-relative" style="z-index:2;">
            <div class="col-lg-8 mb-3 mb-lg-0">
              <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge" style="background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.25); color:#fff; font-size:.72rem; font-weight:600; border-radius:20px; padding:.3rem .75rem;">
                  <i class="bi {{ $greetingIcon }} me-1 text-warning"></i> {{ $greeting }}
                </span>
                <span class="badge" style="background:rgba(16,185,129,0.25); border:1px solid rgba(16,185,129,0.4); color:#6ee7b7; font-size:.72rem; font-weight:600; border-radius:20px; padding:.3rem .75rem;">
                  <span class="pulse-dot d-inline-block me-1" style="width:6px;height:6px;background:#10b981;border-radius:50%;"></span> Active Session
                </span>
              </div>
              <h2 class="fw-bold mb-1 text-white" style="font-family:'Sora',sans-serif; font-size: 1.4rem; letter-spacing:-.02em;">
                Welcome back, {{ Str::words($employee->name ?? 'Employee', 2, '') }}! 👋
              </h2>
              <p class="mb-0 text-white-50" style="font-size: .83rem; max-width: 620px;">
                Access your real-time attendance log, monthly timesheets, and downloadable e-payslips anytime from your employee portal.
              </p>
            </div>

            <div class="col-lg-4 text-lg-end">
              <div class="d-inline-flex flex-column align-items-lg-end p-2 px-3" style="background:rgba(255,255,255,0.1); backdrop-filter:blur(12px); border-radius:var(--r-lg); border:1px solid rgba(255,255,255,0.2);">
                <div class="text-white-50" style="font-size:.62rem; text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Employee ID / Position</div>
                <div class="fw-bold text-white mt-1" style="font-size:.88rem; font-family:'Sora',sans-serif;">
                  <i class="bi bi-person-badge me-1"></i> {{ $employee->employee_id ?? 'EMP-'.$user->id }}
                </div>
                <div style="font-size:.72rem; color:rgba(255,255,255,0.85);">
                  {{ $employee->position ?? 'Faculty / Staff' }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- KPI Row -->
        <div class="row g-2 mb-3">
          @php
            $kpis = [
              ['val'=>$stats['present_days'], 'label'=>'Days Present',   'c'=>'#2563eb','bg'=>'rgba(37,99,235,.10)',  'icon'=>'check-circle-fill',   'sub'=>'This month'],
              ['val'=>$stats['absent_days'],  'label'=>'Days Absent',    'c'=>'#64748b','bg'=>'rgba(100,116,139,.10)', 'icon'=>'x-circle',            'sub'=>'This month'],
              ['val'=>$stats['total_hours'],  'label'=>'Hours Rendered', 'c'=>'#1e40af','bg'=>'rgba(30,64,175,.10)',  'icon'=>'hourglass-split',      'sub'=>'This month'],
              ['val'=>$netPayVal,             'label'=>'Latest Net Pay', 'c'=>'#059669','bg'=>'rgba(5,150,105,.10)',  'icon'=>'wallet2',              'sub'=>'Latest payslip'],
            ];
          @endphp
          @foreach($kpis as $idx => $k)
          <div class="col-12 col-sm-6 col-lg-3 s{{ $idx }}">
            <div class="kpi" style="--kpi-c:{{ $k['c'] }};--kpi-bg:{{ $k['bg'] }};">
              <div class="kpi-accent"></div>
              <div class="kpi-header">
                <div class="kpi-icon"><i class="bi bi-{{ $k['icon'] }}"></i></div>
                <span class="kpi-period">{{ $k['sub'] }}</span>
              </div>
              <div class="kpi-val" style="color:{{ $k['c'] }}; font-size: 1.4rem;">{{ $k['val'] }}</div>
              <div class="kpi-label">{{ $k['label'] }}</div>
            </div>
          </div>
          @endforeach
        </div>

        <!-- Calendar + Payslip Row -->
        <div class="row g-2">

          <!-- Attendance Calendar -->
          <div class="col-lg-8 s1">
            <div class="card h-100">
              <div class="card-hd">
                <div class="card-title">
                  <div class="ct-icon" style="background:rgba(37,99,235,.1);color:var(--brand);">
                    <i class="bi bi-calendar3"></i>
                  </div>
                  Attendance Calendar
                </div>
                <div class="d-flex align-items-center gap-2">
                  <a href="{{ $calPrevUrl }}" class="btn-ghost" title="Previous month">
                    <i class="bi bi-chevron-left"></i>
                  </a>
                  <div style="font-family:'Sora',sans-serif;font-weight:800;font-size:.78rem;color:var(--text);min-width:96px;text-align:center;">
                    {{ $calCursor->format('F Y') }}
                  </div>
                  @if($isCurrentCalMonth)
                    <span class="btn-ghost" style="opacity:.35;cursor:not-allowed;" title="Already at the current month">
                      <i class="bi bi-chevron-right"></i>
                    </span>
                  @else
                    <a href="{{ $calNextUrl }}" class="btn-ghost" title="Next month">
                      <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ $calTodayUrl }}" class="btn-outline btn-sm" style="margin-left:2px;">Today</a>
                  @endif
                </div>
              </div>

              <div class="att-cal-wrap">
                <!-- Day-of-week headers -->
                <div class="cal-month-header">
                  @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
                  <div class="cal-dow">{{ $dow }}</div>
                  @endforeach
                </div>

                <!-- Grid -->
                <div class="cal-grid">
                  @for($blank = 0; $blank < $startDow; $blank++)
                    <div class="cal-cell cal-empty"></div>
                  @endfor

                  @for($d = 1; $d <= $daysInMo; $d++)
                    @php
                      $cellDate   = \Carbon\Carbon::create($calYear, $calMonth, $d);
                      $att        = $attByDay[$d] ?? null;
                      $status     = $att ? strtolower($att->status ?? 'present') : null;
                      $isToday    = $cellDate->isToday();
                      $isFuture   = $cellDate->isFuture() && !$isToday;
                      $isWeekend  = in_array($cellDate->dayOfWeek, [0, 6]);
                      $timeIn     = $att && $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('h:i') : null;

                      $cellClass  = 'cal-cell';
                      if ($isToday)             $cellClass .= ' cal-today';
                      elseif ($isFuture)        $cellClass .= ' cal-future-day';
                      elseif ($status)          $cellClass .= ' cal-'.$status;
                      elseif ($isWeekend)       $cellClass .= ' cal-weekend-empty';
                      else                      $cellClass .= ' cal-no-record';

                      /* tooltip text */
                      if ($att) {
                        $tipOut  = $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : 'n/a';
                        $tipIn   = $att->time_in  ? \Carbon\Carbon::parse($att->time_in)->format('h:i A')  : 'n/a';
                        $tooltip = ucfirst($status ?? 'present') . ' · In: '.$tipIn.' Out: '.$tipOut;
                      } elseif ($isWeekend) {
                        $tooltip = 'Weekend';
                      } elseif ($isFuture) {
                        $tooltip = 'Upcoming';
                      } else {
                        $tooltip = 'No attendance record';
                      }
                    @endphp
                    <button type="button" class="{{ $cellClass }}" @if($isFuture) disabled @endif>
                      <span class="cal-num">{{ $d }}</span>
                      @if($timeIn && !$isFuture)
                        <span class="cal-time-in">{{ $timeIn }}</span>
                      @endif
                      @if($status && !$isFuture)
                        <span class="cal-status-dot"></span>
                      @endif
                      <span class="cal-tip" role="tooltip">{{ $tooltip }}</span>
                    </button>
                  @endfor
                </div>
              </div>

              <!-- Legend -->
              <div class="cal-legend">
                <div class="cal-legend-item">
                  <span class="cal-legend-swatch" style="background:rgba(37,99,235,.25);border:1.5px solid rgba(37,99,235,.4);"></span>Today
                </div>
                <div class="cal-legend-item">
                  <span class="cal-legend-swatch" style="background:rgba(16,185,129,.2);border:1.5px solid rgba(16,185,129,.3);"></span>Present
                </div>
                <div class="cal-legend-item">
                  <span class="cal-legend-swatch" style="background:rgba(245,158,11,.18);border:1.5px solid rgba(245,158,11,.3);"></span>Late
                </div>
                <div class="cal-legend-item">
                  <span class="cal-legend-swatch" style="background:rgba(239,68,68,.14);border:1.5px solid rgba(239,68,68,.25);"></span>Absent
                </div>
                <div class="cal-legend-item">
                  <span class="cal-legend-swatch" style="background:rgba(124,58,237,.12);border:1.5px solid rgba(124,58,237,.25);"></span>On Leave
                </div>
                <div class="cal-legend-item">
                  <span class="cal-legend-swatch" style="background:transparent;border:1.5px dashed var(--border);"></span>No Record
                </div>
              </div>
            </div>
          </div>

          <!-- Right column -->
          <div class="col-lg-4 s2">
            <div class="d-flex flex-column gap-2 h-100">

              <!-- Today's Status -->
              @php
                $todayAtt  = $attByDay[$todayDay] ?? null;
                $todaySt   = $todayAtt ? strtolower($todayAtt->status ?? 'present') : null;
                $todayColors = [
                  'present'  => ['bg'=>'rgba(16,185,129,.1)',  'c'=>'#059669', 'icon'=>'check-circle-fill'],
                  'late'     => ['bg'=>'rgba(245,158,11,.1)',  'c'=>'#b45309', 'icon'=>'exclamation-circle-fill'],
                  'absent'   => ['bg'=>'rgba(239,68,68,.08)',  'c'=>'#dc2626', 'icon'=>'x-circle-fill'],
                  'on_leave' => ['bg'=>'rgba(124,58,237,.08)', 'c'=>'#6d28d9', 'icon'=>'calendar-minus-fill'],
                ];
                $tc = $todayColors[$todaySt] ?? ['bg'=>'var(--bg-2)', 'c'=>'var(--text-3)', 'icon'=>'dash-circle'];
              @endphp
              <div class="card">
                <div class="card-body" style="padding:.85rem 1.1rem;">
                  <div style="font-family:'Sora',sans-serif;font-size:.73rem;font-weight:800;color:var(--text);margin-bottom:.6rem;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-calendar-day-fill" style="color:var(--brand);font-size:.78rem;"></i>
                    Today's Status
                  </div>
                  @if($todayAtt)
                    <div style="background:{{ $tc['bg'] }};border-radius:10px;padding:.7rem .95rem;display:flex;align-items:center;gap:.7rem;">
                      <div style="width:38px;height:38px;border-radius:10px;background:{{ $tc['c'] }}20;display:grid;place-items:center;flex-shrink:0;">
                        <i class="bi bi-{{ $tc['icon'] }}" style="color:{{ $tc['c'] }};font-size:1.05rem;"></i>
                      </div>
                      <div>
                        <div style="font-family:'Sora',sans-serif;font-weight:800;font-size:.84rem;color:{{ $tc['c'] }};">{{ ucfirst($todaySt) }}</div>
                        <div style="font-size:.63rem;color:var(--text-3);margin-top:2px;">
                          @if($todayAtt->time_in) In: {{ \Carbon\Carbon::parse($todayAtt->time_in)->format('h:i A') }} @endif
                          @if($todayAtt->time_out) · Out: {{ \Carbon\Carbon::parse($todayAtt->time_out)->format('h:i A') }} @endif
                        </div>
                      </div>
                    </div>
                  @else
                    <div style="background:var(--bg-2);border-radius:10px;padding:.7rem .95rem;display:flex;align-items:center;gap:.7rem;">
                      <div style="width:38px;height:38px;border-radius:10px;background:var(--border);display:grid;place-items:center;flex-shrink:0;">
                        <i class="bi bi-dash-circle" style="color:var(--text-3);font-size:1.05rem;"></i>
                      </div>
                      <div>
                        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.82rem;color:var(--text-2);">No Record Yet</div>
                        <div style="font-size:.62rem;color:var(--text-3);margin-top:2px;">{{ now()->format('l, F j') }}</div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>

              <!-- Latest Payslip -->
              <div class="card" style="flex:1;">
                <div class="card-hd" style="padding:.72rem 1.1rem;">
                  <div class="card-title" style="font-size:.75rem;">
                    <div class="ct-icon" style="background:rgba(16,185,129,.1);color:#10b981;">
                      <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    Latest Payslip
                  </div>
                </div>
                <div class="card-body" style="padding:.85rem 1.05rem;">
                  @if(isset($payslips) && $payslips->count() > 0)
                    @php $lp = $payslips->first(); @endphp
                    <div class="payslip-card">
                      <div style="font-size:.56rem;opacity:.5;text-transform:uppercase;letter-spacing:.6px;font-weight:700;position:relative;z-index:1;">Pay Period</div>
                      <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.82rem;margin:.12rem 0 .55rem;position:relative;z-index:1;">
                        {{ $lp->pay_period ?? ($lp->sent_at?->format('F Y') ?? '—') }}
                      </div>
                      <div style="font-size:.56rem;opacity:.5;text-transform:uppercase;letter-spacing:.6px;font-weight:700;position:relative;z-index:1;">Net Pay</div>
                      <div style="font-family:'Sora',sans-serif;font-weight:900;font-size:1.55rem;line-height:1;margin-top:.1rem;position:relative;z-index:1;letter-spacing:-.025em;">
                        ₱{{ number_format($lp->total_honorarium ?? 0, 2) }}
                      </div>
                    </div>
                    <div class="d-flex gap-2">
                      <button
                        onclick="viewPayslip('{{ route('employee.payslip.json', $lp->id) }}', '{{ route('employee.payslip.download', $lp->id) }}')"
                        class="btn-primary w-100 btn-sm" style="justify-content:center;">
                        <i class="bi bi-eye"></i> View Payslip
                      </button>
                      <a href="{{ route('employee.payslip.download', $lp->id) }}" class="btn-outline btn-sm" style="flex-shrink:0;" title="Download">
                        <i class="bi bi-download"></i>
                      </a>
                    </div>
                  @else
                    <div class="empty-state" style="padding:1.4rem;">
                      <i class="bi bi-receipt" style="font-size:1.8rem;"></i>
                      <p style="font-size:.72rem;">No payslips yet.</p>
                    </div>
                  @endif
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
      <!-- /OVERVIEW -->


      <!-- ════════════════════════════════
           PANEL: ATTENDANCE
      ════════════════════════════════ -->
      <div class="tab-panel" id="panel-attendance">
        <div class="ph">
          <div>
            <div class="ph-title">Attendance Records</div>
            <div class="ph-sub">Your monthly check-in/out history</div>
          </div>
          <button class="btn-outline" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
          </button>
        </div>

        <div class="row g-2 mb-3">
          @foreach([
            ['val'=>$stats['present_days'],'label'=>'Present','c'=>'#2563eb','bg'=>'rgba(37,99,235,.1)','icon'=>'check-circle-fill'],
            ['val'=>$stats['absent_days'], 'label'=>'Absent', 'c'=>'#ef4444','bg'=>'rgba(239,68,68,.1)','icon'=>'x-circle-fill'],
            ['val'=>$stats['total_hours'], 'label'=>'Hrs',    'c'=>'#10b981','bg'=>'rgba(16,185,129,.1)','icon'=>'hourglass-split'],
          ] as $s)
          <div class="col-6 col-md-3">
            <div class="card" style="padding:.82rem 1rem;">
              <div class="d-flex align-items-center gap-2">
                <div style="width:34px;height:34px;border-radius:9px;background:{{ $s['bg'] }};display:grid;place-items:center;flex-shrink:0;">
                  <i class="bi bi-{{ $s['icon'] }}" style="color:{{ $s['c'] }};font-size:.82rem;"></i>
                </div>
                <div>
                  <div style="font-family:'Sora',sans-serif;font-weight:800;font-size:1.1rem;color:{{ $s['c'] }};">{{ $s['val'] }}</div>
                  <div style="font-size:.6rem;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.3px;">{{ $s['label'] }}</div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <div class="card">
          <div class="card-hd">
            <div class="card-title">
              <div class="ct-icon" style="background:rgba(37,99,235,.1);color:var(--brand);">
                <i class="bi bi-table"></i>
              </div>
              Attendance Log
            </div>
            <input type="text" class="f-input" style="width:190px;font-size:.76rem;" placeholder="Search records…" id="attSearch" oninput="searchAtt()">
          </div>
          <div class="table-responsive">
            <table class="data-table" id="attTable">
              <thead>
                <tr>
                  <th>#</th><th>Date</th><th>Day</th>
                  <th>Time In</th><th>Time Out</th><th>Hours</th><th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($attendances ?? [] as $i => $att)
                <tr>
                  <td style="color:var(--text-3);font-size:.68rem;">{{ $i + 1 }}</td>
                  <td style="font-weight:700;">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</td>
                  <td style="color:var(--text-3);">{{ \Carbon\Carbon::parse($att->date)->format('D') }}</td>
                  <td>{{ $att->time_in  ? \Carbon\Carbon::parse($att->time_in)->format('h:i A')  : '—' }}</td>
                  <td>{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : '—' }}</td>
                  <td style="font-weight:600;">{{ round($att->hours_rendered ?? 0, 2) }}h</td>
                  <td>
                    @php $st = strtolower($att->status ?? 'absent'); @endphp
                    <span class="badge badge-{{ $st }}">
                      <span class="badge-dot" style="background:currentColor;"></span>
                      {{ ucfirst($att->status ?? 'Absent') }}
                    </span>
                  </td>
                </tr>
                @empty
                <tr><td colspan="7">
                  <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <p>No attendance records found.</p>
                  </div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>


      <!-- ════════════════════════════════
           PANEL: TIMESHEETS
      ════════════════════════════════ -->
      <div class="tab-panel" id="panel-timesheets">
        <div class="ph">
          <div>
            <div class="ph-title">Timesheets</div>
            <div class="ph-sub">Submit and track your daily timesheet entries</div>
          </div>
          <button class="btn-primary" data-bs-toggle="modal" data-bs-target="#timesheetModal">
            <i class="bi bi-plus-circle-fill"></i> Submit Timesheet
          </button>
        </div>

        <div class="card">
          <div class="card-hd">
            <div class="card-title">
              <div class="ct-icon" style="background:rgba(124,58,237,.1);color:#7c3aed;">
                <i class="bi bi-list-check"></i>
              </div>
              Timesheet History
            </div>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Type</th><th>Hours</th><th>Status</th></tr>
              </thead>
              <tbody>
                @forelse($timesheets ?? [] as $ts)
                <tr>
                  <td style="font-weight:700;">{{ \Carbon\Carbon::parse($ts->date)->format('M d, Y') }}</td>
                  <td>{{ $ts->time_in  ? \Carbon\Carbon::parse($ts->time_in)->format('h:i A')  : '—' }}</td>
                  <td>{{ $ts->time_out ? \Carbon\Carbon::parse($ts->time_out)->format('h:i A') : '—' }}</td>
                  <td style="font-size:.76rem;">{{ $ts->work_type ?? 'Regular' }}</td>
                  <td style="font-weight:600;">{{ round($ts->hours ?? 0, 2) }}h</td>
                  <td>
                    @php $tss = strtolower($ts->status ?? 'submitted'); @endphp
                    <span class="badge badge-{{ $tss }}">{{ ucfirst($tss) }}</span>
                  </td>
                </tr>
                @empty
                <tr><td colspan="6">
                  <div class="empty-state" style="padding:2rem;">
                    <i class="bi bi-clock-history"></i>
                    <p>No timesheets submitted yet.</p>
                  </div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>


      <!-- ════════════════════════════════
           PANEL: PAYSLIPS
      ════════════════════════════════ -->
      <div class="tab-panel" id="panel-payslips">
        <div class="ph">
          <div>
            <div class="ph-title">My Payslips</div>
            <div class="ph-sub">View and download your released payslips</div>
          </div>
        </div>

        @forelse($payslips ?? [] as $ps)
        <div class="ps-item">
          <div class="ps-icon">
            <i class="bi bi-file-earmark-text-fill"></i>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.86rem;color:var(--text);">
              {{ $ps->pay_period ?? ($ps->sent_at?->format('F Y') ?? '—') }}
            </div>
            <div style="font-size:.68rem;color:var(--text-3);margin-top:3px;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
              <span>Net Pay: <strong style="color:var(--brand);font-family:'Sora',sans-serif;">₱{{ number_format($ps->total_honorarium ?? 0, 2) }}</strong></span>
              <span style="width:3px;height:3px;border-radius:50%;background:var(--text-3);display:inline-block;"></span>
              <span>Issued: {{ $ps->sent_at?->format('M d, Y') ?? '—' }}</span>
              @if(!($ps->viewed ?? true))
                <span style="background:rgba(245,158,11,.18);color:#b45309;font-size:.56rem;font-weight:800;border-radius:4px;padding:1px 7px;letter-spacing:.3px;text-transform:uppercase;">New</span>
              @endif
            </div>
          </div>
          <div class="d-flex gap-2 align-items-center flex-shrink-0">
            <button
              onclick="viewPayslip('{{ route('employee.payslip.json', $ps->id) }}', '{{ route('employee.payslip.download', $ps->id) }}')"
              class="btn-primary btn-sm">
              <i class="bi bi-eye"></i> View
            </button>
            <a href="{{ route('employee.payslip.download', $ps->id) }}" class="btn-outline btn-sm" title="Download PDF">
              <i class="bi bi-download"></i>
            </a>
          </div>
        </div>
        @empty
        <div class="empty-state card py-5">
          <i class="bi bi-receipt"></i>
          <p>No payslips available yet.<br>Payslips will appear here once released by admin.</p>
        </div>
        @endforelse
      </div>


      <!-- ════════════════════════════════
           PANEL: ANNOUNCEMENTS
      ════════════════════════════════ -->
      <div class="tab-panel" id="panel-announcements">
        <div class="ph">
          <div>
            <div class="ph-title">Announcements</div>
            <div class="ph-sub">Official notices and updates from the administration</div>
          </div>
          <select class="f-input" style="width:auto;font-size:.78rem;" id="annFilter" onchange="filterAnn()">
            <option value="all">All Types</option>
            <option value="general">General</option>
            <option value="payroll">Payroll</option>
            <option value="holiday">Holiday</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <div id="annContainer">
          @forelse($announcements ?? [] as $ann)
          @php
            $ac = ['general'=>'#2563eb','payroll'=>'#10b981','holiday'=>'#f59e0b','urgent'=>'#ef4444'][$ann->type ?? 'general'] ?? '#2563eb';
            $ai = ['general'=>'megaphone','payroll'=>'cash-coin','holiday'=>'calendar-heart','urgent'=>'exclamation-triangle'][$ann->type ?? 'general'] ?? 'megaphone';
            $at = $ann->type ?? 'general';
          @endphp
          <div class="ann-card" style="--ann-c:{{ $ac }};" data-type="{{ $at }}">
            <div class="d-flex align-items-start gap-3 mb-2">
              <div style="width:38px;height:38px;border-radius:10px;background:{{ $ac }}18;display:grid;place-items:center;flex-shrink:0;">
                <i class="bi bi-{{ $ai }}" style="color:{{ $ac }};font-size:.9rem;"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                  <span style="font-family:'Sora',sans-serif;font-weight:800;font-size:.87rem;color:var(--text);">{{ $ann->title }}</span>
                  @if(!($ann->is_read ?? false))
                    <span style="background:var(--warn);color:#fff;font-size:.54rem;font-weight:800;border-radius:4px;padding:2px 7px;letter-spacing:.3px;">UNREAD</span>
                  @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span style="background:{{ $ac }}15;color:{{ $ac }};font-size:.6rem;font-weight:800;border-radius:4px;padding:2px 8px;text-transform:uppercase;letter-spacing:.4px;">{{ ucfirst($at) }}</span>
                  <span style="font-size:.64rem;color:var(--text-3);">{{ $ann->created_at?->format('M d, Y · H:i') ?? '—' }}</span>
                </div>
              </div>
            </div>
            <p style="font-size:.8rem;color:var(--text-2);margin:0;line-height:1.7;">{{ $ann->message }}</p>
          </div>
          @empty
          <div class="empty-state card py-5">
            <i class="bi bi-megaphone"></i>
            <p>No announcements yet.<br>Check back later for updates.</p>
          </div>
          @endforelse
        </div>
      </div>


      <!-- ════════════════════════════════
           PANEL: PROFILE
      ════════════════════════════════ -->
      <div class="tab-panel" id="panel-profile">
        <div class="ph">
          <div>
            <div class="ph-title">My Profile</div>
            <div class="ph-sub">Your account information and employment details</div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-lg-4">
            <div class="card overflow-hidden">
              <div class="profile-hero">
                <div class="ph-avatar">{{ $initials }}</div>
                <div style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:800;color:#fff;position:relative;z-index:1;">{{ $employee->name ?? '—' }}</div>
                <div style="font-size:.7rem;opacity:.65;color:#fff;margin-top:3px;position:relative;z-index:1;">{{ $employee->position ?? 'Employee' }}</div>
                <div style="margin-top:.5rem;position:relative;z-index:1;">
                  <span style="background:rgba(255,255,255,.14);border-radius:20px;padding:.18rem .9rem;font-size:.64rem;color:rgba(255,255,255,.8);font-weight:700;">
                    ID: {{ $employee->employee_id ?? $employee->id }}
                  </span>
                </div>
              </div>
              <div class="card-body">
                <div style="font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.55rem;">Contact</div>
                @foreach([
                  ['icon'=>'envelope-fill',  'val'=> $employee->email   ?? '—'],
                  ['icon'=>'telephone-fill', 'val'=> $employee->phone   ?? '—'],
                  ['icon'=>'geo-alt-fill',   'val'=> $employee->address ?? '—'],
                ] as $r)
                <div class="info-row">
                  <i class="bi bi-{{ $r['icon'] }} info-icon"></i>
                  <span class="info-val">{{ $r['val'] }}</span>
                </div>
                @endforeach

                <div style="font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin:.9rem 0 .55rem;">Employment</div>
                @foreach([
                  ['icon'=>'building-fill',  'label'=>'Department', 'val'=> $employee->department?->name ?? '—'],
                  ['icon'=>'briefcase-fill', 'label'=>'Position',   'val'=> $employee->position         ?? '—'],
                  ['icon'=>'cash-coin',      'label'=>'Hourly Rate','val'=> '₱'.number_format($employee->hourly_salary ?? 0, 2)],
                ] as $r)
                <div class="info-row">
                  <i class="bi bi-{{ $r['icon'] }} info-icon"></i>
                  <span class="info-label">{{ $r['label'] }}</span>
                  <span class="info-val">{{ $r['val'] }}</span>
                </div>
                @endforeach

                <div style="margin-top:.9rem;background:var(--brand-light);border:1px solid var(--brand-mid);border-radius:9px;padding:.65rem .85rem;font-size:.73rem;color:var(--brand);display:flex;align-items:flex-start;gap:7px;">
                  <i class="bi bi-info-circle-fill" style="margin-top:2px;flex-shrink:0;"></i>
                  Contact your HR administrator to update your profile information.
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <div class="card">
              <div class="card-hd">
                <div class="card-title">
                  <div class="ct-icon" style="background:rgba(37,99,235,.1);color:var(--brand);">
                    <i class="bi bi-person-fill"></i>
                  </div>
                  Employee Details
                </div>
                <span style="font-size:.67rem;color:var(--text-3);">Read-only — contact HR to update</span>
              </div>
              <div class="card-body">
                <div style="font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.7rem;">Personal Information</div>
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label class="f-label">Full Name</label>
                    <input class="f-input" value="{{ $employee->name ?? '' }}" disabled>
                  </div>
                  <div class="col-md-6">
                    <label class="f-label">Email Address</label>
                    <input class="f-input" value="{{ $employee->email ?? '' }}" disabled>
                  </div>
                  <div class="col-md-6">
                    <label class="f-label">Phone</label>
                    <input class="f-input" value="{{ $employee->phone ?? '—' }}" disabled>
                  </div>
                  <div class="col-md-6">
                    <label class="f-label">Address</label>
                    <input class="f-input" value="{{ $employee->address ?? '—' }}" disabled>
                  </div>
                </div>
                <div style="font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.7rem;">Employment Details</div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="f-label">Position</label>
                    <input class="f-input" value="{{ $employee->position ?? '—' }}" disabled>
                  </div>
                  <div class="col-md-6">
                    <label class="f-label">Department</label>
                    <input class="f-input" value="{{ $employee->department?->name ?? '—' }}" disabled>
                  </div>
                  <div class="col-md-6">
                    <label class="f-label">Hourly Salary</label>
                    <input class="f-input" value="₱{{ number_format($employee->hourly_salary ?? 0, 2) }}" disabled>
                  </div>
                  <div class="col-md-6">
                    <label class="f-label">Employee Type</label>
                    <input class="f-input" value="{{ $employee->type ?? '—' }}" disabled>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /.page-body -->
  </div><!-- /.main-content -->
</div><!-- /.app-shell -->


<!-- ════════════════════════════
     TIMESHEET MODAL
════════════════════════════ -->
<div class="modal fade" id="timesheetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0" style="border-radius:var(--r-lg);overflow:hidden;">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#071022,#1843c0 60%,#0ea5e9 120%);padding:1.1rem 1.4rem;">
        <div style="font-family:'Sora',sans-serif;font-weight:800;color:#fff;font-size:.9rem;display:flex;align-items:center;gap:8px;">
          <i class="bi bi-clock-history"></i> Submit Timesheet
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('employee.timesheets.store') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding:1.3rem;">
          <div class="mb-3">
            <label class="f-label">Date <span style="color:var(--danger);">*</span></label>
            <input type="date" name="date" class="f-input" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="f-label">Time In <span style="color:var(--danger);">*</span></label>
              <input type="time" name="time_in" class="f-input" required>
            </div>
            <div class="col-6">
              <label class="f-label">Time Out</label>
              <input type="time" name="time_out" class="f-input">
            </div>
          </div>
          <div class="mb-3">
            <label class="f-label">Work Type <span style="color:var(--danger);">*</span></label>
            <select name="work_type" class="f-input" required>
              <option value="">Select type…</option>
              <option value="Regular">Regular / Teaching</option>
              <option value="Overtime">Overtime</option>
              <option value="Meeting">Meeting / Training</option>
              <option value="Fieldwork">Field Work</option>
              <option value="WFH">Work From Home</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="f-label">Task / Activity</label>
            <textarea name="task" class="f-input" rows="3" placeholder="Describe tasks completed…" style="resize:vertical;"></textarea>
          </div>
          <div class="mb-1">
            <label class="f-label">Remarks</label>
            <textarea name="remarks" class="f-input" rows="2" placeholder="Optional notes…" style="resize:vertical;"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:.75rem 1.3rem;">
          <button type="button" class="btn-outline" data-bs-dismiss="modal">
            <i class="bi bi-x"></i> Cancel
          </button>
          <button type="submit" class="btn-primary">
            <i class="bi bi-send-fill"></i> Submit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ════════════════════════════
     PAYSLIP VIEW MODAL
════════════════════════════ -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:var(--r-xl)!important;overflow:hidden;border:1px solid var(--border)!important;">
      <div id="psModalBody">
        <!-- Dynamically populated -->
        <div class="ps-modal-loader">
          <div class="spin"></div>
          <div style="margin-top:.9rem;font-size:.82rem;color:var(--text-3);">Loading payslip…</div>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
/* ═══════════════════════════════════════════
   THEME TOGGLE
═══════════════════════════════════════════ */
const html = document.documentElement;

function updateThemeIcon(theme) {
  const icon = document.getElementById('themeIcon');
  if (!icon) return;
  icon.className = theme === 'dark'
    ? 'bi bi-sun-fill'
    : 'bi bi-moon-stars-fill';
}

updateThemeIcon(html.dataset.theme || 'light');

document.getElementById('themeToggle')?.addEventListener('click', () => {
  const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
  html.dataset.theme = next;
  localStorage.setItem('mcc-theme', next);
  updateThemeIcon(next);
});

/* ═══════════════════════════════════════════
   TAB ROUTING
═══════════════════════════════════════════ */
const TAB_META = {
  overview:      { title: 'Overview',           breadcrumb: 'Dashboard'     },
  attendance:    { title: 'Attendance Records',  breadcrumb: 'Attendance'    },
  timesheets:    { title: 'Timesheets',          breadcrumb: 'Timesheets'    },
  payslips:      { title: 'My Payslips',         breadcrumb: 'Payslips'      },
  announcements: { title: 'Announcements',       breadcrumb: 'Announcements' },
  profile:       { title: 'My Profile',          breadcrumb: 'Profile'       },
};

function switchTab(tab) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + tab)?.classList.add('active');
  document.querySelectorAll('.sb-link[data-tab]').forEach(l => l.classList.remove('active'));
  document.getElementById('nav-' + tab)?.classList.add('active');
  const m = TAB_META[tab] || {};
  document.getElementById('tbTitle').textContent      = m.title      || 'Portal';
  document.getElementById('tbBreadcrumb').textContent = m.breadcrumb || 'Portal';
  if (window.innerWidth < 992) document.getElementById('sidebar').classList.remove('show');
}

document.querySelectorAll('.sb-link[data-tab]').forEach(btn => {
  btn.addEventListener('click', () => switchTab(btn.dataset.tab));
});
document.querySelectorAll('[data-switch]').forEach(btn => {
  btn.addEventListener('click', () => switchTab(btn.dataset.switch));
});
document.getElementById('viewAllAnn')?.addEventListener('click',  () => switchTab('announcements'));
document.getElementById('notifBtn')?.addEventListener('click',    () => switchTab('announcements'));
document.getElementById('profileBtn')?.addEventListener('click',  () => switchTab('profile'));

/* ═══════════════════════════════════════════
   MOBILE SIDEBAR
═══════════════════════════════════════════ */
document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('show');
});
document.addEventListener('click', e => {
  const sb  = document.getElementById('sidebar');
  const btn = document.getElementById('mobileMenuBtn');
  if (window.innerWidth < 992 && sb.classList.contains('show') &&
      !sb.contains(e.target) && !btn?.contains(e.target)) {
    sb.classList.remove('show');
  }
});

/* ═══════════════════════════════════════════
   LIVE CLOCK
═══════════════════════════════════════════ */
function tick() {
  const d  = new Date();
  const el = document.getElementById('liveClock');
  if (el) el.textContent =
    d.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' }) +
    '  ·  ' +
    d.toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
}
setInterval(tick, 1000); tick();

/* ═══════════════════════════════════════════
   ATTENDANCE CALENDAR — TAP-TO-SHOW TOOLTIP
   (hover already shows it via CSS; this adds a tap
   toggle for touch devices where :hover is unreliable)
═══════════════════════════════════════════ */
document.querySelectorAll('.cal-cell:not(.cal-empty)').forEach(cell => {
  if (cell.disabled) return;
  cell.addEventListener('click', () => {
    const wasOpen = cell.classList.contains('show-tip');
    document.querySelectorAll('.cal-cell.show-tip').forEach(c => c.classList.remove('show-tip'));
    if (!wasOpen) cell.classList.add('show-tip');
  });
});
document.addEventListener('click', e => {
  if (!e.target.closest('.cal-cell')) {
    document.querySelectorAll('.cal-cell.show-tip').forEach(c => c.classList.remove('show-tip'));
  }
});

/* ═══════════════════════════════════════════
   ATTENDANCE TABLE SEARCH
═══════════════════════════════════════════ */
function searchAtt() {
  const q = document.getElementById('attSearch').value.toLowerCase();
  document.querySelectorAll('#attTable tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

/* ═══════════════════════════════════════════
   ANNOUNCEMENTS FILTER
═══════════════════════════════════════════ */
function filterAnn() {
  const t = document.getElementById('annFilter').value;
  document.querySelectorAll('#annContainer .ann-card').forEach(c => {
    c.style.display = (t === 'all' || c.dataset.type === t) ? '' : 'none';
  });
}

/* ═══════════════════════════════════════════
   PAYSLIP VIEW — FETCH & RENDER
═══════════════════════════════════════════ */
let _psModalInstance = null;

async function viewPayslip(fetchUrl, downloadUrl) {
  const bodyEl = document.getElementById('psModalBody');
  const modalEl = document.getElementById('payslipModal');

  /* Show loading state */
  bodyEl.innerHTML = `
    <div class="ps-modal-loader">
      <div class="spin"></div>
      <div style="margin-top:.9rem;font-size:.82rem;color:var(--text-3);">Loading payslip…</div>
    </div>`;

  if (!_psModalInstance) {
    _psModalInstance = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
  }
  _psModalInstance.show();

  try {
    const res = await fetch(fetchUrl, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    renderPayslipDoc(data, bodyEl, downloadUrl);
  } catch (err) {
    bodyEl.innerHTML = `
      <div style="padding:2.5rem;text-align:center;">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.1);display:grid;place-items:center;margin:0 auto .9rem;">
          <i class="bi bi-exclamation-triangle-fill" style="font-size:1.4rem;color:#dc2626;"></i>
        </div>
        <div style="font-family:'Sora',sans-serif;font-weight:800;font-size:.88rem;color:var(--text);margin-bottom:.4rem;">Could Not Load Payslip</div>
        <div style="font-size:.78rem;color:var(--text-3);">Please try again or download directly.</div>
        <a href="${downloadUrl}" class="btn-primary btn-sm" style="margin-top:1rem;display:inline-flex;">
          <i class="bi bi-download"></i> Download PDF
        </a>
      </div>`;
  }
}

function renderPayslipDoc(raw, container, downloadUrl) {
  /* Normalise — handle both flat and nested structures */
  const ps  = raw.payslip ?? raw;
  const emp = raw.employee ?? ps.employee ?? {};

  const fmt = v => '₱' + parseFloat(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmtDate = d => d ? new Date(d).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) : '—';

  /* Earnings */
  const basicPay   = parseFloat(ps.basic_pay    ?? ps.basic_salary   ?? ps.salary           ?? 0);
  const daysWorked = parseFloat(ps.days_worked  ?? ps.days_present   ?? ps.total_days_worked ?? 0);
  const hoursWork  = parseFloat(ps.hours_worked ?? ps.total_hours     ?? 0);
  const overtime   = parseFloat(ps.overtime_pay ?? ps.ot_pay          ?? 0);
  const allowances = parseFloat(ps.allowances   ?? ps.total_allowance ?? ps.cola             ?? 0);
  const grossPay   = parseFloat(ps.gross_pay    ?? ps.gross           ?? (basicPay + overtime + allowances));

  /* Deductions */
  const sss        = parseFloat(ps.sss_deduction        ?? ps.sss         ?? 0);
  const philhealth = parseFloat(ps.philhealth_deduction ?? ps.philhealth  ?? ps.phic ?? 0);
  const pagibig    = parseFloat(ps.pagibig_deduction    ?? ps.pagibig     ?? ps.hdmf ?? 0);
  const tax        = parseFloat(ps.tax_deduction        ?? ps.withholding_tax ?? ps.tax ?? 0);
  const loans      = parseFloat(ps.loan_deductions      ?? ps.loans       ?? ps.other_deductions ?? 0);
  const totalDed   = sss + philhealth + pagibig + tax + loans;

  /* Net */
  const netPay     = parseFloat(ps.total_honorarium ?? ps.net_pay ?? (grossPay - totalDed));

  /* Status badge */
  const status     = (ps.status ?? 'released').toLowerCase();
  const statusColors = { released: ['#1d4ed8','rgba(37,99,235,.12)'], paid: ['#059669','rgba(16,185,129,.12)'], pending: ['#b45309','rgba(245,158,11,.12)'] };
  const [sc, sbg]  = statusColors[status] ?? statusColors['released'];

  /* Render */
  container.innerHTML = `
  <!-- Header -->
  <div class="ps-doc-header">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.7rem;">
      <div>
        <div style="font-size:.56rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.4);margin-bottom:.15rem;">Official Document</div>
        <div style="font-family:'Sora',sans-serif;font-weight:900;font-size:1.2rem;color:#fff;letter-spacing:-.02em;line-height:1;">Payslip</div>
        <div style="font-size:.68rem;color:rgba(255,255,255,.5);margin-top:4px;">MCC Employee Portal · Digital Payroll System</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:.56rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Pay Period</div>
        <div style="font-family:'Sora',sans-serif;font-weight:700;color:#fff;font-size:.88rem;margin-top:2px;">${ps.pay_period || '—'}</div>
        <div style="font-size:.63rem;color:rgba(255,255,255,.4);margin-top:3px;">Issued: ${fmtDate(ps.sent_at)}</div>
        <div style="margin-top:6px;">
          <span style="background:${sbg};color:${sc};font-size:.58rem;font-weight:800;border-radius:5px;padding:3px 10px;text-transform:uppercase;letter-spacing:.4px;">${status}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal close button -->
  <button type="button" onclick="bootstrap.Modal.getInstance(document.getElementById('payslipModal')).hide()"
    style="position:absolute;top:14px;right:16px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:#fff;display:grid;place-items:center;cursor:pointer;z-index:10;font-size:.7rem;">
    <i class="bi bi-x-lg"></i>
  </button>

  <!-- Employee info -->
  <div style="padding:1.2rem 1.8rem .6rem;border-bottom:1px solid var(--border);">
    <div style="font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.6rem;">Employee Information</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem .8rem;">
      <div>
        <div style="font-size:.6rem;color:var(--text-3);font-weight:600;">Full Name</div>
        <div style="font-weight:700;font-size:.84rem;color:var(--text);margin-top:1px;">${emp.name || '—'}</div>
      </div>
      <div>
        <div style="font-size:.6rem;color:var(--text-3);font-weight:600;">Employee ID</div>
        <div style="font-weight:700;font-size:.84rem;color:var(--text);margin-top:1px;">${emp.employee_id || ps.employee_id || '—'}</div>
      </div>
      <div>
        <div style="font-size:.6rem;color:var(--text-3);font-weight:600;">Department</div>
        <div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${emp.department?.name || '—'}</div>
      </div>
      <div>
        <div style="font-size:.6rem;color:var(--text-3);font-weight:600;">Position</div>
        <div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${emp.position || '—'}</div>
      </div>
      ${daysWorked > 0 ? `<div><div style="font-size:.6rem;color:var(--text-3);font-weight:600;">Days Worked</div><div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${daysWorked} day(s)</div></div>` : ''}
      ${hoursWork  > 0 ? `<div><div style="font-size:.6rem;color:var(--text-3);font-weight:600;">Hours Worked</div><div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${hoursWork}h</div></div>` : ''}
    </div>
  </div>

  <!-- Earnings -->
  <div style="padding:.9rem 1.8rem .4rem;">
    <div style="font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.5rem;">Earnings</div>
    <div class="ps-table-bg">
      ${basicPay > 0 ? `
      <div class="ps-row">
        <span class="ps-row-key"><i class="bi bi-cash" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Basic Pay</span>
        <span class="ps-row-val earn">${fmt(basicPay)}</span>
      </div>` : ''}
      ${overtime > 0 ? `
      <div class="ps-row">
        <span class="ps-row-key"><i class="bi bi-clock-history" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Overtime Pay</span>
        <span class="ps-row-val earn">${fmt(overtime)}</span>
      </div>` : ''}
      ${allowances > 0 ? `
      <div class="ps-row">
        <span class="ps-row-key"><i class="bi bi-gift" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Allowances</span>
        <span class="ps-row-val earn">${fmt(allowances)}</span>
      </div>` : ''}
      <div class="ps-subtotal" style="background:rgba(16,185,129,.06);border-top:2px solid rgba(16,185,129,.12);">
        <span style="color:var(--text);font-family:'Sora',sans-serif;">Gross Pay</span>
        <span style="color:#059669;font-family:'Sora',sans-serif;font-size:.9rem;">${fmt(grossPay || basicPay)}</span>
      </div>
    </div>
  </div>

  <!-- Deductions -->
  ${totalDed > 0 ? `
  <div style="padding:.6rem 1.8rem .4rem;">
    <div style="font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.5rem;">Mandatory Deductions</div>
    <div class="ps-table-bg">
      ${sss > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-shield-check" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>SSS</span><span class="ps-row-val deduct">– ${fmt(sss)}</span></div>` : ''}
      ${philhealth > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-heart-pulse" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>PhilHealth</span><span class="ps-row-val deduct">– ${fmt(philhealth)}</span></div>` : ''}
      ${pagibig > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-house-heart" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Pag-IBIG (HDMF)</span><span class="ps-row-val deduct">– ${fmt(pagibig)}</span></div>` : ''}
      ${tax > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-percent" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Withholding Tax</span><span class="ps-row-val deduct">– ${fmt(tax)}</span></div>` : ''}
      ${loans > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-credit-card" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Loans / Other</span><span class="ps-row-val deduct">– ${fmt(loans)}</span></div>` : ''}
      <div class="ps-subtotal" style="background:rgba(239,68,68,.05);border-top:2px solid rgba(239,68,68,.1);">
        <span style="color:var(--text);font-family:'Sora',sans-serif;">Total Deductions</span>
        <span style="color:#dc2626;font-family:'Sora',sans-serif;font-size:.9rem;">– ${fmt(totalDed)}</span>
      </div>
    </div>
  </div>` : ''}

  <!-- Net Pay -->
  <div class="ps-net-pay" style="margin-top:.8rem;">
    <div>
      <div style="font-size:.58rem;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.7px;font-weight:700;margin-bottom:.2rem;">Net Pay</div>
      <div style="font-family:'Sora',sans-serif;font-weight:900;font-size:2rem;color:#fff;letter-spacing:-.04em;line-height:1;">${fmt(netPay)}</div>
      <div style="font-size:.63rem;color:rgba(255,255,255,.35);margin-top:4px;">
        ${ps.pay_period || ''} · ${fmtDate(ps.sent_at)}
      </div>
    </div>
    <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.12);display:grid;place-items:center;border:1.5px solid rgba(255,255,255,.18);flex-shrink:0;">
      <i class="bi bi-cash-coin" style="font-size:1.35rem;color:#fff;"></i>
    </div>
  </div>

  <!-- Footer actions -->
  <div style="padding:.9rem 1.8rem 1.4rem;display:flex;gap:.6rem;justify-content:flex-end;border-top:1px solid var(--border);margin-top:.8rem;">
    <button type="button"
      onclick="bootstrap.Modal.getInstance(document.getElementById('payslipModal')).hide()"
      class="btn-outline btn-sm">
      <i class="bi bi-x"></i> Close
    </button>
    <a href="${downloadUrl}" class="btn-primary btn-sm" download>
      <i class="bi bi-download"></i> Download PDF
    </a>
  </div>`;
}

/* ═══════════════════════════════════════════
   HELP DIALOG
═══════════════════════════════════════════ */
function showHelp() {
  Swal.fire({
    icon: 'info', title: 'Help & Support',
    html: `<div style="text-align:left;font-size:.84rem;line-height:1.8;">
      <p><strong>Overview</strong> — Snapshot of your attendance, hours, calendar, and latest payslip.</p>
      <p><strong>Attendance</strong> — Full monthly records with time-in, time-out, and status.</p>
      <p><strong>Timesheets</strong> — Submit daily entries for admin review and approval.</p>
      <p><strong>Payslips</strong> — View and download released payslips from administration.</p>
      <p><strong>Announcements</strong> — Notices and updates from HR and management.</p>
      <p><strong>Profile</strong> — View your employment and contact details.</p>
      <div style="margin-top:1rem;padding:.7rem .9rem;background:#f8fafd;border-radius:9px;font-size:.76rem;color:#64748b;">
        For system issues, contact your HR administrator or IT support.
      </div>
    </div>`,
    confirmButtonColor: '#2563eb',
    confirmButtonText:  'Got it!'
  });
}

/* ═══════════════════════════════════════════
   SESSION ALERTS
═══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  @if(session('success'))
    Swal.fire({
      icon: 'success', title: 'Done!', text: @json(session('success')),
      confirmButtonColor: '#2563eb', toast: true, position: 'top-end',
      timer: 4000, timerProgressBar: true, showConfirmButton: false,
    });
  @endif
  @if(session('error'))
    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonColor: '#2563eb' });
  @endif
});
</script>
</body>
</html>