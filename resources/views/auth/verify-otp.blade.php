<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f4f6f9;
        }
        .otp-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            text-align: center;
            font-size: 1.2em;
            letter-spacing: 5px; /* Para maganda tingnan ang 6-digit input */
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 5px;
            display: block;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="otp-container">
        <h2 style="margin-bottom: 25px;">Verify Your Login</h2>

        @if(session('message'))
            <p class="success-message">{{ session('message') }}</p>
        @endif

        @if(!empty($sessionMissing) && $sessionMissing)
            <p style="color: #6c757d;">
                Waiting for access — if you already received a code by email, enter the email you used to log in and paste the 6-digit code below. 
                If you haven't started the login flow, <a href="{{ route('index') }}">return to the login page</a> and sign in to request a code.
            </p>
        @else
            <p style="color: #6c757d;">
                Please enter the 6-digit verification code sent to your registered email address.
            </p>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}">
            @csrf
             
            @if(!empty($sessionMissing) && $sessionMissing)
                <div class="form-group">
                    <label for="email">Email address used to log in:</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="you@example.com"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        @error('email') style="border-color: #dc3545;" @enderror
                    >
 
                    @error('email')
                        <span class="error-message" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            @endif
 
            <div class="form-group">
                <label for="otp">One-Time Code (OTP):</label>
                <input 
                    id="otp" 
                    type="text" 
                    name="otp" 
                    placeholder="— — — — — —"
                    maxlength="6"
                    required 
                    autofocus
                    autocomplete="off"
                    @error('otp') style="border-color: #dc3545;" @enderror 
                >
 
                @error('otp')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
             
            <div class="form-group">
                <button type="submit">Verify Code and Login</button>
            </div>
             
            <p style="font-size: 0.9em; margin-top: 20px;">
                Didn't receive the code? 
                <a href="{{ route('otp.resend') }}">Resend Code</a> — or <a href="{{ route('index') }}">go back to login</a> to start again.
            </p>
        </form>
 
    </div>
 
+    @if(session('info'))
+        <script>
+            window.addEventListener('DOMContentLoaded', function () {
+                alert({!! json_encode(session('info')) !!});
+            });
+        </script>
+    @endif
+
</body>
</html>