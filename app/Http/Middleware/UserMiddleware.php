<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isRoleUser()) {
            return response()->json(['message' => 'Bạn không có quyền truy cập khu vực người dùng.'], 403);
        }
        if (Auth::user()->trang_thai === 'blocked') {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị quản trị viên khóa.',
                'ly_do_block' => Auth::user()->ly_do_block,
                'block_den_ngay' => Auth::user()->block_den_ngay,
            ], 403);
        }
        return $next($request);
    }
}
