<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — MCC Payroll</title>
    <meta name="description" content="Administrator login for MCC Payroll Management System.">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #ffffff;
            --bg-glass-strong: rgba(255, 255, 255, 0.88);

            --text-primary: #0f1729;
            --text-secondary: #5a6478;
            --text-tertiary: #8892a4;

            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --accent-soft: rgba(37, 99, 235, 0.06);
            --accent-ring: rgba(37, 99, 235, 0.12);

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
            border: 2px solid rgba(37, 99, 235, 0.15);
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
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
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
 
        .input-hint {
            display: block;
            margin-top: 8px;
            font-size: 0.78rem;
            color: var(--text-tertiary);
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

        /* ===================== SUBMIT BUTTON ===================== */
        .btn-login {
            width: 100%;
            padding: 12px 20px;
            margin-top: 6px;
            font-family: var(--font);
            font-size: 0.9rem;
            font-weight: 650;
            color: var(--accent);
            background: var(--bg-primary);
            border: 1.5px solid var(--accent);
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login:disabled {
            opacity: 0.55;
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
            border: 2px solid rgba(37, 99, 235, 0.25);
            border-radius: 50%;
            border-top-color: var(--accent);
            animation: spin 0.7s ease-in-out infinite;
        }
        .btn-login:hover .loading-spinner {
            border-color: rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
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
<body data-show-otp="{{ session('show_otp_modal') ? '1' : '0' }}" data-otp-info="{{ e(session('info','')) }}" data-otp-email="{{ e(old('email','')) }}" data-otp-error="{{ e($errors->first('otp')) }}">

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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5l-8-3Z"/></svg>
                    Administrator
                </div>
            </div>
            <h1>Admin access</h1>
            <p>Sign in to the management dashboard</p>
        </div>

        <form action="{{ route('admin.login') }}" method="POST" id="loginForm">
            @csrf
            <input type="hidden" name="user_type" value="admin">

            <div class="form-group">
                <label for="email">Email address</label>
                <div class="input-wrapper">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@mcc.edu.ph or admin@mcclawis.edu.ph"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                    >
                    <span class="input-hint">Use an MCC or MCC Lawis email address</span>
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

            <button type="submit" class="btn-login" id="loginBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                <span id="btnText">Sign in as Admin</span>
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
    document.addEventListener('DOMContentLoaded', function () {
        const loginBtn   = document.getElementById('loginBtn');
        const loginForm  = document.getElementById('loginForm');
        const togglePw   = document.getElementById('togglePw');
        const lockTime   = 60;
        const maxAttempts = 3;

        // Restore lockout state if still active
        const lockedUntil = localStorage.getItem('adminLockedUntil');
        if (lockedUntil && Date.now() < parseInt(lockedUntil)) {
            disableButtonWithCountdown(loginBtn, Math.ceil((parseInt(lockedUntil) - Date.now()) / 1000));
        }

        // Password toggle
        if (togglePw) {
            togglePw.addEventListener('click', function () {
                const pw = document.getElementById('password');
                const isHidden = pw.type === 'password';
                pw.type = isHidden ? 'text' : 'password';
                this.classList.toggle('visible', isHidden);
            });
        }

        // Submit spinner
        if (loginForm) {
            loginForm.addEventListener('submit', function () {
                loginBtn.disabled = true;
                document.getElementById('btnText').innerHTML = '<span class="loading-spinner"></span> Signing in…';
            });
        }

        // Success flash
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
                confirmButtonColor: '#2563eb',
                timer: timerDuration,
                showConfirmButton: false
            });
        @endif

        // Error flash with lockout
        @if(session('error'))
            let errorMsg  = {!! json_encode(session('error')) !!};
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
                    title: 'Too Many Attempts',
                    html: `You've reached <b>${maxAttempts}</b> failed attempts.<br>Please wait <b>${lockTime}</b> seconds.`,
                    allowOutsideClick: false,
                    confirmButtonColor: '#2563eb',
                });
                disableButtonWithCountdown(loginBtn, lockTime);
            } else {
                let title = 'Access Denied';
                if (errorMsg.includes('User not yet registered'))    title = 'User Not Found';
                else if (errorMsg.includes('Password is wrong'))     title = 'Wrong Password';
                else if (errorMsg.includes('Access denied'))         title = 'Unauthorized';

                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: errorMsg,
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#2563eb',
                });
            }
        @endif

        // Countdown lockout
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
                    localStorage.removeItem('adminLockedUntil');
                    return;
                }
                button.querySelector('#btnText').textContent = `Locked (${seconds}s)`;
            }, 1000);
        }
    });
    </script>

    <!-- ===================== OTP MODAL ===================== -->
    <style>
        .otp-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(8, 12, 22, 0.55);
            backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            z-index: 9999; padding: 20px;
            animation: otpFade 0.2s ease;
        }
        @keyframes otpFade { from { opacity: 0; } to { opacity: 1; } }
        .otp-modal {
            background: #fff; width: 100%; max-width: 420px;
            border-radius: 18px; padding: 30px 28px 24px;
            box-shadow: 0 24px 56px rgba(2, 6, 23, 0.45);
            text-align: center;
            animation: otpPop 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }
        @keyframes otpPop { from { opacity: 0; transform: translateY(12px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .otp-modal .otp-shield {
            width: 58px; height: 58px; margin: 0 auto 16px;
            border-radius: 50%; background: var(--accent-soft);
            border: 1px solid var(--accent-ring);
            display: flex; align-items: center; justify-content: center; color: var(--accent);
        }
        .otp-modal .otp-shield svg { width: 26px; height: 26px; }
        .otp-modal h3 { margin: 0 0 8px; font-size: 1.2rem; font-weight: 800; color: var(--text-primary); }
        .otp-modal p.otp-sub { margin: 0 0 22px; color: var(--text-secondary); font-size: 0.9rem; line-height: 1.5; }

        .otp-boxes { display: flex; gap: 9px; justify-content: space-between; margin-bottom: 6px; }
        .otp-boxes input {
            width: 100%; aspect-ratio: 1 / 1; min-width: 0;
            text-align: center; font-family: var(--font);
            font-size: 1.4rem; font-weight: 700; color: var(--text-primary);
            background: #f8fafc; border: 1.5px solid var(--border-input);
            border-radius: 11px; outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .otp-boxes input:focus { border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-ring); }
        .otp-boxes input.filled { border-color: var(--accent); background: #fff; }
        .otp-boxes.error input { border-color: #dc2626; background: #fef2f2; }
        .otp-boxes.error { animation: otpShake 0.4s ease; }
        @keyframes otpShake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }

        .otp-modal .actions { display: flex; gap: 10px; margin-top: 20px; }
        .otp-modal .btn { flex: 1; padding: 12px 14px; border-radius: 10px; border: none; cursor: pointer; font-family: var(--font); font-size: 0.9rem; font-weight: 650; display: inline-flex; align-items: center; justify-content: center; gap: 7px; transition: all 0.2s ease; }
        .otp-modal .btn-primary { background: var(--accent); color: #fff; }
        .otp-modal .btn-primary:hover { background: var(--accent-hover); box-shadow: 0 6px 16px rgba(37,99,235,0.3); }
        .otp-modal .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; box-shadow: none; }
        .otp-modal .btn-ghost { background: #f1f5f9; color: #374151; }
        .otp-modal .btn-ghost:hover { background: #e2e8f0; }
        .otp-modal .btn svg { width: 16px; height: 16px; }
        .otp-modal .otp-resend { margin: 18px 0 0; font-size: 0.85rem; color: var(--text-secondary); }
        .otp-modal .resend-btn { background: none; border: none; color: var(--accent); font-family: var(--font); font-weight: 650; font-size: 0.85rem; cursor: pointer; padding: 0; }
        .otp-modal .resend-btn:disabled { color: var(--text-tertiary); cursor: not-allowed; }
        .otp-modal .resend-btn:not(:disabled):hover { text-decoration: underline; }
        .otp-modal .otp-spinner { width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
    </style>

    <div id="otpModalOverlay" class="otp-modal-overlay" aria-hidden="true">
        <div class="otp-modal" role="dialog" aria-modal="true" aria-labelledby="otpTitle">
            <div class="otp-shield">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <h3 id="otpTitle">Enter verification code</h3>
            <p class="otp-sub">{{ session('info', 'Enter the 6-digit code sent to your registered email to finish signing in.') }}</p>

            <form id="otpModalForm" method="POST" action="{{ route('otp.verify') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="email" id="otpModalEmail" value="">

                <div class="otp-boxes" id="otpModalBoxes" role="group" aria-label="6-digit verification code">
                    <input type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="1" aria-label="Digit 1">
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 2">
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 3">
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 4">
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 5">
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 6">
                </div>
                <input type="hidden" name="otp" id="otpModalValue">

                <p id="otpModalError" role="alert" style="display:none; margin:12px 0 0; color:#dc2626; font-size:0.83rem; font-weight:500;"></p>

                <div class="actions">
                    <button type="button" id="otpCancel" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="otpSubmitBtn">
                        <span id="otpSubmitText">Verify &amp; continue</span>
                    </button>
                </div>
            </form>

            <p class="otp-resend">Didn't receive a code?
                <button type="button" class="resend-btn" id="otpResendBtn" data-href="{{ route('otp.resend') }}">Resend</button>
            </p>
        </div>
    </div>

    <script>
    (function () {
        const overlay   = document.getElementById('otpModalOverlay');
        const emailEl   = document.getElementById('otpModalEmail');
        const cancel    = document.getElementById('otpCancel');
        const form      = document.getElementById('otpModalForm');
        const boxesWrap = document.getElementById('otpModalBoxes');
        const boxes     = Array.from(boxesWrap.querySelectorAll('input'));
        const hidden    = document.getElementById('otpModalValue');
        const submitBtn = document.getElementById('otpSubmitBtn');
        const submitTxt = document.getElementById('otpSubmitText');

        function sync() {
            const code = boxes.map(b => b.value).join('');
            hidden.value = code;
            boxes.forEach(b => b.classList.toggle('filled', b.value !== ''));
            return code;
        }
        function focusBox(i) { if (i >= 0 && i < boxes.length) boxes[i].focus(); }

        function distribute(str, start) {
            const digits = str.replace(/\D/g, '').slice(0, boxes.length - start).split('');
            digits.forEach((d, k) => { boxes[start + k].value = d; });
            focusBox(Math.min(start + digits.length, boxes.length - 1));
            if (sync().length === boxes.length) maybeSubmit();
        }

        let submitting = false;
        function maybeSubmit() {
            if (submitting) return;
            submitting = true;
            submitBtn.disabled = true;
            submitTxt.innerHTML = '<span class="otp-spinner"></span> Verifying…';
            setTimeout(() => form.submit(), 150);
        }

        boxes.forEach((box, i) => {
            box.addEventListener('input', function () {
                if (this.value.length > 1) { distribute(this.value, i); return; }
                this.value = this.value.replace(/\D/g, '');
                if (this.value) focusBox(i + 1);
                if (sync().length === boxes.length) maybeSubmit();
            });
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && i > 0) {
                    focusBox(i - 1); boxes[i - 1].value = ''; sync();
                } else if (e.key === 'ArrowLeft' && i > 0) { e.preventDefault(); focusBox(i - 1); }
                else if (e.key === 'ArrowRight' && i < boxes.length - 1) { e.preventDefault(); focusBox(i + 1); }
            });
            box.addEventListener('paste', function (e) {
                e.preventDefault();
                distribute((e.clipboardData || window.clipboardData).getData('text'), i);
            });
            box.addEventListener('focus', function () { this.select(); });
        });

        form.addEventListener('submit', function (e) {
            if (sync().length !== boxes.length) {
                e.preventDefault();
                boxesWrap.classList.add('error');
                focusBox(boxes.findIndex(b => !b.value));
                setTimeout(() => boxesWrap.classList.remove('error'), 500);
                return;
            }
            submitBtn.disabled = true;
            submitTxt.innerHTML = '<span class="otp-spinner"></span> Verifying…';
        });

        function showModal(prefillEmail) {
            if (prefillEmail) emailEl.value = prefillEmail;
            overlay.style.display = 'flex';
            overlay.setAttribute('aria-hidden', 'false');
            // Double rAF guarantees the modal has painted before we focus,
            // otherwise focus() can silently no-op on the still-hidden input.
            requestAnimationFrame(() => requestAnimationFrame(() => focusBox(0)));
        }
        function hideModal() {
            overlay.style.display = 'none';
            overlay.setAttribute('aria-hidden', 'true');
        }

        cancel.addEventListener('click', hideModal);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) hideModal(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.style.display === 'flex') hideModal();
        });

        // ── Resend cooldown (persists across the resend page navigation) ──
        const resendBtn = document.getElementById('otpResendBtn');
        const COOLDOWN = 45, KEY = 'adminOtpResendUntil';
        function startCooldown(until) {
            const tick = () => {
                const left = Math.ceil((until - Date.now()) / 1000);
                if (left <= 0) { resendBtn.disabled = false; resendBtn.textContent = 'Resend'; localStorage.removeItem(KEY); clearInterval(t); return; }
                resendBtn.disabled = true; resendBtn.textContent = `Resend in ${left}s`;
            };
            tick(); const t = setInterval(tick, 1000);
        }
        const saved = parseInt(localStorage.getItem(KEY) || '0', 10);
        if (saved && Date.now() < saved) startCooldown(saved);
        resendBtn.addEventListener('click', function () {
            const until = Date.now() + COOLDOWN * 1000;
            localStorage.setItem(KEY, String(until));
            startCooldown(until);
            window.location.href = this.dataset.href;
        });

        // Open the OTP step when the server asks for it (fresh login) OR when a
        // submitted code was rejected — previously a wrong code bounced back to
        // a blank-looking login form with no feedback and the modal closed.
        const otpError = document.body.dataset.otpError || '';
        const showOtp = document.body.dataset.showOtp === '1'
            || (document.body.dataset.otpInfo && document.body.dataset.otpInfo.length > 0)
            || otpError.length > 0;

        if (showOtp) {
            showModal(document.body.dataset.otpEmail || '');
            if (otpError) {
                const errEl = document.getElementById('otpModalError');
                errEl.textContent = otpError;
                errEl.style.display = 'block';
                boxesWrap.classList.add('error');
                setTimeout(() => boxesWrap.classList.remove('error'), 500);
            }
        }
    })();
    </script>
</body>
</html>