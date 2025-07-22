@component('mail::message')
# Đơn hàng bị hủy

Xin chào {{ $order->user->name ?? 'quý khách' }},

Chúng tôi xin thông báo rằng đơn hàng **#{{ $order->ma_don_hang }}** của bạn đã bị **huỷ**.

👉 **Lý do**: {{ $reason ?: 'Không xác định' }}

@if($order->trang_thai_thanh_toan === 'da_thanh_toan')
💵 Đơn hàng đã được thanh toán. Chúng tôi sẽ hoàn tiền trong thời gian sớm nhất.
@endif

Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ chúng tôi để được hỗ trợ.

Trân trọng,  
**{{ config('app.name') }}**
@endcomponent
