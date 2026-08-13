<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password reset code</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 16px rgba(15,23,41,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0284c7,#0369a1); padding:28px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:20px; font-weight:800; letter-spacing:-0.02em;">MCC Payroll System</h1>
                            <p style="margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:13px;">Attendance Checker — Password Reset</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 8px; color:#0f1729; font-size:16px; font-weight:600;">Hello{{ $userName ? ' ' . $userName : '' }},</p>
                            <p style="margin:0 0 24px; color:#5a6478; font-size:14px; line-height:1.6;">
                                You requested to reset your password for the Attendance Checker portal. Enter the code below to continue.
                            </p>

                            <div style="text-align:center; margin:0 0 24px;">
                                <div style="display:inline-block; background:#ecfeff; border:1px solid #a5f3fc; border-radius:12px; padding:18px 32px;">
                                    <span style="font-size:36px; font-weight:800; letter-spacing:10px; color:#0369a1; font-family:'Courier New', monospace;">{{ $otp }}</span>
                                </div>
                            </div>

                            <p style="margin:0 0 8px; color:#5a6478; font-size:13px; line-height:1.6; text-align:center;">
                                This code expires in <strong style="color:#0f1729;">10 minutes</strong>.
                            </p>

                            <div style="margin:24px 0 0; padding:14px 16px; background:#fef2f2; border-left:4px solid #dc2626; border-radius:8px;">
                                <p style="margin:0; color:#991b1b; font-size:13px; line-height:1.6;">
                                    <strong>Never share this code.</strong> If you didn't request a password reset, you can safely ignore this email — your password will stay the same.
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
