<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; // Import User model để IDE nhận diện
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Notifications\VerifyEmail;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email đã được xác minh trước đó.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Gửi notification VerifyEmail (Laravel tự build link signed)
        $user->notify(new VerifyEmail);

        return response()->json([
            'message' => 'Đã gửi lại email xác nhận. Vui lòng kiểm tra hộp thư.'
        ], Response::HTTP_OK);
    }

    /**
     * Handle email verification link click.
     */
    public function verify(Request $request, $id, $hash)
    {
        /** @var User $user */
        $user = Auth::user();

        // Kiểm tra user đăng nhập trùng với id trong URL
        if (!$user || $user->getKey() != $id) {
            return response()->json([
                'message' => 'User không hợp lệ.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Kiểm tra hash email
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Hash không hợp lệ.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Kiểm tra đã verify chưa
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email đã được xác minh.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Đánh dấu verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'message' => 'Xác nhận email thành công!'
        ], Response::HTTP_OK);
    }
}