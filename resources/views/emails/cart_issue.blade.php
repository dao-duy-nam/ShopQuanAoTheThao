<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo về giỏ hàng của bạn</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 0;
        }
        .email-container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        h2 {
            color: #333333;
        }
        p {
            font-size: 15px;
            color: #555555;
        }
        ul {
            padding-left: 20px;
            color: #c0392b;
        }
        li {
            margin-bottom: 8px;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #888888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h2>Xin chào quý khách,</h2>

        <p>Chúng tôi phát hiện có một số vấn đề với các sản phẩm trong giỏ hàng của bạn như sau:</p>

        <ul>
            @foreach($messages as $msg)
                <li>{{ $msg }}</li>
            @endforeach
        </ul>

        <p>Vui lòng truy cập giỏ hàng để kiểm tra và cập nhật lại trước khi tiến hành thanh toán.</p>

        <p>Xin lỗi vì sự bất tiện này và cảm ơn bạn đã đồng hành cùng chúng tôi!</p>

        <div class="footer">
            &copy; {{ date('Y') }} Cửa hàng của bạn. Mọi quyền được bảo lưu.
        </div>
    </div>
</body>
</html>
