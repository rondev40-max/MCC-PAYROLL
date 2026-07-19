<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Portal Login - MCC Payroll</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            /* Blue marlin token system: abyss (deep dorsal) → navy → brand blue
               → flash (the iridescent cyan a marlin's flank throws off when
               it's lit up hunting) → belly (silver-white underside). */
            --marlin-abyss: #061529;
            --marlin-navy: #0f2f66;
            --marlin-blue: #2563eb;
            --marlin-blue-hover: #1d4ed8;
            --marlin-flash: #38bdf8;
            --marlin-belly: #f8fafc;
            --ink: #1f2937;
            --muted: #6b7280;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f7;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(6, 21, 41, 0.25);
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            max-width: 940px;
            width: 100%;
        }

        /* ── Left panel: the marlin's water ──
           Deep abyss at the top fading down through navy into brand blue,
           with a flash of cyan at the corner — the same vertical gradient
           a marlin's body runs, dark dorsal ridge to lit-up flank. */
        .login-left {
            position: relative;
            overflow: hidden;
            color: white;
            padding: 55px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            isolation: isolate;
            background:
                radial-gradient(circle at 85% 90%, rgba(56, 189, 248, 0.35), transparent 55%),
                linear-gradient(165deg, var(--marlin-abyss) 0%, var(--marlin-navy) 45%, var(--marlin-blue) 100%);
        }

        /* Hunting stripes: a marlin flashes faint vertical bars down its
           flank when it's actively chasing bait — used here at low opacity
           as the panel's texture instead of a stock pattern. */
        .login-left::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(
                90deg,
                rgba(255,255,255,0.05) 0px,
                rgba(255,255,255,0.05) 2px,
                transparent 2px,
                transparent 34px
            );
            z-index: 0;
        }

        .login-left > * { position: relative; z-index: 1; }

        .fin-badge {
            width: 58px; height: 58px;
            margin-bottom: 22px;
        }

        .login-left h1 {
            font-family: 'Outfit', 'Segoe UI', sans-serif;
            font-size: 2.15rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            margin-bottom: 14px;
            line-height: 1.15;
        }

        .login-left p {
            font-size: 1rem;
            color: #cfe0f7;
            line-height: 1.7;
            max-width: 34ch;
        }

        .feature-list {
            list-style: none;
            margin-top: 28px;
            text-align: left;
        }

        .feature-list li {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.9rem; margin-bottom: 11px; color: #dce8fa;
        }

        .feature-list li::before {
            content: "";
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--marlin-flash);
            box-shadow: 0 0 8px 1px rgba(56, 189, 248, 0.8);
            flex-shrink: 0;
        }

        /* Signature element: a single line-art marlin, mid-leap, anchored
           to the bottom-right of the panel. It drifts very slightly on a
           loop — the one animated flourish on the page. */
        .marlin-mark {
            position: absolute;
            bottom: -18px;
            right: -30px;
            width: 260px;
            height: auto;
            opacity: 0.22;
            z-index: 0;
            animation: marlin-drift 7s ease-in-out infinite;
        }

        @keyframes marlin-drift {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50%      { transform: translate(-6px, -10px) rotate(-2deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            .marlin-mark { animation: none; }
        }

        /* ── Right panel: the boat deck — clean, quiet, functional ── */
        .login-right {
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-section { text-align: center; margin-bottom: 25px; }

        .logo {
            width: 72px; height: 72px;
            border-radius: 50%;
            border: 3px solid rgba(37, 99, 235, 0.3);
            object-fit: cover;
            margin-bottom: 12px;
            display: block; margin-left: auto; margin-right: auto;
        }

        .login-right h2 {
            font-family: 'Outfit', 'Segoe UI', sans-serif;
            color: var(--ink);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 6px;
            text-align: center;
        }
        .subtitle { color: var(--muted); font-size: 0.9rem; margin-bottom: 28px; text-align: center; }

        .form-group { margin-bottom: 18px; position: relative; }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 7px;
            font-size: 0.9rem;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 42px 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--marlin-blue);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            bottom: 13px;
            cursor: pointer;
            color: #999;
            font-size: 1rem;
            line-height: 1;
            user-select: none;
        }

        .toggle-pw:hover { color: var(--marlin-blue); }

        .row-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .checkbox-group { display: flex; align-items: center; gap: 8px; }
        .checkbox-group input  { width: auto; cursor: pointer; accent-color: var(--marlin-blue); }
        .checkbox-group label  { margin-bottom: 0; cursor: pointer; font-weight: 400; font-size: 0.88rem; }

        .forgot-link {
            color: var(--marlin-blue);
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
        }
        .forgot-link:hover { color: var(--marlin-blue-hover); text-decoration: underline; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--marlin-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-login:hover   { background: var(--marlin-blue-hover); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4); }
        .btn-login:active  { transform: translateY(0); }
        .btn-login:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .btn-login:focus-visible { outline: 3px solid var(--marlin-flash); outline-offset: 2px; }

        .login-footer { text-align: center; margin-top: 18px; color: var(--muted); font-size: 0.88rem; }
        .login-footer a { color: var(--marlin-blue); text-decoration: none; font-weight: 600; }
        .login-footer a:hover { color: var(--marlin-blue-hover); text-decoration: underline; }

        .back-link {
            display: inline-flex; align-items: center; gap: 5px;
            color: var(--marlin-blue); text-decoration: none; font-weight: 500;
            margin-bottom: 18px; font-size: 0.88rem;
        }
        .back-link:hover { color: var(--marlin-blue-hover); }

        .validation-errors {
            background: #fff0f0;
            border: 1px solid #fcc;
            color: #c0392b;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .loading-spinner {
            display: inline-block;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s ease-in-out infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 768px) {
            .login-container { grid-template-columns: 1fr; }
            .login-left { padding: 40px 30px; min-height: 220px; }
            .login-left h1 { font-size: 1.7rem; }
            .feature-list { display: none; }
            .marlin-mark { width: 180px; }
            .login-right { padding: 35px 25px; }
        }

        @media (max-width: 480px) {
            body { padding: 10px; }
            .login-container { border-radius: 16px; }
            .login-left { padding: 30px 22px; }
            .login-right { padding: 25px 20px; }
            .login-right h2 { font-size: 1.5rem; }
            .logo { width: 60px; height: 60px; }
        }
    </style>
</head>
<body>
    <div class="login-container">

        {{-- ── Left branding panel ── --}}
        <div class="login-left">
            <svg class="fin-badge" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M32 6C32 6 40 22 40 36C40 45 36.5 52 32 58C27.5 52 24 45 24 36C24 22 32 6 32 6Z"
                      fill="rgba(56,189,248,0.18)" stroke="#7dd3fc" stroke-width="1.6" stroke-linejoin="round"/>
                <path d="M32 14C32 14 35.5 24 35.5 34" stroke="#e0f2fe" stroke-width="1.2" stroke-linecap="round" opacity="0.6"/>
            </svg>

            <h1>Employee Portal</h1>
            <p>Your personal hub for payroll, attendance, and workplace info.</p>
            <ul class="feature-list">
                <li>View and download your payslips</li>
                <li>Check attendance records</li>
                <li>Submit and track timesheets</li>
                <li>Read announcements</li>
            </ul>

            {{-- Signature element: line-art marlin, mid-leap --}}
            <svg class="marlin-mark" viewBox="0 0 300 160" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M8 118 C 60 60, 120 40, 300 30" stroke="white" stroke-width="1.4" fill="none" opacity="0.9"/>
                <path d="M8 118
                         C 30 100, 55 84, 95 80
                         C 150 74, 195 84, 230 62
                         L 300 30
                         L 226 76
                         C 210 92, 190 100, 168 104
                         C 130 112, 90 112, 62 128
                         C 46 136, 24 132, 8 118 Z"
                      fill="white" opacity="0.85"/>
                <path d="M95 80 C 88 52, 78 32, 60 18 C 82 24, 100 42, 108 66 Z" fill="white" opacity="0.85"/>
                <path d="M62 128 C 44 132, 26 146, 12 158 C 26 142, 30 130, 40 120 Z" fill="white" opacity="0.85"/>
                <path d="M168 104 C 172 116, 182 126, 196 130 C 180 128, 166 120, 158 108 Z" fill="white" opacity="0.85"/>
                <circle cx="242" cy="52" r="2.4" fill="#061529" opacity="0.7"/>
            </svg>
        </div>

        {{-- ── Right form panel ── --}}
        <div class="login-right">
            <a href="/" class="back-link">← Back to Home</a>

            <div class="logo-section">
                <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="logo">
            </div>

            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to your employee account</p>

            {{--
                FIX: Removed inline session error/success divs — feedback shown via SweetAlert only.
                Validation errors ($errors) stay inline since they're per-field and can be multiple.
            --}}
            @if ($errors->any())
                <div class="validation-errors">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- FIX: form action changed from route('login.submit') which POSTs to the admin root '/'
                       to route('employee.login') which correctly POSTs to /employee/login --}}
            <form action="{{ route('employee.login') }}" method="POST" id="loginForm">
                @csrf
                {{-- Tells LoginController::authenticate this is an employee login --}}
                <input type="hidden" name="user_type" value="employee">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <span class="toggle-pw" id="togglePw" title="Show/hide password">👁</span>
                </div>

                <div class="row-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    {{-- Link to a forgot-password page if you add one for employees --}}
                    {{-- <a href="{{ route('employee.forgot.form') }}" class="forgot-link">Forgot password?</a> --}}
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="btnText">Sign In</span>
                </button>

                <div class="login-footer">
                    <p>Don't have an account? <a href="{{ route('register.form') }}">Register here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: {!! json_encode(session('error')) !!},
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Try again'
    });
    @endif

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: {!! json_encode(session('success')) !!},
        confirmButtonColor: '#2563eb'
    });
    @endif

    @if(session('logout_success'))
    Swal.fire({
        icon: 'info',
        title: 'Logged out',
        text: {!! json_encode(session('logout_success')) !!},
        confirmButtonColor: '#2563eb',
        timer: 3000,
        timerProgressBar: true
    });
    @endif

    // Password visibility toggle
    document.getElementById('togglePw').addEventListener('click', function () {
        const pw = document.getElementById('password');
        const isHidden = pw.type === 'password';

        pw.type = isHidden ? 'text' : 'password';
        this.textContent = isHidden ? '🙈' : '👁';
    });

    // Disable button while submitting
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        const text = document.getElementById('btnText');

        btn.disabled = true;
        text.innerHTML = '<span class="loading-spinner"></span> Signing in...';
    });
</script>
</body>
</html>