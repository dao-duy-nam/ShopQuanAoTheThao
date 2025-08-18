@component('mail::message')
# ✅ Thanh toán bằng vi thành công!

Đơn hàng **{{ $order->ma_don_hang }}** của bạn đã được thanh toán thành công.  
Chúng tôi sẽ xử lý và giao hàng sớm nhất.

@component('mail::panel')
Số tiền đã thanh toán: **{{ number_format($order->so_tien_thanh_toan) }} VNĐ**
@endcomponent

@component('mail::button', ['url' => url('/orders/' . $order->id), 'color' => 'success'])
Theo dõi đơn hàng
@endcomponent

Cảm ơn bạn,  
{{ config('app.name') }}
@endcomponent
