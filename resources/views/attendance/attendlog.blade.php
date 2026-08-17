<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Attendance Login — MCC Payroll</title>
    <meta name="description" content="Attendance login for MCC Payroll Management System.">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Only pull in reCAPTCHA when a site key actually exists. Loading it with
         an empty render= parameter gives you a grecaptcha object that never
         becomes ready, which is what left the sign-in button stuck. --}}
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif
    <script src="{{ asset('js/recaptcha-login.js') }}" defer></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #ffffff;
            --bg-glass-strong: rgba(255, 255, 255, 0.88);

            --text-primary: #0f1729;
            --text-secondary: #5a6478;
            --text-tertiary: #8892a4;

            --accent: #0284c7; /* Sky blue/cyan color scheme for attendance */
            --accent-hover: #0369a1;
            --accent-soft: rgba(2, 132, 199, 0.06);
            --accent-ring: rgba(2, 132, 199, 0.12);

            --border: rgba(0, 0, 0, 0.08);
            --border-input: #dce1ea;
            --border-input-focus: var(--accent);

            --radius-sm: 10px;
            --radius-lg: 20px;
            --radius-pill: 999px;

            --shadow-sm: 0 2px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 24px 48px rgba(0, 0, 0, 0.12);
            --shadow-input-focus: 0 0 0 3px var(--accent-ring);

            --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        html {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            font-family: var(--font);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* ===================== BACKGROUND ===================== */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
            filter: blur(3px) brightness(0.3) saturate(1.1);
            transform: scale(1.04);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(12, 17, 28, 0.35) 0%,
                rgba(12, 17, 28, 0.55) 100%
            );
            z-index: -1;
        }

        /* ===================== BACK LINK ===================== */
        .back-link {
            position: fixed;
            top: 24px;
            left: clamp(16px, 4vw, 40px);
            z-index: 50;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
        }
        .back-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .back-link svg { width: 15px; height: 15px; }

        /* ===================== LOGIN CARD ===================== */
        .login-card {
            width: 100%;
            max-width: 420px;
            margin: 24px;
            padding: 40px 36px 36px;
            background: var(--bg-glass-strong);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: var(--shadow-lg);
            animation: cardIn 0.5s ease-out both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ===================== HEADER ===================== */
        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .login-logo {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(2, 132, 199, 0.15);
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--accent-soft);
            border: 1px solid var(--accent-ring);
            padding: 5px 12px;
            border-radius: var(--radius-pill);
        }
        .login-badge svg { width: 12px; height: 12px; }

        .login-header h1 {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .login-header p {
            font-size: 0.86rem;
            color: var(--text-secondary);
        }

        /* ===================== FORM ===================== */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 11px 42px 11px 14px;
            font-family: var(--font);
            font-size: 0.9rem;
            color: var(--text-primary);
            background: var(--bg-primary);
            border: 1.5px solid var(--border-input);
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            outline: none;
        }

        .input-wrapper input::placeholder {
            color: var(--text-tertiary);
        }

        .input-wrapper input:focus {
            border-color: var(--border-input-focus);
            box-shadow: var(--shadow-input-focus);
            background: #fff;
        }

        .input-icon {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            pointer-events: none;
        }
        .input-icon svg { width: 17px; height: 17px; }

        .toggle-pw {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            padding: 2px;
            border: none;
            background: none;
            transition: color 0.2s ease;
        }
        .toggle-pw:hover { color: var(--accent); }
        .toggle-pw svg { width: 17px; height: 17px; }
        .toggle-pw .icon-eye-off { display: none; }
        .toggle-pw.visible .icon-eye { display: none; }
        .toggle-pw.visible .icon-eye-off { display: block; }

        /* ===================== OPTIONS ROW ===================== */
        .options-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .forgot-link {
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--accent);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .forgot-link:hover { color: var(--accent-hover); text-decoration: underline; }

        /* ===================== SUBMIT BUTTON ===================== */
        .btn-login {
            width: 100%;
            padding: 12px 20px;
            font-family: var(--font);
            font-size: 0.9rem;
            font-weight: 650;
            color: #fff;
            background: var(--accent);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(2, 132, 199, 0.2);
        }

        .btn-login:hover {
            background: var(--accent-hover);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
            transform: translateY(-1px);
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-login:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        .btn-login svg { width: 17px; height: 17px; }

        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.7s ease-in-out infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===================== FOOTER ===================== */
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        .login-footer p {
            font-size: 0.82rem;
            color: var(--text-secondary);
        }

        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .login-footer a:hover { color: var(--accent-hover); text-decoration: underline; }

        /* ===================== PAGE FOOTER ===================== */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 14px 16px;
            font-size: 0.74rem;
            color: rgba(255, 255, 255, 0.4);
            pointer-events: none;
        }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 480px) {
            .login-card {
                margin: 16px;
                padding: 28px 22px 24px;
                border-radius: 16px;
            }
            .login-header h1 { font-size: 1.25rem; }
            .login-logo { width: 48px; height: 48px; }
            .login-logo-row { gap: 10px; }
            .back-link { top: 16px; left: 16px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-card { animation: none; }
        }
    </style>
</head>
<body>

    <!-- ===================== BACK LINK ===================== -->
    <a href="{{ url('/') }}" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Home
    </a>

    <!-- ===================== LOGIN CARD ===================== -->
    <div class="login-card">

        <div class="login-header">
            <div class="login-logo-row">
                <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="login-logo">
                <div class="login-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                    Attendance Checker
                </div>
            </div>
            <h1>Verification access</h1>
            <p>Sign in to record logs</p>
        </div>

        <form id="attendance-login-form" action="{{ route('attendance.attendlog') }}" method="POST"
              data-recaptcha-login
              data-recaptcha-site-key="{{ config('services.recaptcha.site_key') }}"
              data-recaptcha-action="login"
              data-busy-label="Signing in…">
            @csrf
            <input type="hidden" name="user_type" value="attendance">
            <input type="hidden" name="g-recaptcha-response" id="recaptcha_token">

            <div class="form-group">
                <label for="email">Attendance Email</label>
                <div class="input-wrapper">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="checker@mcc.edu.ph"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                    >
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-pw" id="togglePw" title="Show or hide password" aria-label="Toggle password visibility">
                        <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <div class="options-row">
                <a href="{{ route('attendance.forgot.form') }}" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login" id="login-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                <span id="btnText" data-btn-text>Sign in</span>
            </button>

            <div class="login-footer">
                <p><a href="{{ url('/') }}">← Back to portal selection</a></p>
            </div>
        </form>
    </div>

    <!-- ===================== PAGE FOOTER ===================== -->
    <div class="page-footer">
        &copy; {{ date('Y') }} Madridejos Community College
    </div>

    <script>
    /*
     * Submit handling now lives in public/js/recaptcha-login.js, shared with
     * the admin and employee sign-in pages so all three behave identically.
     * The form opts in with the data-recaptcha-login attributes above.
     */

    document.addEventListener('DOMContentLoaded', function () {
        const togglePw   = document.getElementById('togglePw');
        const loginBtn   = document.getElementById('login-btn');
        const lockTime   = 60;   // seconds
        const maxAttempts = 3;

        // Parse localStorage values
        const lockedUntilRaw = localStorage.getItem('attendanceLockedUntil');
        const lockedUntil    = lockedUntilRaw ? parseInt(lockedUntilRaw, 10) : null;

        // Restore countdown
        if (lockedUntil && Date.now() < lockedUntil) {
            disableButtonWithCountdown(loginBtn, Math.ceil((lockedUntil - Date.now()) / 1000));
        }

        // Password visibility toggle
        if (togglePw) {
            togglePw.addEventListener('click', function () {
                const pw = document.getElementById('password');
                const isHidden = pw.type === 'password';
                pw.type = isHidden ? 'text' : 'password';
                this.classList.toggle('visible', isHidden);
            });
        }

        // Error message flash & local rate limiting
        const errorMsg         = @json(session('error'));
        const validationErrors = @json($errors->all());
        const errorOccurred    = errorMsg || validationErrors.length > 0;
        const message          = errorMsg || (validationErrors.length > 0 ? validationErrors.join('<br>') : null);

        if (errorOccurred) {
            let attempts = parseInt(localStorage.getItem('attendanceAttempts') || '0', 10);

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
                    title: 'Too Many Attempts',
                    html: `You have reached <b>${maxAttempts}</b> failed attempts.<br>Please wait <b>${lockTime}</b> seconds before trying again.`,
                    allowOutsideClick: false,
                    confirmButtonColor: '#0284c7',
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
                    confirmButtonColor: '#0284c7',
                });
            }
        }

        // Success flash
        @if(session('success'))
            localStorage.removeItem('attendanceAttempts');
            localStorage.removeItem('attendanceLockedUntil');
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                confirmButtonColor: '#0284c7',
                timer: 2000,
                showConfirmButton: false,
            });
        @endif

        // Countdown lockout function
        function disableButtonWithCountdown(button, seconds) {
            if (!button) return;
            button.disabled = true;
            const originalHTML = button.innerHTML;

            button.querySelector('#btnText').textContent = `Locked (${seconds}s)`;

            const interval = setInterval(() => {
                seconds--;
                if (seconds <= 0) {
                    clearInterval(interval);
                    button.disabled = false;
                    button.innerHTML = originalHTML;
                    localStorage.removeItem('attendanceLockedUntil');
                    return;
                }
                button.querySelector('#btnText').textContent = `Locked (${seconds}s)`;
            }, 1000);
        }
    });
    </script>
</body>
</html>