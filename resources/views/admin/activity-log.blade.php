<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Activity Log</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root{
            --brand:#3498db;
            --brand-600:#2980b9;
            --muted:#f5f6f8;
            --card:#ffffff;
            --text:#111111;
        }
        .night-mode {
            --brand:#222831;
            --brand-600:#393e46;
            --muted:#18191a;
            --card:#c5c8ce;
            --text:#ffffff;
        }
        body{ background:var(--muted); color:var(--text); transition:background .3s, color .3s; }
        .app{ min-height:100vh; }
        .sidebar{
            background: linear-gradient(180deg, var(--brand), var(--brand-600));
            color:#fff; width:260px; position:sticky; top:0; height:100vh; padding:1.25rem 1rem;
            box-shadow: 0 10px 25px rgba(52,152,219,.25);
        }
        .sidebar .nav-link{
            color:#e3f2fd; border-radius:.75rem; padding:.6rem .8rem; font-weight:500;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active{
            background:#fff; color:var(--brand-600);
        }
        .sidebar .section-title{ font-size:.8rem; text-transform:uppercase; opacity:.85; margin:.9rem .5rem .3rem; }
        .content{ flex:1; }
        .topbar{ background:var(--card); border-bottom:1px solid #f0f0f0; padding:.75rem 1rem; position:sticky; top:0; z-index:1020; }
        .logout-icon{ font-size:1.4rem; color:var(--brand); }
        .logout-icon:hover{ color:#85c1e9; }
        .card-soft{ background:var(--card); border:1px solid #f0f0f0; border-radius:1rem; box-shadow:0 8px 20px rgba(0,0,0,.03); }

        @media (max-width: 992px){
            .sidebar{ position:fixed; transform:translateX(-100%); transition:.25s; z-index:1030; }
            .sidebar.show{ transform:none; }
            .content{ margin-left:0!important; }
            }
            .sidebar, #sidebarMobileBtn, .sidebar-overlay { display: none !important; }
            .sidebar-shift { margin-left: 0 !important; }
            </style>
            @include('layouts.sidebar-styles')
            </head>
            <body>
            @include('layouts.sidebar')
            <div class="sidebar-shift w-100">
            <div class="app d-flex">
            <div class="content w-100">
            {{-- TOPBAR --}}
            <div class="topbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0">System Activity Log</h5>
                </div>
                
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div class="user-welcome d-none d-md-block">
                        <h6 class="mb-0 text-muted">Welcome, <span class="user-name">{{ Auth::user()->name ?? 'User' }}</span></h6>
                    </div>
                    <a href="{{ route('logout') }}" 
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right logout-icon"></i>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf 
                    </form>
                </div>
            </div>

            <div class="container-fluid py-4">
                <div class="card-soft p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Recent Activities</h5>
                        <a href="{{ route('admin.user-management') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to User Management
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Target</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td>
                                            @if ($activity->causer)
                                                <div class="fw-bold">{{ $activity->causer->name }}</div>
                                                <small class="text-muted">{{ $activity->causer->email }}</small>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $activity->event === 'created' ? 'success' : ($activity->event === 'deleted' ? 'danger' : 'primary') }}">
                                                {{ ucfirst($activity->event) }}
                                            </span>
                                            <span class="ms-1">{{ $activity->description }}</span>
                                        </td>
                                        <td>
                                            @if ($activity->subject)
                                                {{-- Example: App\Models\User -> User --}}
                                                {{ class_basename($activity->subject_type) }} ID: {{ $activity->subject->id }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-nowrap" title="{{ $activity->created_at->toDateTimeString() }}">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="bi bi-moon-stars fs-3 text-muted"></i>
                                            <h5 class="mt-2">No activity recorded yet.</h5>
                                            <p class="text-muted">Perform some actions like creating or updating a user to see logs here.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>