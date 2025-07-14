@component('mail::message')
# Thiếu hàng trong đơn hàng

Xin chào {{ $order->user->name ?? 'quý khách' }},

Cảm ơn bạn đã thanh toán đơn hàng **#{{ $order->ma_don_hang }}**.

Tuy nhiên, chúng tôi xin thông báo rằng đơn hàng này đang **thiếu một số sản phẩm** do hết hàng. Bộ phận hỗ trợ sẽ kiểm tra và xử lý đơn hàng sớm nhất có thể.

Chúng tôi xin lỗi vì sự bất tiện này.

Trân trọng,  
**{{ config('app.name') }}**
@endcomponent
