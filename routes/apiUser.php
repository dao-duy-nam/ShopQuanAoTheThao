<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\CartController;
use App\Http\Controllers\API\Client\ReviewController;
use App\Http\Controllers\Api\Payment\VnpayController;
use App\Http\Controllers\Api\Client\ProductController;
use App\Http\Controllers\Api\Client\ClientOrderController;
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
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/client/profile', [ClientAccountController::class, 'profile']);
    Route::put('/client/profile', [ClientAccountController::class, 'updateProfile']);
    Route::put('/client/change-password', [ClientAccountController::class, 'changePassword']);
    Route::post('/client/logout', [ClientAccountController::class, 'logout']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/client/orders', [ClientOrderController::class, 'store']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/client/orders/from-cart', [ClientOrderController::class, 'storeFromCart']);
    Route::get('/client/orders/{id}', [ClientOrderController::class, 'show']);
    Route::get('/client/orders/', [ClientOrderController::class, 'index']);
    Route::post('order/huy-don/{id}', [ClientOrderController::class, 'huyDon']);
Route::post('order/tra-hang/{id}', [ClientOrderController::class, 'traHang']);

});
// Route::post('/client/orders', [ClientOrderController::class, 'store']);

Route::prefix('payment/vnpay')->group(function () {
    Route::post('create', [VnpayController::class, 'createPayment'])->middleware('auth:sanctum');
    Route::get('return', [VnpayController::class, 'callback'])->name('payment.vnpay.callback');


    Route::match(['GET', 'POST'], 'ipn', [VnpayController::class, 'ipn'])->name('payment.vnpay.ipn');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::put('/update/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/remove/{id}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
        // Route::get('/checkout-info', [CartController::class, 'getCheckoutInfo']);
    });
});
