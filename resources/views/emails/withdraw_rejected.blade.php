<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rút tiền bị từ chối</title>
</head>
<body>
    <h2>Xin chào {{ $user->name }},</h2>
    <p>Rất tiếc, yêu cầu rút tiền của bạn đã bị từ chối.</p>
    <p><strong>Số tiền:</strong> {{ $amount }} VND</p>
    <p><strong>Lý do:</strong> {{ $reason }}</p>
    <p><strong>Thời gian:</strong> {{ $date }}</p>
    <p>Nếu có thắc mắc, vui lòng liên hệ bộ phận hỗ trợ.</p>
</body>
</html>

