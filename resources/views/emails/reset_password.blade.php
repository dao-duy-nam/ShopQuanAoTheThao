<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Xác thực OTP</title>
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
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #e53935;
            text-align: center;
            padding: 15px;
            border: 2px dashed #e53935;
            margin: 20px 0;
            border-radius: 8px;
            background-color: #fff3f3;
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
        <h1>Xác thực OTP</h1>
        <p>Xin chào {{ $name }},</p>
        <p>Bạn đã yêu cầu đặt lại mật khẩu. Vui lòng sử dụng mã OTP bên dưới để xác nhận:</p>

        <div class="otp-code">{{ $otp }}</div>

        <p>Mã OTP chỉ có hiệu lực trong vòng <strong>2 phút</strong>. Vui lòng không chia sẻ mã này với bất kỳ ai.</p>

        <p>Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này.</p>

        <div class="footer">
            <p>Trân trọng,</p>
            <p><strong>{{ config('app.name') }}</strong></p>
        </div>
    </div>
</body>
</html>
