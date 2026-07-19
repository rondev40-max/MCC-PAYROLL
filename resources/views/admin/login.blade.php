<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    {{-- reCAPTCHA REMOVED --}}
    <style>
        :root {
            --marlin-blue: #2563eb;
            --marlin-blue-hover: #1d4ed8;
            --marlin-blue-soft: #eaf1fe;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f4f6f9;
        }

        main {
            flex: 1;
            display: flex;
            min-height: 0;
        }

        .left-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .left-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
            filter: blur(6px) brightness(70%);
            transform: scale(1.05);
            z-index: -2;
        }

        .left-section::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.5), rgba(0, 0, 0, 0.55));
            z-index: -1;
        }

        .branding {
            max-width: 520px;
        }

        .branding h1 {
            font-size: 3.2rem;
            font-weight: 900;
            margin-bottom: 15px;
            text-shadow: 3px 3px 15px rgba(0, 0, 0, 0.7);
        }

        .branding p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0;
            opacity: 0.9;
        }

        .right-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f6f9;
            padding: 40px 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 12px 38px rgba(0, 0, 0, 0.25);
            text-align: center;
            max-width: 420px;
            width: 100%;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px auto;
            display: block;
            border-radius: 50%;
            border: 3px solid rgba(37, 99, 235, 0.4);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            object-fit: cover;
        }

        h2 {
            color: #243143;
            margin-bottom: 12px;
            font-size: 1.9rem;
        }

        .subtitle {
            color: #6c7a89;
            margin-bottom: 28px;
            font-size: 1rem;
        }

        /* Admin is a secondary role relative to Employee login, so the badge
           stays as a soft-tinted pill rather than a solid fill — it echoes
           the outlined button treatment below instead of competing with it. */
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--marlin-blue-soft);
            color: var(--marlin-blue);
            padding: 6px 18px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 0.6px;
            border: 1px solid rgba(37, 99, 235, 0.25);
        }

        .input-group {
            text-align: left;
            margin-bottom: 18px;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            color: #505f72;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .input-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dce4ec;
            border-radius: 10px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.25s ease;
        }

        .input-group.password-toggle {
            position: relative;
        }

        .input-group.password-toggle .toggle-password {
            position: absolute;
            right: 15px;
            top: calc(50% + 10px);
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }

        .input-group input:focus {
            border-color: var(--marlin-blue);
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        /* Secondary / outlined action — Administrator Login is not the most
           frequently used entry point (Employee Login is), so this button
           is a "ghost" button: white fill, Marlin Blue border + text, and
           it fills solid on hover instead of starting solid. */
        button {
            width: 100%;
            padding: 15px;
            background: #ffffff;
            border: 2px solid var(--marlin-blue);
            border-radius: 10px;
            color: var(--marlin-blue);
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        button:hover {
            background: var(--marlin-blue);
            border-color: var(--marlin-blue);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .back-link {
            margin-top: 24px;
            font-size: 0.95rem;
        }

        .back-link a {
            color: var(--marlin-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            padding: 12px;
            background: transparent;
            color: #fff;
            font-size: 0.9rem;
            z-index: 1000;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
        }

        footer span {
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .branding h1 { font-size: 2.6rem; }
            .branding p  { font-size: 1rem; }
        }

        @media (max-width: 768px) {
            main { flex-direction: column; }
            .left-section { min-height: 32vh; }
            .branding h1 { font-size: 2.2rem; }
            .right-section { padding: 25px 15px 40px; }
            .container { padding: 30px 22px; border-radius: 18px; }
            button { font-size: 1rem; }
        }

        @media (max-width: 480px) {
            .left-section { min-height: 28vh; }
            .branding h1 { font-size: 1.9rem; }
            .container { padding: 26px 18px; border-radius: 16px; }
            .logo { width: 70px; height: 70px; }
            h2 { font-size: 1.7rem; }
        }

        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <main>
        <div class="left-section">
            <div class="branding">
                <h1>MCC Digital Payroll With Real-Time Analytics</h1>
            </div>
        </div>

        <div class="right-section">
            <div class="container">
                <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="logo">
                <div class="admin-badge">
                    <i class="fas fa-user-shield"></i>
                    <span>Administrator Login</span>
                </div>
                <h2>Welcome Back</h2>
                <p class="subtitle">Enter your credentials to access the administrator dashboard.</p>

                {{-- Plain form — no reCAPTCHA, no OTP, direct submit --}}
                <form action="{{ route('admin.login') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_type" value="admin">

                    <div class="input-group">
                        <label for="email">Admin Email</label>
                        <input type="email" id="email" name="email"
                               placeholder="Enter admin email" required
                               value="{{ old('email') }}">
                    </div>

                    <div class="input-group password-toggle">
                        <label for="password">Admin Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Enter admin password" required>
                        <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                    </div>

                    <button id="adminLoginBtn" type="submit">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login as Admin</span>
                    </button>

                    <p class="back-link">
                        <a href="{{ url('/') }}">
                            <i class="fas fa-arrow-left"></i>
                            Back to Main Selection
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </main>

    <footer>
        &copy; {{ date('Y') }} MCC Payroll Management System. All Rights Reserved.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const adminBtn     = document.querySelector('#adminLoginBtn');
            const togglePassword = document.getElementById('togglePassword');
            const lockTime     = 60;
            const maxAttempts  = 3;
            const marlinBlue   = '#2563eb';

            // Restore lockout state if still active
            const lockedUntil = localStorage.getItem('adminLockedUntil');
            if (lockedUntil && Date.now() < parseInt(lockedUntil)) {
                disableButtonWithCountdown(adminBtn, Math.ceil((parseInt(lockedUntil) - Date.now()) / 1000));
            }

            // Success flash (after redirect back from controller)
            @if(session('success'))
                localStorage.removeItem('adminAttempts');
                localStorage.removeItem('adminLockedUntil');

                const successMessage = "{{ session('success') }}";
                let titleText     = 'Welcome Back!';
                let timerDuration = 2000;

                if (successMessage.includes('Super Admin')) {
                    titleText     = 'Welcome Super Admin!';
                    timerDuration = 3000;
                } else if (successMessage.includes('Dashboard') || successMessage.includes('Redirecting')) {
                    titleText     = 'Welcome Administrator!';
                    timerDuration = 3000;
                }

                Swal.fire({
                    icon: 'success',
                    title: titleText,
                    text: successMessage,
                    confirmButtonColor: marlinBlue,
                    timer: timerDuration,
                    showConfirmButton: false
                });
            @endif

            // Error flash
            @if(session('error'))
                let errorMsg  = '{{ session('error') }}';
                let attempts  = parseInt(localStorage.getItem('adminAttempts') || '0');
                const stillLocked = lockedUntil && Date.now() < parseInt(lockedUntil);

                if (!stillLocked) {
                    attempts++;
                    localStorage.setItem('adminAttempts', attempts);
                }

                if (attempts >= maxAttempts) {
                    const unlockTime = Date.now() + lockTime * 1000;
                    localStorage.setItem('adminLockedUntil', unlockTime);
                    localStorage.setItem('adminAttempts', 0);
                    Swal.fire({
                        icon: 'error',
                        title: 'Too Many Attempts!',
                        html: `You have reached <b>${maxAttempts}</b> failed attempts.<br>Please wait <b>${lockTime}</b> seconds before trying again.`,
                        allowOutsideClick: false,
                        confirmButtonColor: marlinBlue,
                    });
                    disableButtonWithCountdown(adminBtn, lockTime);
                } else {
                    let title = 'Access Denied!';
                    if (errorMsg.includes('User not yet registered'))         title = 'User Not Yet Registered';
                    else if (errorMsg.includes('Password is wrong'))          title = 'Login Failed';
                    else if (errorMsg.includes('Access denied'))              title = 'Unauthorized Role';

                    Swal.fire({
                        icon: 'error',
                        title: title,
                        text: errorMsg,
                        confirmButtonText: 'Try Again',
                        confirmButtonColor: marlinBlue,
                        background: '#fff',
                        color: '#333',
                    });
                }
            @endif

            // Countdown lockout helper
           function disableButtonWithCountdown(button, seconds) {
    if (!button) return;
    button.disabled = true;
    button.style.opacity = "0.6";
    button.style.cursor = "not-allowed";
    const originalText = button.innerHTML;

    // ✅ Show immediately, don't wait 1 second
    button.innerHTML = `<i class="fas fa-clock"></i> Locked (${seconds}s)`;

    const interval = setInterval(() => {
        seconds--;
        if (seconds <= 0) {                  // ✅ check BEFORE updating text
            clearInterval(interval);
            button.disabled = false;
            button.style.opacity = "1";
            button.style.cursor = "pointer";
            button.innerHTML = originalText;
            localStorage.removeItem('attendanceLockedUntil');
            return;
        }
        button.innerHTML = `<i class="fas fa-clock"></i> Locked (${seconds}s)`;
    }, 1000);
}

            // Password show/hide toggle
            if (togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const input = document.getElementById('password');
                    const type  = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });

        // DevTools detection
        devtools.detect(function (status) {
            if (status) {
                document.body.innerHTML = '<div style="background:white;width:100vw;height:100vh;position:fixed;top:0;left:0;z-index:9999;"></div>';
            }
        });
    </script>
</body>
</html>