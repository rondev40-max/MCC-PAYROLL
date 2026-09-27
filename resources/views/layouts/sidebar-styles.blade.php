{{-- Head partial for pages that @include('layouts.sidebar'): put this before </head> --}}
{{-- and give <body> the "has-fixed-sidebar" class so content clears the fixed sidebar. --}}
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --sidebar-w:    220px;
    --sidebar-bg:   #0f172a;
    --sidebar-text: rgba(226,232,240,0.75);
    --sidebar-hover:rgba(255,255,255,0.06);
    --sidebar-active:rgba(37,99,235,0.85);
  }

  .night-mode {
    --sidebar-bg:   #060a14;
  }

  /* ─── Sidebar Fixed Left Layout ──────────────────── */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--sidebar-bg);
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    position: fixed;
    left: 0; top: 0; bottom: 0;
    z-index: 1030;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.07) transparent;
    font-family: 'Plus Jakarta Sans', sans-serif;
    line-height: 1.5;
    text-align: left;
  }

  .sidebar-header {
    padding: 1.1rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    flex-shrink: 0;
  }

  .sidebar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sidebar-logo img {
    width: 34px; height: 34px;
    border-radius: 8px;
    object-fit: contain;
    background: rgba(255,255,255,0.08);
    padding: 4px;
  }

  .brand-name {
    font-size: .82rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.2px;
  }

  .brand-sub {
    font-size: .65rem;
    color: rgba(255,255,255,0.38);
    font-weight: 400;
    letter-spacing: .3px;
  }

  .sidebar-nav {
    flex: 1;
    padding: .6rem .65rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 1px;
    overflow-y: auto;
    overflow-x: hidden;
  }

  .nav-label {
    font-size: .6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.4px;
    color: rgba(255,255,255,0.22);
    padding: .9rem .55rem .25rem;
  }

  .sidebar .nav-link,
  .sidebar-btn {
    color: var(--sidebar-text);
    border-radius: 10px;
    padding: .5rem .65rem;
    font-size: .82rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 9px;
    transition: background .15s, color .15s;
    white-space: nowrap;
    text-decoration: none;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    font-family: 'Plus Jakarta Sans', sans-serif;
    cursor: pointer;
  }

  .sidebar .nav-link i,
  .sidebar-btn i {
    font-size: .95rem;
    width: 17px;
    flex-shrink: 0;
    opacity: .8;
  }

  .sidebar .nav-link:hover,
  .sidebar-btn:hover {
    background: var(--sidebar-hover);
    color: #fff;
    /* transform removed — no lift action on nav items */
  }

  .sidebar .nav-link:hover i,
  .sidebar-btn:hover i { opacity: 1; }

  .sidebar .nav-link.active {
    background: var(--sidebar-active);
    color: #fff;
  }

  .sidebar .nav-link.active i { opacity: 1; }

  /* Sidebar dropdown — light card, matching the admin dashboard's sidebar */
  .sidebar .dropdown-menu {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 8px 24px rgba(15,23,42,0.10);
    padding: .35rem;
    background: #fff;
    color: #0f172a;
    z-index: 1050;
  }

  .sidebar .dropdown-item {
    border-radius: 7px;
    padding: .42rem .8rem;
    font-size: .8rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 500;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: background .13s;
  }

  .sidebar .dropdown-item:hover {
    background: #eff6ff;
    color: #2563eb;
  }

  .sidebar-footer {
    padding: .65rem;
    border-top: 1px solid rgba(255,255,255,0.05);
    flex-shrink: 0;
  }

  /* ─── Page Offset ───────────────────────────────── */
  /* !important because host pages reset body padding in their own styles. */
  body.has-fixed-sidebar {
    padding-left: var(--sidebar-w) !important;
  }

  /* ─── Content Wrapper Shift ────────────────────── */
  .sidebar-shift {
    margin-left: var(--sidebar-w);
    min-width: 0;
    flex-grow: 1;
    flex-shrink: 0;
    width: calc(100% - var(--sidebar-w));
    display: block !important;
  }

  .night-mode .sidebar .dropdown-menu { background: #1a2133; border-color: #cbd5e1; color: #e2e8f0; }
  .night-mode .sidebar .dropdown-item { color: #e2e8f0; }
  .night-mode .sidebar .dropdown-item:hover { background: rgba(37,99,235,0.15); }

  @media print {
    .sidebar { display: none !important; }
    body.has-fixed-sidebar { padding-left: 0 !important; }
    .sidebar-shift { margin-left: 0; width: 100%; }
  }
</style>
