<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo: Tài khoản được mở khóa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #2c3e50;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #e1e4e8;
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        h2 {
            color: #27ae60;
            margin-top: 0;
        }
        p {
            font-size: 16px;
            line-height: 1.7;
            margin: 10px 0;
        }
        blockquote {
            background-color: #e8f8f5;
            padding: 15px 20px;
            border-left: 5px solid #27ae60;
            margin: 15px 0;
            font-style: italic;
            color: #1e8449;
            border-radius: 5px;
        }
        .footer {
            margin-top: 25px;
            font-size: 13px;
            color: #7f8c8d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xin chào {{ $user->name }},</h2>
        <p>Tài khoản của bạn đã được <strong style="color: #27ae60;">mở khóa</strong> và bạn có thể đăng nhập lại bình thường.</p>
        <p>Nếu bạn gặp vấn đề khi đăng nhập hoặc có thắc mắc, vui lòng liên hệ với <a href="mailto:support@yourapp.com" style="color: #3498db; text-decoration: none;">bộ phận hỗ trợ</a>.</p>
        <p>Chúc bạn một ngày tốt lành!<br>Đội ngũ quản trị</p>

        <div class="footer">
            Đây là email tự động. Vui lòng không trả lời trực tiếp email này.
        </div>
    </div>
</body>
</html>
