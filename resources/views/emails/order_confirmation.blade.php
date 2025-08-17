@component('mail::message')
# 🎉 Cảm ơn bạn đã đặt hàng!

Xin chào **{{ $order->ten_nguoi_dat }}**,  
Đơn hàng **{{ $order->ma_don_hang }}** của bạn đã được tạo thành công.

---

**Tổng tiền cần thanh toán:**  
@component('mail::panel')
💰 {{ number_format($order->so_tien_thanh_toan) }} VNĐ
@endcomponent

---

@component('mail::button', ['url' => url('/orders/' . $order->id), 'color' => 'success'])
🔎 Xem chi tiết đơn hàng
@endcomponent

Cảm ơn bạn đã tin tưởng,  
**{{ config('app.name') }}** 🛍️
@endcomponent
