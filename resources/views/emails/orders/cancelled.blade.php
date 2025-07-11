@component('mail::message')
# Xin lỗi quý khách,

Chúng tôi rất tiếc phải thông báo rằng **đơn hàng #{{ $order->ma_don_hang }}** của bạn đã bị **hủy**.

👉 Lý do: **{{ $reason ?: 'Không xác định' }}**

@if($order->trang_thai_thanh_toan === 'da_thanh_toan')
Số tiền bạn đã thanh toán sẽ được xử lý hoàn tiền (nếu có).
@endif

Nếu có thắc mắc, bạn vui lòng liên hệ bộ phận hỗ trợ để được giải đáp sớm nhất.

Cảm ơn bạn đã tin tưởng và mua sắm tại {{ config('app.name') }}!

Trân trọng,<br>
{{ config('app.name') }}
@endcomponent
