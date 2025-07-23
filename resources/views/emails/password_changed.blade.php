<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu thành công</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        p {
            font-size: 14px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xin chào {{ $user->name }},</h2>
        <p>Mật khẩu của bạn đã được thay đổi thành công.</p>
        <p>Nếu bạn không thực hiện yêu cầu này, vui lòng liên hệ ngay với chúng tôi.</p>
        <p class="footer">-- Hệ thống hỗ trợ</p>
    </div>
</body>
</html>
