{{-- resources/views/partials/employees-menu.blade.php --}}
{{-- Single source of truth for the sidebar "Employees" dropdown. --}}
{{-- Every admin sidebar (layouts/admin, layouts/sidebar, and the inline copies --}}
{{-- in admin/dashboard, admin/deductions/index, evaluation/evaluation) includes --}}
{{-- this partial, so the six categories can never drift out of sync again. --}}
{{-- Add a new employee category HERE and it shows up everywhere. --}}

@php
  $employeeCategories = [
    'teaching' => [
      'label' => 'Teaching',
      'items' => [
        ['route' => 'fulltime.index',  'match' => 'fulltime.*',        'icon' => 'bi-person-badge',     'label' => 'Full-Time Instructors'],
        ['route' => 'parttime.index',  'match' => 'parttime.*',        'icon' => 'bi-person-check',     'label' => 'Part-Time Instructors'],
      ],
    ],
    'non-teaching' => [
      'label' => 'Non-Teaching',
      'items' => [
        ['route' => 'staff.index',           'match' => 'staff.*',           'icon' => 'bi-person-workspace', 'label' => 'Staff'],
        ['route' => 'utility.index',         'match' => 'utility.*',         'icon' => 'bi-tools',            'label' => 'Utility Workers'],
        ['route' => 'watchman.index',        'match' => 'watchman.*',        'icon' => 'bi-shield-lock',      'label' => 'Watchman'],
        ['route' => 'admin-personnel.index', 'match' => 'admin-personnel.*', 'icon' => 'bi-person-gear',      'label' => 'Admin Personnel'],
      ],
    ],
  ];

  $employeeMatches = array_merge(...array_map(
    fn($group) => array_column($group['items'], 'match'),
    array_values($employeeCategories)
  ));
  $employeesActive = request()->routeIs(...$employeeMatches);
@endphp

<div class="dropdown employees-menu">
  <button class="sidebar-btn dropdown-toggle {{ $employeesActive ? 'active' : '' }}" type="button"
    data-bs-toggle="dropdown" aria-expanded="false">
    <i class="bi bi-people"></i><span>Employees</span>
  </button>

  <ul class="dropdown-menu employees-menu-list">
    @foreach ($employeeCategories as $group)
      @if (!$loop->first)
        <li><hr class="dropdown-divider my-1"></li>
      @endif
      <li class="employees-menu-group">{{ $group['label'] }}</li>
      @foreach ($group['items'] as $item)
        <li>
          <a class="dropdown-item {{ request()->routeIs($item['match']) ? 'active' : '' }}"
            href="{{ route($item['route']) }}">
            <i class="bi {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
          </a>
        </li>
      @endforeach
    @endforeach
  </ul>
</div>

@once
  <style>
    /* Scoped to .employees-menu so it layers on top of whatever sidebar CSS
       the host page already defines instead of fighting it. */
    .employees-menu .employees-menu-list {
      min-width: 232px;
    }

    .employees-menu .employees-menu-group {
      padding: .45rem .8rem .25rem;
      font-size: .62rem;
      font-weight: 700;
      letter-spacing: .07em;
      text-transform: uppercase;
      opacity: .55;
      pointer-events: none;
    }

    .employees-menu .dropdown-item {
      display: flex;
      align-items: center;
      gap: 9px;
      border-radius: 7px;
      white-space: nowrap;
      transition: background .13s ease, color .13s ease, transform .13s ease;
    }

    .employees-menu .dropdown-item i {
      flex: 0 0 1rem;
      font-size: .95rem;
      text-align: center;
      opacity: .75;
    }

    .employees-menu .dropdown-item:hover,
    .employees-menu .dropdown-item:focus-visible {
      background: var(--brand-light, #eff6ff);
      color: var(--brand, #2563eb);
      transform: translateX(2px);
    }

    .employees-menu .dropdown-item:hover i,
    .employees-menu .dropdown-item.active i {
      opacity: 1;
    }

    .employees-menu .dropdown-item.active {
      background: var(--brand-light, #eff6ff);
      color: var(--brand, #2563eb);
      font-weight: 600;
    }

    .night-mode .employees-menu .dropdown-item:hover,
    .night-mode .employees-menu .dropdown-item:focus-visible,
    .night-mode .employees-menu .dropdown-item.active {
      background: rgba(37, 99, 235, .15);
    }
  </style>
@endonce
