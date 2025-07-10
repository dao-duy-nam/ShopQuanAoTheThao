<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\CartController;
use App\Http\Controllers\API\Client\ReviewController;
use App\Http\Controllers\Api\Payment\VnpayController;
use App\Http\Controllers\Api\Client\ProductController;
use App\Http\Controllers\Api\Client\ClientOrderController;
use App\Http\Controllers\Api\Client\DiscountCodeController;
use App\Http\Controllers\Api\Client\ClientAccountController;
use App\Http\Controllers\Api\Client\ForgotPasswordController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('resend-otp', [ForgotPasswordController::class, 'resendOtp']);
    Route::post('verify-otp-password', [ForgotPasswordController::class, 'verifyForgotOtp']);
    Route::post('reset-password',  [ForgotPasswordController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'user'])->group(function () {
    // Product reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);


    Route::get('/client/profile', [ClientAccountController::class, 'profile']);
    Route::post('/client/profile', [ClientAccountController::class, 'updateProfile']);
    Route::put('/client/change-password', [ClientAccountController::class, 'changePassword']);
    Route::post('/client/logout', [ClientAccountController::class, 'logout']);

    Route::post('/client/discount-code/check', [DiscountCodeController::class, 'check']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders', [ClientOrderController::class, 'storeOrder']);
});
Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/client/orders/from-cart', [ClientOrderController::class, 'storeFromCart']);

    // Client orders
    Route::post('/client/orders', [ClientOrderController::class, 'store']);
    // Route::post('/client/orders/from-cart', [ClientOrderController::class, 'storeFromCart']);

    Route::get('/client/orders/{id}', [ClientOrderController::class, 'show']);
    Route::get('/client/orders/', [ClientOrderController::class, 'index']);
    Route::post('order/huy-don/{id}', [ClientOrderController::class, 'huyDon']);
    Route::post('order/tra-hang/{id}', [ClientOrderController::class, 'traHang']);


    Route::prefix('payment/vnpay')->group(function () {
        Route::post('create', [VnpayController::class, 'createPayment'])->middleware('auth:sanctum');
        Route::get('return', [VnpayController::class, 'callback'])->name('payment.vnpay.callback');
        Route::match(['GET', 'POST'], 'ipn', [VnpayController::class, 'ipn'])->name('payment.vnpay.ipn');
    });
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::put('/update/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/remove/{id}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
        // Route::get('/checkout-info', [CartController::class, 'getCheckoutInfo']);
    });
});
