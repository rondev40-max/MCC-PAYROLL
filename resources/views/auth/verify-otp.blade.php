<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Security Check</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .otp-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .otp-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Top accent border */
        .otp-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #007bff, #00d2ff);
        }

        .icon-container {
            width: 70px;
            height: 70px;
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .alert-success, .alert-info {
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-info {
            background: #e1f0ff;
            color: #0c5460;
            border: 1px solid #b8daff;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #4a4a4a;
        }
        input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
            background: #f8f9fa;
        }
        input[type="email"]:focus {
            border-color: #007bff;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 12px;
            transition: all 0.3s ease;
            outline: none;
            background: #f8f9fa;
            color: #007bff;
        }
        input[type="text"]:focus {
            border-color: #007bff;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        
        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        .btn-verify:active {
            transform: translateY(0);
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .footer-links {
            margin-top: 25px;
            font-size: 0.9rem;
            color: #6c757d;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .footer-links a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .footer-links a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="otp-wrapper">
        <div class="otp-container">
            <div class="icon-container">
                <i class="fas fa-shield-alt"></i>
            </div>
            
            <h2>Verify Your Login</h2>

            @if(session('message'))
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('message') }}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @if(!empty($sessionMissing) && $sessionMissing)
                <p class="subtitle">
                    Waiting for access. Enter your registered email and the 6-digit code we sent you. 
                    If you haven't started, <a href="{{ route('index') }}" style="color:#007bff; font-weight:600;">return to login</a>.
                </p>
            @else
                <p class="subtitle">
                    We've sent a 6-digit verification code to your registered email address. Please enter it below.
                </p>
            @endif

            <form method="POST" action="{{ route('otp.verify') }}">
                @csrf
                
                @if(!empty($sessionMissing) && $sessionMissing)
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            placeholder="you@example.com"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            @error('email') style="border-color: #dc3545; background: #fff8f8;" @enderror
                        >
                        @error('email')
                            <span class="error-message" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                @endif
    
                <div class="form-group">
                    <label for="otp">One-Time Code (OTP)</label>
                    <input 
                        id="otp" 
                        type="text" 
                        name="otp" 
                        placeholder="&bull;&bull;&bull;&bull;&bull;&bull;"
                        maxlength="6"
                        required 
                        autofocus
                        autocomplete="off"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        @error('otp') style="border-color: #dc3545; background: #fff8f8;" @enderror 
                    >
                    @error('otp')
                        <span class="error-message" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <button type="submit" class="btn-verify">
                    <i class="fas fa-check"></i> Verify &amp; Login
                </button>
                
                <div class="footer-links">
                    Didn't receive the code? <br>
                    <a href="{{ route('otp.resend') }}">Resend Code</a> or <a href="{{ route('index') }}">Go Back</a>
                </div>
            </form>
        </div>
    </div>
 
</body>
</html>