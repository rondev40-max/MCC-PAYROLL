{{-- Shared chrome for the DTR screens. Kept in one partial so the index and the
     record itself cannot drift apart the way the sidebar copies did. --}}
<style>
  :root {
    --brand: #2563eb;
    --brand-dark: #1d4ed8;
    --brand-light: #eef4ff;
    --safe: #0f766e;
    --warn: #d97706;
    --danger: #dc2626;

    --bg: #f6f8fb;
    --card: #ffffff;
    --text: #0f1729;
    --text-2: #4b5a70;
    --text-3: #8494a9;
    --border: #e6ebf2;
    --border-2: #f1f4f9;

    --r-sm: 9px;
    --r-md: 13px;
    --r-lg: 17px;

    --sh-xs: 0 1px 2px rgba(15,23,41,.04);
    --sh-sm: 0 1px 2px rgba(15,23,41,.04), 0 2px 6px rgba(15,23,41,.05);
    --sh-md: 0 2px 4px rgba(15,23,41,.04), 0 8px 20px -6px rgba(15,23,41,.10);
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--bg);
    color: var(--text);
    -webkit-font-smoothing: antialiased;
    line-height: 1.55;
  }

  a { color: inherit; text-decoration: none; }

  .wrap { max-width: 1080px; margin: 0 auto; padding: 28px 20px 64px; }

  /* ── Top bar ─────────────────────────────────────────── */
  .topbar {
    background: #101725;
    border-bottom: 1px solid rgba(255,255,255,.07);
  }
  .topbar-inner {
    max-width: 1080px; margin: 0 auto; padding: 14px 20px;
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
  }
  .topbar-brand {
    display: flex; align-items: center; gap: 10px;
    color: #fff; font-weight: 700; font-size: .95rem; letter-spacing: -.01em;
  }
  .topbar-brand small {
    display: block; font-size: .66rem; font-weight: 500;
    color: rgba(226,232,240,.5); letter-spacing: 0;
  }
  .topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 8px; }

  /* ── Buttons ─────────────────────────────────────────── */
  .btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    padding: 9px 16px; border-radius: var(--r-sm);
    font-size: .82rem; font-weight: 600; font-family: inherit;
    border: 1px solid transparent; cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
    white-space: nowrap;
  }
  .btn-primary { background: var(--brand); color: #fff; }
  .btn-primary:hover { background: var(--brand-dark); color: #fff; }
  .btn-outline { background: var(--card); color: var(--text); border-color: var(--border); }
  .btn-outline:hover { background: var(--brand-light); border-color: var(--brand); color: var(--brand-dark); }
  .btn-ghost { background: rgba(255,255,255,.08); color: #fff; border-color: rgba(255,255,255,.15); }
  .btn-ghost:hover { background: rgba(255,255,255,.16); color: #fff; }
  .btn-sm { padding: 6px 12px; font-size: .76rem; }
  .btn:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }

  /* ── Page header ─────────────────────────────────────── */
  .page-head { margin-bottom: 22px; }
  .eyebrow {
    font-size: .66rem; font-weight: 700; letter-spacing: .14em;
    text-transform: uppercase; color: var(--text-3); margin-bottom: 6px;
  }
  .page-title {
    font-size: 1.6rem; font-weight: 800; letter-spacing: -.03em; margin-bottom: 4px;
  }
  .page-sub { color: var(--text-2); font-size: .9rem; }

  /* ── Card ────────────────────────────────────────────── */
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    box-shadow: var(--sh-sm);
    overflow: hidden;
  }
  .card-head {
    padding: 16px 20px; border-bottom: 1px solid var(--border-2);
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  }
  .card-title { font-size: .95rem; font-weight: 700; letter-spacing: -.01em; }
  .card-body { padding: 20px; }

  /* ── Toolbar ─────────────────────────────────────────── */
  .toolbar {
    display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;
    margin-bottom: 18px;
  }
  .field { display: flex; flex-direction: column; gap: 5px; }
  .field label {
    font-size: .68rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: var(--text-3);
  }
  .field select, .field input {
    padding: 9px 12px; border-radius: var(--r-sm);
    border: 1.5px solid var(--border); background: var(--card);
    font-family: inherit; font-size: .85rem; color: var(--text);
    min-width: 170px;
  }
  .field select:focus, .field input:focus {
    outline: none; border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
  }

  /* ── Flash ───────────────────────────────────────────── */
  .flash {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px; border-radius: var(--r-md); margin-bottom: 18px;
    font-size: .85rem;
    background: rgba(15,118,110,.08); border: 1px solid rgba(15,118,110,.25);
    color: #0f766e;
  }

  /* ── Empty ───────────────────────────────────────────── */
  .empty { text-align: center; padding: 56px 24px; color: var(--text-3); }
  .empty-title { font-size: 1rem; font-weight: 700; color: var(--text); margin: 12px 0 4px; }
  .empty-sub { font-size: .85rem; max-width: 42ch; margin: 0 auto; }

  @media print {
    .no-print { display: none !important; }
  }
</style>
