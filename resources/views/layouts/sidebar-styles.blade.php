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
    transition: transform .3s;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.07) transparent;
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
  }

  .sidebar .nav-link:hover i,
  .sidebar-btn:hover i { opacity: 1; }

  .sidebar .nav-link.active {
    background: var(--sidebar-active);
    color: #fff;
  }

  .sidebar .nav-link.active i { opacity: 1; }

  /* Sidebar dropdown */
  .sidebar .dropdown-menu {
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    padding: .35rem;
    background: #1e293b;
    z-index: 1050;
  }

  .sidebar .dropdown-item {
    border-radius: 7px;
    padding: .42rem .8rem;
    font-size: .8rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 500;
    color: rgba(255,255,255,0.85);
    display: flex;
    align-items: center;
    gap: 7px;
    transition: background .13s;
  }

  .sidebar .dropdown-item:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
  }

  .sidebar-footer {
    padding: .65rem;
    border-top: 1px solid rgba(255,255,255,0.05);
    flex-shrink: 0;
  }

  /* ─── Responsive Sidebar ─────────────────────────── */
  .sidebar-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1025;
  }

  /* ─── Content Wrapper Shift ────────────────────── */
  .sidebar-shift {
    margin-left: var(--sidebar-w);
    min-width: 0;
    flex-grow: 1;
    flex-shrink: 0;
    width: calc(100% - var(--sidebar-w));
    display: block !important;
    transition: margin-left .25s ease-in-out;
  }

  @media (max-width: 991.98px) {
    .sidebar {
      transform: translateX(-100%);
    }
    .sidebar.open {
      transform: translateX(0);
    }
    .sidebar-overlay.show {
      display: block;
    }
    .sidebar-shift {
      margin-left: 0 !important;
    }
  }

  .night-mode .sidebar .dropdown-menu { background: #060a14; }
</style>
