<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <input type="email" name="email" placeholder="Email" required>

    <input type="password" name="password" placeholder="Mật khẩu mới" required>

    <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required>

    <button type="submit">Đặt lại mật khẩu</button>
</form>
