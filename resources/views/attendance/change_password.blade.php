<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Attendance Checker</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    {{-- FIX: FA moved to <head> so icons are present on first paint --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/gh/nicolauns/devtools.detect@1.2.0/devtools-detect.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after {
            /* FIX: box-sizing so padding never causes overflow */
            box-sizing: border-box;
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

        /* ── Left branding panel ── */
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
            inset: 0;
            background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center / cover;
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

        .branding h1 {
            font-size: 3.2rem;
            font-weight: 900;
            margin-bottom: 15px;
            text-shadow: 3px 3px 15px rgba(0, 0, 0, 0.7);
        }

        /* ── Right form panel ── */
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
            margin: 0 auto 25px;
            display: block;
            border-radius: 50%;
            border: 3px solid rgba(52, 152, 219, 0.4);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            object-fit: cover;
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

        h2 {
            color: #243143;
            margin: 0 0 8px;
            font-size: 1.9rem;
        }

        .subtitle {
            color: #6c7a89;
            margin-bottom: 28px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .input-group {
            text-align: left;
            margin-bottom: 18px;
            position: relative;
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
            padding: 13px 44px 13px 14px; /* right padding for toggle icon */
            border: 1px solid #dce4ec;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.25s ease;
        }

        .input-group input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.15);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            bottom: 14px;
            cursor: pointer;
            color: #888;
            font-size: 0.95rem;
            line-height: 1;
        }

        .toggle-password:hover { color: #3498db; }

        /* Password strength indicator */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
            background: #e0e0e0;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0;
            border-radius: 2px;
            transition: width 0.3s ease, background 0.3s ease;
        }

        .strength-label {
            font-size: 0.78rem;
            margin-top: 4px;
            font-weight: 600;
            text-align: right;
        }

        button[type="submit"] {
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

        button[type="submit"]:hover {
            background: #217dbb;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.35);
        }

        button[type="submit"]:disabled {
            opacity: 0.65;
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

        .links a:hover { text-decoration: underline; }

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
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        }

        footer span { font-weight: 600; }

        @media (max-width: 768px) {
            main { flex-direction: column; }
            .left-section { min-height: 32vh; }
            .branding h1 { font-size: 2.2rem; }
            .right-section { padding: 25px 15px 40px; }
            .container { padding: 30px 22px; border-radius: 18px; }
        }

        @media (max-width: 480px) {
            .left-section { min-height: 28vh; }
            .branding h1 { font-size: 1.9rem; }
            .container { padding: 26px 18px; border-radius: 16px; }
        }
    </style>
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
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <span>Set New Password</span>
                </div>
                <h2>Change Password</h2>
                <p class="subtitle">Choose a strong password of at least 8 characters.</p>

                <form action="{{ route('attendance.reset.submit') }}" method="POST" id="change-password-form">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="input-group">
                        <label for="password">New Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Min. 8 characters"
                            minlength="8"
                            required
                            autofocus
                        >
                        <i class="fas fa-eye-slash toggle-password" id="togglePassword" aria-label="Toggle password visibility"></i>
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-label" id="strengthLabel" style="color:#aaa;">—</div>
                    </div>

                    <div class="input-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Repeat your password"
                            minlength="8"
                            required
                        >
                        <i class="fas fa-eye-slash toggle-password" id="toggleConfirm" aria-label="Toggle confirm visibility"></i>
                    </div>

                    <button type="submit" id="submit-btn">
                        <i class="fas fa-save" aria-hidden="true"></i>
                        Update Password
                    </button>
                </form>

                <div class="links">
                    <a href="{{ route('attendance.attendlog.form') }}">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        &copy; {{ date('Y') }} <span>MCC Payroll Management System</span>. All Rights Reserved.
    </footer>

    <script>
        // ── Password strength meter ──
        const passwordInput  = document.getElementById('password');
        const confirmInput   = document.getElementById('password_confirmation');
        const strengthFill   = document.getElementById('strengthFill');
        const strengthLabel  = document.getElementById('strengthLabel');
        const submitBtn      = document.getElementById('submit-btn');

        function getStrength(pw) {
            let score = 0;
            if (pw.length >= 8)  score++;
            if (pw.length >= 12) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            return score;
        }

        const levels = [
            { label: '—',       color: '#aaa',    width: '0%'   },
            { label: 'Weak',    color: '#e74c3c', width: '25%'  },
            { label: 'Fair',    color: '#f39c12', width: '50%'  },
            { label: 'Good',    color: '#3498db', width: '75%'  },
            { label: 'Strong',  color: '#27ae60', width: '90%'  },
            { label: 'Strong',  color: '#27ae60', width: '100%' },
        ];

        passwordInput.addEventListener('input', function () {
            const score = this.value.length === 0 ? 0 : Math.max(1, getStrength(this.value));
            const lv = levels[score] || levels[0];
            strengthFill.style.width      = lv.width;
            strengthFill.style.background = lv.color;
            strengthLabel.textContent     = lv.label;
            strengthLabel.style.color     = lv.color;
        });

        // ── Password toggle ──
        function makeToggle(toggleId, inputId) {
            document.getElementById(toggleId).addEventListener('click', function () {
                const inp = document.getElementById(inputId);
                const isPassword = inp.type === 'password';
                inp.type = isPassword ? 'text' : 'password';
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        makeToggle('togglePassword', 'password');
        makeToggle('toggleConfirm',  'password_confirmation');

        // ── Confirm match check on submit ──
        document.getElementById('change-password-form').addEventListener('submit', function (e) {
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords do not match',
                    text: 'Please make sure both password fields are identical.',
                    confirmButtonColor: '#3498db'
                });
                return;
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating…';
        });

        // ── Session messages via SweetAlert ──
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                confirmButtonColor: '#3498db'
            });
        @endif

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Password Updated!',
                text: @json(session('success')),
                confirmButtonColor: '#3498db'
            });
        @endif

        // ── DevTools detection ──
        devtools.detect(function(status) {
            if (status) {
                document.body.innerHTML = '<div style="background:white;width:100vw;height:100vh;position:fixed;top:0;left:0;z-index:9999;"></div>';
            }
        });
    </script>
</body>
</html>