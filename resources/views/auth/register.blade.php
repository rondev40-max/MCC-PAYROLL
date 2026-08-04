<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Create Account — MCC Payroll</title>
    <meta name="description" content="Register for the MCC Employee Portal to access payslips, attendance, and timesheets.">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #ffffff;
            --bg-page: #f3f5f9;
            --bg-glass: rgba(255, 255, 255, 0.72);
            --bg-glass-strong: rgba(255, 255, 255, 0.88);

            --text-primary: #0f1729;
            --text-secondary: #5a6478;
            --text-tertiary: #8892a4;
            --text-on-dark: #ffffff;

            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --accent-soft: rgba(37, 99, 235, 0.06);
            --accent-ring: rgba(37, 99, 235, 0.12);

            --border: rgba(0, 0, 0, 0.08);
            --border-input: #dce1ea;
            --border-input-focus: var(--accent);

            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --danger-text: #b91c1c;

            --strength-weak: #dc2626;
            --strength-fair: #d97706;
            --strength-good: #16a34a;
            --strength-strong: #2563eb;

            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-pill: 999px;

            --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-sm: 0 2px 6px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.08);
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
            padding: 40px 0;
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

        /* ===================== REGISTER CARD ===================== */
        .register-card {
            width: 100%;
            max-width: 440px;
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
        .register-header {
            text-align: center;
            margin-bottom: 26px;
        }

        .register-logo {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(37, 99, 235, 0.15);
            box-shadow: var(--shadow-sm);
            margin-bottom: 14px;
        }

        .register-badge {
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
            margin-bottom: 12px;
        }
        .register-badge svg { width: 12px; height: 12px; }

        .register-header h1 {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .register-header p {
            font-size: 0.86rem;
            color: var(--text-secondary);
        }

        /* ===================== VALIDATION ERRORS ===================== */
        .validation-errors {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 0.82rem;
            line-height: 1.6;
        }

        /* ===================== FORM ===================== */
        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .input-wrapper { position: relative; }

        .input-wrapper input,
        .input-wrapper select {
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
            appearance: none;
        }

        .input-wrapper input::placeholder { color: var(--text-tertiary); }

        .input-wrapper input:focus,
        .input-wrapper select:focus {
            border-color: var(--border-input-focus);
            box-shadow: var(--shadow-input-focus);
            background: #fff;
        }

        .input-wrapper.is-invalid input,
        .input-wrapper.is-invalid select { border-color: var(--danger-text); }

        .field-error {
            font-size: 0.78rem;
            color: var(--danger-text);
            margin-top: 5px;
        }

        /* Input icon (visual anchor, non-toggle fields) */
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

        /* Select chevron */
        .select-chevron {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-tertiary);
            display: flex;
            align-items: center;
            pointer-events: none;
        }
        .select-chevron svg { width: 15px; height: 15px; }

        /* Password toggle */
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

        /* ===================== PASSWORD STRENGTH ===================== */
        .password-strength-bar-container {
            height: 5px;
            background-color: var(--border-input);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 9px;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .strength-weak { background-color: var(--strength-weak); }
        .strength-fair { background-color: var(--strength-fair); }
        .strength-good { background-color: var(--strength-good); }
        .strength-strong { background-color: var(--strength-strong); }

        .strength-text {
            font-size: 0.76rem;
            color: var(--text-tertiary);
            margin-top: 6px;
            display: block;
        }

        /* ===================== TERMS ===================== */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 20px;
            margin-bottom: 22px;
        }
        .terms-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            accent-color: var(--accent);
            cursor: pointer;
            flex-shrink: 0;
        }
        .terms-row label {
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-secondary);
            line-height: 1.5;
            cursor: pointer;
        }
        .terms-row a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: underline;
        }
        .terms-row a:hover { color: var(--accent-hover); }

        /* ===================== SUBMIT BUTTON ===================== */
        .btn-register {
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
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2);
        }

        .btn-register:hover:not(:disabled) {
            background: var(--accent-hover);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }
        .btn-register:active:not(:disabled) { transform: translateY(0); }

        .btn-register:disabled {
            background: #b0bec5;
            color: #f1f3f5;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-register:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        .btn-register svg { width: 17px; height: 17px; }

        /* ===================== FOOTER ===================== */
        .register-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }
        .register-footer p { font-size: 0.82rem; color: var(--text-secondary); }
        .register-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .register-footer a:hover { color: var(--accent-hover); text-decoration: underline; }

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

        /* Custom SweetAlert */
        .swal2-popup { border-radius: 15px !important; font-family: var(--font); }
        .swal2-title { color: var(--text-primary) !important; }
        .swal2-html-container {
            max-height: 60vh;
            overflow-y: auto;
            text-align: left;
            line-height: 1.6;
            color: var(--text-secondary);
            padding: 0 1em 1em 1em;
        }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 480px) {
            .register-card { margin: 16px; padding: 28px 22px 24px; border-radius: 16px; }
            .register-header h1 { font-size: 1.2rem; }
            .register-logo { width: 48px; height: 48px; }
            .back-link { top: 16px; left: 16px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .register-card { animation: none; }
        }
    </style>
</head>
<body>

    <!-- ===================== BACK LINK ===================== -->
    <a href="{{ url('/') }}" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Home
    </a>

    <!-- ===================== REGISTER CARD ===================== -->
    <div class="register-card">

        <div class="register-header">
            <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="register-logo">
            <div class="register-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                Employee Portal
            </div>
            <h1>Create your account</h1>
            <p>Register to access the employee portal</p>
        </div>

        @if ($errors->any())
            <div class="validation-errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="/register" method="POST" id="registerForm">
            @csrf

            <div class="form-group">
                <label for="name">Full name</label>
                <div class="input-wrapper @error('name') is-invalid @enderror">
                    <input type="text" id="name" name="name" placeholder="Juan Dela Cruz" value="{{ old('name') }}" required autofocus>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                    </span>
                </div>
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <div class="input-wrapper @error('email') is-invalid @enderror">
                    <input type="email" id="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required autocomplete="email">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                </div>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper @error('password') is-invalid @enderror">
                    <input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                    <button type="button" class="toggle-pw" id="togglePw" title="Show or hide password" aria-label="Toggle password visibility">
                        <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
                <div class="password-strength-bar-container">
                    <div id="password-strength-bar" class="password-strength-bar"></div>
                </div>
                <small id="password-strength-text" class="strength-text">Password strength: Very Weak</small>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm password</label>
                <div class="input-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter your password" required autocomplete="new-password">
                    <button type="button" class="toggle-pw" id="toggleConfirmPw" title="Show or hide password" aria-label="Toggle password visibility">
                        <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <div class="input-wrapper @error('role') is-invalid @enderror">
                    <select name="role" id="role" required onchange="toggleCourseField()">
                        <option value="">Select role</option>
                        <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="attendance_checker" {{ old('role') == 'attendance_checker' ? 'selected' : '' }}>Attendance Checker</option>
                    </select>
                    <span class="select-chevron">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                </div>
                @error('role')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" id="course-group" style="display: {{ old('role') == 'attendance_checker' ? 'block' : 'none' }}">
                <label for="course">Department / course</label>
                <div class="input-wrapper @error('course') is-invalid @enderror">
                    <select name="course" id="course">
                        <option value="">Select department</option>
                        <option value="staff" {{ old('course') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="utility" {{ old('course') == 'utility' ? 'selected' : '' }}>Utility</option>
                        <option value="bsit" {{ old('course') == 'bsit' ? 'selected' : '' }}>BSIT</option>
                        <option value="bsba" {{ old('course') == 'bsba' ? 'selected' : '' }}>BSBA</option>
                        <option value="bshm" {{ old('course') == 'bshm' ? 'selected' : '' }}>BSHM</option>
                        <option value="bsed" {{ old('course') == 'bsed' ? 'selected' : '' }}>BSED</option>
                        <option value="beed" {{ old('course') == 'beed' ? 'selected' : '' }}>BEED</option>
                    </select>
                    <span class="select-chevron">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                </div>
                @error('course')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="terms-row">
                <input type="checkbox" id="terms" name="terms">
                <label for="terms">
                    I agree to the
                    <a href="javascript:void(0);" id="terms-link">Terms and Conditions</a>
                </label>
            </div>

            <button type="submit" class="btn-register" id="register-btn" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                <span id="btnText">Create account</span>
            </button>

            <div class="register-footer">
                <p>Already have an account? <a href="/">Sign in here</a></p>
            </div>
        </form>
    </div>

    <!-- ===================== PAGE FOOTER ===================== -->
    <div class="page-footer">
        &copy; {{ date('Y') }} Madridejos Community College
    </div>

    <script>
        // --- PASSWORD VISIBILITY TOGGLES ---
        document.getElementById('togglePw').addEventListener('click', function () {
            const pw = document.getElementById('password');
            const isHidden = pw.type === 'password';
            pw.type = isHidden ? 'text' : 'password';
            this.classList.toggle('visible', isHidden);
        });

        document.getElementById('toggleConfirmPw').addEventListener('click', function () {
            const pw = document.getElementById('password_confirmation');
            const isHidden = pw.type === 'password';
            pw.type = isHidden ? 'text' : 'password';
            this.classList.toggle('visible', isHidden);
        });
        // --- END PASSWORD VISIBILITY TOGGLES ---

        // --- TOGGLE COURSE FIELD FUNCTION ---
        function toggleCourseField() {
            const roleSelect = document.getElementById('role');
            const courseGroup = document.getElementById('course-group');

            if (roleSelect.value === 'attendance_checker') {
                courseGroup.style.display = 'block';
            } else {
                courseGroup.style.display = 'none';
                document.getElementById('course').value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', toggleCourseField);
        document.getElementById('role').addEventListener('change', toggleCourseField);
        // --- END TOGGLE COURSE FIELD FUNCTION ---

        // --- PASSWORD STRENGTH CHECK ---
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');

        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                const password = passwordInput.value;
                let score = 0;

                if (password.length >= 12) score++;
                if (/[a-z]/.test(password)) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/\d/.test(password)) score++;
                if (/[@$!%*?&]/.test(password)) score++;

                const maxScore = 5;
                const percentage = (score / maxScore) * 100;
                strengthBar.style.width = percentage + '%';

                if (score === 0) {
                    strengthBar.className = 'password-strength-bar strength-weak';
                    strengthText.textContent = 'Password strength: Very Weak';
                } else if (score <= 2) {
                    strengthBar.className = 'password-strength-bar strength-weak';
                    strengthText.textContent = 'Password strength: Weak';
                } else if (score <= 3) {
                    strengthBar.className = 'password-strength-bar strength-fair';
                    strengthText.textContent = 'Password strength: Fair';
                } else if (score <= 4) {
                    strengthBar.className = 'password-strength-bar strength-good';
                    strengthText.textContent = 'Password strength: Good';
                } else {
                    strengthBar.className = 'password-strength-bar strength-strong';
                    strengthText.textContent = 'Password strength: Strong';
                }
            });
        }
        // --- END PASSWORD STRENGTH CHECK ---

        // --- TERMS AND CONDITIONS POPUP ---
        document.getElementById('terms-link').addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we fetch the terms and conditions.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch("{{ url('/terms') }}")
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok. Status: ' + response.status);
                    }
                    return response.text();
                })
                .then(html => {
                    Swal.fire({
                        icon: 'info',
                        title: '<strong>Terms and Conditions</strong>',
                        html: html,
                        width: '80%',
                        confirmButtonText: 'Got it!',
                        confirmButtonColor: '#2563eb',
                        showClass: { popup: 'animate__animated animate__fadeInDown' },
                        hideClass: { popup: 'animate__animated animate__fadeOutUp' }
                    });
                })
                .catch(error => {
                    console.error('Error fetching terms and conditions:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Load Content',
                        text: 'The terms and conditions could not be loaded at this time. Please check your connection or try again later.'
                    });
                });
        });
        // --- END TERMS AND CONDITIONS POPUP ---

        // --- REGISTER BUTTON STATE ---
        const termsCheckbox = document.getElementById('terms');
        const registerBtn = document.getElementById('register-btn');

        if (termsCheckbox && registerBtn) {
            function updateRegisterBtnState() {
                registerBtn.disabled = !termsCheckbox.checked;
            }
            termsCheckbox.addEventListener('change', updateRegisterBtnState);
            updateRegisterBtnState();
        }
        // --- END REGISTER BUTTON STATE ---

        // --- SUBMIT LOADING STATE ---
        document.getElementById('registerForm').addEventListener('submit', function () {
            const btn = document.getElementById('register-btn');
            const text = document.getElementById('btnText');
            btn.disabled = true;
            text.textContent = 'Creating account…';
        });
        // --- END SUBMIT LOADING STATE ---

        // --- DEVTOOLS DETECTION ---
        devtools.detect(function (status) {
            if (status) {
                document.body.innerHTML = '<div style="background: white; width: 100vw; height: 100vh; position: fixed; top: 0; left: 0; z-index: 9999;"></div>';
            }
        });
        // --- END DEVTOOLS DETECTION ---
    </script>
</body>
</html>