<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thanh toán thành công</title>
</head>
<body>
    <h2>Xin chào {{ $order->user->name ?? 'bạn' }},</h2>

    <p>Chúng tôi xác nhận rằng bạn đã <strong>thanh toán thành công</strong> cho đơn hàng <strong>#{{ $order->ma_don_hang }}</strong>.</p>

    <p><strong>Số tiền:</strong> {{ number_format($order->so_tien_thanh_toan) }} VND</p>
    <p>Chúng tôi sẽ tiến hành xử lý đơn hàng của bạn sớm nhất.</p>

    <p>Cảm ơn bạn đã mua sắm tại {{ config('app.name') }}!</p>
</body>
</html>
