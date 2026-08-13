<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Verify your login — MCC Payroll</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --accent-soft: rgba(37, 99, 235, 0.08);
            --accent-ring: rgba(37, 99, 235, 0.18);
            --text-primary: #0f1729;
            --text-secondary: #5a6478;
            --text-tertiary: #8892a4;
            --danger: #dc2626;
            --success: #15803d;
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
            content: "";
            position: fixed;
            inset: 0;
            background: url('{{ asset('images/mcc.jpg') }}') no-repeat center center/cover;
            filter: blur(3px) brightness(0.32) saturate(1.1);
            transform: scale(1.05);
            z-index: -2;
        }
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg, rgba(12,17,28,0.4), rgba(12,17,28,0.62));
            z-index: -1;
        }

        .otp-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-lg);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.22);
            padding: 40px 36px 32px;
            text-align: center;
            animation: cardIn 0.5s ease-out both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(14px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .shield {
            width: 68px;
            height: 68px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: var(--accent-soft);
            border: 1px solid var(--accent-ring);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
        }
        .shield svg { width: 30px; height: 30px; }

        h1 { font-size: 1.4rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; }
        .subtitle { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.55; margin-bottom: 24px; }
        .subtitle a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .subtitle a:hover { text-decoration: underline; }

        .flash {
            display: flex;
            align-items: center;
            gap: 9px;
            text-align: left;
            font-size: 0.86rem;
            padding: 11px 14px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .flash svg { width: 17px; height: 17px; flex-shrink: 0; }
        .flash-success { background: #dcfce7; color: var(--success); border: 1px solid #bbf7d0; }
        .flash-info { background: #e0edff; color: #1e40af; border: 1px solid #bfdbfe; }

        .field { text-align: left; margin-bottom: 20px; }
        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 7px;
        }
        .field input[type="email"] {
            width: 100%;
            padding: 12px 14px;
            font-family: var(--font);
            font-size: 0.92rem;
            border: 1.5px solid var(--border-input);
            border-radius: var(--radius-sm);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field input[type="email"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-ring);
        }

        /* ── Segmented OTP boxes ── */
        .otp-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: left;
            margin-bottom: 10px;
        }
        .otp-boxes {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .otp-boxes input {
            width: 100%;
            aspect-ratio: 1 / 1;
            min-width: 0;
            text-align: center;
            font-family: var(--font);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            background: #f8fafc;
            border: 1.5px solid var(--border-input);
            border-radius: var(--radius-sm);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .otp-boxes input:focus {
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 3px var(--accent-ring);
        }
        .otp-boxes input.filled {
            border-color: var(--accent);
            background: #fff;
        }
        .otp-boxes.error input {
            border-color: var(--danger);
            background: #fef2f2;
        }
        .otp-boxes.error { animation: shake 0.4s ease; }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--danger);
            font-size: 0.82rem;
            font-weight: 500;
            text-align: left;
            margin-top: 8px;
        }
        .error-message svg { width: 15px; height: 15px; flex-shrink: 0; }

        .btn-verify {
            width: 100%;
            margin-top: 18px;
            padding: 13px;
            font-family: var(--font);
            font-size: 0.94rem;
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
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .btn-verify:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(37,99,235,0.32); }
        .btn-verify:active { transform: translateY(0); }
        .btn-verify:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-verify svg { width: 17px; height: 17px; }

        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .footer-links {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid rgba(0,0,0,0.08);
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .resend-btn {
            background: none;
            border: none;
            color: var(--accent);
            font-family: var(--font);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
        }
        .resend-btn:disabled { color: var(--text-tertiary); cursor: not-allowed; }
        .resend-btn:not(:disabled):hover { text-decoration: underline; }
        .footer-links .divider { color: var(--text-tertiary); margin: 0 8px; }
        .footer-links a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }

        @media (max-width: 420px) {
            .otp-card { padding: 32px 22px 26px; }
            .otp-boxes { gap: 7px; }
            .otp-boxes input { font-size: 1.25rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .otp-card, .otp-boxes.error { animation: none; }
            .btn-verify:hover { transform: none; }
        }
    </style>
</head>
<body>
    @php $sessionMissing = !empty($sessionMissing) && $sessionMissing; @endphp

    <div class="otp-card">
        <div class="shield">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
        </div>

        <h1>Verify your login</h1>

        @if(session('message'))
            <div class="flash flash-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                <div>{{ session('message') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="flash flash-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        @if($sessionMissing)
            <p class="subtitle">
                Your verification session has timed out. Enter your registered email and the
                6-digit code we sent you. If you haven't started,
                <a href="{{ route('index') }}">return to login</a>.
            </p>
        @else
            <p class="subtitle">
                We've sent a 6-digit verification code to your registered email address.
                Enter it below to continue. The code expires in 5 minutes.
            </p>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" id="otpForm" autocomplete="off">
            @csrf

            @if($sessionMissing)
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com"
                           value="{{ old('email') }}" required autocomplete="email"
                           @error('email') style="border-color: var(--danger); background:#fef2f2;" @enderror>
                    @error('email')
                        <span class="error-message" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            @endif

            <span class="otp-label">One-time code</span>
            <div class="otp-boxes @error('otp') error @enderror" id="otpBoxes" role="group" aria-label="6-digit verification code">
                <input type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="1" aria-label="Digit 1" autofocus>
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 2">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 3">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 4">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 5">
                <input type="text" inputmode="numeric" maxlength="1" aria-label="Digit 6">
            </div>

            <!-- Assembled value actually submitted & validated server-side -->
            <input type="hidden" name="otp" id="otpValue" value="{{ old('otp') }}">

            @error('otp')
                <span class="error-message" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    {{ $message }}
                </span>
            @enderror

            <button type="submit" class="btn-verify" id="verifyBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span id="btnText">Verify &amp; continue</span>
            </button>
        </form>

        <div class="footer-links">
            Didn't receive the code?
            <button type="button" class="resend-btn" id="resendBtn"
                    data-href="{{ route('otp.resend') }}">Resend code</button>
            <span class="divider">·</span>
            <a href="{{ route('index') }}">Back to login</a>
        </div>
    </div>

    <script>
    (function () {
        const boxes    = Array.from(document.querySelectorAll('#otpBoxes input'));
        const hidden   = document.getElementById('otpValue');
        const boxesWrap = document.getElementById('otpBoxes');
        const form     = document.getElementById('otpForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const btnText  = document.getElementById('btnText');

        // Keep the hidden field (the one that is validated) in sync with the boxes.
        function sync() {
            const code = boxes.map(b => b.value).join('');
            hidden.value = code;
            boxes.forEach(b => b.classList.toggle('filled', b.value !== ''));
            return code;
        }

        function focusBox(i) {
            if (i >= 0 && i < boxes.length) boxes[i].focus();
        }

        // Distribute a multi-character string (paste or autofill) across the boxes.
        function distribute(str, startIndex) {
            const digits = str.replace(/\D/g, '').slice(0, boxes.length - startIndex).split('');
            digits.forEach((d, k) => { boxes[startIndex + k].value = d; });
            const next = Math.min(startIndex + digits.length, boxes.length - 1);
            focusBox(next);
            const code = sync();
            if (code.length === boxes.length) maybeSubmit();
        }

        boxes.forEach((box, i) => {
            box.addEventListener('input', function () {
                // Handles both single keystrokes and one-tap SMS/email autofill,
                // which can drop all 6 digits into the first box at once.
                if (this.value.length > 1) {
                    distribute(this.value, i);
                    return;
                }
                this.value = this.value.replace(/\D/g, '');
                if (this.value) focusBox(i + 1);
                const code = sync();
                if (code.length === boxes.length) maybeSubmit();
            });

            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && i > 0) {
                    focusBox(i - 1);
                    boxes[i - 1].value = '';
                    sync();
                } else if (e.key === 'ArrowLeft' && i > 0) {
                    e.preventDefault(); focusBox(i - 1);
                } else if (e.key === 'ArrowRight' && i < boxes.length - 1) {
                    e.preventDefault(); focusBox(i + 1);
                }
            });

            box.addEventListener('paste', function (e) {
                e.preventDefault();
                distribute((e.clipboardData || window.clipboardData).getData('text'), i);
            });

            box.addEventListener('focus', function () { this.select(); });
        });

        // Pre-fill boxes from a bounced-back value WITHOUT auto-submitting —
        // otherwise a rejected code would silently resubmit itself in a loop.
        (function prefill() {
            const digits = (hidden.value || '').replace(/\D/g, '').slice(0, boxes.length).split('');
            digits.forEach((d, k) => { boxes[k].value = d; });
            sync();
        })();

        let submitting = false;
        function maybeSubmit() {
            if (submitting) return;
            submitting = true;
            verifyBtn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Verifying…';
            // Small delay so the last digit visibly lands before navigation.
            setTimeout(() => form.submit(), 150);
        }

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

        // ── Resend cooldown (persists across the resend page navigation) ──
        const resendBtn = document.getElementById('resendBtn');
        const COOLDOWN = 45; // seconds
        const KEY = 'otpResendUntil';

        function startCooldown(until) {
            const tick = () => {
                const left = Math.ceil((until - Date.now()) / 1000);
                if (left <= 0) {
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend code';
                    localStorage.removeItem(KEY);
                    clearInterval(timer);
                    return;
                }
                resendBtn.disabled = true;
                resendBtn.textContent = `Resend code in ${left}s`;
            };
            tick();
            const timer = setInterval(tick, 1000);
        }

        const savedUntil = parseInt(localStorage.getItem(KEY) || '0', 10);
        if (savedUntil && Date.now() < savedUntil) startCooldown(savedUntil);

        resendBtn.addEventListener('click', function () {
            const until = Date.now() + COOLDOWN * 1000;
            localStorage.setItem(KEY, String(until));
            startCooldown(until);
            window.location.href = this.dataset.href;
        });
    })();
    </script>
</body>
</html>
