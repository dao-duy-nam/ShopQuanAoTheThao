<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
            return response()->json(['message' => 'Email hoặc mật khẩu không đúng.']);
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
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng.']);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Tài khoản đã xác minh.']);
        }

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expired_at = now()->addMinutes(2);
        $user->otp_attempts = 0;
        $user->otp_locked_until = null;
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
            return response()->json(['message' => 'Người dùng không tồn tại.']);
        }

        $permanentLockThreshold = now()->addYears(1);

        $lockedUntil = $user->otp_locked_until ? Carbon::parse($user->otp_locked_until) : null;

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
            $user->otp_locked_until = null;
            $user->save();
        }

        $otpExpiredAt = $user->otp_expired_at ? Carbon::parse($user->otp_expired_at) : null;

        if ($user->otp !== $request->otp || !$otpExpiredAt || now()->gt($otpExpiredAt)) {
            $user->otp_attempts += 1;

            if ($user->otp_attempts == 5) {
                $user->otp_locked_until = now()->addMinutes(3);
            } elseif ($user->otp_attempts == 6) {
                $user->otp_locked_until = now()->addMinutes(15);
            } elseif ($user->otp_attempts >= 7) {
                $user->otp_locked_until = now()->addYears(100);
            }

            $user->save();

            $lockedUntil = $user->otp_locked_until ? Carbon::parse($user->otp_locked_until) : null;

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
        $user->otp_attempts = 0;
        $user->otp_locked_until = null;
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
