@component('mail::message')
# Xin chào {{ $user->name }},

Bạn vừa đăng nhập thành công vào hệ thống **Sportigo** vào lúc {{ $loginTime }}.

Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!

Nếu bạn không phải là người đăng nhập, vui lòng liên hệ ngay với bộ phận hỗ trợ.

Chúc bạn một ngày tốt lành!

<br>
Trân trọng,<br>
Đội ngũ Sportigo
@endcomponent
