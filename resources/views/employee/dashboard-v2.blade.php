@use('Illuminate\Support\Str')
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MCC Employee Portal — {{ $displayName ?? ($user->name ?? 'Dashboard') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  {{-- Instrument Serif was requested here but never used by a single rule —
       dropped, along with the 300 and italic DM Sans cuts nothing referenced. --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

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
    /* Brand blue is deliberately unchanged — it is the same hue the admin
       portal uses, and the two halves of the system should read as one product.
       Everything around it is what was rebuilt. */
    --brand:        #2563eb;
    --brand-dark:   #1d4ed8;
    --brand-light:  #eef4ff;
    --brand-mid:    #d5e2fd;
    --accent:       #059669;
    --warn:         #d97706;
    --danger:       #dc2626;
    --safe:         #0f766e;
    --purple:       #7c3aed;
    --cyan:         #0891b2;

    --sb-w: 244px;
    --tb-h: 62px;

    /* Neutrals carry a slight blue bias so they sit under the brand rather
       than fighting it. A pure grey here reads as unconsidered. */
    --bg:           #f6f8fb;
    --bg-2:         #eef2f7;
    --card:         #ffffff;
    --card-hover:   #fbfcfe;

    --text:         #0f1729;
    --text-2:       #4b5a70;
    --text-3:       #8494a9;
    --text-inv:     #ffffff;

    --border:       #e6ebf2;
    --border-2:     #f1f4f9;

    --th-bg:        #f8fafc;
    --tr-hover:     #f4f8ff;
    --tr-stripe:    #fcfdff;

    --input-bg:     #f8fafc;
    --input-focus:  #ffffff;

    /* Flat deep slate. The old three-stop navy-to-electric-blue gradient was
       the single most dated element on the page and fought every card next
       to it for attention. */
    --sb-bg-1:      #101725;
    --sb-bg-2:      #0d1420;
    --sb-link-hover: rgba(255,255,255,.06);
    --sb-link-active: rgba(37,99,235,.9);
    --sb-text:      rgba(226,232,240,.62);
    --sb-text-hi:   #ffffff;
    --sb-label:     rgba(255,255,255,.26);
    --sb-border:    rgba(255,255,255,.07);

    /* Elevation is for things that genuinely float — menus, modals, the mobile
       drawer. A card that just sits on the page gets a hairline border and no
       shadow: stacking a shadow under every surface flattens the hierarchy it
       was supposed to create. */
    --sh-xs: 0 1px 2px rgba(15,23,41,.04);
    --sh-sm: 0 1px 2px rgba(15,23,41,.05);
    --sh-md: 0 4px 12px -2px rgba(15,23,41,.08), 0 2px 4px -2px rgba(15,23,41,.04);
    --sh-lg: 0 16px 40px -12px rgba(15,23,41,.20);

    /* Type scale. These were ad-hoc rem values scattered through the markup —
       .52, .54, .56, .57, .58, .6, .63, .68, .72, .79, .82, .85, .86 — thirteen
       sizes with no relationship, and the small end (.52rem is 8.3px) below
       what anyone can comfortably read. Seven steps, nothing under 11px. */
    --fs-2xs:  .6875rem;  /* 11px — micro labels, uppercase only */
    --fs-xs:   .75rem;    /* 12px — captions, meta */
    --fs-sm:   .8125rem;  /* 13px — secondary text, table cells */
    --fs-md:   .875rem;   /* 14px — body */
    --fs-lg:   1rem;      /* 16px — card titles */
    --fs-xl:   1.25rem;   /* 20px — page titles */
    --fs-2xl:  1.75rem;   /* 28px — KPI figures */

    /* Spacing, on a 4px grid. */
    --sp-1:  .25rem;
    --sp-2:  .5rem;
    --sp-3:  .75rem;
    --sp-4:  1rem;
    --sp-5:  1.25rem;
    --sp-6:  1.5rem;
    --sp-8:  2rem;
    --sp-10: 2.5rem;

    /* Tighter than the old 9/13/17/22. Large radii on small elements are the
       loudest single tell of a dated interface. */
    --r-sm: 6px;
    --r-md: 10px;
    --r-lg: 14px;
    --r-xl: 18px;

    --ease: cubic-bezier(.4,0,.2,1);
    --t:    all .16s var(--ease);
    --t-slow: all .28s var(--ease);
  }

  /* ══════════════════════════════════════════════
     DARK MODE TOKENS
  ══════════════════════════════════════════════ */
  [data-theme="dark"] {
    /* Not a naive inversion: text-3 in particular was #4a6080, which failed
       to read as text against the old card. Contrast was rebuilt per token. */
    --brand:        #4d82f3;
    --brand-dark:   #2563eb;
    --accent:       #34d399;
    --warn:         #fbbf24;
    --danger:       #f87171;
    --safe:         #5eead4;

    --bg:           #0b0f16;
    --bg-2:         #10151f;
    --card:         #141a24;
    --card-hover:   #1a212d;

    --text:         #e8edf5;
    --text-2:       #9aabc2;
    --text-3:       #6c7f96;
    --text-inv:     #0f1729;

    --border:       #222b3a;
    --border-2:     #1a222e;

    --th-bg:        #121822;
    --tr-hover:     #1b2434;
    --tr-stripe:    #121822;

    --input-bg:     #111721;
    --input-focus:  #19212e;

    --brand-light:  rgba(77,130,243,.14);
    --brand-mid:    rgba(77,130,243,.28);

    --sb-bg-1:      #0c111b;
    --sb-bg-2:      #090d15;

    --sh-xs: 0 1px 2px rgba(0,0,0,.3);
    --sh-sm: 0 1px 2px rgba(0,0,0,.3), 0 2px 6px rgba(0,0,0,.35);
    --sh-md: 0 2px 4px rgba(0,0,0,.3), 0 8px 20px -6px rgba(0,0,0,.5);
    --sh-lg: 0 12px 40px -10px rgba(0,0,0,.6);
  }

  /* ══════════════════════════════════════════════
     RESET & BASE
  ══════════════════════════════════════════════ */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; }

  /* The portal's real legibility problem was scale: sizes ran from .56rem to
     .82rem, i.e. 9px to 13px, and much of it is set inline across the page.
     Lifting the root lifts every rem in one move — including the inline ones —
     without touching two thousand lines of markup. */
  html { font-size: 17px; }

  @media (max-width: 640px) { html { font-size: 16px; } }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
    transition: background .28s var(--ease), color .28s var(--ease);
  }

  h1,h2,h3,h4,h5,h6 { font-family: 'Sora', sans-serif; text-wrap: balance; }

  /* Digits that stack in columns — pay figures, hours, dates — must align. */
  .data-table td, .kpi-val, .psu-digit, .ps-item strong { font-variant-numeric: tabular-nums; }

  :focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; border-radius: 4px; }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
  }

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
  /* Flat, not a gradient, and no decorative glow orbs behind the nav. Two
     blurred radial blobs used to sit in the corners; they added atmosphere to
     a surface whose whole job is to let you find the section you want. */
  .sidebar {
    width: var(--sb-w);
    background: var(--sb-bg-1);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    height: 100vh;
    overflow: hidden;
    position: relative;
    z-index: 100;
    border-right: 1px solid rgba(255,255,255,.06);
  }

  /* Brand */
  .sb-brand {
    padding: 1.05rem .95rem .9rem;
    border-bottom: 1px solid var(--sb-border);
    display: flex; align-items: center; gap: 10px;
    flex-shrink: 0; position: relative; z-index: 1;
  }
  .sb-brand-icon {
    width: 32px; height: 32px; border-radius: var(--r-sm);
    background: rgba(255,255,255,.1);
    display: grid; place-items: center; flex-shrink: 0;
    border: 1px solid rgba(255,255,255,.14);
  }
  .sb-brand-icon img { max-width: 19px; filter: brightness(0) invert(1); }
  .sb-brand-text  { font-family: 'Sora', sans-serif; font-size: var(--fs-md); font-weight: 700; color: #fff; line-height: 1.15; letter-spacing: -.01em; }
  .sb-brand-sub   { font-size: var(--fs-2xs); color: rgba(255,255,255,.34); margin-top: 1px; letter-spacing: .06em; text-transform: uppercase; }

  /* Profile pill */
  .sb-profile {
    padding: .65rem .9rem .72rem;
    border-bottom: 1px solid var(--sb-border);
    position: relative; z-index: 1;
  }
  /* No card around the identity. It is not a control and nothing happens when
     you click it, so the raised panel and its hover state were both promises
     the interface does not keep. */
  .sb-profile-inner {
    padding: .2rem .1rem;
    display: flex; align-items: center; gap: 10px;
    cursor: default;
  }
  .sb-avatar {
    width: 34px; height: 34px; border-radius: var(--r-sm);
    background: var(--brand);
    display: grid; place-items: center;
    font-family: 'Sora', sans-serif; font-size: var(--fs-xs); font-weight: 700;
    color: #fff; flex-shrink: 0; position: relative;
  }
  .sb-avatar-dot {
    position: absolute; bottom: -2px; right: -2px;
    width: 9px; height: 9px; border-radius: 50%;
    background: var(--accent); border: 2px solid var(--sb-bg-1);
  }
  .sb-name { font-family: 'Sora', sans-serif; font-size: var(--fs-sm); font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 142px; }
  .sb-role { font-size: var(--fs-xs); color: rgba(255,255,255,.4); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 142px; }

  /* Nav */
  .sb-nav { flex: 1; padding: .5rem .72rem; overflow-y: auto; position: relative; z-index: 1; }
  .sb-nav::-webkit-scrollbar { display: none; }

  .nav-label {
    font-size: var(--fs-2xs); font-weight: 700; text-transform: uppercase; letter-spacing: .12em;
    color: var(--sb-label); padding: var(--sp-4) .45rem var(--sp-2);
    font-family: 'DM Sans', sans-serif;
  }

  .sb-link {
    display: flex; align-items: center; gap: 10px;
    padding: .5rem .7rem;
    border-radius: var(--r-sm);
    color: var(--sb-text);
    font-size: var(--fs-md); font-weight: 500;
    cursor: pointer; transition: background .16s var(--ease), color .16s var(--ease);
    border: none; background: transparent; width: 100%;
    text-decoration: none; text-align: left;
    font-family: 'DM Sans', sans-serif;
    position: relative;
    margin-bottom: 1px;
  }
  .sb-link i { font-size: var(--fs-lg); width: 18px; flex-shrink: 0; opacity: .75; }
  /* The icon used to slide 1px right on hover. A nav row is not a thing that
     needs to acknowledge the pointer twice. */
  .sb-link:hover { background: var(--sb-link-hover); color: var(--sb-text-hi); }
  .sb-link:hover i { opacity: 1; }
  .sb-link:focus-visible { outline: 2px solid var(--brand); outline-offset: -2px; }
  /* The active row is the brand block itself — no extra accent rail needed
     once the sidebar behind it is flat. */
  .sb-link.active {
    background: var(--sb-link-active);
    color: #fff; font-weight: 600;
  }
  .sb-link.active i { opacity: 1; }

  .sb-badge {
    margin-left: auto; font-size: var(--fs-2xs); font-weight: 700;
    background: var(--warn); color: #fff;
    border-radius: 20px; padding: 0 6px; min-width: 18px; text-align: center;
    line-height: 1.5;
  }
  /* Static. It pulsed on a 2s loop and glowed — an unread marker only has to
     be noticed once, and a looping animation in the corner of the eye is
     harder to ignore than to read. */
  .sb-dot {
    margin-left: auto; width: 7px; height: 7px; border-radius: 50%;
    background: var(--warn); flex-shrink: 0;
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
    display: flex; align-items: center; gap: var(--sp-3);
    padding: 0 var(--sp-6);
    flex-shrink: 0; z-index: 50;
    transition: background .3s, border-color .3s;
  }

  .tb-page-info .tb-title {
    font-family: 'Sora', sans-serif; font-size: var(--fs-md); font-weight: 600;
    color: var(--text); line-height: 1.2; letter-spacing: -.015em;
  }
  .tb-page-info .tb-breadcrumb {
    font-size: var(--fs-xs); color: var(--text-3); margin-top: 1px;
    display: flex; align-items: center; gap: 3px;
  }

  .tb-divider { width: 1px; height: 20px; background: var(--border); flex-shrink: 0; }

  /* No chip around the time — it is a readout, not a control. */
  .tb-clock {
    font-family: 'DM Sans', sans-serif; font-size: var(--fs-sm); font-weight: 500;
    color: var(--text-2); white-space: nowrap;
    font-variant-numeric: tabular-nums;
    transition: color .3s;
  }

  .icon-btn {
    width: 34px; height: 34px; border-radius: var(--r-sm);
    background: transparent; border: 1px solid transparent;
    display: grid; place-items: center;
    color: var(--text-2); cursor: pointer; transition: var(--t);
    position: relative; flex-shrink: 0;
  }
  .icon-btn:hover { background: var(--bg-2); color: var(--text); }
  .icon-btn:focus-visible { outline: 2px solid var(--brand); outline-offset: 1px; }
  .icon-btn .n-dot {
    position: absolute; top: 6px; right: 6px;
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--warn); border: 1.5px solid var(--card);
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
    font-family: 'Sora', sans-serif; font-size:var(--fs-xs); font-weight: 800;
    color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(37,99,235,.3);
  }
  .tb-uname { font-size: .74rem; font-weight: 700; color: var(--text); line-height: 1.1; }
  .tb-urole { font-size:var(--fs-2xs); color: var(--text-3); }

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
  .ph { margin-bottom: var(--sp-5); display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: var(--sp-3); }
  .ph-title { font-family: 'Sora', sans-serif; font-weight: 700; font-size: var(--fs-xl); color: var(--text); margin: 0; letter-spacing: -.025em; text-wrap: balance; }
  .ph-sub   { font-size: var(--fs-md); color: var(--text-2); margin: var(--sp-1) 0 0; max-width: 62ch; }

  /* ══════════════════════════════════════════════
     CARDS
  ══════════════════════════════════════════════ */
  /* Border only — no resting shadow. Every card having one meant nothing on the
     page could rise above anything else. */
  .card {
    background: var(--card);
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: background .3s, border-color .3s;
  }
  .card-hd {
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-2);
    display: flex; align-items: center; justify-content: space-between; gap: var(--sp-3);
    transition: border-color .3s;
  }
  .card-title {
    font-family: 'Sora', sans-serif; font-size: var(--fs-lg); font-weight: 600;
    color: var(--text); display: flex; align-items: center; gap: var(--sp-2); letter-spacing: -.015em;
  }
  .ct-icon {
    width: 26px; height: 26px; border-radius: var(--r-sm);
    display: grid; place-items: center; font-size: var(--fs-sm); flex-shrink: 0;
  }
  .card-body { padding: var(--sp-5); }

  /* ══════════════════════════════════════════════
     KPI CARDS
  ══════════════════════════════════════════════ */
  /* A statistic is a number and its name. This card previously carried a
     gradient accent bar across its top edge, a blurred radial blob bleeding out
     of the bottom-right corner, a drop shadow, and a 2px lift on hover — four
     decorations around one figure, and the lift implied it could be clicked,
     which it cannot. What is left is the number, its label, and one quiet icon
     carrying the only colour. */
  .kpi {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: var(--sp-4);
    position: relative;
    transition: border-color .16s var(--ease);
    height: 100%;
  }
  .kpi:hover { border-color: var(--brand-mid); }

  .kpi-icon {
    width: 32px; height: 32px; border-radius: var(--r-sm);
    display: grid; place-items: center; font-size: var(--fs-md);
    background: var(--kpi-bg, rgba(37,99,235,.1));
    color: var(--kpi-c, var(--brand)); flex-shrink: 0;
  }
  .kpi-header  { display: flex; align-items: center; justify-content: space-between; gap: var(--sp-2); }
  .kpi-period  { font-size: var(--fs-2xs); color: var(--text-3); font-weight: 500; }
  .kpi-val     {
    font-family: 'Sora', sans-serif; font-size: var(--fs-2xl); font-weight: 700;
    line-height: 1.1; color: var(--text); margin-top: var(--sp-3);
    letter-spacing: -.03em; font-variant-numeric: tabular-nums;
  }
  .kpi-label   { font-size: var(--fs-sm); font-weight: 500; color: var(--text-2); margin-top: var(--sp-1); }

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
    font-size:var(--fs-2xs); font-weight: 800; text-transform: uppercase;
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
    font-size:var(--fs-2xs); margin-top: 3px; font-weight: 600;
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
    font-size:var(--fs-2xs);
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
    font-size:var(--fs-2xs); font-weight: 600; color: var(--text-2);
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
    font-size:var(--fs-2xs); font-weight: 800; text-transform: uppercase; letter-spacing: .6px;
    color: var(--text-3); margin-bottom: .45rem; display: block;
  }

  /* ══════════════════════════════════════════════
     DATA TABLE
  ══════════════════════════════════════════════ */
  .data-table { width: 100%; border-collapse: collapse; font-size: var(--fs-md); }
  .data-table thead th {
    background: var(--th-bg);
    color: var(--text-2);
    font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: var(--fs-2xs);
    text-transform: uppercase; letter-spacing: .08em;
    border-bottom: 1px solid var(--border);
    padding: var(--sp-2) var(--sp-4); white-space: nowrap;
    position: sticky; top: 0; z-index: 2;
    transition: background .3s;
  }
  .data-table tbody td {
    border-bottom: 1px solid var(--border-2);
    vertical-align: middle;
    padding: var(--sp-3) var(--sp-4); color: var(--text);
    transition: background .1s;
  }
  .data-table tbody tr:last-child td { border-bottom: none; }
  /* Zebra striping removed. With a hairline under every row the stripes were a
     second, competing way of saying the same thing. */
  .data-table tbody tr:hover td { background: var(--tr-hover); }
  /* Figures line up in their columns. */
  .data-table td.num, .data-table th.num { text-align: right; font-variant-numeric: tabular-nums; }

  /* ══════════════════════════════════════════════
     PAYSLIP LIST ITEMS
  ══════════════════════════════════════════════ */
  .ps-item {
    display: flex; align-items: center; gap: var(--sp-4);
    background: var(--card); border: 1px solid var(--border);
    border-radius: var(--r-md); padding: var(--sp-4) var(--sp-5);
    transition: border-color .16s var(--ease); margin-bottom: var(--sp-2);
  }
  /* The row used to slide 2px sideways and gain a shadow on hover. It is a list
     item with buttons in it, not a draggable card. */
  .ps-item:hover { border-color: var(--brand-mid); }
  .ps-icon {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    background: var(--brand-light); color: var(--brand);
    display: grid; place-items: center; font-size: var(--fs-lg); flex-shrink: 0;
  }

  /* ══════════════════════════════════════════════
     BADGES
  ══════════════════════════════════════════════ */
  /* Sentence case, not uppercase. A status is a word you read, and shouting
     "PRESENT" at someone about their own attendance is not more informative. */
  .badge {
    display: inline-flex; align-items: center; gap: var(--sp-1);
    border-radius: var(--r-sm); padding: .15rem .5rem;
    font-size: var(--fs-xs); font-weight: 600;
    text-transform: capitalize; white-space: nowrap;
    font-family: 'DM Sans', sans-serif; line-height: 1.5;
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
  .f-error { font-size:var(--fs-xs); font-weight: 600; color: var(--danger); margin-top: .3rem; }
  .f-input.is-invalid { border-color: var(--danger) !important; }
  .pw-field { position: relative; }
  .pw-field .f-input { padding-right: 2.3rem; }
  .pw-toggle {
    position: absolute; top: 50%; right: .55rem; transform: translateY(-50%);
    background: none; border: none; color: var(--text-3); cursor: pointer;
    padding: 4px; display: grid; place-items: center; font-size: .82rem;
  }
  .pw-toggle:hover { color: var(--brand); }
  .f-input {
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm); padding: .58rem .9rem;
    font-size: .84rem; font-family: 'DM Sans', sans-serif;
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
  /* Flat fill, not a gradient with a glow. A solid button reads as a control;
     a glowing one reads as an advert. */
  /* Buttons set in the body face, not the display face. Sora is a display
     family — at 12px, bold and letter-spaced, it made every control shout. */
  .btn-primary {
    background: var(--brand);
    color: #fff; border: none; border-radius: var(--r-sm); padding: .5rem 1rem;
    font-family: 'DM Sans', sans-serif; font-size: var(--fs-md); font-weight: 600;
    cursor: pointer; transition: var(--t); display: inline-flex; align-items: center; gap: var(--sp-2);
    text-decoration: none; line-height: 1.4;
  }
  .btn-primary:hover { background: var(--brand-dark); color: #fff; }
  .btn-primary:active { transform: translateY(.5px); }
  .btn-primary.btn-sm { padding: .35rem .75rem; font-size: var(--fs-sm); }

  .btn-outline {
    background: var(--card); color: var(--text); border: 1px solid var(--border);
    border-radius: var(--r-sm); padding: .5rem 1rem;
    font-family: 'DM Sans', sans-serif; font-size: var(--fs-md); font-weight: 500;
    cursor: pointer; transition: var(--t); display: inline-flex; align-items: center; gap: var(--sp-2);
    text-decoration: none; line-height: 1.4;
  }
  .btn-outline:hover { background: var(--bg-2); color: var(--text); border-color: var(--brand-mid); }
  .btn-outline.btn-sm { padding: .35rem .75rem; font-size: var(--fs-sm); }

  .btn-primary:focus-visible,
  .btn-outline:focus-visible,
  .btn-ghost:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }

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
  .info-label { font-size:var(--fs-2xs); font-weight: 700; color: var(--text-3); text-transform: uppercase; letter-spacing: .4px; width: 84px; flex-shrink: 0; }
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
     WELCOME HEADER
     Deep slate, matching the sidebar and the payslip document header, so the
     three dark surfaces in this app read as one family instead of three.
  ══════════════════════════════════════════════ */
  /* Page heading, not a hero panel.
     This was a dark slab with a 135° two-stop gradient sitting above the
     content — the heaviest element on a page whose job is to show four numbers
     and a calendar. A greeting does not need a background; it needs to be the
     first thing you read and then get out of the way. The two facts on the
     right sit on the page too, separated by a rule rather than a container. */
  .welcome-card {
    display: flex;
    align-items: flex-end;
    gap: var(--sp-6);
    flex-wrap: wrap;
    justify-content: space-between;
    padding: 0 0 var(--sp-5);
    margin-bottom: var(--sp-5);
    border-bottom: 1px solid var(--border);
  }

  .welcome-main { flex: 1 1 320px; min-width: 0; }

  .welcome-eyebrow {
    font-size: var(--fs-2xs);
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--text-3);
    margin-bottom: var(--sp-2);
  }

  .welcome-title {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: var(--fs-xl);
    letter-spacing: -.025em;
    color: var(--text);
    margin: 0 0 var(--sp-1);
    text-wrap: balance;
  }

  .welcome-sub {
    font-size: var(--fs-md);
    color: var(--text-2);
    margin: 0;
    max-width: 58ch;
  }

  .welcome-meta {
    display: flex;
    gap: var(--sp-8);
    flex-shrink: 0;
  }

  .welcome-meta-item { display: flex; flex-direction: column; gap: 2px; }

  .welcome-meta-label {
    font-size: var(--fs-2xs);
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--text-3);
  }

  .welcome-meta-value {
    font-family: 'Sora', sans-serif;
    font-weight: 600;
    font-size: var(--fs-md);
    color: var(--text);
  }

  @media (max-width: 640px) {
    .welcome-card { gap: var(--sp-4); padding-bottom: var(--sp-4); }
    .welcome-meta { gap: var(--sp-6); }
  }

  /* ══════════════════════════════════════════════
     UNVERIFIED EMAIL BANNER (Payslips tab only)
  ══════════════════════════════════════════════ */
  .verify-banner {
    display: flex;
    align-items: center;
    gap: .9rem;
    padding: .85rem 1.15rem;
    margin-bottom: .8rem;
    border-radius: var(--r-md);
    border: 1px solid rgba(217,119,6,.28);
    background: rgba(217,119,6,.07);
  }

  .verify-banner-icon {
    width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
    display: grid; place-items: center; font-size: .95rem;
    background: rgba(217,119,6,.14); color: #b45309;
    border: 1px solid rgba(217,119,6,.24);
  }
  [data-theme="dark"] .verify-banner-icon { color: #fbbf24; }

  .verify-banner-copy { flex: 1; min-width: 0; }

  .verify-banner-title {
    font-family: 'Sora', sans-serif; font-weight: 700;
    font-size: .8rem; color: var(--text); letter-spacing: -.01em;
  }

  .verify-banner-sub {
    font-size: .7rem; color: var(--text-2); margin-top: 2px; line-height: 1.5;
  }
  .verify-banner-sub strong { color: var(--text); font-weight: 600; }

  .verify-banner-action { flex-shrink: 0; }

  @media (max-width: 560px) {
    .verify-banner { flex-wrap: wrap; }
    .verify-banner-action { width: 100%; }
    .verify-banner-action .btn-outline { width: 100%; justify-content: center; }
  }

  /* ══════════════════════════════════════════════
     PAYSLIP LOCK BAR
     Sits above the payslip list and states, in plain words, whether the
     section is sealed. Amber while locked, teal once open — deliberately
     not the brand blue, so "unlocked" never reads as just another button.
  ══════════════════════════════════════════════ */
  .ps-lockbar {
    display: flex; align-items: center; gap: .9rem;
    padding: .85rem 1.15rem; margin-bottom: 1rem;
    border-radius: var(--r-md);
    border: 1px solid rgba(245,158,11,.28);
    background: rgba(245,158,11,.07);
    transition: var(--t);
  }
  .ps-lockbar.is-open {
    border-color: rgba(13,148,136,.3);
    background: rgba(13,148,136,.07);
  }

  .ps-lockbar-icon {
    width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
    display: grid; place-items: center; font-size: .95rem;
    background: rgba(245,158,11,.14); color: #b45309;
    border: 1px solid rgba(245,158,11,.24);
  }
  .ps-lockbar.is-open .ps-lockbar-icon {
    background: rgba(13,148,136,.14); color: #0f766e;
    border-color: rgba(13,148,136,.26);
  }
  [data-theme="dark"] .ps-lockbar-icon         { color: #fbbf24; }
  [data-theme="dark"] .ps-lockbar.is-open .ps-lockbar-icon { color: #5eead4; }

  .ps-lockbar-copy  { flex: 1; min-width: 0; }
  .ps-lockbar-title {
    font-family: 'Sora', sans-serif; font-weight: 700;
    font-size: .8rem; color: var(--text); letter-spacing: -.01em;
  }
  .ps-lockbar-sub {
    font-size: .7rem; color: var(--text-2); margin-top: 2px; line-height: 1.5;
  }
  .ps-lockbar-sub strong { color: var(--text); font-weight: 600; }
  .ps-lockbar-action { flex-shrink: 0; }

  @media (max-width: 560px) {
    .ps-lockbar { flex-wrap: wrap; }
    .ps-lockbar-action { width: 100%; justify-content: center; }
  }

  /* ══════════════════════════════════════════════
     PAYSLIP UNLOCK MODAL
  ══════════════════════════════════════════════ */
  .psu-card {
    border-radius: var(--r-xl) !important;
    border: 1px solid var(--border) !important;
    background: var(--card) !important;
    overflow: hidden;
  }

  .psu-head {
    padding: 1.9rem 1.9rem 1.2rem;
    text-align: center;
    border-bottom: 1px solid var(--border-2);
  }
  .psu-shield {
    width: 52px; height: 52px; border-radius: 15px; margin: 0 auto .9rem;
    display: grid; place-items: center; font-size: 1.35rem;
    background: rgba(13,148,136,.12); color: #0f766e;
    border: 1px solid rgba(13,148,136,.22);
  }
  [data-theme="dark"] .psu-shield { color: #5eead4; }

  .psu-title {
    font-family: 'Sora', sans-serif; font-weight: 800;
    font-size: 1.05rem; color: var(--text); margin: 0 0 .35rem;
    letter-spacing: -.02em;
  }
  .psu-lede {
    font-size: .78rem; color: var(--text-2); margin: 0;
    line-height: 1.6; max-width: 34ch; margin-inline: auto;
  }
  .psu-lede strong { color: var(--text); font-weight: 600; }

  .psu-body { padding: 1.4rem 1.9rem 1.6rem; }

  .psu-alert {
    border-radius: var(--r-sm); padding: .6rem .8rem; margin-bottom: 1rem;
    font-size: .74rem; line-height: 1.5; text-align: center;
  }
  .psu-alert-error {
    background: rgba(239,68,68,.1); color: #b91c1c;
    border: 1px solid rgba(239,68,68,.2);
  }
  [data-theme="dark"] .psu-alert-error { color: #fca5a5; }

  /* Six separate boxes rather than one field: it shows the expected length
     without a placeholder, and makes a mistyped digit obvious. */
  .psu-otp {
    display: grid; grid-template-columns: repeat(6, 1fr);
    gap: .45rem; margin-bottom: 1rem;
  }
  .psu-digit {
    aspect-ratio: 1 / 1.15; width: 100%;
    border-radius: var(--r-sm);
    border: 1.5px solid var(--border);
    background: var(--input-bg); color: var(--text);
    font-family: 'Sora', sans-serif; font-size: 1.15rem; font-weight: 700;
    text-align: center; transition: var(--t);
    font-variant-numeric: tabular-nums;
  }
  .psu-digit.is-filled { border-color: var(--brand); background: var(--input-focus); }
  .psu-digit:focus {
    outline: none; border-color: var(--brand);
    background: var(--input-focus);
    box-shadow: 0 0 0 3px rgba(37,99,235,.14);
  }

  .psu-meta {
    display: flex; align-items: center; justify-content: space-between;
    gap: .6rem; margin-bottom: 1.1rem;
    font-size: .7rem; color: var(--text-3);
  }
  .psu-resend {
    background: none; border: none; padding: 0;
    font-family: 'DM Sans', sans-serif; font-size: .72rem; font-weight: 600;
    color: var(--brand); cursor: pointer; transition: var(--t);
  }
  .psu-resend:disabled { color: var(--text-3); cursor: default; }
  .psu-resend:not(:disabled):hover { text-decoration: underline; }

  .psu-submit { width: 100%; justify-content: center; }
  .psu-submit:disabled { opacity: .5; cursor: not-allowed; }

  .psu-cancel {
    display: block; width: 100%; margin-top: .7rem;
    background: none; border: none; padding: .4rem;
    font-family: 'DM Sans', sans-serif; font-size: .74rem; font-weight: 500;
    color: var(--text-3); cursor: pointer;
  }
  .psu-cancel:hover { color: var(--text-2); }

  .psu-digit:focus-visible,
  .psu-resend:focus-visible,
  .psu-cancel:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }

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
    background: linear-gradient(135deg, #101725 0%, #1c2b4a 100%);
    padding: 1.6rem 1.8rem;
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
    .cal-num { font-size:var(--fs-xs); }
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
      /* $displayName is resolved by the controller: the master-list name if the
         account matches a row there, otherwise users.name, which is always set
         at registration. The portal used to read $employee->name and print the
         literal "Employee" whenever no master-list row matched — which included
         every account whose master-list email differed in case or had a stray
         space, so real people were addressed as "Employee". */
      $displayName = $displayName ?? trim($employee->name ?? '') ?: ($user->name ?? 'Employee');

      $initials = collect(preg_split('/\s+/', trim($displayName), -1, PREG_SPLIT_NO_EMPTY))
        ->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))
        ->take(2)->implode('') ?: 'E';

      // Hoisted derived values — computed once, up top, so nothing below depends on
      // markup order. (Previously some of these were computed inline inside whichever
      // component happened to render first, which broke silently if blocks got reordered.)
      $readAnnouncementIds = $readAnnouncementIds ?? [];
      $unread = isset($announcements) ? $announcements->whereNotIn('id', $readAnnouncementIds)->count() : 0;
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
          <div class="sb-name" title="{{ $displayName }}">{{ $displayName }}</div>
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
        @if($unread > 0) <span class="sb-dot" id="annSbDot"></span> @endif
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
          <i class="bi bi-house-fill" style="font-size:var(--fs-2xs);"></i>
          <span>MCC Portal</span>
          <i class="bi bi-chevron-right" style="font-size:var(--fs-2xs);"></i>
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
          @if(isset($unread) && $unread > 0) <span class="n-dot" id="annNDot"></span> @endif
        </div>

        <div class="tb-user" id="profileBtn">
          <div class="tb-avatar">{{ $initials }}</div>
          <div class="d-none d-sm-block">
            {{-- The email-verified tick used to sit here. Verification state is
                 only consequential for payslip delivery, so it is stated once in
                 the Payslips tab and nowhere else. --}}
            {{-- First name only: the top bar is a narrow strip. The full name
                 is on the title attribute and in the sidebar. --}}
            <div class="tb-uname" title="{{ $displayName }}">
              {{ Str::words($displayName, 1, '') }}
            </div>
            <div class="tb-urole">{{ $employee->position ?? 'Employee' }}</div>
          </div>
          <i class="bi bi-chevron-down d-none d-sm-block" style="font-size:var(--fs-2xs);color:var(--text-3);margin-left:2px;"></i>
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

      {{-- The "verify your email" notice used to live here, outside every tab,
           so it followed the employee around the whole portal. It only has
           consequences for payslip delivery, so it now sits in the Payslips
           tab where it is actionable. --}}

      <!-- ════════════════════════════════
           PANEL: OVERVIEW
      ════════════════════════════════ -->
      <div class="tab-panel active" id="panel-overview">

        @php
          $latestPayslip = isset($payslips) && $payslips->count() > 0 ? $payslips->first() : null;
          // `total_honorarium` is the GROSS figure, not the net one — the
          // payslip email subtracts deductions from it to reach take-home. This
          // KPI showed it under a "Latest Net Pay" label, overstating pay by the
          // whole of the employee's deductions. takeHome() prefers the recorded
          // net and falls back to the honorarium only for payslips issued before
          // the breakdown was kept.
          $netPayVal = $latestPayslip
            ? '₱' . number_format($latestPayslip->takeHome(), 2)
            : '—';
        @endphp

        {{-- Welcome header. Was a three-stop gradient with two blurred glow
             orbs, a backdrop-filter panel, a time-of-day badge and an "Active
             Session" pill — a lot of chrome above the numbers people came for.
             Flattened to match the rest of the restyled portal. --}}
        <div class="welcome-card">
          <div class="welcome-main">
            <div class="welcome-eyebrow">Employee portal</div>
            <h2 class="welcome-title">
              Welcome back, {{ $displayName }}
            </h2>
            <p class="welcome-sub">
              Your attendance log, timesheets and e-payslips, all in one place.
            </p>
          </div>

          <div class="welcome-meta">
            <div class="welcome-meta-item">
              <span class="welcome-meta-label">Employee ID</span>
              <span class="welcome-meta-value">{{ $employee->employee_id ?? 'EMP-'.$user->id }}</span>
            </div>
            <div class="welcome-meta-item">
              <span class="welcome-meta-label">Position</span>
              <span class="welcome-meta-value">{{ $employee->position ?? 'Faculty / Staff' }}</span>
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
              <div class="kpi-header">
                <div class="kpi-icon"><i class="bi bi-{{ $k['icon'] }}"></i></div>
                <span class="kpi-period">{{ $k['sub'] }}</span>
              </div>
              {{-- The figure reads in the page's text colour. Four cards each
                   shouting a different hue made none of them the important one;
                   the icon still carries the category colour. --}}
              <div class="kpi-val">{{ $k['val'] }}</div>
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
                        <div style="font-size:var(--fs-2xs);color:var(--text-3);margin-top:2px;">
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
                        <div style="font-size:var(--fs-2xs);color:var(--text-3);margin-top:2px;">{{ now()->format('l, F j') }}</div>
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
                      <div style="font-size:var(--fs-2xs);opacity:.5;text-transform:uppercase;letter-spacing:.6px;font-weight:700;position:relative;z-index:1;">Pay Period</div>
                      <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.82rem;margin:.12rem 0 .55rem;position:relative;z-index:1;">
                        {{ $lp->pay_period ?? ($lp->sent_at?->format('F Y') ?? '—') }}
                      </div>
                      <div style="font-size:var(--fs-2xs);opacity:.5;text-transform:uppercase;letter-spacing:.6px;font-weight:700;position:relative;z-index:1;">Net Pay</div>
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
                      <a href="{{ route('employee.payslip.download', $lp->id) }}" class="btn-outline btn-sm" style="flex-shrink:0;" title="Download" onclick="return downloadPayslip(event, this.href);">
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
                  <div style="font-size:var(--fs-2xs);color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.3px;">{{ $s['label'] }}</div>
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
                  <td style="color:var(--text-3);font-size:var(--fs-xs);">{{ $i + 1 }}</td>
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

        {{-- Unverified email. Scoped to this tab: it is the payslip flow that
             depends on the address being reachable, and repeating it on every
             screen only taught people to ignore it. --}}
        @if(is_null(Auth::user()->email_verified_at))
          <div class="verify-banner">
            <div class="verify-banner-icon"><i class="bi bi-envelope-exclamation-fill"></i></div>
            <div class="verify-banner-copy">
              <div class="verify-banner-title">Email address not verified</div>
              <div class="verify-banner-sub">
                Payslips and access codes are sent to <strong>{{ Auth::user()->email }}</strong>.
                Verify it to be sure they reach you.
              </div>
            </div>
            <form method="POST" action="{{ route('verification.resend') }}" class="m-0 verify-banner-action">
              @csrf
              <button type="submit" class="btn-outline btn-sm">
                <i class="bi bi-send-fill"></i> Resend link
              </button>
            </form>
          </div>
        @endif

        {{-- Step-up verification state. Payslips carry net pay and government
             deduction figures, so opening one needs a code sent to the address
             on the account — not just a logged-in session. --}}
        <div class="ps-lockbar {{ ($payslipUnlocked ?? false) ? 'is-open' : '' }}" id="psLockBar">
          <div class="ps-lockbar-icon">
            <i class="bi {{ ($payslipUnlocked ?? false) ? 'bi-shield-check' : 'bi-shield-lock' }}" id="psLockIcon"></i>
          </div>
          <div class="ps-lockbar-copy">
            <div class="ps-lockbar-title" id="psLockTitle">
              {{ ($payslipUnlocked ?? false) ? 'Payslips unlocked' : 'Payslips are protected' }}
            </div>
            <div class="ps-lockbar-sub" id="psLockSub">
              @if($payslipUnlocked ?? false)
                Re-locks automatically in <span id="psLockCountdown">&mdash;</span>.
              @else
                Opening a payslip sends a 6-digit code to <strong>{{ $maskedEmail ?? 'your email' }}</strong>.
              @endif
            </div>
          </div>
          <button type="button" class="btn-outline btn-sm ps-lockbar-action" id="psLockAction">
            <i class="bi {{ ($payslipUnlocked ?? false) ? 'bi-lock' : 'bi-unlock' }}" id="psLockActionIcon"></i>
            <span id="psLockActionText">{{ ($payslipUnlocked ?? false) ? 'Lock now' : 'Unlock' }}</span>
          </button>
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
            <div style="font-size:var(--fs-xs);color:var(--text-3);margin-top:3px;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
              {{-- Was `total_honorarium` labelled "Net Pay". That column is the
                   GROSS figure — the payslip email subtracts deductions from it
                   to reach net — so this row overstated take-home pay by the
                   whole of everyone's deductions. --}}
              <span>Net Pay: <strong style="color:var(--brand);font-family:'Sora',sans-serif;">₱{{ number_format($ps->takeHome(), 2) }}</strong></span>
              @if($ps->gross_pay !== null && (float) $ps->total_deductions > 0)
                <span style="width:3px;height:3px;border-radius:50%;background:var(--text-3);display:inline-block;"></span>
                <span>Gross ₱{{ number_format($ps->gross_pay, 2) }} · less ₱{{ number_format($ps->total_deductions, 2) }}</span>
              @endif
              <span style="width:3px;height:3px;border-radius:50%;background:var(--text-3);display:inline-block;"></span>
              <span>Issued: {{ $ps->sent_at?->format('M d, Y') ?? '—' }}</span>
              @if(!($ps->viewed ?? true))
                <span style="background:rgba(245,158,11,.18);color:#b45309;font-size:var(--fs-2xs);font-weight:800;border-radius:4px;padding:1px 7px;letter-spacing:.3px;text-transform:uppercase;">New</span>
              @endif
            </div>
          </div>
          <div class="d-flex gap-2 align-items-center flex-shrink-0">
            <button
              onclick="viewPayslip('{{ route('employee.payslip.json', $ps->id) }}', '{{ route('employee.payslip.download', $ps->id) }}')"
              class="btn-primary btn-sm">
              <i class="bi bi-eye"></i> View
            </button>
            {{-- The itemised wage. Gated by the same unlock as the rest of the
                 payslip contents, so it goes through openLiquidation() rather
                 than being a bare link. --}}
            <button
              onclick="openLiquidation('{{ route('employee.payslip.liquidation', $ps->id) }}')"
              class="btn-outline btn-sm" title="See how this pay was computed">
              <i class="bi bi-list-columns-reverse"></i> Breakdown
            </button>
            <a href="{{ route('employee.payslip.download', $ps->id) }}" class="btn-outline btn-sm" title="Download PDF" onclick="return downloadPayslip(event, this.href);">
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
          <div class="d-flex align-items-center gap-2">
            @if($unread > 0)
              <button class="btn-outline btn-sm" id="markAllReadBtn" onclick="markAllAnnouncementsRead()">
                <i class="bi bi-check2-all"></i> Mark all as read
              </button>
            @endif
            <select class="f-input" style="width:auto;font-size:.78rem;" id="annFilter" onchange="filterAnn()">
              <option value="all">All Types</option>
              <option value="general">General</option>
              <option value="payroll">Payroll</option>
              <option value="holiday">Holiday</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>

        <div id="annContainer">
          @forelse($announcements ?? [] as $ann)
          @php
            $ac = ['general'=>'#2563eb','payroll'=>'#10b981','holiday'=>'#f59e0b','urgent'=>'#ef4444'][$ann->type ?? 'general'] ?? '#2563eb';
            $ai = ['general'=>'megaphone','payroll'=>'cash-coin','holiday'=>'calendar-heart','urgent'=>'exclamation-triangle'][$ann->type ?? 'general'] ?? 'megaphone';
            $at = $ann->type ?? 'general';
            $annIsUnread = !in_array($ann->id, $readAnnouncementIds ?? []);
          @endphp
          <div class="ann-card" style="--ann-c:{{ $ac }};{{ $annIsUnread ? 'cursor:pointer;' : '' }}" data-type="{{ $at }}" data-ann-id="{{ $ann->id }}" data-read="{{ $annIsUnread ? '0' : '1' }}" @if($annIsUnread) onclick="markAnnouncementRead({{ $ann->id }}, this)" @endif>
            <div class="d-flex align-items-start gap-3 mb-2">
              <div style="width:38px;height:38px;border-radius:10px;background:{{ $ac }}18;display:grid;place-items:center;flex-shrink:0;">
                <i class="bi bi-{{ $ai }}" style="color:{{ $ac }};font-size:.9rem;"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                  <span style="font-family:'Sora',sans-serif;font-weight:800;font-size:.87rem;color:var(--text);">{{ $ann->title }}</span>
                  @if($annIsUnread)
                    <span class="ann-unread-tag" style="background:var(--warn);color:#fff;font-size:var(--fs-2xs);font-weight:800;border-radius:4px;padding:2px 7px;letter-spacing:.3px;">UNREAD</span>
                  @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span style="background:{{ $ac }}15;color:{{ $ac }};font-size:var(--fs-2xs);font-weight:800;border-radius:4px;padding:2px 8px;text-transform:uppercase;letter-spacing:.4px;">{{ ucfirst($at) }}</span>
                  <span style="font-size:var(--fs-2xs);color:var(--text-3);">{{ $ann->created_at?->format('M d, Y · H:i') ?? '—' }}</span>
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
                <div style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:800;color:#fff;position:relative;z-index:1;">{{ $displayName }}</div>
                <div style="font-size:.7rem;opacity:.65;color:#fff;margin-top:3px;position:relative;z-index:1;">{{ $employee->position ?? 'Employee' }}</div>
                <div style="margin-top:.5rem;position:relative;z-index:1;">
                  <span style="background:rgba(255,255,255,.14);border-radius:20px;padding:.18rem .9rem;font-size:var(--fs-2xs);color:rgba(255,255,255,.8);font-weight:700;">
                    ID: {{ $employee->employee_id ?? $employee?->id ?? 'N/A' }}
                  </span>
                </div>
              </div>
              <div class="card-body">
                <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.55rem;">Contact</div>
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

                <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin:.9rem 0 .55rem;">Employment</div>
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
                  Update your name, email, or password from the panel on the right. Contact HR to change your position, department, or salary.
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <div class="card mb-3">
              <div class="card-hd">
                <div class="card-title">
                  <div class="ct-icon" style="background:rgba(37,99,235,.1);color:var(--brand);">
                    <i class="bi bi-person-fill"></i>
                  </div>
                  Employee Details
                </div>
                <span style="font-size:var(--fs-xs);color:var(--text-3);">Name &amp; email editable · rest managed by HR</span>
              </div>
              <div class="card-body">
                <form action="{{ route('employee.profile.update') }}" method="POST">
                  @csrf
                  <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.7rem;">Personal Information</div>
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label class="f-label">Full Name</label>
                      {{-- Seeded from the account, not the master-list row.
                           portalUpdateProfile() writes users.name / users.email,
                           so those are the values being edited; reading them off
                           $employee left both boxes empty for anyone without a
                           master-list match, and would have written the master
                           list's spelling of the address onto the account. --}}
                      <input type="text" name="name" class="f-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? $displayName) }}" required>
                      @error('name') <div class="f-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                      <label class="f-label">Email Address</label>
                      <input type="email" name="email" class="f-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
                      @error('email') <div class="f-error">{{ $message }}</div> @enderror
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
                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn-primary btn-sm">
                      <i class="bi bi-check2"></i> Save Changes
                    </button>
                  </div>
                </form>

                <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin:1.2rem 0 .7rem;padding-top:1rem;border-top:1px solid var(--border-2);">Employment Details</div>
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

            <!-- Change Password -->
            <div class="card">
              <div class="card-hd">
                <div class="card-title">
                  <div class="ct-icon" style="background:rgba(124,58,237,.1);color:#7c3aed;">
                    <i class="bi bi-shield-lock-fill"></i>
                  </div>
                  Change Password
                </div>
              </div>
              <div class="card-body">
                <form action="{{ route('employee.profile.update') }}" method="POST" id="pwForm">
                  @csrf
                  {{-- portalUpdateProfile() validates name and email as
                       required on every submit, including this password-only
                       form. These carried $employee->name / $employee->email,
                       which are empty for an account with no master-list row —
                       so changing your password failed validation outright. --}}
                  <input type="hidden" name="name"  value="{{ $user->name  ?? $displayName }}">
                  <input type="hidden" name="email" value="{{ $user->email ?? '' }}">
                  <div class="row g-3 mb-1">
                    <div class="col-md-4">
                      <label class="f-label">Current Password</label>
                      <div class="pw-field">
                        <input type="password" name="current_password" class="f-input @error('current_password') is-invalid @enderror" autocomplete="current-password">
                        <button type="button" class="pw-toggle" onclick="togglePw(this)"><i class="bi bi-eye"></i></button>
                      </div>
                      @error('current_password') <div class="f-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                      <label class="f-label">New Password</label>
                      <div class="pw-field">
                        <input type="password" name="new_password" class="f-input @error('new_password') is-invalid @enderror" autocomplete="new-password" minlength="8">
                        <button type="button" class="pw-toggle" onclick="togglePw(this)"><i class="bi bi-eye"></i></button>
                      </div>
                      @error('new_password') <div class="f-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                      <label class="f-label">Confirm New Password</label>
                      <div class="pw-field">
                        <input type="password" name="new_password_confirmation" class="f-input" autocomplete="new-password" minlength="8">
                        <button type="button" class="pw-toggle" onclick="togglePw(this)"><i class="bi bi-eye"></i></button>
                      </div>
                    </div>
                  </div>
                  <div style="font-size:var(--fs-xs);color:var(--text-3);margin-bottom:1rem;">
                    <i class="bi bi-info-circle"></i> Minimum 8 characters. Leave blank if you don't want to change your password.
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn-primary btn-sm">
                      <i class="bi bi-shield-check"></i> Update Password
                    </button>
                  </div>
                </form>
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
     PAYSLIP UNLOCK MODAL — step-up email verification
════════════════════════════ -->
<div class="modal fade" id="payslipUnlockModal" tabindex="-1" aria-hidden="true" aria-labelledby="psuTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content psu-card">

      <div class="psu-head">
        <div class="psu-shield"><i class="bi bi-shield-lock-fill"></i></div>
        <h2 class="psu-title" id="psuTitle">Verify it&rsquo;s you</h2>
        <p class="psu-lede" id="psuLede">
          We&rsquo;ll email a 6-digit code to <strong>{{ $maskedEmail ?? 'your address' }}</strong>
          before opening your payslip.
        </p>
      </div>

      <div class="psu-body">

        <div class="psu-alert" id="psuAlert" role="alert" hidden></div>

        <div class="psu-otp" id="psuOtp">
          <input class="psu-digit" type="text" inputmode="numeric" autocomplete="one-time-code"
                 maxlength="1" aria-label="Digit 1">
          <input class="psu-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 2">
          <input class="psu-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 3">
          <input class="psu-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 4">
          <input class="psu-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 5">
          <input class="psu-digit" type="text" inputmode="numeric" maxlength="1" aria-label="Digit 6">
        </div>

        <div class="psu-meta">
          <span id="psuExpiry">&nbsp;</span>
          <button type="button" class="psu-resend" id="psuResend" disabled>Resend code</button>
        </div>

        <button type="button" class="btn-primary psu-submit" id="psuSubmit" disabled>
          <i class="bi bi-unlock-fill"></i> <span id="psuSubmitText">Unlock payslip</span>
        </button>

        <button type="button" class="psu-cancel" data-bs-dismiss="modal">Not now</button>
      </div>

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
   PROFILE — PASSWORD FIELD TOGGLE & CONFIRM CHECK
═══════════════════════════════════════════ */
function togglePw(btn) {
  const input = btn.previousElementSibling;
  const icon  = btn.querySelector('i');
  const showing = input.type === 'text';
  input.type = showing ? 'password' : 'text';
  icon.className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
}

document.getElementById('pwForm')?.addEventListener('submit', (e) => {
  const form = e.target;
  const newPw     = form.querySelector('[name="new_password"]').value;
  const confirmPw = form.querySelector('[name="new_password_confirmation"]').value;
  if (newPw && newPw !== confirmPw) {
    e.preventDefault();
    Swal.fire({ icon: 'error', title: 'Passwords Don\'t Match', text: 'New password and confirmation must be identical.', confirmButtonColor: '#2563eb' });
  }
});

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

/* The per-section URLs (/employee/payslips, /employee/profile, …) redirect here
   with ?tab=, so a bookmark or an old link still lands on the right section. */
switchTab(@json($activeTab ?? 'overview'));
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
   ANNOUNCEMENTS — MARK AS READ
═══════════════════════════════════════════ */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function _onAnnouncementsCleared() {
  document.getElementById('annSbDot')?.remove();
  document.getElementById('annNDot')?.remove();
  document.getElementById('markAllReadBtn')?.remove();
}

async function markAnnouncementRead(id, cardEl) {
  if (cardEl?.dataset.read === '1') return; // already read, avoid duplicate calls
  try {
    const res = await fetch(`/employee/announcements/${id}/read`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    if (cardEl) {
      cardEl.dataset.read = '1';
      cardEl.style.cursor = 'default';
      cardEl.removeAttribute('onclick');
      cardEl.querySelector('.ann-unread-tag')?.remove();
    }
    if (data.unread === 0) _onAnnouncementsCleared();
  } catch (err) {
    console.error('Could not mark announcement as read:', err);
  }
}

async function markAllAnnouncementsRead() {
  try {
    const res = await fetch('/employee/announcements/read-all', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    document.querySelectorAll('#annContainer .ann-card').forEach(c => {
      c.dataset.read = '1';
      c.style.cursor = 'default';
      c.removeAttribute('onclick');
      c.querySelector('.ann-unread-tag')?.remove();
    });
    _onAnnouncementsCleared();
  } catch (err) {
    console.error('Could not mark all announcements as read:', err);
  }
}

/* ═══════════════════════════════════════════
   PAYSLIP STEP-UP VERIFICATION

   A payslip shows net pay, government deductions and an address, so an
   authenticated session alone is not enough to open one: the employee has to
   prove they still hold the mailbox the payslip went to. The server is the
   authority here (see App\Support\PayslipGate) — everything below is
   convenience so the employee is not bounced into a raw 423 page.
═══════════════════════════════════════════ */
const PS_LOCK = {
  statusUrl: @json(route('employee.payslip.access.status')),
  sendUrl:   @json(route('employee.payslip.access.send')),
  verifyUrl: @json(route('employee.payslip.access.verify')),
  lockUrl:   @json(route('employee.payslip.access.lock')),
  unlocked:  @json((bool) ($payslipUnlocked ?? false)),
  expiresIn: @json((int) ($payslipUnlockedFor ?? 0)),
  maskedEmail: @json($maskedEmail ?? 'your email'),
  resendIn:  0,
  /* Resolved when the employee finishes (or abandons) the unlock dialog. */
  pending:   null,
};

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

function psPost(url, body) {
  return fetch(url, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf(),
    },
    body: JSON.stringify(body || {}),
  });
}

/* ── Lock bar ─────────────────────────────── */
let _psCountdownTimer = null;

function psRenderLockBar() {
  const bar = document.getElementById('psLockBar');
  if (!bar) return;

  const open = PS_LOCK.unlocked;
  bar.classList.toggle('is-open', open);
  document.getElementById('psLockIcon').className   = open ? 'bi bi-shield-check' : 'bi bi-shield-lock';
  document.getElementById('psLockTitle').textContent = open ? 'Payslips unlocked' : 'Payslips are protected';
  document.getElementById('psLockActionIcon').className = open ? 'bi bi-lock' : 'bi bi-unlock';
  document.getElementById('psLockActionText').textContent = open ? 'Lock now' : 'Unlock';

  const sub = document.getElementById('psLockSub');
  if (open) {
    sub.innerHTML = 'Re-locks automatically in <span id="psLockCountdown">—</span>.';
    psStartCountdown();
  } else {
    clearInterval(_psCountdownTimer);
    const masked = document.createElement('strong');
    masked.textContent = PS_LOCK.maskedEmail;
    sub.textContent = 'Opening a payslip sends a 6-digit code to ';
    sub.appendChild(masked);
    sub.append('.');
  }
}

function psStartCountdown() {
  clearInterval(_psCountdownTimer);
  const tick = () => {
    const el = document.getElementById('psLockCountdown');
    if (PS_LOCK.expiresIn <= 0) {
      clearInterval(_psCountdownTimer);
      PS_LOCK.unlocked = false;
      psRenderLockBar();
      return;
    }
    if (el) {
      const m = Math.floor(PS_LOCK.expiresIn / 60);
      const s = String(PS_LOCK.expiresIn % 60).padStart(2, '0');
      el.textContent = `${m}:${s}`;
    }
    PS_LOCK.expiresIn--;
  };
  tick();
  _psCountdownTimer = setInterval(tick, 1000);
}

/* ── Unlock dialog ────────────────────────── */
let _psuModal = null;
let _psuResendTimer = null;

function psuEl(id) { return document.getElementById(id); }

function psuAlert(message, kind = 'error') {
  const el = psuEl('psuAlert');
  if (!el) return;
  if (!message) { el.hidden = true; el.textContent = ''; return; }
  el.hidden = false;
  el.className = `psu-alert psu-alert-${kind}`;
  el.textContent = message;
}

function psuDigits() { return Array.from(document.querySelectorAll('.psu-digit')); }
function psuCode()   { return psuDigits().map(i => i.value).join(''); }

function psuClear() {
  psuDigits().forEach(i => { i.value = ''; i.classList.remove('is-filled'); });
  psuEl('psuSubmit').disabled = true;
}

function psuSyncSubmit() {
  psuEl('psuSubmit').disabled = psuCode().length !== 6;
}

function psuStartResendTimer(seconds) {
  clearInterval(_psuResendTimer);
  PS_LOCK.resendIn = seconds;
  const btn = psuEl('psuResend');

  const tick = () => {
    if (PS_LOCK.resendIn <= 0) {
      clearInterval(_psuResendTimer);
      btn.disabled = false;
      btn.textContent = 'Resend code';
      return;
    }
    btn.disabled = true;
    btn.textContent = `Resend in ${PS_LOCK.resendIn}s`;
    PS_LOCK.resendIn--;
  };
  tick();
  _psuResendTimer = setInterval(tick, 1000);
}

/**
 * Make sure payslips are open, prompting for a code if they are not.
 * Resolves true once unlocked, false if the employee backs out.
 */
function ensurePayslipUnlocked() {
  if (PS_LOCK.unlocked) return Promise.resolve(true);

  const modalEl = psuEl('payslipUnlockModal');
  if (!_psuModal) {
    _psuModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });

    modalEl.addEventListener('shown.bs.modal', () => psuDigits()[0]?.focus());
    modalEl.addEventListener('hidden.bs.modal', () => {
      clearInterval(_psuResendTimer);
      // Backing out is a "no", not a hang — settle any waiting caller.
      if (PS_LOCK.pending) { PS_LOCK.pending(PS_LOCK.unlocked); PS_LOCK.pending = null; }
    });
  }

  // A second call while the dialog is already up must not strand the first
  // caller's promise — settle it before taking over the slot.
  if (PS_LOCK.pending) { PS_LOCK.pending(false); PS_LOCK.pending = null; }

  psuClear();
  psuAlert('');
  psuEl('psuExpiry').textContent = 'Sending code…';
  _psuModal.show();
  psuSendCode();

  return new Promise(resolve => { PS_LOCK.pending = resolve; });
}

async function psuSendCode() {
  psuAlert('');
  try {
    const res  = await psPost(PS_LOCK.sendUrl);
    const data = await res.json().catch(() => ({}));

    if (res.ok && data.unlocked) {
      // Server says it is already open — nothing to type.
      psuFinish(true);
      return;
    }

    if (!res.ok) {
      psuAlert(data.message || 'Could not send the code. Please try again.');
      psuStartResendTimer(data.resend_in || 15);
      psuEl('psuExpiry').textContent = '';
      return;
    }

    psuEl('psuExpiry').textContent = `Code expires in ${Math.round((data.expires_in || 300) / 60)} minutes`;
    psuStartResendTimer(data.resend_in || 60);
  } catch (e) {
    psuAlert('Network error while sending the code.');
    psuStartResendTimer(15);
  }
}

async function psuSubmitCode() {
  const code = psuCode();
  if (code.length !== 6) return;

  const btn = psuEl('psuSubmit');
  btn.disabled = true;
  psuEl('psuSubmitText').textContent = 'Verifying…';
  psuAlert('');

  try {
    const res  = await psPost(PS_LOCK.verifyUrl, { code });
    const data = await res.json().catch(() => ({}));

    if (res.ok && data.ok) {
      PS_LOCK.expiresIn = data.expires_in || 600;
      psuFinish(true);
      return;
    }

    psuAlert(data.message || 'That code is not correct.');
    psuClear();
    psuDigits()[0]?.focus();
  } catch (e) {
    psuAlert('Network error while verifying the code.');
  } finally {
    psuEl('psuSubmitText').textContent = 'Unlock payslip';
    psuSyncSubmit();
  }
}

function psuFinish(ok) {
  PS_LOCK.unlocked = ok;
  psRenderLockBar();
  _psuModal?.hide();
  if (PS_LOCK.pending) { PS_LOCK.pending(ok); PS_LOCK.pending = null; }
}

/* ── Wiring ───────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const digits = psuDigits();

  digits.forEach((input, i) => {
    input.addEventListener('input', () => {
      input.value = input.value.replace(/\D/g, '').slice(0, 1);
      input.classList.toggle('is-filled', !!input.value);
      if (input.value && i < digits.length - 1) digits[i + 1].focus();
      psuSyncSubmit();
    });

    input.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !input.value && i > 0) digits[i - 1].focus();
      if (e.key === 'Enter') psuSubmitCode();
    });

    // Let a whole pasted code fill the row rather than one box.
    input.addEventListener('paste', e => {
      e.preventDefault();
      const text = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
      text.split('').forEach((ch, n) => {
        if (digits[n]) { digits[n].value = ch; digits[n].classList.add('is-filled'); }
      });
      psuSyncSubmit();
      digits[Math.min(text.length, digits.length - 1)]?.focus();
    });
  });

  psuEl('psuSubmit')?.addEventListener('click', psuSubmitCode);
  psuEl('psuResend')?.addEventListener('click', psuSendCode);

  psuEl('psLockAction')?.addEventListener('click', async () => {
    if (PS_LOCK.unlocked) {
      await psPost(PS_LOCK.lockUrl);
      PS_LOCK.unlocked = false;
      PS_LOCK.expiresIn = 0;
      psRenderLockBar();
    } else {
      ensurePayslipUnlocked();
    }
  });

  psRenderLockBar();
});

/* Download goes through the same gate, otherwise the middleware would just
   bounce the navigation and the employee would see a redirect, not a reason. */
function downloadPayslip(event, url) {
  event.preventDefault();
  ensurePayslipUnlocked().then(ok => { if (ok) window.location.href = url; });
  return false;
}

/* The wage liquidation is a full page rather than a modal — it is a document
   people print and bring to the payroll office. Same unlock gate as every other
   route that reveals payslip contents. */
function openLiquidation(url) {
  ensurePayslipUnlocked().then(ok => { if (ok) window.location.href = url; });
}

/* ═══════════════════════════════════════════
   PAYSLIP VIEW — FETCH & RENDER
═══════════════════════════════════════════ */
let _psModalInstance = null;

async function viewPayslip(fetchUrl, downloadUrl) {
  if (!await ensurePayslipUnlocked()) return;

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
        'X-CSRF-TOKEN': csrf()
      }
    });

    // The window can lapse between the check above and this request.
    if (res.status === 423) {
      _psModalInstance.hide();
      PS_LOCK.unlocked = false;
      psRenderLockBar();
      if (await ensurePayslipUnlocked()) viewPayslip(fetchUrl, downloadUrl);
      return;
    }

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

  /* Deductions.

     These read the breakdown the payroll run records on the payslip. The old
     fallback chains guessed at column names the API never returns — `pagibig`
     rather than `pag_ibig`, `sss_deduction`, `tax_deduction` — so most resolved
     to 0 and the deductions block was hidden entirely. GSIS had no row at all. */
  const sss        = parseFloat(ps.sss             ?? ps.sss_deduction        ?? 0);
  const philhealth = parseFloat(ps.philhealth      ?? ps.philhealth_deduction ?? ps.phic ?? 0);
  const pagibig    = parseFloat(ps.pag_ibig        ?? ps.pagibig_deduction    ?? ps.hdmf ?? 0);
  const tax        = parseFloat(ps.withholding_tax ?? ps.tax_deduction        ?? ps.tax  ?? 0);
  const gsis       = parseFloat(ps.gsis            ?? 0);
  const loans      = parseFloat(ps.other_deductions ?? ps.loan_deductions ?? ps.loans ?? 0);
  const totalDed   = parseFloat(ps.total_deductions ?? (sss + philhealth + pagibig + tax + gsis + loans));

  /* Net.

     `total_honorarium` is the GROSS figure — the payslip email subtracts
     deductions from it to reach net — so preferring it here labelled gross pay
     as take-home. The recorded net wins; the subtraction is the fallback. */
  const netPay     = parseFloat(ps.net_pay ?? (grossPay - totalDed));

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
        <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.4);margin-bottom:.15rem;">Official Document</div>
        <div style="font-family:'Sora',sans-serif;font-weight:900;font-size:1.2rem;color:#fff;letter-spacing:-.02em;line-height:1;">Payslip</div>
        <div style="font-size:var(--fs-xs);color:rgba(255,255,255,.5);margin-top:4px;">MCC Employee Portal · Digital Payroll System</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:var(--fs-2xs);color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Pay Period</div>
        <div style="font-family:'Sora',sans-serif;font-weight:700;color:#fff;font-size:.88rem;margin-top:2px;">${ps.pay_period || '—'}</div>
        <div style="font-size:var(--fs-2xs);color:rgba(255,255,255,.4);margin-top:3px;">Issued: ${fmtDate(ps.sent_at)}</div>
        <div style="margin-top:6px;">
          <span style="background:${sbg};color:${sc};font-size:var(--fs-2xs);font-weight:800;border-radius:5px;padding:3px 10px;text-transform:uppercase;letter-spacing:.4px;">${status}</span>
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
    <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.6rem;">Employee Information</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem .8rem;">
      <div>
        <div style="font-size:var(--fs-2xs);color:var(--text-3);font-weight:600;">Full Name</div>
        <div style="font-weight:700;font-size:.84rem;color:var(--text);margin-top:1px;">${emp.name || '—'}</div>
      </div>
      <div>
        <div style="font-size:var(--fs-2xs);color:var(--text-3);font-weight:600;">Employee ID</div>
        <div style="font-weight:700;font-size:.84rem;color:var(--text);margin-top:1px;">${emp.employee_id || ps.employee_id || '—'}</div>
      </div>
      <div>
        <div style="font-size:var(--fs-2xs);color:var(--text-3);font-weight:600;">Department</div>
        <div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${emp.department?.name || '—'}</div>
      </div>
      <div>
        <div style="font-size:var(--fs-2xs);color:var(--text-3);font-weight:600;">Position</div>
        <div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${emp.position || '—'}</div>
      </div>
      ${daysWorked > 0 ? `<div><div style="font-size:var(--fs-2xs);color:var(--text-3);font-weight:600;">Days Worked</div><div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${daysWorked} day(s)</div></div>` : ''}
      ${hoursWork  > 0 ? `<div><div style="font-size:var(--fs-2xs);color:var(--text-3);font-weight:600;">Hours Worked</div><div style="font-weight:600;font-size:.8rem;color:var(--text-2);margin-top:1px;">${hoursWork}h</div></div>` : ''}
    </div>
  </div>

  <!-- Earnings -->
  <div style="padding:.9rem 1.8rem .4rem;">
    <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.5rem;">Earnings</div>
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
    <div style="font-size:var(--fs-2xs);font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-3);margin-bottom:.5rem;">Mandatory Deductions</div>
    <div class="ps-table-bg">
      ${sss > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-shield-check" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>SSS</span><span class="ps-row-val deduct">– ${fmt(sss)}</span></div>` : ''}
      ${philhealth > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-heart-pulse" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>PhilHealth</span><span class="ps-row-val deduct">– ${fmt(philhealth)}</span></div>` : ''}
      ${pagibig > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-house-heart" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Pag-IBIG (HDMF)</span><span class="ps-row-val deduct">– ${fmt(pagibig)}</span></div>` : ''}
      ${tax > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-percent" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>Withholding Tax</span><span class="ps-row-val deduct">– ${fmt(tax)}</span></div>` : ''}
      ${gsis > 0 ? `<div class="ps-row"><span class="ps-row-key"><i class="bi bi-bank" style="font-size:.7rem;margin-right:4px;color:var(--text-3);"></i>GSIS</span><span class="ps-row-val deduct">– ${fmt(gsis)}</span></div>` : ''}
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
      <div style="font-size:var(--fs-2xs);color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.7px;font-weight:700;margin-bottom:.2rem;">Net Pay</div>
      <div style="font-family:'Sora',sans-serif;font-weight:900;font-size:2rem;color:#fff;letter-spacing:-.04em;line-height:1;">${fmt(netPay)}</div>
      <div style="font-size:var(--fs-2xs);color:rgba(255,255,255,.35);margin-top:4px;">
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