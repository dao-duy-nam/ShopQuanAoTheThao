<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Đặt lại mật khẩu</title>
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
        a.button {
            display: inline-block;
            padding: 12px 25px;
            margin: 20px 0;
            font-weight: 600;
            color: white !important;
            background-color: #007BFF;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        a.button:hover {
            background-color: #0056b3;
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
        <h1>Đặt lại mật khẩu</h1>
        <p>Chào bạn,</p>
        <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
        <p>Vui lòng nhấn vào nút dưới đây để đặt lại mật khẩu:</p>
        <p><a href="{{ $resetLink }}" class="button" target="_blank" rel="noopener noreferrer">Đặt lại mật khẩu</a></p>
        <p>Nếu bạn không yêu cầu, bạn có thể bỏ qua email này một cách an toàn.</p>
        <div class="footer">
            <p>Trân trọng,</p>
            <p><strong>{{ config('app.name') }}</strong></p>
        </div>
    </div>
</body>
</html>
