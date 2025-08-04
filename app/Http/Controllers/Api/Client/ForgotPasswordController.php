<?php

namespace App\Http\Controllers\Api\Client;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;



class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.regex' => 'Email không đúng định dạng hoặc domain không hợp lệ.'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại trong hệ thống.'], 404);
        }

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expired_at = now()->addMinutes(2);
        $user->otp_send_count += 1;
        $user->save();

        Mail::to($user->email)->queue(new ResetPasswordMail($user, $otp));

        return response()->json(['message' => 'Mã OTP đã được gửi đến email.']);
    }


    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng.'], 404);
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');

        $otpLockedUntil = $user->otp_locked_until_forgot ? Carbon::parse($user->otp_locked_until_forgot) : null;

        if (!$otpLockedUntil || $otpLockedUntil->toDateString() !== $now->toDateString()) {
            $user->otp_attempts_forgot = 0;
            $user->otp_locked_until_forgot = $now;
            $user->save();
        }
        if ($user->otp_send_count >= 10) {
            return response()->json(['message' => 'Bạn đã vượt quá số lần gửi OTP trong ngày. Vui lòng thử lại vào ngày mai.'],429);
        }


        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expired_at = $now->copy()->addMinutes(2);
        $user->otp_send_count += 1;
        $user->save();

        Mail::to($user->email)->queue(new ResetPasswordMail($user, $otp));

        return response()->json(['message' => 'Mã OTP mới đã được gửi đến email.']);
    }

    public function verifyForgotOtp(Request $request)
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
            return response()->json(['message' => 'Không tìm thấy người dùng.'], 404);
        }

        $permanentLockThreshold = now()->addYears(1);
        $lockedUntil = $user->otp_locked_until_forgot ? Carbon::parse($user->otp_locked_until_forgot) : null;


        if ($lockedUntil && $lockedUntil->greaterThan($permanentLockThreshold)) {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị khóa vĩnh viễn do nhập sai OTP quá nhiều lần. Vui lòng liên hệ nhân viên hỗ trợ để mở khóa.'
            ],403);
        }


        if ($lockedUntil && now()->lt($lockedUntil)) {
            $diff = now()->diff($lockedUntil);
            $timeLeft = ($diff->i ? $diff->i . ' phút ' : '') . ($diff->s ? $diff->s . ' giây' : '');

            return response()->json([
                'message' => "Tài khoản bị khóa do nhập sai nhiều lần. Vui lòng thử lại sau $timeLeft."
            ],429);
        }


        if ($lockedUntil && now()->gt($lockedUntil)) {
            $user->otp_locked_until_forgot = null;
            $user->save();
        }

        $otpExpiredAt = $user->otp_expired_at ? Carbon::parse($user->otp_expired_at) : null;


        if ($user->otp !== $request->otp || !$otpExpiredAt || now()->gt($otpExpiredAt)) {
            $user->otp_attempts_forgot += 1;


            if ($user->otp_attempts_forgot == 5) {
                $user->otp_locked_until_forgot = now()->addMinutes(3);
            } elseif ($user->otp_attempts_forgot == 6) {
                $user->otp_locked_until_forgot = now()->addMinutes(15);
            } elseif ($user->otp_attempts_forgot == 7) {
                $user->otp_locked_until_forgot = now()->addMinutes(30);
            } elseif ($user->otp_attempts_forgot >= 8) {
                $user->otp_locked_until_forgot = now()->addYears(100);
            }


            $user->save();

            $lockedUntil = $user->otp_locked_until_forgot ? Carbon::parse($user->otp_locked_until_forgot) : null;

            if ($lockedUntil && $lockedUntil->greaterThan($permanentLockThreshold)) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa vĩnh viễn do nhập sai OTP quá nhiều lần. Vui lòng liên hệ nhân viên hỗ trợ để mở khóa.'
                ],403);
            }

            return response()->json(['message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.'],400);
        }


        $user->otp = null;
        $user->otp_expired_at = null;
        $user->otp_attempts_forgot = 0;
        $user->otp_locked_until_forgot = null;
        $user->otp_verified_for_reset = true;
        $user->save();

        return response()->json(['message' => 'Xác minh OTP thành công.']);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|string|min:6|confirmed'
        ], [
            'new_password.required' => 'Mật khẩu mới không được để trống.',
            'new_password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->otp_verified_for_reset) {
            return response()->json(['message' => 'Bạn chưa xác minh OTP hoặc email không hợp lệ.'], 403);
        }

        $user->password = bcrypt($request->new_password);
        $user->otp = null;
        $user->otp_expired_at = null;
        $user->otp_verified_for_reset = false;
        $user->save();

        return response()->json(['message' => 'Đổi mật khẩu thành công.']);
    }
}
