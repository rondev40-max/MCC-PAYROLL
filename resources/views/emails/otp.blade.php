<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One-Time Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        
        <h2 style="color: #0056b3;">Hello,</h2>

        <p>You recently requested a One-Time Verification Code (OTP) to proceed with your login. Here is your code:</p>

        <div style="text-align: center; margin: 25px 0; padding: 15px; background-color: #f0f0f0; border-radius: 8px;">
            <h1 style="color: #d9534f; font-size: 36px; margin: 0;">{{ $otp }}</h1>
        </div>

        <p style="font-size: 14px; color: #777;">
            **Important:** This code will expire in 5 minutes. Please enter it immediately.
        </p>

        <p style="font-weight: bold; color: #d9534f;">Do not share this code with anyone.</p>

        <p>Thank you for using our system!</p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #aaa;">This is an automated email. Please do not reply.</p>
    </div>

</body>
</html>