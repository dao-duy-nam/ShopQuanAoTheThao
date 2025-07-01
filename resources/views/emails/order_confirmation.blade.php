@component('mail::message')
# Cảm ơn bạn đã đặt hàng!

Đơn hàng của bạn: **{{ $order->ma_don_hang }}**

Tổng tiền: **{{ number_format($order->so_tien_thanh_toan) }} VNĐ**

Trạng thái: **{{ $order->trang_thai_don_hang }}**

---

@component('mail::button', ['url' => 'https://your-website.com/orders/' . $order->id])
Xem chi tiết đơn hàng
@endcomponent

Cảm ơn bạn,<br>
{{ config('app.name') }}
@endcomponent
