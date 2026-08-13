<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Set New Password — Attendance Checker</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: #0284c7;
            --accent-hover: #0369a1;
            --accent-soft: rgba(2, 132, 199, 0.08);
            --accent-ring: rgba(2, 132, 199, 0.18);
            --text-primary: #0f1729;
            --text-secondary: #5a6478;
            --text-tertiary: #8892a4;
            --border-input: #dce1ea;
            --radius-sm: 12px;
            --radius-lg: 22px;
            --radius-pill: 999px;
            --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        html { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

        body {
            font-family: var(--font); color: var(--text-primary);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 24px; position: relative; overflow-x: hidden;
        }
        body::before {
            content: ""; position: fixed; inset: 0;
            background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
            filter: blur(3px) brightness(0.32) saturate(1.1); transform: scale(1.05); z-index: -2;
        }
        body::after {
            content: ""; position: fixed; inset: 0;
            background: linear-gradient(180deg, rgba(12,17,28,0.4), rgba(12,17,28,0.62)); z-index: -1;
        }

        .card {
            width: 100%; max-width: 440px;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: var(--radius-lg);
            box-shadow: 0 24px 48px rgba(0,0,0,0.22);
            padding: 40px 36px 30px; text-align: center;
            animation: cardIn 0.5s ease-out both;
        }
        @keyframes cardIn { from { opacity:0; transform: translateY(14px) scale(0.98);} to { opacity:1; transform: translateY(0) scale(1);} }

        .logo { width: 60px; height: 60px; margin: 0 auto 14px; display: block; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-ring); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--accent-soft); color: var(--accent); border: 1px solid var(--accent-ring);
            padding: 5px 14px; border-radius: var(--radius-pill);
            font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 16px;
        }
        .badge svg { width: 12px; height: 12px; }
        h1 { font-size: 1.4rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; }
        .subtitle { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.55; margin-bottom: 24px; }

        .field { text-align: left; margin-bottom: 16px; position: relative; }
        .field label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 7px; }
        .field input {
            width: 100%; padding: 12px 44px 12px 14px;
            font-family: var(--font); font-size: 0.92rem;
            border: 1.5px solid var(--border-input); border-radius: var(--radius-sm);
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }

        .toggle-pw {
            position: absolute; right: 12px; top: 34px; cursor: pointer;
            background: none; border: none; color: var(--text-tertiary); display: flex; padding: 4px;
        }
        .toggle-pw:hover { color: var(--accent); }
        .toggle-pw svg { width: 18px; height: 18px; }
        .toggle-pw .icon-off { display: none; }
        .toggle-pw.visible .icon-on { display: none; }
        .toggle-pw.visible .icon-off { display: block; }

        .strength-bar { height: 5px; border-radius: 3px; margin-top: 8px; background: #e5e9f0; overflow: hidden; }
        .strength-fill { height: 100%; width: 0; border-radius: 3px; transition: width 0.3s ease, background 0.3s ease; }
        .strength-label { font-size: 0.75rem; margin-top: 5px; font-weight: 600; text-align: right; color: var(--text-tertiary); }

        .btn {
            width: 100%; margin-top: 8px; padding: 13px;
            font-family: var(--font); font-size: 0.94rem; font-weight: 650;
            color: #fff; background: var(--accent); border: none; border-radius: var(--radius-sm);
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .btn:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(2,132,199,0.32); }
        .btn:active { transform: translateY(0); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn svg { width: 17px; height: 17px; }
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .links { margin-top: 22px; padding-top: 18px; border-top: 1px solid rgba(0,0,0,0.08); font-size: 0.85rem; }
        .links a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .links a:hover { text-decoration: underline; }

        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; padding: 14px 16px; font-size: 0.74rem; color: rgba(255,255,255,0.4); pointer-events: none; }

        @media (max-width: 420px) { .card { padding: 32px 22px 24px; } }
        @media (prefers-reduced-motion: reduce) { .card { animation: none; } .btn:hover { transform: none; } }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="logo">
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Set New Password
        </div>
        <h1>Create a new password</h1>
        <p class="subtitle">Choose a strong password with at least 8 characters. Mix in uppercase, numbers and symbols for extra strength.</p>

        <form action="{{ route('attendance.reset.submit') }}" method="POST" id="change-password-form" autocomplete="off">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="field">
                <label for="password">New password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 characters" minlength="8" required autofocus autocomplete="new-password">
                <button type="button" class="toggle-pw" id="togglePassword" aria-label="Toggle password visibility">
                    <svg class="icon-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="icon-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="strength-label" id="strengthLabel">—</div>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat your password" minlength="8" required autocomplete="new-password">
                <button type="button" class="toggle-pw" id="toggleConfirm" aria-label="Toggle confirm visibility">
                    <svg class="icon-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="icon-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>

            <button type="submit" class="btn" id="submit-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                <span id="btnText">Update password</span>
            </button>
        </form>

        <div class="links">
            <a href="{{ route('attendance.attendlog.form') }}">← Back to login</a>
        </div>
    </div>

    <div class="page-footer">&copy; {{ date('Y') }} Madridejos Community College</div>

    <script>
    (function () {
        const passwordInput = document.getElementById('password');
        const confirmInput  = document.getElementById('password_confirmation');
        const strengthFill  = document.getElementById('strengthFill');
        const strengthLabel = document.getElementById('strengthLabel');
        const submitBtn     = document.getElementById('submit-btn');
        const btnText       = document.getElementById('btnText');
        const form          = document.getElementById('change-password-form');

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
            { label: '—',      color: '#8892a4', width: '0%'   },
            { label: 'Weak',   color: '#dc2626', width: '25%'  },
            { label: 'Fair',   color: '#f59e0b', width: '50%'  },
            { label: 'Good',   color: '#0284c7', width: '75%'  },
            { label: 'Strong', color: '#16a34a', width: '90%'  },
            { label: 'Strong', color: '#16a34a', width: '100%' },
        ];
        passwordInput.addEventListener('input', function () {
            const score = this.value.length === 0 ? 0 : Math.max(1, getStrength(this.value));
            const lv = levels[score] || levels[0];
            strengthFill.style.width = lv.width;
            strengthFill.style.background = lv.color;
            strengthLabel.textContent = lv.label;
            strengthLabel.style.color = lv.color;
        });

        function makeToggle(btnId, inputId) {
            const btn = document.getElementById(btnId);
            btn.addEventListener('click', function () {
                const inp = document.getElementById(inputId);
                inp.type = inp.type === 'password' ? 'text' : 'password';
                this.classList.toggle('visible');
            });
        }
        makeToggle('togglePassword', 'password');
        makeToggle('toggleConfirm', 'password_confirmation');

        form.addEventListener('submit', function (e) {
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Passwords do not match', text: 'Please make sure both password fields are identical.', confirmButtonColor: '#0284c7' });
                return;
            }
            submitBtn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Updating…';
        });

        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonColor: '#0284c7' });
        @endif
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Password Updated', text: @json(session('success')), confirmButtonColor: '#0284c7', timer: 2500, showConfirmButton: false });
        @endif
    })();
    </script>
</body>
</html>
