<?php

namespace App\Http\Controllers\Api\Client;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|regex:/^\S+@[a-zA-Z]+[a-zA-Z0-9.-]*\.[a-z]{2,}$/',
            'password' => 'required|string|confirmed|min:6',
        ], [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã được đăng ký.',
            'email.regex' => 'Email không đúng định dạng hoặc domain không hợp lệ.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'vai_tro_id' => User::ROLE_USER
        ]);

        return response()->json([
            'message' => 'Đăng ký thành công.'
        ]);
    }





    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|regex:/^\S+@[a-zA-Z]+[a-zA-Z0-9.-]*\.[a-z]{2,}$/',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.regex' => 'Email không đúng định dạng hoặc domain không hợp lệ.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email hoặc mật khẩu không đúng.'],401);
        }

        
        if ($user->trang_thai === 'blocked') {
            if (is_null($user->block_den_ngay) || now()->lessThan($user->block_den_ngay)) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đang bị khóa.',
                    'ly_do_block' => $user->ly_do_block,
                    'block_den_ngay' => $user->block_den_ngay,
                ], 403);
            }


            $user->update([
                'trang_thai' => 'active',
                'block_den_ngay' => null,
                'ly_do_block' => null,
                'kieu_block' => null,
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Tài khoản chưa xác minh.',
                'status' => 'need_verification'
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'message' => 'Đăng nhập thành công.'
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng.'],404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Tài khoản đã xác minh.']);
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');

        $otpLockedUntil = $user->otp_locked_until_verify ? Carbon::parse($user->otp_locked_until_verify) : null;

        if (!$otpLockedUntil || $otpLockedUntil->toDateString() !== $now->toDateString()) {
            $user->otp_send_count = 0;
            $user->otp_locked_until_verify = $now;
            $user->save();
        }
        if ($user->otp_send_count >= 10) {
            return response()->json(['message' => 'Bạn đã vượt quá số lần gửi OTP trong ngày. Vui lòng thử lại vào ngày mai.']);
        }


        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expired_at = now()->addMinutes(2);
        $user->otp_send_count += 1;
        $user->save();

        Mail::to($user->email)->send(new SendOtpMail($user, $otp));

        return response()->json(['message' => 'Mã OTP đã được gửi đến email.']);
    }



    public function verifyOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'otp.required' => 'Mã OTP không được để trống.',
            'otp.digits' => 'Mã OTP phải gồm 6 chữ số.',
        ]);


        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại.'],404);
        }

        $permanentLockThreshold = now()->addYears(1);

        $lockedUntil = $user->otp_locked_until_verify ? Carbon::parse($user->otp_locked_until_verify) : null;

        if ($lockedUntil && $lockedUntil->greaterThan($permanentLockThreshold)) {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị khóa vĩnh viễn do nhập sai OTP quá nhiều lần. Vui lòng liên hệ nhân viên hỗ trợ để mở khóa.'
            ]);
        }

        if ($lockedUntil && now()->lt($lockedUntil)) {
            $diff = now()->diff($lockedUntil);
            $timeLeft = ($diff->i ? $diff->i . ' phút ' : '') . ($diff->s ? $diff->s . ' giây' : '');

            return response()->json([
                'message' => "Tài khoản bị khóa do nhập sai nhiều lần. Vui lòng thử lại sau $timeLeft."
            ],);
        }


        if ($lockedUntil && now()->gt($lockedUntil)) {
            $user->otp_locked_until_verify = null;
            $user->save();
        }

        $otpExpiredAt = $user->otp_expired_at ? Carbon::parse($user->otp_expired_at) : null;

        if ($user->otp !== $request->otp || !$otpExpiredAt || now()->gt($otpExpiredAt)) {
            $user->otp_attempts_verify += 1;

            if ($user->otp_attempts_verify == 5) {
                $user->otp_locked_until_verify = now()->addMinutes(3);
            } elseif ($user->otp_attempts_verify == 6) {
                $user->otp_locked_until_verify = now()->addMinutes(15);
            } elseif ($user->otp_attempts_verify == 7) {
                $user->otp_locked_until_verify = now()->addMinutes(30);
            } elseif ($user->otp_attempts_verify >= 8) {
                $user->otp_locked_until_verify = now()->addYears(100);
            }


            $user->save();

            $lockedUntil = $user->otp_locked_until_verify ? Carbon::parse($user->otp_locked_until_verify) : null;

            if ($lockedUntil && $lockedUntil->greaterThan($permanentLockThreshold)) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa vĩnh viễn do nhập sai OTP quá nhiều lần. Vui lòng liên hệ nhân viên hỗ trợ để mở khóa.'
                ]);
            }

            return response()->json(['message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.']);
        }

        $user->email_verified_at = now();
        $user->otp = null;
        $user->otp_expired_at = null;
        $user->otp_attempts_verify = 0;
        $user->otp_locked_until_verify = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Xác minh thành công.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
