<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your verification code</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 16px rgba(15,23,41,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2563eb,#1d4ed8); padding:28px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:20px; font-weight:800; letter-spacing:-0.02em;">MCC Payroll System</h1>
                            <p style="margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:13px;">Secure login verification</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 8px; color:#0f1729; font-size:16px; font-weight:600;">Hello,</p>
                            <p style="margin:0 0 24px; color:#5a6478; font-size:14px; line-height:1.6;">
                                Use the verification code below to finish signing in to your account.
                            </p>

                            <div style="text-align:center; margin:0 0 24px;">
                                <div style="display:inline-block; background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:18px 32px;">
                                    <span style="font-size:36px; font-weight:800; letter-spacing:10px; color:#1d4ed8; font-family:'Courier New', monospace;">{{ $otp }}</span>
                                </div>
                            </div>

                            <p style="margin:0 0 8px; color:#5a6478; font-size:13px; line-height:1.6; text-align:center;">
                                This code expires in <strong style="color:#0f1729;">5 minutes</strong>.
                            </p>

                            <div style="margin:24px 0 0; padding:14px 16px; background:#fef2f2; border-left:4px solid #dc2626; border-radius:8px;">
                                <p style="margin:0; color:#991b1b; font-size:13px; line-height:1.6;">
                                    <strong>Never share this code.</strong> MCC staff will never ask you for it. If you didn't try to sign in, you can safely ignore this email.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid #eef2f7; text-align:center;">
                            <p style="margin:0; color:#94a3b8; font-size:12px;">This is an automated message — please do not reply.</p>
                            <p style="margin:6px 0 0; color:#cbd5e1; font-size:12px;">&copy; {{ date('Y') }} Madridejos Community College</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
