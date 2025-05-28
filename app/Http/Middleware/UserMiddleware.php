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

        return $next($request);
    }
}
