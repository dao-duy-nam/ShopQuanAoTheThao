<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\CartController;
use App\Http\Controllers\Api\Client\AIChatController;
use App\Http\Controllers\Api\Client\BannerController;
use App\Http\Controllers\API\Client\ReviewController;
use App\Http\Controllers\Api\Client\WalletController;
use App\Http\Controllers\Api\Payment\VnpayController;
use App\Http\Controllers\Api\Client\PostApiController;
use App\Http\Controllers\Api\Client\ProductController;
use App\Http\Controllers\Api\Client\CategoryController;
use App\Http\Controllers\Api\Client\WishlistController;
use App\Http\Controllers\Api\Payment\ZaloPayController;
use App\Http\Controllers\Api\Client\ClientOrderController;
use App\Http\Controllers\Api\Client\ShippingApiController;
use App\Http\Controllers\Api\Client\DiscountCodeController;
use App\Http\Controllers\Api\Client\ClientAccountController;
use App\Http\Controllers\Api\Client\ClientContactController;
use App\Http\Controllers\Api\Client\ClientMessageController;
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
    Route::get('/filter', [ProductController::class, 'filter']);
    Route::get('/filter-attribute', [ProductController::class, 'filterByAttributeValues']); // Lọc giá trị thuộc tính 
    Route::get('{id}', [ProductController::class, 'show']);
    Route::get('/{id}/related', [ProductController::class, 'related']); // Lấy sản phẩm liên quan
});
Route::get('/filter-values', [ProductController::class, 'getFilterableValues']); // Lấy các giá trị thuộc tính
Route::get('banner', [BannerController::class, 'index']);
Route::get('categories', [CategoryController::class, 'index']);
Route::post('/chat', [AIChatController::class, 'generate']);
Route::get('/posts', [PostApiController::class, 'index']);
Route::get('/posts/{id}', [PostApiController::class, 'show']);
Route::get('/phi-ship', [ShippingApiController::class, 'getPhiShip']);
Route::middleware(['auth:sanctum', 'user'])->group(function () {
    // Product reviews
    Route::prefix('review')->group(function () {
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::put('/{id}', [ReviewController::class, 'update']);
    });

    Route::get('/products/{id}/review', [ReviewController::class, 'index']);



    Route::get('/client/profile', [ClientAccountController::class, 'profile']);
    Route::post('/client/profile', [ClientAccountController::class, 'updateProfile']);
    Route::put('/client/change-password', [ClientAccountController::class, 'changePassword']);
    Route::post('/client/logout', [ClientAccountController::class, 'logout']);
    Route::get('/client/overview', [ClientAccountController::class, 'getUserOverview']);

    // Wallet API
    Route::get('/wallet', [WalletController::class, 'getBalance']);
    // Route::get('/wallet/transactions', [WalletController::class, 'getTransactions']);
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/wallet/check-pending', [WalletController::class, 'checkPendingTransaction']);

    Route::post('/client/discount-code/check', [DiscountCodeController::class, 'check']);
    Route::get('/client/discount-codes', [DiscountCodeController::class, 'userDiscounts']);
});

Route::get('/wallet/vnpay/callback', [WalletController::class, 'vnpayWalletCallback']);
Route::get('/wallet/vnpay/ipn', [WalletController::class, 'vnpayWalletIpn']);
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
    Route::post('order/da-giao/{id}', [ClientOrderController::class, 'daGiao']);

    Route::get('/orders/check-pending-payment', [ClientOrderController::class, 'checkPendingPayment']);

    Route::prefix('/tin-nhans')->group(function () {
        Route::get('/', [ClientMessageController::class, 'getMessagesWithAdmin']);
        Route::post('/', [ClientMessageController::class, 'sendMessageToAdmin']);
    });

    Route::post('/wallet/vnpay/return', [\App\Http\Controllers\Api\Payment\VnpayController::class, 'walletVnpayReturn']);
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::put('/update/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/remove/{id}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
        // Route::get('/checkout-info', [CartController::class, 'getCheckoutInfo']);
    });
});
Route::prefix('payment/vnpay')->group(function () {
    Route::post('create', [VnpayController::class, 'createPayment'])->middleware('auth:sanctum');
    Route::get('return', [VnpayController::class, 'callback'])->name('payment.vnpay.callback');
    Route::match(['GET', 'POST'], 'ipn', [VnpayController::class, 'ipn'])->name('payment.vnpay.ipn');
});
Route::prefix('payment/zalopay')->group(function () {
    Route::post('create', [ZaloPayController::class, 'createPayment'])->middleware('auth:sanctum');
    Route::match(['GET', 'POST'], 'callback', [ZaloPayController::class, 'callback'])->name('payment.zalopay.callback');
});

Route::prefix('wishlists')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [WishlistController::class, 'index']);
    Route::post('/', [WishlistController::class, 'store']);
    Route::delete('/{id}', [WishlistController::class, 'destroy']);
});
Route::prefix('contact')->group(function () {
    Route::post('/', [ClientContactController::class, 'store']);
    Route::get('/types', [ClientContactController::class, 'contactTypes']);
});
