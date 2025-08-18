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

        <p>Chúng tôi rất cảm ơn bạn đã dành thời gian liên hệ và chia sẻ ý kiến của mình với chúng tôi.</p>

        <p>Bạn đã gửi đến chúng tôi nội dung sau:</p>
        <blockquote>{{ $contact->message }}</blockquote>

        <p>Dưới đây là phản hồi từ đội ngũ hỗ trợ của chúng tôi:</p>
        <blockquote>{{ $replyContent }}</blockquote>

        <p>Chúng tôi luôn trân trọng mọi phản hồi từ bạn vì đó là cơ sở để chúng tôi cải thiện chất lượng dịch vụ ngày càng tốt hơn.</p>

        <p>Nếu bạn còn bất kỳ thắc mắc hoặc yêu cầu nào khác, xin đừng ngần ngại liên hệ lại. Chúng tôi luôn sẵn sàng hỗ trợ bạn trong thời gian sớm nhất.</p>

        <p>Trân trọng,<br><strong>Đội ngũ Hỗ trợ Khách hàng</strong></p>

        <div class="footer">
            Email này được gửi tự động từ hệ thống. Vui lòng không trả lời lại email này.
        </div>
    </div>
</body>
</html>
