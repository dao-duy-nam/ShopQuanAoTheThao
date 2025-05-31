<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\AuthController;
use App\Http\Controllers\Api\Client\ForgotPasswordController;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('resend-otp', [ForgotPasswordController::class, 'resendOtp']);
    Route::post('verify-otp-password', [ForgotPasswordController::class, 'verifyForgotOtp']);
    Route::post('reset-password',  [ForgotPasswordController::class, 'resetPassword']);
});


Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Route::post('/dat-hang',        [OrderController::class,   'store']);
    // Route::post('/binh-luan',       [CommentController::class, 'store']);
    // Route::post('/yeu-thich',       [FavoriteController::class,'store']);


});
