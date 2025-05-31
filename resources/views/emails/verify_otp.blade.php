<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Xác minh OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #007BFF;
            font-weight: 700;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
        }
        .otp-box {
            background-color: #007BFF;
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 6px;
            padding: 15px 30px;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
            user-select: all;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Xin chào {{ $name }},</h1>
        <p>Bạn vừa yêu cầu xác minh tài khoản của mình bằng mã OTP.</p>
        <p>Mã OTP của bạn là:</p>
        <div class="otp-box">{{ $otp }}</div>
        <p>Mã OTP có hiệu lực trong 2 phút. Vui lòng không chia sẻ mã này với bất kỳ ai để bảo vệ tài khoản của bạn.</p>
        <div class="footer">
            <p>Nếu bạn không thực hiện yêu cầu này, bạn có thể bỏ qua email này một cách an toàn.</p>
            <p><strong>{{ config('app.name') }}</strong></p>
        </div>
    </div>
</body>
</html>
