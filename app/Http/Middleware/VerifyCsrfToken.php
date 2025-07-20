<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Các URI bỏ qua CSRF verification
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/payment/zalopay/callback',
    ];
}
