<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Master List - Madridejos Community College</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  {{-- DataTables 2 still needs jQuery, so it must be in the bundle (jq-3.7.0) or nothing initialises. --}}
  {{-- Bundle: jQuery + DataTables + Buttons (CSV / Excel / Print) + Responsive, Bootstrap 5 styling. --}}
  <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.3.6/b-3.2.5/b-html5-3.2.5/b-print-3.2.5/r-3.0.7/datatables.min.css" rel="stylesheet">

  <style>
    :root {
      /* Shared app tokens — same family used on the timesheet pages */
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
      --primary-tint: #eff6ff;
      --primary-ring: rgba(37, 99, 235, 0.16);

      --slate-50:  #f8fafc;
      --slate-100: #f1f5f9;
      --slate-200: #e2e8f0;
      --slate-300: #cbd5e1;
      --slate-400: #94a3b8;
      --slate-500: #64748b;
      --slate-600: #475569;
      --slate-700: #334155;
      --slate-900: #0f172a;

      --danger: #dc2626;
      --danger-dark: #b91c1c;
      --danger-tint: #fef2f2;

      /* Employee types — one distinct hue each */
      --type-fulltime: #2563eb;  --type-fulltime-tint: #eff6ff;
      --type-parttime: #7c3aed;  --type-parttime-tint: #f5f3ff;
      --type-staff:    #0f766e;  --type-staff-tint:    #f0fdfa;
      --type-utility:  #b45309;  --type-utility-tint:  #fffbeb;

      /* Departments — shown as a coloured dot on a neutral pill */
      --dept-bsit: #dc2626;
      --dept-bsba: #16a34a;
      --dept-bshm: #d97706;
      --dept-education: #2563eb;

      --radius-sm: 8px;
      --radius: 12px;
      --radius-lg: 16px;
      --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.05);
      --shadow-md: 0 1px 3px rgba(15, 23, 42, 0.06), 0 6px 16px rgba(15, 23, 42, 0.06);
      --shadow-lg: 0 24px 48px -12px rgba(15, 23, 42, 0.25);
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--slate-50);
      color: var(--slate-900);
      margin: 0;
      -webkit-font-smoothing: antialiased;
    }

    a { text-decoration: none; }

    :focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

    /* ---------- Page shell ---------- */
    .ml-page {
      max-width: 1320px;
      margin: 0 auto;
      padding: 2rem 2rem 3rem;
    }

    .ml-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 1.5rem;
    }

    .ml-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--primary);
      margin-bottom: 0.35rem;
    }

    .ml-header h1 {
      font-size: 1.65rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      margin: 0 0 0.25rem;
    }

    .ml-header p { margin: 0; color: var(--slate-500); font-size: 0.92rem; }

    .ml-header-actions { display: flex; gap: 0.6rem; align-items: center; }

    /* ---------- Buttons ---------- */
    .btn-ml {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      height: 40px;
      padding: 0 1rem;
      border-radius: var(--radius-sm);
      border: 1px solid transparent;
      font: 600 0.88rem/1 'Plus Jakarta Sans', sans-serif;
      cursor: pointer;
      white-space: nowrap;
      transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }

    .btn-ml-primary { background: var(--primary); color: #fff; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.3); }
    .btn-ml-primary:hover { background: var(--primary-dark); color: #fff; }

    .btn-ml-outline { background: #fff; color: var(--slate-700); border-color: var(--slate-200); box-shadow: var(--shadow-sm); }
    .btn-ml-outline:hover, .btn-ml-outline[aria-expanded="true"] { background: var(--slate-50); border-color: var(--slate-300); color: var(--slate-900); }

    .btn-ml-ghost { background: transparent; color: var(--slate-500); }
    .btn-ml-ghost:hover { background: var(--slate-100); color: var(--slate-900); }
    .btn-ml-ghost.is-idle { visibility: hidden; }

    .btn-ml-danger { background: var(--danger); color: #fff; }
    .btn-ml-danger:hover { background: var(--danger-dark); color: #fff; }
    .btn-ml-danger:disabled { opacity: 0.7; cursor: progress; }

    .btn-ml-danger-ghost { background: transparent; color: var(--danger); }
    .btn-ml-danger-ghost:hover { background: var(--danger-tint); color: var(--danger-dark); }

    .dropdown-menu {
      border: 1px solid var(--slate-200);
      border-radius: var(--radius);
      box-shadow: var(--shadow-md);
      padding: 0.35rem;
      min-width: 190px;
      font-size: 0.88rem;
    }
    .dropdown-item {
      border-radius: 6px;
      padding: 0.5rem 0.7rem;
      display: flex;
      align-items: center;
      gap: 9px;
      font-weight: 500;
      color: var(--slate-700);
    }
    .dropdown-item i { color: var(--slate-400); font-size: 1rem; }
    .dropdown-item:hover, .dropdown-item:focus { background: var(--slate-100); color: var(--slate-900); }
    .dropdown-item small { margin-left: auto; color: var(--slate-400); font-size: 0.72rem; }

    /* ---------- Table card ---------- */
    .ml-card {
      background: #fff;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      overflow: hidden;
    }

    .ml-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem 1rem;
      flex-wrap: wrap;
      padding: 1rem 1.25rem;
      border-bottom: 1px solid var(--slate-200);
    }

    /* Segmented type filter */
    .ml-tabs {
      display: inline-flex;
      gap: 2px;
      padding: 3px;
      background: var(--slate-100);
      border-radius: 10px;
      overflow-x: auto;
      max-width: 100%;
      scrollbar-width: none;
    }
    .ml-tab {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      height: 32px;
      padding: 0 0.8rem;
      border: 0;
      border-radius: 8px;
      background: transparent;
      color: var(--slate-600);
      font: 600 0.84rem/1 'Plus Jakarta Sans', sans-serif;
      cursor: pointer;
      white-space: nowrap;
      transition: background 0.15s ease, color 0.15s ease;
    }
    .ml-tab:hover { color: var(--slate-900); }
    .ml-tab.is-active { background: #fff; color: var(--slate-900); box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12); }
    .ml-tab-count {
      min-width: 22px;
      padding: 3px 6px;
      border-radius: 999px;
      background: rgba(100, 116, 139, 0.12);
      color: var(--slate-500);
      font-size: 0.72rem;
      font-weight: 700;
      text-align: center;
    }
    .ml-tab.is-active .ml-tab-count { background: var(--primary-tint); color: var(--primary); }

    .ml-filters { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }

    .ml-search { position: relative; }
    .ml-search i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--slate-400);
      pointer-events: none;
    }
    .ml-search input {
      width: 240px;
      height: 40px;
      padding: 0 0.9rem 0 2.2rem;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius-sm);
      background: #fff;
      font: 500 0.88rem 'Plus Jakarta Sans', sans-serif;
      color: var(--slate-900);
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ml-search input::placeholder { color: var(--slate-400); }

    .ml-select {
      height: 40px;
      width: auto;
      min-width: 170px;
      padding: 0 2.2rem 0 0.9rem;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius-sm);
      font: 500 0.88rem 'Plus Jakarta Sans', sans-serif;
      color: var(--slate-700);
      box-shadow: none;
    }

    .ml-search input:focus, .ml-select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-ring);
    }

    /* ---------- DataTable ---------- */
    .ml-table-wrap { padding: 0; }

    table.ml-table {
      --bs-table-bg: transparent;
      --bs-table-hover-bg: transparent;
      margin: 0 !important;
      border-collapse: separate;
      border-spacing: 0;
      width: 100% !important;
    }

    .ml-table thead th {
      background: var(--slate-50);
      border-bottom: 1px solid var(--slate-200) !important;
      border-top: 0;
      padding: 0.7rem 1rem !important;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--slate-500);
      white-space: nowrap;
    }
    .ml-table thead th:first-child, .ml-table tbody td:first-child { padding-left: 1.25rem !important; }
    .ml-table thead th:last-child,  .ml-table tbody td:last-child  { padding-right: 1.25rem !important; }

    /* Sort arrow sits right after the label; it shows on hover and on the sorted column */
    .ml-table thead th .dt-column-header { justify-content: flex-start; gap: 6px; }
    .ml-table thead th.col-rate .dt-column-header { flex-direction: row; justify-content: flex-end; }
    .ml-table thead th .dt-column-order { position: relative !important; right: auto !important; width: 10px !important; opacity: 0; transition: opacity 0.15s ease; }
    .ml-table thead th.dt-orderable-asc:hover .dt-column-order,
    .ml-table thead th.dt-orderable-desc:hover .dt-column-order { opacity: 0.5; }
    .ml-table thead th.dt-ordering-asc .dt-column-order,
    .ml-table thead th.dt-ordering-desc .dt-column-order { opacity: 1; color: var(--primary); }
    .ml-table thead th.dt-orderable-asc:hover,
    .ml-table thead th.dt-orderable-desc:hover { color: var(--slate-900); outline: none; }

    .ml-table tbody td {
      padding: 0.65rem 1rem !important;
      border-bottom: 1px solid var(--slate-100);
      vertical-align: middle;
      font-size: 0.9rem;
      color: var(--slate-700);
    }
    .ml-table tbody tr:last-child td { border-bottom: 0; }
    .ml-table tbody tr[data-id] { cursor: pointer; }
    .ml-table tbody tr[data-id]:hover > td { background: #f8faff; }

    .ml-table .col-index { width: 52px; color: var(--slate-400); font-variant-numeric: tabular-nums; font-size: 0.82rem; text-align: left !important; }
    .ml-table .col-rate { text-align: right !important; white-space: nowrap; }
    .ml-table .col-actions { width: 1%; text-align: right !important; white-space: nowrap; }

    .ml-person { display: flex; align-items: center; gap: 12px; min-width: 0; }

    .ml-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      flex-shrink: 0;
      display: grid;
      place-items: center;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      background: var(--av-tint, var(--slate-100));
      color: var(--av-ink, var(--slate-600));
    }

    .ml-name {
      display: block;
      padding: 0;
      border: 0;
      background: none;
      font: 700 0.92rem/1.3 'Plus Jakarta Sans', sans-serif;
      color: var(--slate-900);
      text-align: left;
      cursor: pointer;
    }
    .ml-name:hover { color: var(--primary); }
    .ml-sub { display: block; font-size: 0.78rem; color: var(--slate-500); margin-top: 1px; }

    .ml-email { color: var(--slate-600); }
    .ml-email:hover { color: var(--primary); text-decoration: underline; }
    .ml-muted { color: var(--slate-300); }

    .ml-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      height: 24px;
      padding: 0 0.6rem;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 700;
      white-space: nowrap;
    }
    .ml-badge-dept { background: #fff; border: 1px solid var(--slate-200); color: var(--slate-700); }
    .ml-badge-dept::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--dot, var(--slate-400)); }
    .dept-bsit      { --dot: var(--dept-bsit); }
    .dept-bsba      { --dot: var(--dept-bsba); }
    .dept-bshm      { --dot: var(--dept-bshm); }
    .dept-education { --dot: var(--dept-education); }

    .type-fulltime { --av-tint: var(--type-fulltime-tint); --av-ink: var(--type-fulltime); }
    .type-parttime { --av-tint: var(--type-parttime-tint); --av-ink: var(--type-parttime); }
    .type-staff    { --av-tint: var(--type-staff-tint);    --av-ink: var(--type-staff); }
    .type-utility  { --av-tint: var(--type-utility-tint);  --av-ink: var(--type-utility); }
    .ml-badge-type { background: var(--av-tint, var(--slate-100)); color: var(--av-ink, var(--slate-600)); }

    .ml-rate { font-weight: 700; color: var(--slate-900); font-variant-numeric: tabular-nums; }
    .ml-rate-unit { font-size: 0.75rem; font-weight: 500; color: var(--slate-400); margin-left: 2px; }

    .ml-actions { display: inline-flex; gap: 2px; }
    .ml-icon-btn {
      width: 32px;
      height: 32px;
      display: inline-grid;
      place-items: center;
      border: 0;
      border-radius: 8px;
      background: transparent;
      color: var(--slate-400);
      font-size: 0.95rem;
      cursor: pointer;
      transition: background 0.15s ease, color 0.15s ease;
    }
    .ml-icon-btn:hover { background: var(--slate-100); color: var(--slate-900); }
    .ml-icon-btn.is-danger:hover { background: var(--danger-tint); color: var(--danger); }

    /* DataTables' Bootstrap 5 renderer wraps each layout row in a plain Bootstrap
       ".row mt-2" (gutters + top margin); flatten them so the table runs edge to
       edge inside the card. */
    .ml-card .dt-container > .row { --bs-gutter-x: 0; margin: 0 !important; }
    .ml-card .dt-container > .row > * { padding: 0; }

    /* DataTables footer: info · rows per page · paging */
    .ml-card .dt-container > .row:not(.dt-layout-table) {
      padding: 0.85rem 1.25rem;
      border-top: 1px solid var(--slate-200);
      background: #fff;
      align-items: center;
    }
    .ml-card .dt-info { color: var(--slate-500); font-size: 0.84rem; font-weight: 500; padding: 0 !important; }
    .ml-card .dt-info strong { color: var(--slate-900); }
    .ml-card .dt-layout-end { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; justify-content: flex-end; }
    .ml-card .dt-length { display: flex; align-items: center; gap: 8px; color: var(--slate-500); font-size: 0.84rem; font-weight: 500; }
    .ml-card .dt-length label { margin: 0; }
    .ml-card .dt-length select {
      height: 34px;
      min-width: 72px;
      padding: 0 1.9rem 0 0.7rem;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius-sm);
      font: 600 0.84rem 'Plus Jakarta Sans', sans-serif;
      color: var(--slate-700);
      margin: 0 !important;
    }
    .ml-card .dt-paging .pagination { margin: 0; gap: 4px; }
    .ml-card .dt-paging .page-link {
      min-width: 34px;
      height: 34px;
      display: grid;
      place-items: center;
      padding: 0 0.6rem;
      border: 1px solid transparent;
      border-radius: var(--radius-sm) !important;
      color: var(--slate-600);
      font-size: 0.84rem;
      font-weight: 600;
      background: transparent;
      box-shadow: none !important;
    }
    .ml-card .dt-paging .page-link:hover { background: var(--slate-100); color: var(--slate-900); }
    .ml-card .dt-paging .active > .page-link { background: var(--primary); border-color: var(--primary); color: #fff; }
    .ml-card .dt-paging .disabled > .page-link { color: var(--slate-300); background: transparent; }

    /* Empty / no-match state rendered by DataTables */
    .ml-card td.dt-empty { padding: 3.5rem 1rem !important; cursor: default; }
    .ml-empty { display: flex; flex-direction: column; align-items: center; gap: 0.35rem; color: var(--slate-500); }
    .ml-empty i { font-size: 2.2rem; color: var(--slate-300); margin-bottom: 0.35rem; }
    .ml-empty strong { color: var(--slate-700); font-size: 0.98rem; }
    .ml-empty span { font-size: 0.85rem; }

    /* ---------- Modals ---------- */
    .ml-modal .modal-content {
      border: 0;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }
    .modal-backdrop.show { opacity: 0.45; }
    .ml-modal .modal-header { border: 0; padding: 1.5rem 1.5rem 0.25rem; align-items: flex-start; }
    .ml-modal .modal-body { padding: 1rem 1.5rem 1.25rem; }
    .ml-modal .modal-footer {
      border-top: 1px solid var(--slate-100);
      background: var(--slate-50);
      padding: 0.85rem 1.5rem;
      gap: 0.5rem;
    }
    .ml-modal .modal-footer > * { margin: 0; }
    .ml-modal .btn-close { box-shadow: none; opacity: 0.45; }
    .ml-modal .btn-close:hover { opacity: 0.8; }

    .ml-profile { display: flex; align-items: center; gap: 14px; }
    .ml-profile .ml-avatar { width: 56px; height: 56px; font-size: 1.1rem; }
    .ml-profile h2 { font-size: 1.15rem; font-weight: 800; margin: 0 0 0.35rem; letter-spacing: -0.01em; }

    .ml-details { margin: 0.5rem 0 0; display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid var(--slate-200); border-radius: var(--radius); overflow: hidden; }
    .ml-details > div { padding: 0.8rem 1rem; border-bottom: 1px solid var(--slate-100); min-width: 0; }
    .ml-details > div:nth-child(odd) { border-right: 1px solid var(--slate-100); }
    .ml-details > div.is-wide { grid-column: 1 / -1; border-right: 0; }
    .ml-details > div:last-child, .ml-details > div:nth-last-child(2):not(.is-wide):nth-child(odd) { border-bottom: 0; }
    .ml-details dt { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: var(--slate-400); margin-bottom: 0.25rem; }
    .ml-details dd { margin: 0; font-size: 0.92rem; font-weight: 600; color: var(--slate-900); overflow-wrap: anywhere; }
    .ml-details dd a { color: var(--primary); }

    .ml-confirm { text-align: center; padding-top: 0.5rem; }
    .ml-confirm-icon {
      width: 56px;
      height: 56px;
      margin: 0 auto 1rem;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: var(--danger-tint);
      color: var(--danger);
      font-size: 1.5rem;
      box-shadow: 0 0 0 8px rgba(220, 38, 38, 0.06);
    }
    .ml-confirm h2 { font-size: 1.15rem; font-weight: 800; margin: 0 0 0.4rem; }
    .ml-confirm p { color: var(--slate-500); font-size: 0.9rem; margin: 0 auto; max-width: 340px; }
    .ml-confirm-error {
      display: none;
      margin-top: 1rem;
      padding: 0.6rem 0.8rem;
      border-radius: var(--radius-sm);
      background: var(--danger-tint);
      color: var(--danger-dark);
      font-size: 0.85rem;
      font-weight: 500;
      text-align: left;
    }
    .ml-confirm-error.is-visible { display: block; }

    /* ---------- Toast ---------- */
    .ml-toast {
      border: 0;
      border-radius: var(--radius);
      background: var(--slate-900);
      color: #fff;
      box-shadow: var(--shadow-lg);
      font-size: 0.88rem;
      font-weight: 500;
    }
    .ml-toast .toast-body { display: flex; align-items: center; gap: 10px; padding: 0.8rem 1rem; }
    .ml-toast .toast-body i { color: #4ade80; font-size: 1.05rem; }

    @media (max-width: 992px) {
      .ml-page { padding: 1.5rem 1rem 2.5rem; }
      .ml-toolbar { flex-direction: column; align-items: stretch; }
      .ml-filters { width: 100%; }
      .ml-search { flex: 1 1 220px; }
      .ml-search input { width: 100%; }
      .ml-select { flex: 1 1 160px; }
    }

    @media (max-width: 576px) {
      .ml-header-actions { width: 100%; }
      .ml-header-actions > * { flex: 1; }
      .ml-header-actions .btn-ml { width: 100%; }
      .ml-details { grid-template-columns: 1fr; }
      .ml-details > div:nth-child(odd) { border-right: 0; }
      .ml-card .dt-container > .row:not(.dt-layout-table) { flex-direction: column; gap: 0.75rem; }
    }

    @media print {
      body { background: #fff; }
      .ml-header-actions, .ml-toolbar, .col-actions, .ml-card .dt-container > .row:not(.dt-layout-table) { display: none !important; }
      .ml-card { border: 0; box-shadow: none; }
    }
  </style>
  @include('layouts.sidebar-styles')
</head>
<body class="has-fixed-sidebar">
  @include('layouts.sidebar')

  @php
    // One place that knows how each employee type is labelled, coloured and paid.
    $typeMeta = [
      'fulltime' => ['label' => 'Full-time', 'unit' => 'hr'],
      'parttime' => ['label' => 'Part-time', 'unit' => 'hr'],
      'staff'    => ['label' => 'Staff',     'unit' => 'day'],
      'utility'  => ['label' => 'Utility',   'unit' => 'day'],
    ];
    $typeKeyOf = fn ($employee) => match ($employee->type ?? '') {
      'Full-time Instructor' => 'fulltime',
      'Part-time Instructor' => 'parttime',
      'Staff'                => 'staff',
      'Utility'              => 'utility',
      default                => 'fulltime',
    };
    // BSED and BEED are both filed under "Education" in the department filter.
    $deptGroupOf = function (?string $department) {
      $dept = strtoupper(trim($department ?? ''));
      return match (true) {
        $dept === ''                                                  => '',
        in_array($dept, ['BSED', 'BEED']) || str_contains($dept, 'EDUC') => 'EDUCATION',
        default                                                       => $dept,
      };
    };
    $typeCounts = $employees->countBy(fn ($e) => $typeKeyOf($e));
    $activeType = array_key_exists($selectedEmployeeType, $typeMeta) ? $selectedEmployeeType : 'all';
  @endphp

  <main class="ml-page">

    <header class="ml-header">
      <div>
        <div class="ml-eyebrow"><i class="bi bi-people-fill"></i> Records</div>
        <h1>Master List</h1>
        <p>Every employee on payroll at Madridejos Community College.</p>
      </div>
      <div class="ml-header-actions">
        <div class="dropdown">
          <button class="btn-ml btn-ml-outline" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-box-arrow-up"></i> Export <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" type="button" data-export="csv"><i class="bi bi-filetype-csv"></i> CSV <small>.csv</small></button></li>
            <li><button class="dropdown-item" type="button" data-export="excel"><i class="bi bi-file-earmark-spreadsheet"></i> Excel <small>.xlsx</small></button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item" type="button" data-export="print"><i class="bi bi-printer"></i> Print</button></li>
          </ul>
        </div>
        <a href="{{ route('master.list.add') }}" class="btn-ml btn-ml-primary">
          <i class="bi bi-plus-lg"></i> Add Employee
        </a>
      </div>
    </header>

    <section class="ml-card" aria-label="Employees">
      <div class="ml-toolbar">
        <div class="ml-tabs" role="group" aria-label="Filter by employee type">
          <button type="button" class="ml-tab {{ $activeType === 'all' ? 'is-active' : '' }}" data-type="all" aria-pressed="{{ $activeType === 'all' ? 'true' : 'false' }}">
            All <span class="ml-tab-count">{{ $employees->count() }}</span>
          </button>
          @foreach($typeMeta as $key => $meta)
            <button type="button" class="ml-tab {{ $activeType === $key ? 'is-active' : '' }}" data-type="{{ $key }}" aria-pressed="{{ $activeType === $key ? 'true' : 'false' }}">
              {{ $meta['label'] }} <span class="ml-tab-count">{{ $typeCounts[$key] ?? 0 }}</span>
            </button>
          @endforeach
        </div>

        <div class="ml-filters">
          <label class="ml-search">
            <span class="visually-hidden">Search employees</span>
            <i class="bi bi-search"></i>
            <input type="search" id="mlSearch" placeholder="Search employees…" autocomplete="off" value="{{ request('search', '') }}">
          </label>
          <select class="form-select ml-select" id="mlDepartment" aria-label="Filter by department">
            <option value="all">All departments</option>
            @foreach($departments as $dept)
              <option value="{{ $dept }}" {{ $selectedDepartment === $dept ? 'selected' : '' }}>{{ $dept === 'EDUCATION' ? 'Education (BSED / BEED)' : $dept }}</option>
            @endforeach
          </select>
          <button type="button" class="btn-ml btn-ml-ghost is-idle" id="mlClear">
            <i class="bi bi-x-lg"></i> Clear
          </button>
        </div>
      </div>

      <div class="ml-table-wrap">
        <table class="table ml-table" id="employeesTable" style="width: 100%;">
          <thead>
            <tr>
              <th class="col-index">#</th>
              <th class="exportable">Employee</th>
              <th class="exportable">Email</th>
              <th class="exportable">Department</th>
              <th class="exportable">Type</th>
              <th class="exportable col-rate">Rate</th>
              <th class="col-actions"><span class="visually-hidden">Actions</span></th>
            </tr>
          </thead>
          <tbody>
            @foreach($employees as $employee)
              @php
                $typeKey   = $typeKeyOf($employee);
                $meta      = $typeMeta[$typeKey];
                $name      = trim($employee->employee_name ?? '') ?: 'Unknown';
                $nameParts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
                $initials  = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . substr(count($nameParts) > 1 ? end($nameParts) : '', 0, 1));
                $dept      = trim($employee->department ?? '');
                $deptGroup = $deptGroupOf($dept);
                $deptClass = $deptGroup ? 'dept-' . strtolower($deptGroup) : '';
                $rate      = (float) ($employee->rate ?? 0);
                $rateText  = $rate > 0 ? '₱' . number_format($rate, 2) : '';
                // Only show the designation under the name when it says more than the type badge does.
                $designation = trim($employee->designation ?? '');
                $showDesignation = $designation !== '' && strcasecmp($designation, $employee->type ?? '') !== 0;
              @endphp
              <tr data-id="{{ $employee->id }}"
                  data-type-key="{{ $typeKey }}"
                  data-type-label="{{ $employee->type }}"
                  data-name="{{ $name }}"
                  data-initials="{{ $initials }}"
                  data-designation="{{ $designation }}"
                  data-email="{{ $employee->email }}"
                  data-department="{{ $dept }}"
                  data-dept-class="{{ $deptClass }}"
                  data-rate="{{ $rateText }}"
                  data-rate-unit="{{ $meta['unit'] }}"
                  data-added="{{ $employee->created_at ? \Illuminate\Support\Carbon::parse($employee->created_at)->format('M j, Y') : '' }}"
                  data-edit-url="{{ route('master.list.edit', ['id' => $employee->id, 'type' => $typeKey]) }}">
                <td class="col-index"></td>
                <td data-order="{{ $name }}" data-export="{{ $name }}">
                  <div class="ml-person">
                    <div class="ml-avatar type-{{ $typeKey }}" aria-hidden="true">{{ $initials }}</div>
                    <div style="min-width: 0;">
                      <button type="button" class="ml-name js-view">{{ $name }}</button>
                      @if($showDesignation)
                        <span class="ml-sub">{{ $designation }}</span>
                      @endif
                    </div>
                  </div>
                </td>
                <td data-export="{{ $employee->email }}">
                  @if($employee->email)
                    <a href="mailto:{{ $employee->email }}" class="ml-email">{{ $employee->email }}</a>
                  @else
                    <span class="ml-muted">—</span>
                  @endif
                </td>
                <td data-search="{{ $deptGroup }} {{ $dept }} {{ $deptGroup === 'EDUCATION' ? 'Education' : '' }}" data-order="{{ $dept ?: 'ZZZ' }}" data-export="{{ $dept }}">
                  @if($dept)
                    <span class="ml-badge ml-badge-dept {{ $deptClass }}">{{ $dept }}</span>
                  @else
                    <span class="ml-muted">—</span>
                  @endif
                </td>
                <td data-search="{{ $typeKey }} {{ $employee->type }}" data-export="{{ $employee->type }}">
                  <span class="ml-badge ml-badge-type type-{{ $typeKey }}">{{ $meta['label'] }}</span>
                </td>
                <td class="col-rate" data-order="{{ $rate }}" data-export="{{ $rateText ? $rateText . ' / ' . ($meta['unit'] === 'hr' ? 'hour' : 'day') : '' }}">
                  @if($rateText)
                    <span class="ml-rate">{{ $rateText }}</span><span class="ml-rate-unit">/{{ $meta['unit'] }}</span>
                  @else
                    <span class="ml-muted">—</span>
                  @endif
                </td>
                <td class="col-actions">
                  <div class="ml-actions">
                    <button type="button" class="ml-icon-btn js-view" title="View details" aria-label="View {{ $name }}"><i class="bi bi-eye"></i></button>
                    <a href="{{ route('master.list.edit', ['id' => $employee->id, 'type' => $typeKey]) }}" class="ml-icon-btn" title="Edit" aria-label="Edit {{ $name }}"><i class="bi bi-pencil"></i></a>
                    <button type="button" class="ml-icon-btn is-danger js-delete" title="Delete" aria-label="Delete {{ $name }}"><i class="bi bi-trash3"></i></button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
  </main>

  {{-- ── Employee details modal ── --}}
  <div class="modal fade ml-modal" id="detailsModal" tabindex="-1" aria-labelledby="detailsName" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="ml-profile">
            <div class="ml-avatar" id="detailsAvatar" aria-hidden="true"></div>
            <div>
              <h2 id="detailsName"></h2>
              <span class="ml-badge ml-badge-type" id="detailsType"></span>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <dl class="ml-details">
            <div class="is-wide"><dt>Designation</dt><dd id="detailsDesignation"></dd></div>
            <div class="is-wide"><dt>Email</dt><dd id="detailsEmail"></dd></div>
            <div><dt>Department</dt><dd id="detailsDepartment"></dd></div>
            <div><dt>Rate</dt><dd id="detailsRate"></dd></div>
            <div class="is-wide"><dt>Added to master list</dt><dd id="detailsAdded"></dd></div>
          </dl>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn-ml btn-ml-danger-ghost" id="detailsDelete"><i class="bi bi-trash3"></i> Delete</button>
          <div class="d-flex gap-2">
            <button type="button" class="btn-ml btn-ml-outline" data-bs-dismiss="modal">Close</button>
            <a href="#" class="btn-ml btn-ml-primary" id="detailsEdit"><i class="bi bi-pencil"></i> Edit details</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Delete confirmation modal ── --}}
  <div class="modal fade ml-modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
      <div class="modal-content">
        <div class="modal-body ml-confirm">
          <div class="ml-confirm-icon"><i class="bi bi-trash3"></i></div>
          <h2 id="deleteTitle">Delete employee?</h2>
          <p><strong id="deleteName" style="color: var(--slate-900);"></strong> will be removed from the <span id="deleteType"></span> list. This can't be undone.</p>
          <div class="ml-confirm-error" id="deleteError" role="alert"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-ml btn-ml-outline flex-fill" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn-ml btn-ml-danger flex-fill" id="deleteConfirm">Delete employee</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div class="toast ml-toast" id="mlToast" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="4000">
      <div class="toast-body"><i class="bi bi-check-circle-fill"></i><span id="mlToastText"></span></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.3.6/b-3.2.5/b-html5-3.2.5/b-print-3.2.5/r-3.0.7/datatables.min.js"></script>

  <script>
    (() => {
      const tableEl = document.getElementById('employeesTable');
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const deleteUrl = @json(route('master.list.delete'));
      const flashKey = 'ml-flash';

      const searchInput = document.getElementById('mlSearch');
      const deptSelect = document.getElementById('mlDepartment');
      const clearBtn = document.getElementById('mlClear');
      const tabs = Array.from(document.querySelectorAll('.ml-tab'));
      let activeType = tabs.find(t => t.classList.contains('is-active'))?.dataset.type || 'all';

      // ── DataTable ─────────────────────────────────────
      const emptyState = (icon, title, hint) =>
        `<div class="ml-empty"><i class="bi bi-${icon}"></i><strong>${title}</strong><span>${hint}</span></div>`;

      const dt = new DataTable(tableEl, {
        order: [[1, 'asc']],
        pageLength: 8,   // every list in the system pages by 8
        lengthMenu: [8, 16, 24, 32, { label: 'All', value: -1 }],
        autoWidth: false,
        responsive: { details: false },   // the details modal shows every field instead
        columnDefs: [
          { targets: 0, orderable: false, searchable: false, responsivePriority: 6 },
          { targets: 1, responsivePriority: 1 },
          { targets: 2, responsivePriority: 5 },
          { targets: 3, responsivePriority: 4 },
          { targets: 4, responsivePriority: 3 },
          { targets: 5, responsivePriority: 3 },
          { targets: 6, orderable: false, searchable: false, responsivePriority: 2 },
        ],
        layout: {
          topStart: null,
          topEnd: null,
          bottomStart: 'info',
          bottomEnd: ['pageLength', 'paging'],
        },
        language: {
          info: 'Showing <strong>_START_–_END_</strong> of <strong>_TOTAL_</strong> employees',
          infoEmpty: 'No employees to show',
          infoFiltered: '(filtered from _MAX_)',
          lengthMenu: 'Rows per page _MENU_',
          emptyTable: emptyState('inbox', 'No employees yet', 'Add an employee to start the master list.'),
          zeroRecords: emptyState('search', 'No matching employees', 'Try a different search, type or department.'),
          paginate: {
            first: '<i class="bi bi-chevron-double-left"></i>',
            previous: '<i class="bi bi-chevron-left"></i>',
            next: '<i class="bi bi-chevron-right"></i>',
            last: '<i class="bi bi-chevron-double-right"></i>',
          },
        },
      });

      // Number rows by their position in the current view, not their source order.
      dt.on('draw', () => {
        const start = dt.page.info().start;
        dt.column(0, { page: 'current' }).nodes().each((cell, i) => { cell.textContent = start + i + 1; });
      });

      // ── Filters ───────────────────────────────────────
      // Type and department cells carry a data-search value whose first word is the filter key.
      const firstWord = value => String(value || '').trim().split(/\s+/)[0];

      function applyFilters() {
        const dept = deptSelect.value;
        dt.search(searchInput.value);
        dt.column(4).search(activeType === 'all' ? '' : value => firstWord(value) === activeType);
        dt.column(3).search(dept === 'all' ? '' : value => firstWord(value) === dept);
        dt.draw();
        // Toggle visibility rather than display so the toolbar does not reflow.
        clearBtn.classList.toggle('is-idle', !searchInput.value && dept === 'all' && activeType === 'all');
      }

      function setType(type) {
        activeType = type;
        tabs.forEach(tab => {
          const on = tab.dataset.type === type;
          tab.classList.toggle('is-active', on);
          tab.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
      }

      tabs.forEach(tab => tab.addEventListener('click', () => { setType(tab.dataset.type); applyFilters(); }));
      searchInput.addEventListener('input', applyFilters);
      deptSelect.addEventListener('change', applyFilters);
      clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        deptSelect.value = 'all';
        setType('all');
        applyFilters();
        searchInput.focus();
      });

      // ── Export (DataTables Buttons) ───────────────────
      // Exports follow the current search, filters and sort order.
      const stamp = new Date().toISOString().slice(0, 10);
      const exportOptions = {
        columns: '.exportable',
        format: { body: (data, row, column, node) => node?.dataset.export ?? String(data).replace(/<[^>]*>/g, '').trim() },
      };
      new DataTable.Buttons(dt, {
        buttons: [
          { extend: 'csvHtml5', name: 'csv', filename: `employee-master-list-${stamp}`, bom: true, exportOptions },
          { extend: 'excelHtml5', name: 'excel', filename: `employee-master-list-${stamp}`, title: 'Employee Master List — Madridejos Community College', exportOptions },
          {
            extend: 'print',
            name: 'print',
            title: 'Employee Master List',
            messageTop: `Madridejos Community College · printed ${new Date().toLocaleDateString()}`,
            exportOptions,
            customize: win => {
              const style = win.document.createElement('style');
              style.textContent = `
                body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: #0f172a; padding: 24px; }
                h1 { font-size: 20px; margin: 0 0 4px; }
                div { color: #64748b; font-size: 12px; margin-bottom: 14px; }
                table { width: 100%; border-collapse: collapse; font-size: 12px; }
                th { text-align: left; background: #f1f5f9; padding: 8px; border-bottom: 1px solid #cbd5e1; }
                td { padding: 7px 8px; border-bottom: 1px solid #e2e8f0; }`;
              win.document.head.appendChild(style);
            },
          },
        ],
      });
      document.querySelectorAll('[data-export]').forEach(btn => {
        btn.addEventListener('click', () => dt.button(`${btn.dataset.export}:name`).trigger());
      });

      // ── Details modal ─────────────────────────────────
      const detailsModal = new bootstrap.Modal('#detailsModal');
      const deleteModal = new bootstrap.Modal('#deleteModal');
      const $ = id => document.getElementById(id);
      const typeLabels = { fulltime: 'Full-time', parttime: 'Part-time', staff: 'Staff', utility: 'Utility' };
      let current = null;   // dataset of the employee the modals are about

      function fillText(el, value, fallback = '—') {
        el.textContent = value || fallback;
        el.classList.toggle('ml-muted', !value);
      }

      function openDetails(row) {
        current = row.dataset;
        $('detailsAvatar').textContent = current.initials;
        $('detailsAvatar').className = `ml-avatar type-${current.typeKey}`;
        $('detailsName').textContent = current.name;
        $('detailsType').textContent = current.typeLabel || typeLabels[current.typeKey];
        $('detailsType').className = `ml-badge ml-badge-type type-${current.typeKey}`;
        fillText($('detailsDesignation'), current.designation);
        fillText($('detailsAdded'), current.added);

        const email = $('detailsEmail');
        email.replaceChildren();
        if (current.email) {
          const link = document.createElement('a');
          link.href = `mailto:${current.email}`;
          link.textContent = current.email;
          email.append(link);
          email.classList.remove('ml-muted');
        } else {
          fillText(email, '');
        }

        const dept = $('detailsDepartment');
        dept.replaceChildren();
        if (current.department) {
          const badge = document.createElement('span');
          badge.className = `ml-badge ml-badge-dept ${current.deptClass}`;
          badge.textContent = current.department;
          dept.append(badge);
          dept.classList.remove('ml-muted');
        } else {
          fillText(dept, '');
        }

        fillText($('detailsRate'), current.rate ? `${current.rate} / ${current.rateUnit === 'hr' ? 'hour' : 'day'}` : '');
        $('detailsEdit').href = current.editUrl;
        detailsModal.show();
      }

      // ── Delete modal ──────────────────────────────────
      const deleteBtn = $('deleteConfirm');
      const deleteError = $('deleteError');

      function openDelete(data) {
        current = data;
        $('deleteName').textContent = data.name;
        $('deleteType').textContent = (typeLabels[data.typeKey] || 'employee').toLowerCase();
        deleteError.classList.remove('is-visible');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete employee';
        deleteModal.show();
      }

      $('detailsDelete').addEventListener('click', () => {
        const data = current;
        $('detailsModal').addEventListener('hidden.bs.modal', () => openDelete(data), { once: true });
        detailsModal.hide();
      });

      deleteBtn.addEventListener('click', async () => {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Deleting…';
        deleteError.classList.remove('is-visible');

        try {
          const response = await fetch(deleteUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ ids: [Number(current.id)], type: current.typeKey }),
          });
          const result = await response.json().catch(() => ({}));
          if (!response.ok || !result.success) {
            throw new Error(result.message || 'The server could not delete this employee. Please try again.');
          }
          try { sessionStorage.setItem(flashKey, `${current.name} was removed from the master list.`); } catch (e) {}
          window.location.reload();
        } catch (error) {
          deleteError.textContent = error.message;
          deleteError.classList.add('is-visible');
          deleteBtn.disabled = false;
          deleteBtn.textContent = 'Try again';
        }
      });

      // ── Row interactions ──────────────────────────────
      tableEl.querySelector('tbody').addEventListener('click', event => {
        const row = event.target.closest('tr[data-id]');
        if (!row) return;
        if (event.target.closest('.js-delete')) { openDelete(row.dataset); return; }
        if (event.target.closest('a')) return;   // edit link and mailto behave normally
        if (event.target.closest('.js-view') || !event.target.closest('button')) openDetails(row);
      });

      // ── Toast (after a delete reload, or a flash from the server) ──
      let flash = @json(session('success'));
      try {
        flash = sessionStorage.getItem(flashKey) || flash;
        sessionStorage.removeItem(flashKey);
      } catch (e) {}
      if (flash) {
        $('mlToastText').textContent = flash;
        bootstrap.Toast.getOrCreateInstance('#mlToast').show();
      }

      applyFilters();
    })();
  </script>
</body>
</html>
