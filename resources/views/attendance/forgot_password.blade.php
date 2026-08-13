<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Forgot Password — Attendance Checker</title>
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

        .field { text-align: left; margin-bottom: 4px; }
        .field label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 7px; }
        .input-wrapper { position: relative; }
        .input-wrapper input {
            width: 100%; padding: 12px 42px 12px 14px;
            font-family: var(--font); font-size: 0.92rem;
            border: 1.5px solid var(--border-input); border-radius: var(--radius-sm);
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrapper input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-ring); }
        .input-icon { position: absolute; right: 13px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary); display: flex; pointer-events: none; }
        .input-icon svg { width: 17px; height: 17px; }

        .btn {
            width: 100%; margin-top: 18px; padding: 13px;
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
    <a href="{{ route('attendance.attendlog.form') }}" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Login
    </a>

    <div class="card">
        <img src="{{ asset('images/logo.png') }}" alt="MCC Logo" class="logo">
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2 19 4M2 22l6-6M12.5 6.5 17 11M8 12l1.5 1.5"/><circle cx="16.5" cy="7.5" r="4.5"/></svg>
            Password Recovery
        </div>
        <h1>Forgot password?</h1>
        <p class="subtitle">Enter the email linked to your attendance account and we'll send you a 6-digit verification code.</p>

        <form action="{{ route('attendance.forgot.send') }}" method="POST" id="forgotForm">
            @csrf
            <div class="field">
                <label for="email">Attendance checker email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder="checker@mcc.edu.ph"
                           value="{{ old('email') }}" required autofocus autocomplete="email">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn" id="sendBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                <span id="btnText">Send verification code</span>
            </button>
        </form>

        <div class="links">
            <a href="{{ route('attendance.attendlog.form') }}">← Back to login</a>
        </div>
    </div>

    <div class="page-footer">&copy; {{ date('Y') }} Madridejos Community College</div>

    <script>
    (function () {
        const form = document.getElementById('forgotForm');
        const btn = document.getElementById('sendBtn');
        const btnText = document.getElementById('btnText');

        form.addEventListener('submit', function () {
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Sending…';
        });

        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonColor: '#0284c7' });
        @endif
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Code Sent', text: @json(session('success')), confirmButtonColor: '#0284c7', timer: 2500, showConfirmButton: false });
        @endif
    })();
    </script>
</body>
</html>
