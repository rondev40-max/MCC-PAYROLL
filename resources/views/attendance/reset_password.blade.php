<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Verify Code — Attendance Checker</title>
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
            --danger: #dc2626;
            --border-input: #dce1ea;
            --radius-sm: 12px;
            --radius-lg: 22px;
            --radius-pill: 999px;
            --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        html { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

        body {
            font-family: var(--font);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: ""; position: fixed; inset: 0;
            background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
            filter: blur(3px) brightness(0.32) saturate(1.1);
            transform: scale(1.05); z-index: -2;
        }
        body::after {
            content: ""; position: fixed; inset: 0;
            background: linear-gradient(180deg, rgba(12,17,28,0.4), rgba(12,17,28,0.62));
            z-index: -1;
        }

        .back-link {
            position: fixed; top: 24px; left: clamp(16px, 4vw, 40px); z-index: 50;
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.7);
            text-decoration: none; padding: 8px 14px; border-radius: var(--radius-pill);
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(10px); transition: all 0.2s ease;
        }
        .back-link:hover { color: #fff; background: rgba(255,255,255,0.15); }
        .back-link svg { width: 15px; height: 15px; }

        .card {
            width: 100%; max-width: 440px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: var(--radius-lg);
            box-shadow: 0 24px 48px rgba(0,0,0,0.22);
            padding: 40px 36px 30px;
            text-align: center;
            animation: cardIn 0.5s ease-out both;
        }
        @keyframes cardIn { from { opacity:0; transform: translateY(14px) scale(0.98);} to { opacity:1; transform: translateY(0) scale(1);} }

        .logo { width: 60px; height: 60px; margin: 0 auto 14px; display: block; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-ring); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--accent-soft); color: var(--accent);
            border: 1px solid var(--accent-ring);
            padding: 5px 14px; border-radius: var(--radius-pill);
            font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
            margin-bottom: 16px;
        }
        .badge svg { width: 12px; height: 12px; }
        h1 { font-size: 1.4rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; }
        .subtitle { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.55; margin-bottom: 6px; }
        .subtitle strong { color: var(--text-primary); }
        .sent-to { font-size: 0.85rem; color: var(--accent); font-weight: 600; margin-bottom: 22px; word-break: break-all; }

        .otp-label { display: block; font-size: 0.8rem; font-weight: 600; text-align: left; margin-bottom: 10px; }
        .otp-boxes { display: flex; gap: 10px; justify-content: space-between; margin-bottom: 6px; }
        .otp-boxes input {
            width: 100%; aspect-ratio: 1 / 1; min-width: 0;
            text-align: center; font-family: var(--font);
            font-size: 1.5rem; font-weight: 700; color: var(--text-primary);
            background: #f8fafc; border: 1.5px solid var(--border-input);
            border-radius: var(--radius-sm); outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .otp-boxes input:focus { border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-ring); }
        .otp-boxes input.filled { border-color: var(--accent); background: #fff; }
        .otp-boxes.error input { border-color: var(--danger); background: #fef2f2; }
        .otp-boxes.error { animation: shake 0.4s ease; }
        @keyframes shake { 0%,100%{transform:translateX(0);} 25%{transform:translateX(-6px);} 75%{transform:translateX(6px);} }

        .btn {
            width: 100%; margin-top: 18px; padding: 13px;
            font-family: var(--font); font-size: 0.94rem; font-weight: 650;
            color: #fff; background: var(--accent); border: none;
            border-radius: var(--radius-sm); cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .btn:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(2,132,199,0.32); }
        .btn:active { transform: translateY(0); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn svg { width: 17px; height: 17px; }
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .links { margin-top: 22px; padding-top: 18px; border-top: 1px solid rgba(0,0,0,0.08); font-size: 0.85rem; color: var(--text-secondary); display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; align-items: center; }
        .resend-btn { background: none; border: none; color: var(--accent); font-family: var(--font); font-size: 0.85rem; font-weight: 650; cursor: pointer; padding: 0; }
        .resend-btn:disabled { color: var(--text-tertiary); cursor: not-allowed; }
        .resend-btn:not(:disabled):hover { text-decoration: underline; }
        .links .divider { color: var(--text-tertiary); }
        .links a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .links a:hover { text-decoration: underline; }

        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; padding: 14px 16px; font-size: 0.74rem; color: rgba(255,255,255,0.4); pointer-events: none; }

        @media (max-width: 420px) {
            .card { padding: 32px 22px 24px; }
            .otp-boxes { gap: 7px; }
            .otp-boxes input { font-size: 1.25rem; }
        }
        @media (prefers-reduced-motion: reduce) { .card, .otp-boxes.error { animation: none; } .btn:hover { transform: none; } }
    </style>
</head>
<body>
    <a href="{{ route('attendance.attendlog.form') }}" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Login
    </a>

    <div class="card">
        <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="logo">
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
            Identity Verification
        </div>
        <h1>Enter your code</h1>
        <p class="subtitle">We sent a 6-digit verification code to</p>
        <p class="sent-to">{{ $email ?? 'your email' }}</p>

        <form action="{{ route('attendance.verify') }}" method="POST" id="otpForm" autocomplete="off">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">

            <span class="otp-label">One-time code</span>
            <div class="otp-boxes @error('otp') error @enderror" id="otpBoxes" role="group" aria-label="6-digit verification code">
                <input type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="1" aria-label="Digit 1" autofocus>
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 2">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 3">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 4">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 5">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 6">
            </div>
            <input type="hidden" name="otp" id="otpValue" value="{{ old('otp') }}">

            <button type="submit" class="btn" id="verifyBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span id="btnText">Verify code</span>
            </button>
        </form>

        <!-- Resend re-requests a fresh code from the forgot-password endpoint -->
        <form action="{{ route('attendance.forgot.send') }}" method="POST" id="resendForm" style="display:none;">
            @csrf
            <input type="hidden" name="email" value="{{ $email ?? '' }}">
        </form>

        <div class="links">
            <span>Didn't get it?</span>
            <button type="button" class="resend-btn" id="resendBtn">Resend code</button>
            <span class="divider">·</span>
            <a href="{{ route('attendance.forgot.form') }}">Use a different email</a>
        </div>
    </div>

    <div class="page-footer">&copy; {{ date('Y') }} Madridejos Community College</div>

    <script>
    (function () {
        const boxes     = Array.from(document.querySelectorAll('#otpBoxes input'));
        const hidden    = document.getElementById('otpValue');
        const boxesWrap = document.getElementById('otpBoxes');
        const form      = document.getElementById('otpForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const btnText   = document.getElementById('btnText');

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
            verifyBtn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Verifying…';
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
                if (e.key === 'Backspace' && !this.value && i > 0) { focusBox(i - 1); boxes[i - 1].value = ''; sync(); }
                else if (e.key === 'ArrowLeft' && i > 0) { e.preventDefault(); focusBox(i - 1); }
                else if (e.key === 'ArrowRight' && i < boxes.length - 1) { e.preventDefault(); focusBox(i + 1); }
            });
            box.addEventListener('paste', function (e) { e.preventDefault(); distribute((e.clipboardData || window.clipboardData).getData('text'), i); });
            box.addEventListener('focus', function () { this.select(); });
        });

        // Pre-fill from a bounced-back value WITHOUT auto-submitting, so a
        // rejected code doesn't resubmit itself in a loop on page reload.
        (function prefill() {
            const digits = (hidden.value || '').replace(/\D/g, '').slice(0, boxes.length).split('');
            digits.forEach((d, k) => { boxes[k].value = d; });
            sync();
        })();

        form.addEventListener('submit', function (e) {
            if (sync().length !== boxes.length) {
                e.preventDefault();
                boxesWrap.classList.add('error');
                focusBox(boxes.findIndex(b => !b.value));
                setTimeout(() => boxesWrap.classList.remove('error'), 500);
                return;
            }
            verifyBtn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Verifying…';
        });

        // ── Resend cooldown ──
        const resendBtn = document.getElementById('resendBtn');
        const resendForm = document.getElementById('resendForm');
        const COOLDOWN = 45, KEY = 'attendanceOtpResendUntil';
        function startCooldown(until) {
            const tick = () => {
                const left = Math.ceil((until - Date.now()) / 1000);
                if (left <= 0) { resendBtn.disabled = false; resendBtn.textContent = 'Resend code'; localStorage.removeItem(KEY); clearInterval(t); return; }
                resendBtn.disabled = true; resendBtn.textContent = `Resend in ${left}s`;
            };
            tick(); const t = setInterval(tick, 1000);
        }
        const saved = parseInt(localStorage.getItem(KEY) || '0', 10);
        if (saved && Date.now() < saved) startCooldown(saved);
        resendBtn.addEventListener('click', function () {
            if (!resendForm.querySelector('input[name="email"]').value) {
                window.location.href = "{{ route('attendance.forgot.form') }}";
                return;
            }
            const until = Date.now() + COOLDOWN * 1000;
            localStorage.setItem(KEY, String(until));
            resendForm.submit();
        });

        // ── Session flash via SweetAlert ──
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Invalid Code', text: @json(session('error')), confirmButtonColor: '#0284c7' });
        @endif
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Code Sent', text: @json(session('success')), confirmButtonColor: '#0284c7', timer: 2500, showConfirmButton: false });
        @endif
    })();
    </script>
</body>
</html>
