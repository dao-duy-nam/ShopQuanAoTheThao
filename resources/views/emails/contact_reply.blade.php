<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Phản hồi liên hệ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }

        h2 {
            color: #2c3e50;
            margin-top: 0;
        }

        blockquote {
            background-color: #f9f9f9;
            border-left: 5px solid #3498db;
            margin: 20px 0;
            padding: 10px 20px;
            font-style: italic;
            color: #555;
        }

        p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .footer {
            font-size: 13px;
            color: #888;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Phản hồi liên hệ</h2>

        <p>Xin chào <strong>{{ $contact->name }}</strong>,</p>

        <p>Chúng tôi đã nhận được liên hệ từ bạn với nội dung:</p>
        <blockquote>{{ $contact->message }}</blockquote>

        <p>Phản hồi từ chúng tôi:</p>
        <blockquote>{{ $replyContent }}</blockquote>

        <p>Trân trọng,<br>Đội ngũ hỗ trợ</p>

        <div class="footer">
            Email này được gửi từ hệ thống. Vui lòng không trả lời lại.
        </div>
    </div>
</body>
</html>
