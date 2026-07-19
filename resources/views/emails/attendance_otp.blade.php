<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        .header h1 {
            margin: 0;
            color: #333333;
        }
        .content {
            padding: 20px 0;
            text-align: center;
        }
        .content p {
            color: #555555;
            line-height: 1.6;
        }
        .otp-code {
            display: inline-block;
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            background-color: #eaf5fb;
            padding: 10px 20px;
            border-radius: 4px;
            letter-spacing: 2px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MCC Payroll System</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>You requested to reset your password for the Attendance Checker portal. Please use the following One-Time Password (OTP) to proceed.</p>
            <div class="otp-code">{{ $otp }}</div>
            <p>This OTP is valid for 10 minutes. If you did not request a password reset, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} MCC. All rights reserved.</p>
        </div>
    </div>
</body>
</html>