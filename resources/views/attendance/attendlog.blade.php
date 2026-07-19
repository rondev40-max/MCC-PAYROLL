<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Attendance Login</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    {{-- FIX #3: Corrected broken reCAPTCHA script tag. The original had the Blade
         expression inside the attribute quotes, which split the src attribute early
         and prevented the reCAPTCHA library from loading at all. --}}
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <style>
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
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.5), rgba(0, 0, 0, 0.55));
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
            border: 3px solid rgba(52, 152, 219, 0.4);
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

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #3498db;
            color: white;
            padding: 6px 18px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 0.6px;
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
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.15);
        }

        button {
            width: 100%;
            padding: 15px;
            background: #3498db;
            border: none;
            border-radius: 10px;
            color: white;
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
            background: #217dbb;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.35);
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .links {
            margin-top: 16px;
            font-size: 0.95rem;
        }

        .links a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .back-link {
            margin-top: 24px;
            font-size: 0.95rem;
        }

        .back-link a {
            color: #3498db;
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
                <h1>MCC Payroll System</h1>
            </div>
        </div>

        <div class="right-section">
            <div class="container">
                <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="logo">
                <div class="role-badge">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Attendance Login</span>
                </div>
                <h2>Welcome Back</h2>
                <p class="subtitle">Provide your credentials to continue to the attendance dashboard.</p>

                <form id="attendance-login-form" action="{{ route('attendance.attendlog') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_type" value="attendance">
                    <input type="hidden" name="g-recaptcha-response" id="recaptcha_token">
                    <div class="input-group">
                        <label for="email">Attendance Email</label>
                        <input type="email" id="email" name="email"
                               placeholder="Enter attendance email"
                               required
                               value="{{ old('email') }}">
                    </div>
                    <div class="input-group password-toggle">
                        <label for="password">Attendance Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Enter attendance password"
                               required>
                        <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                    </div>
                    <button id="login-btn" type="submit">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login as Attendance</span>
                    </button>
                    <p class="links">
                        <a href="{{ route('attendance.forgot.form') }}">Forgot password?</a>
                    </p>
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
        &copy; {{ date('Y') }} <span>MCC Payroll Management System</span>. All Rights Reserved.
    </footer>

    <script>
        // ─────────────────────────────────────────────────────────────────────
        // FIX #3: reCAPTCHA script tag is now correctly formed (see <head>),
        // so grecaptcha.ready() will fire as expected.
        // ─────────────────────────────────────────────────────────────────────
        grecaptcha.ready(function () {
    const form     = document.getElementById('attendance-login-form');
    const loginBtn = document.getElementById('login-btn');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        loginBtn.disabled = true;
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verifying...</span>';

        @if(app()->environment('local'))
            {{-- Skip reCAPTCHA in local dev --}}
            document.getElementById('recaptcha_token').value = 'dev-bypass';
            HTMLFormElement.prototype.submit.call(form);
        @else
            grecaptcha
                .execute('{{ config('services.recaptcha.site_key') }}', { action: 'login' })
                .then(function (token) {
                    document.getElementById('recaptcha_token').value = token;
                    HTMLFormElement.prototype.submit.call(form);
                })
                .catch(function () {
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> <span>Login as Attendance</span>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: 'reCAPTCHA verification failed. Please try again.',
                        confirmButtonColor: '#3498db',
                    });
                });
        @endif
    });
});

        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const loginBtn       = document.getElementById('login-btn');
            const lockTime       = 60;   // seconds
            const maxAttempts    = 3;

            // Parse localStorage values to integers before any numeric comparison.
            const lockedUntilRaw = localStorage.getItem('attendanceLockedUntil');
            const lockedUntil    = lockedUntilRaw ? parseInt(lockedUntilRaw, 10) : null;

            // Restore countdown if user reloads while still locked.
            if (lockedUntil && Date.now() < lockedUntil) {
                disableButtonWithCountdown(loginBtn, Math.ceil((lockedUntil - Date.now()) / 1000));
            }

            // ─── Error Handling ───────────────────────────────────────────────
            const errorMsg         = @json(session('error'));
            const validationErrors = @json($errors->all());
            const errorOccurred    = errorMsg || validationErrors.length > 0;
            const message          = errorMsg || (validationErrors.length > 0 ? validationErrors.join('<br>') : null);

            if (errorOccurred) {
                let attempts = parseInt(localStorage.getItem('attendanceAttempts') || '0', 10);

                // Only increment when not already inside a lock window.
                if (!lockedUntil || Date.now() > lockedUntil) {
                    attempts++;
                    localStorage.setItem('attendanceAttempts', String(attempts));
                }

                if (attempts >= maxAttempts) {
                    const unlockTime = Date.now() + lockTime * 1000;
                    localStorage.setItem('attendanceLockedUntil', String(unlockTime));
                    localStorage.setItem('attendanceAttempts', '0');

                    Swal.fire({
                        icon: 'error',
                        title: 'Too Many Attempts!',
                        html: `You have reached <b>${maxAttempts}</b> failed attempts.<br>Please wait <b>${lockTime}</b> seconds before trying again.`,
                        allowOutsideClick: false,
                        confirmButtonColor: '#3498db',
                    });
                    disableButtonWithCountdown(loginBtn, lockTime);

                } else {
                    let title = 'Login Failed';
                    if (message && message.includes('User not yet registered')) {
                        title = 'User Not Registered';
                    } else if (message && (message.includes('Password is wrong') || message.includes('credentials do not match'))) {
                        title = 'Incorrect Credentials';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: title,
                        html: message,
                        confirmButtonText: 'Try Again',
                        confirmButtonColor: '#3498db',
                        showClass: { popup: 'animate__animated animate__shakeX' },
                        hideClass: { popup: 'animate__animated animate__fadeOut' },
                    });
                }
            }

            // ─── Successful Login ─────────────────────────────────────────────
            @if(session('success'))
                localStorage.removeItem('attendanceAttempts');
                localStorage.removeItem('attendanceLockedUntil');
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: @json(session('success')),
                    confirmButtonColor: '#3498db',
                    timer: 2000,
                    showConfirmButton: false,
                });
            @endif

            // ─── Countdown Function ───────────────────────────────────────────
            // Renders initial text immediately (no blank first-second gap on
            // page restore), decrements first inside the tick, and clears the
            // interval before touching the DOM at expiry to avoid a "0s" flash.
            function disableButtonWithCountdown(button, seconds) {
                if (!button) return;
                button.disabled      = true;
                button.style.opacity = '0.6';
                button.style.cursor  = 'not-allowed';
                const originalText   = button.innerHTML;

                button.innerHTML = `<i class="fas fa-clock"></i> Locked (${seconds}s)`;

                const interval = setInterval(() => {
                    seconds--;

                    if (seconds <= 0) {
                        clearInterval(interval);
                        button.disabled      = false;
                        button.style.opacity = '1';
                        button.style.cursor  = 'pointer';
                        button.innerHTML     = originalText;
                        localStorage.removeItem('attendanceLockedUntil');
                        return;
                    }

                    button.innerHTML = `<i class="fas fa-clock"></i> Locked (${seconds}s)`;
                }, 1000);
            }

            // ─── Password Toggle ──────────────────────────────────────────────
            if (togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const passwordInput = document.getElementById('password');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });

        // ─────────────────────────────────────────────────────────────────────
        // NOTE (FIX #4 — server-side change required):
        // The localStorage-based rate limiting above is a UX convenience only.
        // It is trivially bypassed by clearing storage or using incognito mode.
        // Server-side throttling is already implemented in AttendanceController
        // using session-based lockout. For stronger protection, also apply the
        // built-in throttle middleware to the login route:
        //
        //   Route::post('/attendance/login', ...)->middleware('throttle:3,1');
        // ─────────────────────────────────────────────────────────────────────
    </script>
</body>
</html>