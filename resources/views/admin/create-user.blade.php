<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create New Admin</title>
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
    body{ background:var(--muted); color:var(--text); }
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
    .card-soft{ background:var(--card); border:1px solid #f0f0f0; border-radius:1rem; box-shadow:0 8px 20px rgba(0,0,0,.03); }

    .sidebar-btn {
      background-color: #3498db; color: white; text-align: left; margin-bottom: 5px; border: none; width: 100%; padding: 8px 12px; border-radius: 5px; transition: 0.3s;
    }
    .sidebar-btn:hover, .sidebar-btn:focus, .sidebar-btn.active {
      background-color: white; color: #3498db; border: 1px solid #3498db;
    }

    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .password-strength {
        height: 5px;
        margin-top: 5px;
    }
  </style>
</head>
<body>
  <div class="app d-flex">
    <div class="content w-100">
      <div class="topbar d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Create New Admin User</h5>
        <a href="{{ url('/logout') }}" class="logout-icon" title="Log out">
            <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>

      <div class="container-fluid py-4">
        <div class="card-soft p-4 mx-auto" style="max-width: 700px;">
          <h5 class="mb-3">New Administrator Details</h5>

          @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
          @endif

          <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="admin">

            <div class="mb-3">
              <label for="name" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">
                        Min 12 chars, with uppercase, lowercase, number, and special character.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.user-management') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill"></i> Create Admin
                </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Mobile sidebar toggle
      const mobileMenuBtn = document.getElementById('mobileMenuBtn');
      const sidebar = document.getElementById('sidebar');
      if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', function () {
          sidebar.classList.toggle('show');
        });
      }

      // Logout confirmation
      const logoutBtn = document.getElementById('logoutBtn');
      if(logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
          e.preventDefault();
          Swal.fire({
            title: 'Confirm Logout',
            text: "Are you sure you want to log out?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3498db',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Log Out'
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = this.href;
            }
          });
        });
      }
    });
  </script>
</body>
</html>