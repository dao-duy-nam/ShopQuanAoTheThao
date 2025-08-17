<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rút tiền thành công</title>
</head>
<body>
    <h2>Xin chào {{ $user->name }},</h2>
    <p>Yêu cầu rút tiền của bạn đã được xử lý thành công.</p>
    <p><strong>Số tiền:</strong> {{ $amount }} VND</p>
    <p><strong>Thời gian:</strong> {{ $date }}</p>
    <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
</body>
</html>
